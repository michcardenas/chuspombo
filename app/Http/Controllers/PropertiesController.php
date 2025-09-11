<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\SmoobuClient;
use App\Models\SmoobuApartmentMeta;
use App\Models\SmoobuApartmentImage;
use Carbon\Carbon;

class PropertiesController extends Controller
{
    /** @var \App\Services\SmoobuClient */
    protected SmoobuClient $smoobu;

    public function __construct(SmoobuClient $smoobu)
    {
        $this->smoobu = $smoobu;
    }

    /**
 * Lista todas las propiedades con filtros opcionales (Smoobu).
 */
public function index(Request $request)
{
    try {
        // Página + SEO (igual que antes)
        $pagina = \App\Models\Pagina::with('meta')->find(2);
        if (!$pagina) {
            $paginapropiedades = new \App\Models\Pagina();
            $seo = new \App\Models\PaginaMeta();
        } else {
            $paginapropiedades = $pagina;
            $seo = $pagina->meta ?? new \App\Models\PaginaMeta();
        }

        // Smoobu: obtener apartamentos (cache corto)
        $api = $this->smoobu;
        $apartments = Cache::remember('smoobu.apartments', 300, function () use ($api) {
            return $api->apartments(); // [['id'=>..., 'name'=>...], ...]
        });
        $apartments = collect($apartments);

        // ---- NUEVO: soporte para nombre del select "Apartamento" del home ----
        $selectedId = $request->input('apartment_id') ?? $request->input('Apartamento');

        if (filled($selectedId)) {
            $apartments = $apartments->where('id', (int) $selectedId)->values();
        }

        // Rango de fechas opcional
        $checkin  = $request->date('checkin');
        $checkout = $request->date('checkout');

        // ---- NUEVO: Filtrar por disponibilidad real si hay rango ----
        if ($checkin && $checkout && $apartments->count() > 0) {
            $ids = $apartments->pluck('id')->values();
            $from = $checkin->format('Y-m-d');
            $to   = $checkout->format('Y-m-d');

            // Generar las fechas (noches) del rango [checkin, checkout)
            $dates = [];
            $cursor = $checkin->copy();
            while ($cursor->lt($checkout)) {
                $dates[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }

            $availableIds = collect();

            // 1) Intentar un endpoint de availability del wrapper (si existe)
            try {
                // Estructura esperada: ['data' => [aptId => ['Y-m-d' => true/false|'available'|'booked'...]]]
                $availabilityResponse = $api->availability($from, $to, $ids->all());
                $data = collect($availabilityResponse['data'] ?? []);

                $availableIds = $data->filter(function ($byDay) use ($dates) {
                    foreach ($dates as $d) {
                        $v = $byDay[$d] ?? null;
                        // considera no disponible si es false/0/'booked'/'unavailable' o null
                        if ($v === false || $v === 0 || $v === 'booked' || $v === 'unavailable' || $v === null) {
                            return false;
                        }
                    }
                    return true;
                })->keys()->map(fn ($k) => (int) $k);
            } catch (\Throwable $e1) {
                // 2) Fallback: revisar reservas y detectar solape
                try {
                    // Estructura común esperada:
                    // ['data' => [ ['apartment_id'=>..., 'checkin'=>..., 'checkout'=>...], ... ]]
                    $reservationsResp = $api->reservations($from, $to, $ids->all());
                    $reservations = collect($reservationsResp['data'] ?? []);

                    $unavailableById = $reservations->groupBy(function ($r) {
                        return (int) ($r['apartment_id'] ?? $r['apartmentId'] ?? $r['apartment'] ?? 0);
                    })->filter(function ($rows) use ($checkin, $checkout) {
                        return $rows->contains(function ($r) use ($checkin, $checkout) {
                            // normalizar campos
                            $ci = \Illuminate\Support\Carbon::parse($r['checkin'] ?? $r['checkIn'] ?? $r['from'] ?? null);
                            $co = \Illuminate\Support\Carbon::parse($r['checkout'] ?? $r['checkOut'] ?? $r['to'] ?? null);
                            if (!$ci || !$co) return false;
                            // solape si (ci < checkout) y (co > checkin)
                            return $ci->lt($checkout) && $co->gt($checkin);
                        });
                    })->keys();

                    $availableIds = $ids->diff($unavailableById)->values();
                } catch (\Throwable $e2) {
                    // Si tampoco hay reservas, no filtramos por disponibilidad
                    \Log::warning('No availability source; skipping availability filter', [
                        'e1' => $e1->getMessage(),
                        'e2' => $e2->getMessage(),
                    ]);
                    $availableIds = $ids->values();
                }
            }

            // Aplicar filtro final por disponibilidad
            $apartments = $apartments->whereIn('id', $availableIds->all())->values();
        }

        // Rates (promedio simple) para los que quedaron
        $ratesByApt = [];
        if ($checkin && $checkout && $apartments->count() > 0) {
            try {
                $ids = $apartments->pluck('id')->values()->all();
                $ratesResponse = $api->rates($checkin->format('Y-m-d'), $checkout->format('Y-m-d'), $ids);

                $byId = $ratesResponse['data'] ?? [];
                foreach ($byId as $aptId => $days) {
                    $prices = [];
                    foreach ($days as $day => $info) {
                        $p = $info['price'] ?? null;
                        if (is_numeric($p) && $p > 0) $prices[] = (float)$p;
                    }
                    $ratesByApt[$aptId] = count($prices) ? round(array_sum($prices) / count($prices)) : null;
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudieron obtener rates en index()', ['e' => $e->getMessage()]);
                $ratesByApt = [];
            }
        }

        // Mapear al formato que la vista espera
        $properties = $apartments->map(function ($apt) use ($ratesByApt) {
            $id   = $apt['id'] ?? null;
            $name = trim($apt['name'] ?? 'Propiedad');

            // Imagen local por ID; fallback a placeholder
            $thumb = (function () use ($id) {
                foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.png"] as $rel) {
                    if (file_exists(public_path($rel))) return asset($rel);
                }
                return asset('images/property-placeholder.jpg');
            })();

            return [
                '_id'       => $id,
                'title'     => $name,
                'picture'   => ['thumbnail' => $thumb],
                'address'   => ['city' => 'Galicia', 'country' => 'España'],
                'bedrooms'  => 0,
                'bathrooms' => 0,
                'prices'    => [
                    'basePrice' => $ratesByApt[$id] ?? null,
                    'currency'  => 'EUR',
                ],
                'rating'    => 5,
            ];
        })->values()->all();

        return view('properties.index', [
            'properties'        => $properties,
            'filters'           => $request->all(),
            'paginapropiedades' => $paginapropiedades,
            'seo'               => $seo
        ]);
    } catch (\Exception $e) {
        Log::error('Error en index properties:', ['e' => $e->getMessage()]);
        return view('properties.index', [
            'properties'        => [],
            'filters'           => $request->all(),
            'paginapropiedades' => new \App\Models\Pagina(),
            'seo'               => new \App\Models\PaginaMeta(),
            'error'             => 'No se pudieron cargar las propiedades. Inténtelo de nuevo más tarde.'
        ]);
    }
}



   /**
 * Detalle de propiedad (Smoobu).
 */
public function show($id)
{
    try {
        $api = $this->smoobu;
        $id  = (int) $id;

        // Detalle (cache 5 min)
        $detail = Cache::remember("smoobu.apartment.$id", 300, fn() => $api->apartment($id));
        if (empty($detail)) {
            return redirect()->route('properties.index')->with('error', 'Propiedad no encontrada.');
        }

        // Overrides locales (meta + galería)
        $meta    = SmoobuApartmentMeta::find($id);
        $imagesQ = SmoobuApartmentImage::where('apartment_id', $id)
            ->where('is_active', true)->orderBy('sort_order')->get();
        $gallery = $imagesQ->map(fn($i) => asset($i->path))->values()->all();

        // Precio: promedio rates 7d -> 30d | override | minimal | maximal
        $priceFromRates = null;
        try {
            $start = now()->toDateString();
            $end   = now()->addDays(7)->toDateString();
            $resp  = $api->rates($start, $end, [$id]);
            $rows  = $resp['data'][$id] ?? [];
            $nums  = [];
            foreach ($rows as $r) {
                $p = $r['price'] ?? null;
                if (is_numeric($p) && $p > 0) $nums[] = (float) $p;
            }
            if (count($nums)) $priceFromRates = (int) round(array_sum($nums) / count($nums));

            if (!$priceFromRates) {
                $start2 = now()->toDateString();
                $end2   = now()->addDays(30)->toDateString();
                $resp2  = $api->rates($start2, $end2, [$id]);
                $rows2  = $resp2['data'][$id] ?? [];
                $nums2  = [];
                foreach ($rows2 as $r) {
                    $p = $r['price'] ?? null;
                    if (is_numeric($p) && $p > 0) $nums2[] = (float)$p;
                }
                if (count($nums2)) $priceFromRates = (int) round(array_sum($nums2) / count($nums2));
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudieron obtener rates para show()', ['id' => $id, 'e' => $e->getMessage()]);
        }

        $priceOverride = $meta?->base_price_override;
        $priceMinimal  = $detail['price']['minimal'] ?? null;
        $priceMaximal  = $detail['price']['maximal'] ?? null;

        $finalPrice = $priceOverride
            ?? $priceFromRates
            ?? $priceMinimal
            ?? $priceMaximal
            ?? null;

        // Campos principales (por defecto Galicia, España)
        $title   = $meta?->title ?: ($detail['name'] ?? 'Propiedad');
        $city    = $meta?->city ?? ($detail['location']['city'] ?? 'Galicia');
        $country = $meta?->country ?? ($detail['location']['country'] ?? 'España');
        $desc    = $meta?->description ?? ($detail['description'] ?? null);

        // Habitaciones / baños
        $rooms = $detail['rooms'] ?? [];
        $bedrooms  = is_numeric($meta?->bedrooms)  ? (int)$meta->bedrooms  : (
            (isset($rooms['bedrooms']) && is_numeric($rooms['bedrooms'])) ? (int)$rooms['bedrooms'] : ((isset($detail['bedrooms']) && is_numeric($detail['bedrooms'])) ? (int)$detail['bedrooms'] : 0)
        );
        $bathrooms = is_numeric($meta?->bathrooms) ? (float)$meta->bathrooms : (
            (isset($rooms['bathrooms']) && is_numeric($rooms['bathrooms'])) ? (float)$rooms['bathrooms'] : ((isset($detail['bathrooms']) && is_numeric($detail['bathrooms'])) ? (float)$detail['bathrooms'] : 0.0)
        );

        $beds         = $detail['beds'] ?? ($rooms['beds'] ?? null);
        $accommodates = $detail['accommodates'] ?? null;

        // Galería (local por ID -> fallback -> placeholder)
        if (empty($gallery)) {
            foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.png"] as $rel) {
                if (file_exists(public_path($rel))) {
                    $gallery[] = asset($rel);
                    break;
                }
            }
        }
        if (empty($gallery)) {
            $gallery[] = asset('images/property-placeholder.jpg');
        }

        // Booking URL (iframe Smoobu) - opcional
        $bookingBase = config('services.smoobu.booking_base_url'); // ej: https://bookings.smoobu.com/es/TU_CUENTA
        $bookingUrl  = $bookingBase
            ? rtrim($bookingBase, '/') . '?apartmentId=' . urlencode($id) . '&lang=es&iframe=true'
            : null;

        // === Calendar verification (para el Single Calendar Widget) ===
        $calendarVerification =
            ($meta && !empty($meta->calendar_verification)) ? $meta->calendar_verification : (config('services.smoobu.calendar_verifications')[$id] ?? null)
            ?? config('services.smoobu.default_calendar_verification');

        // Payload para la vista
        $property = [
            '_id'    => $id,
            'title'  => $title,
            'gallery' => $gallery,
            'pictures' => collect($gallery)->map(fn($u) => ['original' => $u, 'thumbnail' => $u])->values()->all(),
            'address' => [
                'full'    => trim(($city ?: '') . (($city && $country) ? ', ' : '') . ($country ?: '')),
                'city'    => $city,
                'country' => $country,
            ],
            'bedrooms'     => $bedrooms,
            'bathrooms'    => $bathrooms,
            'beds'         => $beds,
            'accommodates' => $accommodates,
            'prices'       => [
                'basePrice' => (is_numeric($finalPrice) && $finalPrice > 0) ? (int)round($finalPrice) : null,
                'currency'  => 'EUR', // <<< EUR
            ],
            'description'            => $desc,
            'amenities'              => $detail['amenities'] ?? [],
            'bookingUrl'             => $bookingUrl,
            'calendar_verification'  => $calendarVerification,
        ];

        $reviews = []; // sin Guesty

        return view('properties.show', compact('property', 'reviews'));
    } catch (\Throwable $e) {
        Log::error('Error en show() Smoobu: ' . $e->getMessage(), ['id' => $id]);
        return redirect()->route('properties.index')->with('error', 'No se pudo cargar la propiedad.');
    }
}


    /**
     * Utilidad de fechas (se mantiene por compatibilidad).
     */
    private function obtenerFechasOcupadas($checkIn, $checkOut): array
    {
        $fechas = [];
        $inicio = \Carbon\Carbon::parse($checkIn);
        $fin    = \Carbon\Carbon::parse($checkOut);
        $periodo = \Carbon\CarbonPeriod::create($inicio, $fin);
        foreach ($periodo as $fecha) {
            $fechas[] = $fecha->format('Y-m-d');
        }
        return $fechas;
    }

    /* ============================
     * Acciones antiguas de Guesty
     * ============================
     * Se mantienen las firmas, pero ya no hacen llamadas a Guesty.
     * En su lugar devuelven respuesta informando que el flujo va por Smoobu.
     */

    public function createReservation(Request $request, $id)
    {
        return back()->with('info', 'La reserva ahora se realiza desde el portal de Smoobu.');
    }

    public function calculatePrice(Request $request)
    {
        return response()->json([
            'error' => 'El cálculo de precio se gestiona desde el portal de reservas de Smoobu.'
        ], 400);
    }

    public function redirectToPortal(Request $request)
    {
        // Redirige al booking de Smoobu si hay apartmentId
        $bookingBase = config('services.smoobu.booking_base_url');
        $apartmentId = $request->input('listingId');
        if ($bookingBase && $apartmentId) {
            $url = rtrim($bookingBase, '/') . '?apartmentId=' . urlencode($apartmentId) . '&lang=es';
            return redirect()->away($url);
        }
        return back()->with('info', 'No se pudo construir el portal de Smoobu.');
    }

    // Método antes llamado desde Guesty; mantenemos para no romper rutas
    public function getGuestPortalUrl($quoteId, $returnUrl)
    {
        abort(404, 'No disponible: migrado a Smoobu.');
    }

    public function createQuote(Request $request)
    {
        return response()->json([
            'error' => 'La creación de cotizaciones directas no está disponible. Use el calendario de Smoobu.'
        ], 400);
    }

    public function showReservationForm($id)
    {
        // Antes mostraba form propio; ahora redirigimos al booking Smoobu
        $bookingBase = config('services.smoobu.booking_base_url');
        if ($bookingBase) {
            $url = rtrim($bookingBase, '/') . '?apartmentId=' . urlencode($id) . '&lang=es';
            return redirect()->away($url);
        }
        return redirect()->route('properties.show', $id)
            ->with('info', 'Configura services.smoobu.booking_base_url para usar el portal de reservas.');
    }

    public function reserve(Request $request, $id)
    {
        return back()->with('info', 'La reserva se completa en el portal de Smoobu.');
    }

    public function confirmReservation(Request $request, $id)
    {
        return back()->with('info', 'La confirmación de reserva se realiza en Smoobu.');
    }

    public function showPaymentForm(Request $request, $propertyId, $quoteId)
    {
        return redirect()->route('properties.show', $propertyId)
            ->with('info', 'El pago se gestiona directamente en Smoobu.');
    }

    public function tokenizeCard(Request $request, $id)
    {
        return redirect()->route('properties.show', $id)
            ->with('info', 'La tokenización/pago se realiza en Smoobu.');
    }

    public function processReservation(Request $request)
    {
        return back()->with('info', 'La creación de reservas se hace en Smoobu.');
    }

    public function processPayment(Request $request, $id)
    {
        return back()->with('info', 'El pago se realiza en el portal de Smoobu.');
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'apartmentId' => 'required|integer',
            'checkIn'     => 'required|date',
            'checkOut'    => 'required|date|after:checkIn',
            'guestsCount' => 'nullable|integer|min:1'
        ]);

        try {
            $aptId   = (int) $validated['apartmentId'];
            $start   = Carbon::parse($validated['checkIn']);
            $endExcl = Carbon::parse($validated['checkOut']);          // check-out
            $endIncl = $endExcl->copy()->subDay();                      // noches: hasta el día previo

            // Llamada a Smoobu (incluimos ambos extremos, por si la API devuelve inclusivo)
            $resp = $this->smoobu->rates($start->toDateString(), $endIncl->toDateString(), [$aptId]);

            $calendar = $resp['data'][$aptId] ?? [];
            if (empty($calendar)) {
                return response()->json([
                    'ok' => true,
                    'available' => false,
                    'hasPrices' => false,
                    'reason' => 'No hay tarifas para ese rango.'
                ]);
            }

            $available = true;
            $hasPrices = true;
            $total     = 0.0;
            $breakdown = [];

            // Recorremos cada noche entre checkIn y checkOut-1
            $cursor = $start->copy();
            while ($cursor->lte($endIncl)) {
                $d = $cursor->toDateString();
                $row = $calendar[$d] ?? null;

                if (!$row || (int)($row['available'] ?? 0) !== 1) {
                    $available = false;
                }

                $price = $row['price'] ?? null;
                if (!is_numeric($price)) {
                    $hasPrices = false;
                    $price = 0;
                } else {
                    $total += (float) $price;
                }

                $breakdown[] = [
                    'date'      => $d,
                    'available' => (int)($row['available'] ?? 0),
                    'price'     => (float)$price,
                ];
                $cursor->addDay();
            }

            return response()->json([
                'ok'         => true,
                'available'  => $available && $hasPrices,
                'hasPrices'  => $hasPrices,
                'totalPrice' => round($total, 2),
                'currency'   => 'USD',
                'breakdown'  => $breakdown,
            ]);
        } catch (\Throwable $e) {
            Log::error('checkAvailability error', ['e' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
