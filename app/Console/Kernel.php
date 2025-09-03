<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
{
    // Sincronizar propiedades de Guesty cada 6 horas
    $schedule->command('guesty:sync-properties')->everyFourHours();
    
    // Refrescar el token de Guesty cada 23 horas
    $schedule->command('guesty:refresh-token')->dailyAt('02:00');
}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
