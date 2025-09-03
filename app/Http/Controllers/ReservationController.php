<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GuestyService;

class ReservationController extends Controller
{
    protected $guestyService;

    public function __construct(GuestyService $guestyService)
    {
        $this->guestyService = $guestyService;
    }

    /**
     * Mostrar formulario de reserva
     */
    public function create($propertyId)
    {
        try {
            $property = $this->guestyService->getListing($propertyId);
            return view('reservations.create', [
                'property' => $property
            ]);
        } catch (\Exception $e) {
            return redirect()->route('properties.index')
                ->with('error', 'No se pudo cargar la información de la propiedad para la reserva.');
        }
    }

    /**
     * Procesar la reserva
     */
    public function store(Request $request, $propertyId)
    {
        // Validar datos de reserva
        $validated = $request->validate([
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'guests' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);
        
        try {
            // Aquí iría la lógica para crear la reserva en Guesty
            // $reservation = $this->guestyService->createReservation($propertyId, $validated);
            
            // Por ahora, solo redirigimos con un mensaje de éxito
            return redirect()->route('properties.show', $propertyId)
                ->with('success', 'Su solicitud de reserva ha sido recibida. Pronto nos pondremos en contacto con usted.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'No se pudo procesar la reserva. Por favor, inténtelo de nuevo más tarde.');
        }
    }
}