<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuestyToken;

class GuestyTokenSeeder extends Seeder
{
    public function run()
    {
        GuestyToken::where('is_active', true)->update(['is_active' => false]);
        
        $token = env('GUESTY_API_ACCESS_TOKEN');
        $tokenParts = explode('.', $token);
        $payloadB64 = $tokenParts[1] ?? '';
        $payload = $payloadB64 ? json_decode(base64_decode($payloadB64), true) : null;
        $expiresAt = $payload['exp'] ?? (time() + 86400); // 24 horas por defecto
        
        GuestyToken::create([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
        
        $this->command->info('Token de Guesty inicializado correctamente');
    }
}