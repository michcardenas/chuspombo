<?php

namespace App\Http\Controllers;

use App\Services\SmoobuClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(private SmoobuClient $smoobu) {}

 public function availability(Request $request, SmoobuClient $smoobu)
    {
        $v = $request->validate([
            'apartment_id' => 'required|integer',
            'from'         => 'nullable|date',
            'to'           => 'nullable|date',
            'include_dates'=> 'nullable|boolean', // opcional: devolver días sueltos
        ]);

        $aptId = (int) $v['apartment_id'];

        // Rango por defecto: hoy .. +1 año
        $from = isset($v['from']) ? Carbon::parse($v['from'])->startOfDay() : now()->startOfDay();
        $to   = isset($v['to'])   ? Carbon::parse($v['to'])->endOfDay()     : now()->addYear()->endOfDay();
        if ($to->lt($from)) { [$from,$to] = [$to,$from]; } // por si vienen invertidas

        // Trae SOLO las reservas del apartamento dentro del rango de llegada
        $bookings = $smoobu->bookingsForApartment(
            $aptId,
            $from->toDateString(),
            $to->toDateString(),
            100
        );

        // Convierte cada reserva a rango de NOCHES bloqueadas [arrival .. (departure-1)]
        $ranges = [];
        foreach ($bookings as $b) {
            $arrival   = $b['arrival']   ?? $b['check-in'] ?? $b['from'] ?? null;
            $departure = $b['departure'] ?? $b['check-out'] ?? $b['to']  ?? null;
            if (!$arrival || !$departure) continue;

            $a = Carbon::parse($arrival)->toDateString();
            $d = Carbon::parse($departure)->subDay()->toDateString(); // checkout no se bloquea

            if ($a > $d) continue;

            // Recorta al rango solicitado (y además no bloquees más allá del "to" solicitado)
            $clampedFrom = max($a, $from->toDateString());
            $clampedTo   = min($d, $to->copy()->subDay()->toDateString());

            if ($clampedFrom <= $clampedTo) {
                $ranges[] = [
                    'from' => $clampedFrom,
                    'to'   => $clampedTo,
                    // meta opcional por si quieres auditar
                    // 'reservation_id' => $b['id'] ?? null,
                    // 'blocked'        => $b['is-blocked-booking'] ?? false,
                ];
            }
        }

        // Ordena y FUSIONA rangos solapados o adyacentes
        usort($ranges, fn($x,$y) => strcmp($x['from'], $y['from']));
        $merged = [];
        foreach ($ranges as $r) {
            if (!$merged) { $merged[] = $r; continue; }
            $last =& $merged[count($merged)-1];

            // ¿solapa o es adyacente? (adyacente: last.to + 1 día >= r.from)
            $lastToPlus1 = Carbon::parse($last['to'])->addDay()->toDateString();
            if ($r['from'] <= $lastToPlus1) {
                if ($r['to'] > $last['to']) $last['to'] = $r['to'];
            } else {
                $merged[] = $r;
            }
        }

        // Opcional: devolver también días sueltos si ?include_dates=1
        $dates = [];
        if ($request->boolean('include_dates')) {
            foreach ($merged as $r) {
                $d = Carbon::parse($r['from']);
                $end = Carbon::parse($r['to']);
                while ($d->lte($end)) {
                    $dates[] = $d->toDateString();
                    $d->addDay();
                }
            }
        }

        Log::debug('availability', [
            'apt' => $aptId,
            'from'=> $from->toDateString(),
            'to'  => $to->toDateString(),
            'bookings_count' => count($bookings),
            'ranges_out'     => count($merged),
        ]);

        return response()->json([
            'ok'           => true,
            'apartment_id' => $aptId,
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
            'count'        => count($merged),
            'ranges'       => $merged,
            'dates'        => $dates ?: null, // solo si se pidió
        ]);
    }

    /** Cotización con Smoobu (EUR) */
    public function quote(Request $request)
    {
        $data = $request->validate([
            'apartment_id' => 'required|integer',
            'checkin'      => 'required|date|after_or_equal:today',
            'checkout'     => 'required|date|after:checkin',
            'guests'       => 'required|integer|min:1|max:50',
        ]);
        $ci = Carbon::parse($data['checkin'])->toDateString();
        $co = Carbon::parse($data['checkout'])->toDateString();

        [$nights, $subtotal, $breakdown] = $this->computeWithSmoobu((int)$data['apartment_id'], $ci, $co);

        return response()->json([
            'ok'        => true,
            'currency'  => 'EUR',
            'nights'    => $nights,
            'subtotal'  => round($subtotal, 2),
            'tax'       => 0,
            'fees'      => 0,
            'total'     => max(round($subtotal, 2), 1),
            'breakdown' => $breakdown,
        ]);
    }

    /** Inicia checkout y guarda resumen en sesión */
    public function start(Request $request)
    {
        $data = $request->validate([
            'apartment_id'    => 'required|integer',
            'apartment_title' => 'nullable|string|max:255',
            'checkin'         => 'required|date|after_or_equal:today',
            'checkout'        => 'required|date|after:checkin',
            'guests'          => 'required|integer|min:1|max:50',
        ]);
        $ci = Carbon::parse($data['checkin'])->toDateString();
        $co = Carbon::parse($data['checkout'])->toDateString();

        [$nights, $subtotal, $breakdown] = $this->computeWithSmoobu((int)$data['apartment_id'], $ci, $co);
        $tax = 0; $fees = 0; $total = max(round($subtotal + $tax + $fees, 2), 1);

        $checkout = [
            'apartment_id'    => (int)$data['apartment_id'],
            'apartment_title' => $data['apartment_title'] ?? 'Apartamento',
            'checkin'         => $ci,
            'checkout'        => $co,
            'nights'          => $nights,
            'guests'          => (int)$data['guests'],
            'currency'        => 'EUR',
            'breakdown'       => $breakdown,
            'subtotal'        => round($subtotal, 2),
            'tax'             => $tax,
            'fees'            => $fees,
            'total'           => $total,
        ];
        Session::put('checkout', $checkout);
        return redirect()->route('checkout.summary');
    }

    public function summary()
    {
        $checkout = Session::get('checkout');
        if (!$checkout) {
            return redirect()->back()->withErrors(['checkout' => 'No hay datos de checkout.']);
        }
        return view('checkout.summary', compact('checkout'));
    }

    /** Helper: suma tarifas por día desde /rates */
    private function computeWithSmoobu(int $aptId, string $start, string $end): array
    {
        $nights = Carbon::parse($start)->diffInDays(Carbon::parse($end));
        if ($nights <= 0) return [0, 0.0, []];

        $resp = $this->smoobu->rates($start, $end, [$aptId]);

        // Extrae por día de forma robusta (ajusta si tu payload difiere)
        $candidates = $resp['rates'] ?? $resp['data'] ?? $resp['items'] ?? $resp ?? [];
        $byDate = [];
        $push = function($date, $price) use (&$byDate) {
            if ($date === null) return;
            $d = substr($date, 0, 10);
            $byDate[$d] = (float)$price;
        };
        $walk = function($node) use (&$walk, $aptId, $push) {
            if (is_array($node)) {
                if (isset($node['date']) && (isset($node['price']) || isset($node['amount']) || isset($node['rate']))) {
                    $push($node['date'], $node['price'] ?? $node['amount'] ?? $node['rate']);
                }
                if (isset($node['apartmentId']) && (int)$node['apartmentId'] !== (int)$aptId) return;
                foreach ($node as $k => $v) {
                    if (in_array($k, ['days','rates','dates','items','data','calendar','prices','list'])) $walk($v);
                    elseif (is_array($v)) $walk($v);
                }
            }
        };
        $walk($candidates);

        $cursor = Carbon::parse($start);
        $subtotal = 0.0; $breakdown = [];
        for ($i=0; $i<$nights; $i++) {
            $d = $cursor->copy()->addDays($i)->toDateString();
            $p = $byDate[$d] ?? 0.0;
            $breakdown[$d] = (float)$p;
            $subtotal += (float)$p;
        }
        return [$nights, $subtotal, $breakdown];
    }
}
