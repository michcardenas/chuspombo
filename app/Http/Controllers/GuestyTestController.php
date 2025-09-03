<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuestyTestController extends Controller
{
    public function testBaseUrl()
    {
        $baseUrls = [
            'https://booking.guesty.com/api',
            'https://api.guesty.com',
            'https://booking.guesty.com'
        ];
        
        $results = [];
        
        foreach ($baseUrls as $url) {
            try {
                $response = Http::get($url);
                $results[$url] = [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'body_preview' => substr($response->body(), 0, 200)
                ];
            } catch (\Exception $e) {
                $results[$url] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return response()->json($results);
    }
    
    public function testAuthUrl()
    {
        $token = config('services.guesty.access_token');
        $authUrl = 'https://booking.guesty.com/api/listings';
        
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($authUrl, ['limit' => 1]);
            
            return response()->json([
                'status' => $response->status(),
                'success' => $response->successful(),
                'data' => $response->json(),
                'error' => $response->failed() ? $response->body() : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function validateToken()
    {
        $token = config('services.guesty.access_token');
        // Decodifica el token para mostrar su información (solo las partes públicas)
        $tokenParts = explode('.', $token);
        $payloadB64 = $tokenParts[1] ?? '';
        $payload = $payloadB64 ? json_decode(base64_decode($payloadB64), true) : null;
        
        return response()->json([
            'token_valid' => !empty($payload),
            'expires_at' => $payload['exp'] ?? 'unknown',
            'current_time' => time(),
            'is_expired' => ($payload['exp'] ?? 0) < time(),
            'scopes' => $payload['scp'] ?? [],
            'audience' => $payload['aud'] ?? 'unknown'
        ]);
    }
}