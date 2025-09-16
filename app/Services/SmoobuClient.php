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

    public function rates(string $start, string $end, array $apartmentIds): array
    {
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

   public function bookingsAll(array $filters = []): array
{
    // Ejemplos de filtros válidos:
    // ['arrivalFrom' => '2025-09-01', 'arrivalTo' => '2025-12-31', 'pageSize' => 100]
    $resp = $this->client()->get('/reservations', $filters);

    // Si quieres ver rápido qué vino:
    // logger()->debug('Smoobu /reservations raw', ['status' => $resp->status(), 'body' => $resp->body()]);

    // ¡Ojo! La lista está en "bookings"
    return $resp->json('bookings', []);
}

public function bookingsRaw(array $filters = []): array
{
    // Devuelve todo el envoltorio (page_count, total_items, bookings, etc)
    return $this->client()->get('/reservations', $filters)->json();
}

public function bookingsForApartment(
    int $apartmentId,
    ?string $arrivalFrom = null,
    ?string $arrivalTo   = null,
    int $pageSize        = 100
): array {
    $out  = [];
    $page = 1;

    while (true) {
        $filters = array_filter([
            // estos nombres replican lo que usa Smoobu en la UI
            'arrivalFrom' => $arrivalFrom,
            'arrivalTo'   => $arrivalTo,
            'page'        => $page,
            'pageSize'    => $pageSize,
        ]);

        $resp  = $this->client()->get('/reservations', $filters)->throw()->json();
        $items = $resp['bookings'] ?? $resp['reservations'] ?? $resp['items'] ?? [];

        if (empty($items)) break;

        foreach ($items as $r) {
            $apt = data_get($r, 'apartment.id') ?? data_get($r, 'apartmentId') ?? data_get($r, 'apartment_id');
            if ((int) $apt === $apartmentId) {
                $out[] = $r;
            }
        }

        // pagina siguiente (usa tamaño como heurística; si llega incompleta, paramos)
        if (count($items) < $pageSize) break;
        $page++;
        if ($page > 200) break; // safety
    }

    return $out;
}

}
