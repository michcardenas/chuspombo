<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmoobuClient
{
    protected function client()
    {
        return Http::baseUrl(config('services.smoobu.base_url'))
            ->withHeaders(['Api-Key' => config('services.smoobu.key')])
            ->acceptJson();
    }

    

    /** Propiedades (listar IDs y nombres) */
    public function apartments(): array
    {
        // GET /apartments
        return $this->client()->get('/apartments')->json('apartments', []);
    }

    /** Reservas (listado con filtros y paginación) */
    public function bookings(array $filters = []): array
    {
        // GET /reservations?from=YYYY-MM-DD&to=YYYY-MM-DD&page=1&pageSize=100...
        return $this->client()->get('/reservations', $filters)->json();
    }

    /** Detalle de una reserva */
    public function booking(int $reservationId): array
    {
        return $this->client()->get("/reservations/{$reservationId}")->json();
    }

    /** Actualizar una reserva (check-in/out, precio, notas, etc.) */
    public function updateBooking(int $reservationId, array $payload): array
    {
        // PUT /reservations/{id}
        return $this->client()->put("/reservations/{$reservationId}", $payload)->json();
    }

    /** Cancelar una reserva */
    public function cancelBooking(int $reservationId): array
    {
        // DELETE /reservations/{id}
        return $this->client()->delete("/reservations/{$reservationId}")->json();
    }

    /** Tarifas por rango y propiedades */

public function rates(string $start, string $end, array $apartmentIds): array {
    // Debe construir la query que Smoobu espera, e.g. /rates?start_date=...&end_date=...&apartments[]=ID...
    return $this->client()->get('/rates', [
        'start_date'  => $start,
        'end_date'    => $end,
        'apartments'  => $apartmentIds,
        'pageSize'    => 200,
    ])->json();
}

    /** Placeholders (links de online check-in, etc.) para una reserva */
    public function placeholders(int $reservationId): array
    {
        return $this->client()->get("/reservations/{$reservationId}/placeholders")->json('placeholders', []);
    }

    /** Mensajería (huésped/host) */
    public function messageGuest(int $reservationId, ?string $subject, string $html): array
    {
        return $this->client()->post("/reservations/{$reservationId}/messages/send-message-to-guest", [
            'subject'     => $subject,
            'messageBody' => $html,
        ])->json();
    }

    public function messageHost(int $reservationId, ?string $subject, string $html, bool $internal = false): array
    {
        return $this->client()->post("/reservations/{$reservationId}/messages/send-message-to-host", [
            'subject'     => $subject,
            'messageBody' => $html,
            'internal'    => $internal,
        ])->json();
    }

    public function apartment(int $id): array
    {
        return $this->client()->get("/apartments/{$id}")->json();
    }
}
