<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\GuestyTokenService;

class GuestyService
{
    protected $baseUrl;
    protected $tokenService;
    protected $tokenType;

    public function __construct(GuestyTokenService $tokenService)
    {
        $this->baseUrl = config('services.guesty.base_url', 'https://booking.guesty.com/api');
        $this->tokenService = $tokenService;
        $this->tokenType = config('services.guesty.token_type', 'Bearer');
    }

    /**
     * Obtener todas las propiedades desde la API de Guesty
     */
    public function getListings($params = [])
    {
        return $this->makeRequest('GET', '/listings', $params);
    }
    public function getListingReviews($listingId, $limit = 5)
    {
        return $this->makeRequest('GET', '/reviews', [
            'listingId' => $listingId,
            'limit' => $limit
        ]);
    }
    /**
     * Obtener una propiedad específica por ID
     */
    public function getListing($id)
    {
        return $this->makeRequest('GET', "/listings/{$id}");
    }
    public function getListingCalendar($id, $from, $to)
    {
        return $this->makeRequest('GET', "/listings/{$id}/calendar", [
            'from' => $from,
            'to' => $to
        ]);
    }

    public function getGuestyPayUrl(string $quoteId, string $returnUrl): string
    {
        $response = Http::withToken(config('services.guesty.token'))
            ->post('https://api.guesty.com/v1/quotes/' . $quoteId . '/pay', [
                'returnUrl' => $returnUrl,
            ]);

        if ($response->failed()) {
            throw new \Exception('Error al obtener la URL de GuestyPay');
        }

        return $response->json()['paymentUrl']; // o el campo correcto según Guesty
    }


    /**
     * Crear una nueva reserva en Guesty
     */
    public function createReservation($reservationData)
    {
        try {
            $endpoint = "/reservations";
            $response = $this->makeRequest('POST', $endpoint, [], $reservationData);

            if ($response['success']) {
                return $response['data'];
            } else {
                return ['error' => $response['data']];
            }
        } catch (\Exception $e) {
            \Log::error('Error al crear la reserva en Guesty: ' . $e->getMessage());
            return ['error' => ['message' => 'Error inesperado al procesar la reserva']];
        }
    }
    public function getReservationPrice($listingId, $checkIn, $checkOut, $guestsCount = 2)
    {
        return $this->makeRequest('GET', '/reservations/money', [
            'listingId' => $listingId,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'guestsCount' => $guestsCount
        ]);
    }
    public function createReservationQuote($listingId, $checkIn, $checkOut, $guestsCount = 1, $coupons = null)
    {
        $payload = [
            'listingId' => $listingId,
            'checkInDateLocalized' => $checkIn,
            'checkOutDateLocalized' => $checkOut,
            'guestsCount' => $guestsCount
        ];

        if ($coupons) {
            $payload['coupons'] = $coupons;
        }

        return $this->makeRequest('POST', '/reservations/quotes', [], $payload);
    }

    public function getGuestPortalUrl($quoteId, $returnUrl)
    {
        $payload = [
            'returnUrl' => $returnUrl
        ];

        try {
            $response = $this->makeRequest('POST', "/reservations/quotes/{$quoteId}/guest-portal-link", [], $payload);

            if (isset($response['portalUrl'])) {
                return $response['portalUrl'];
            } else {
                throw new \Exception('La respuesta no contiene la URL del portal');
            }
        } catch (\Exception $e) {
            \Log::error('Error al obtener URL del portal de Guesty: ' . $e->getMessage());
            throw new \Exception('No se pudo obtener el enlace al portal de reservas: ' . $e->getMessage());
        }
    }
    /**
     * Método genérico para hacer solicitudes a la API de Guesty
     */
    protected function makeRequest($method, $endpoint, $queryParams = [], $data = null)
    {
        $url = $this->baseUrl . $endpoint;

        // Obtener un token actualizado
        $token = $this->tokenService->getToken();

        if (!$token) {
            throw new \Exception('No se pudo obtener un token válido para Guesty API');
        }

        Log::debug("Guesty API Request", [
            'method' => $method,
            'url' => $url,
            'params' => $queryParams,
            'data' => $data
        ]);

        $response = Http::withHeaders([
            'Authorization' => "{$this->tokenType} {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        if ($method === 'GET') {
            $response = $response->get($url, $queryParams);
        } elseif ($method === 'POST') {
            $response = $response->post($url, $data);
        } elseif ($method === 'PUT') {
            $response = $response->put($url, $data);
        } elseif ($method === 'DELETE') {
            $response = $response->delete($url, $data);
        }

        Log::debug("Guesty API Response", [
            'status' => $response->status(),
            'success' => $response->successful()
        ]);

        if ($response->failed()) {
            Log::error("Guesty API Error", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Guesty API Error: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json();
    }

    public function getQuoteById($quoteId)
    {
        return $this->makeRequest('GET', "/reservations/quotes/{$quoteId}");
    }
}
