<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyPayment;
use App\Services\SmoobuClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    public function __construct(private SmoobuClient $smoobu) {}

    public function index(Request $request)
    {
        $query = PropertyPayment::query();

        // Filtro de búsqueda por huésped
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtro por propiedad
        if ($request->filled('apartment_id')) {
            $query->where('apartment_id', $request->get('apartment_id'));
        }

        // Filtros de fecha
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $pagos = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Obtener nombres de propiedades desde Smoobu
        $apartments = $this->getApartmentsWithNames();

        // Agregar nombres de apartamentos a los pagos
        foreach ($pagos as $pago) {
            $apartmentName = collect($apartments)->firstWhere('id', $pago->apartment_id)['name'] ?? null;
            $pago->apartment_name = $apartmentName;
        }

        return view('admin.pagos.index', compact('pagos', 'apartments'));
    }

    public function show(int $id)
    {
        $pago = PropertyPayment::findOrFail($id);

        // Obtener nombre del apartamento desde Smoobu
        $apartments = $this->getApartmentsWithNames();
        $apartmentName = collect($apartments)->firstWhere('id', $pago->apartment_id)['name'] ?? null;
        $pago->apartment_name = $apartmentName;

        return view('admin.pagos.show', compact('pago'));
    }

    public function destroy(int $id)
    {
        $pago = PropertyPayment::findOrFail($id);

        Log::info('Eliminando pago PayPal desde admin', [
            'pago_id' => $pago->id,
            'paypal_order_id' => $pago->payment_id,
            'guest_name' => $pago->guest_name,
            'amount' => $pago->amount,
            'reservation_id' => $pago->reservation_id,
            'admin_user' => auth()->user()->email ?? 'unknown'
        ]);

        $pago->delete();

        return redirect()->route('admin.pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
    }

    /**
     * Obtener lista de apartamentos con nombres desde Smoobu API (con caché)
     */
    private function getApartmentsWithNames(): array
    {
        return Cache::remember('smoobu_apartments', 300, function () {
            try {
                $apartments = $this->smoobu->apartments();
                return array_map(function ($apt) {
                    return [
                        'id' => $apt['id'],
                        'name' => $apt['name'] ?? "Apartamento #{$apt['id']}"
                    ];
                }, $apartments);
            } catch (\Throwable $e) {
                Log::warning('Error obteniendo apartamentos de Smoobu para admin', [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }
}
