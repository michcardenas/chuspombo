<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Pagina;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Compartir $pagina con TODAS las vistas
        View::composer('*', function ($view) {
            $pagina = Pagina::find(1); // Datos generales
            $view->with('pagina', $pagina);
        });
    }
}
