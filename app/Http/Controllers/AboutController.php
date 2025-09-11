<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AboutController extends Controller
{
    protected $smoobu;

    // Ajusta el tipo del servicio a tu clase real (p.ej. \App\Services\SmoobuApi)
    public function __construct(\App\Services\Smoobu $smoobu)
    {
        $this->smoobu = $smoobu;
    }

    /**
     * Muestra la página de "Nosotros"
     */
    public function index(Request $request)
    {
        try {
            // 1) Traer apartamentos desde Smoobu (cache 5 min)
            $apartments = Cache::remember('smoobu.apartments', 300, function () {
                return $this->smoobu->apartments(); // ej: [['id'=>..., 'name'=>...], ...]
            });

            $apartments = collect($apartments ?? []);

            // 2) Opcional: leer imágenes por apartment desde la tabla (si la tienes)
            $ids = $apartments->pluck('id')->filter()->unique()->values()->all();

            $imagesByApt = collect();
            if (!empty($ids)) {
                $rows = DB::table('smoobu_apartment_images')
                    ->select('apartment_id','path','sort_order','is_active')
                    ->whereIn('apartment_id', $ids)
                    ->where('is_active', 1)
                    ->orderBy('apartment_id')
                    ->orderBy('sort_order')
                    ->get();

                $imagesByApt = collect($rows)->groupBy('apartment_id')->map(function ($rows) {
                    return collect($rows)->pluck('path')->map(function ($p) {
                        $p = is_string($p) ? trim($p) : '';
                        if ($p === '') return null;
                        // Si es relativo, vuelve URL absoluta
                        return str_starts_with($p, 'http') ? $p : asset($p);
                    })->filter()->values();
                });
            }

            // 3) Mapear al formato que la vista espera
            $properties = $apartments->map(function ($apt) use ($imagesByApt) {
                $id   = $apt['id'] ?? null;
                $name = trim($apt['name'] ?? 'Propiedad');

                // thumbnail local rápido (no imprescindible para el pool, pero útil)
                $thumb = (function () use ($id) {
                    foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.png"] as $rel) {
                        if ($id && file_exists(public_path($rel))) return asset($rel);
                    }
                    return asset('images/property-placeholder.jpg');
                })();

                // Lista de imágenes opcionales para que la vista también encuentre por array
                $pictures = $imagesByApt->get($id, collect())->values()->all();

                return [
                    '_id'     => $id,
                    'title'   => $name,
                    'picture' => ['thumbnail' => $thumb],
                    // Estas claves ayudan a la vista a encontrar más URLs además del escaneo local:
                    'pictures' => $pictures, // <- importante si quieres reforzar el pool
                    'gallery'  => $pictures,
                    'images'   => $pictures,
                ];
            })->values()->all();

            Log::info('[AboutController] properties construidas', ['count' => count($properties)]);

            return view('about', [
                'properties' => $properties,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en AboutController@index', ['e' => $e->getMessage()]);
            return view('about', [
                'properties' => [],
            ]);
        }
    }
}
