<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GuestyTokenService;

class RefreshGuestyToken extends Command
{
    protected $signature = 'guesty:refresh-token';
    protected $description = 'Refresh Guesty API token';

    protected $tokenService;

    public function __construct(GuestyTokenService $tokenService)
    {
        parent::__construct();
        $this->tokenService = $tokenService;
    }

    public function handle()
    {
        $this->info('Refrescando token de Guesty API...');
        
        try {
            $token = $this->tokenService->refreshToken();
            
            if ($token) {
                $this->info('Token refrescado exitosamente');
                return 0;
            } else {
                $this->error('No se pudo refrescar el token');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}