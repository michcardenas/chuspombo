<?php

namespace App\Services;

use App\Models\GuestyToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuestyTokenService
{
    protected $authUrl;
    protected $clientId;
    protected $clientSecret;
    
    public function __construct()
    {
        $this->authUrl = 'https://booking.guesty.com/oauth2/token';
        $this->clientId = config('services.guesty.client_id');
        $this->clientSecret = config('services.guesty.client_secret');
    }
    
    /**
     * Obtiene un token activo o genera uno nuevo si es necesario.
     */
    public function getToken()
    {
        // Buscar un token activo en la BD
        $token = GuestyToken::getActiveToken();

        // Si el token aún es válido, lo reutilizamos
        if ($token) {
            Log::info('Usando token existente en la base de datos.');
            return $token->access_token;
        }

        // Si no hay token válido, generamos uno nuevo
        Log::info('No hay token válido, generando uno nuevo.');
        return $this->refreshToken();
    }
    
    /**
     * Obtiene un nuevo token desde la API de Guesty.
     */
    public function refreshToken()
    {
        try {
            Log::info('Solicitando nuevo token de Guesty API');

            // Hacer la solicitud a la API de Guesty
            $response = Http::asForm()->post($this->authUrl, [
                'grant_type' => 'client_credentials',
                'scope' => 'booking_engine:api',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            // Si la solicitud fue exitosa
            if ($response->successful()) {
                $data = $response->json();

                // Extraer datos del token
                $accessToken = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 86400; // 24 horas por defecto
                $tokenType = $data['token_type'] ?? 'Bearer';

                // Guardar el nuevo token en la BD
                GuestyToken::storeNewToken($accessToken, $tokenType, $expiresIn);

                Log::info('Nuevo token de Guesty guardado exitosamente.');

                return $accessToken;
            }

            // Manejo de error 429 (Too Many Requests)
            if ($response->status() == 429) {
                $retryAfter = intval($response->header('Retry-After', 3600)); // Si no hay header, espera 1 hora
                Log::warning("Rate limit alcanzado. Esperando $retryAfter segundos antes de volver a intentar.");
                return null;
            }

            // Si el token no se pudo obtener, registrar el error
            Log::error('Error al obtener token de Guesty: ' . $response->body());

            // Si no hay nuevo token, usar el último que se almacenó (aunque esté expirado)
            $lastToken = GuestyToken::orderBy('created_at', 'desc')->first();
            return $lastToken ? $lastToken->access_token : null;
        } catch (\Exception $e) {
            Log::error('Excepción en GuestyTokenService: ' . $e->getMessage());
            return null;
        }
    }
}
