<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SmoobuClient;

class ReservationController extends Controller
{
    /** @var \App\Services\SmoobuClient */
    protected SmoobuClient $smoobu;

    public function __construct(SmoobuClient $smoobu)
    {
        $this->smoobu = $smoobu;
    }

    /**
     * Mostrar formulario de reserva (o portal Smoobu embebido).
     */
    public function create($propertyId)
    {
        try {
            $id = (int) $propertyId;

            // Detalle básico del apartamento desde Smoobu (para mostrar título, etc.)
            $detail = $this->smoobu->apartment($id);
            if (empty($detail)) {
                return redirect()->route('properties.index')
                    ->with('error', 'No se encontró la propiedad en Smoobu.');
            }

            // Construir URL del portal de reservas Smoobu
            $bookingBase = config('services.smoobu.booking_base_url'); // ej: https://bookings.smoobu.com/es/TU_CUENTA
            $bookingUrl  = $bookingBase
                ? rtrim($bookingBase, '/') . '?apartmentId=' . urlencode($id) . '&lang=es'
                : null;

            // Armar un payload mínimo para la vista (compatibilidad)
            $fullAddress = trim(
                ($detail['location']['city'] ?? '') .
                ((($detail['location']['city'] ?? null) && ($detail['location']['country'] ?? null)) ? ', ' : '') .
                ($detail['location']['country'] ?? '')
            );

            $property = [
                '_id'         => $id,
                'title'       => $detail['name'] ?? 'Propiedad',
                'address'     => ['full' => $fullAddress],
                'accommodates'=> $detail['accommodates'] ?? null,
            ];

            // Si quieres, en la vista reservations/create.blade.php puedes:
            // - Mostrar datos del property
            // - Si $bookingUrl existe, embeber un <iframe src="{{ $bookingUrl }}"> o botón "Reservar ahora"
            return view('reservations.create', [
                'property'   => $property,
                'bookingUrl' => $bookingUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error cargando create() de reserva (Smoobu): ' . $e->getMessage(), ['propertyId' => $propertyId]);
            return redirect()->route('properties.index')
                ->with('error', 'No se pudo cargar la información de la propiedad para la reserva.');
        }
    }

    /**
     * Procesar la reserva: redirige al portal de Smoobu.
     */
    public function store(Request $request, $propertyId)
    {
        // Validación básica (mantengo estructura original por compatibilidad)
        $validated = $request->validate([
            'checkin'  => 'nullable|date',
            'checkout' => 'nullable|date|after:checkin',
            'guests'   => 'nullable|integer|min:1',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'phone'    => 'required|string|max:20',
        ]);

        // Redirigir al portal de reservas de Smoobu para finalizar la reserva
        $bookingBase = config('services.smoobu.booking_base_url'); // ej: https://bookings.smoobu.com/es/TU_CUENTA
        if ($bookingBase) {
            $url = rtrim($bookingBase, '/') . '?apartmentId=' . urlencode((int)$propertyId) . '&lang=es';

            // Nota: Smoobu podría soportar más parámetros en la URL (fechas/huéspedes).
            // Si más adelante confirmas parámetros oficiales, se pueden agregar aquí.
            // p.ej. '&arrival=2025-09-10&departure=2025-09-12&adults=2'

            return redirect()->away($url);
        }

        // Fallback si no hay base URL configurada
        return redirect()->route('properties.show', (int)$propertyId)
            ->with('info', 'Configura "services.smoobu.booking_base_url" para completar la reserva en el portal de Smoobu.');
    }
}
