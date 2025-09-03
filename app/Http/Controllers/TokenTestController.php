<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GuestyTokenService;
use App\Models\GuestyToken;

class TokenTestController extends Controller
{
    protected $tokenService;
    
    public function __construct(GuestyTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }
    
    public function testTokenSystem()
    {
        // Verificar tokens existentes
        $tokens = GuestyToken::orderBy('created_at', 'desc')->get();
        
        // Obtener token activo
        $activeToken = $this->tokenService->getToken();
        
        // Información del token activo
        $tokenInfo = null;
        if ($activeToken) {
            $tokenParts = explode('.', $activeToken);
            $payloadB64 = $tokenParts[1] ?? '';
            $payload = $payloadB64 ? json_decode(base64_decode($payloadB64), true) : null;
            
            $tokenInfo = [
                'token_valid' => !empty($payload),
                'expires_at' => $payload['exp'] ?? 'unknown',
                'current_time' => time(),
                'is_expired' => ($payload['exp'] ?? 0) < time(),
                'scopes' => $payload['scp'] ?? [],
                'audience' => $payload['aud'] ?? 'unknown'
            ];
        }
        
        return response()->json([
            'tokens_count' => $tokens->count(),
            'tokens' => $tokens->map(function($token) {
                return [
                    'id' => $token->id,
                    'created_at' => $token->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => date('Y-m-d H:i:s', $token->expires_at),
                    'is_active' => $token->is_active,
                    'has_expired' => $token->hasExpired(),
                ];
            }),
            'active_token' => $activeToken ? substr($activeToken, 0, 20) . '...' : null,
            'token_info' => $tokenInfo
        ]);
    }
    
    public function manualRefreshToken()
    {
        try {
            $token = $this->tokenService->refreshToken();
            
            return response()->json([
                'success' => !empty($token),
                'token' => $token ? substr($token, 0, 20) . '...' : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}