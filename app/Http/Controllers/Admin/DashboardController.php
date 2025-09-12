<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use Illuminate\Http\Request;
use App\Services\SmoobuClient;      // ⬅️ importa el servicio
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(SmoobuClient $smoobu)  // ⬅️ inyecta el cliente
    {
        // Página principal
        $pagina = Pagina::with('meta')->first() ?? new Pagina();

        // Traer apartamentos desde Smoobu
        try {
            $apartments = $smoobu->apartments(); // GET /apartments
        } catch (\Throwable $e) {
            Log::warning('Smoobu apartments fetch failed: '.$e->getMessage());
            $apartments = [];
        }

        // Mapear al formato esperado por la vista ($featuredProperties)
        $featuredProperties = collect($apartments)->map(function ($a) {
            $id = $a['id']
                ?? $a['apartmentId']
                ?? $a['apartment_id']
                ?? $a['apartmentID']
                ?? $a['apartmentid']
                ?? null;

            $title = $a['name'] ?? $a['title'] ?? 'Sin título';

            $city    = Arr::get($a, 'address.city', $a['city'] ?? null);
            $country = Arr::get($a, 'address.country', $a['country'] ?? null);

            // Smoobu /apartments normalmente no trae foto; si viniera, la tomamos
            $thumb = Arr::get($a, 'picture.thumbnail', $a['thumbnail'] ?? null);
            if ($thumb && !str_starts_with($thumb, 'http')) {
                $thumb = asset($thumb);
            }

            return [
                '_id'     => $id ? (string) $id : null,
                'title'   => $title,
                'address' => ['city' => $city, 'country' => $country],
                'picture' => ['thumbnail' => $thumb],
            ];
        })
        ->filter(fn($p) => !empty($p['_id']))
        ->values();

        // Pasa también $featuredProperties a la vista del dashboard
        return view('admin.dashboard', compact('pagina', 'featuredProperties'));
    }
}
