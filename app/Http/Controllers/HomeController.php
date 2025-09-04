<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\SmoobuClient;

class HomeController extends Controller
{
    protected SmoobuClient $smoobu;

    public function __construct(SmoobuClient $smoobu)
    {
        $this->smoobu = $smoobu;
    }

/**
 * Mostrar la página de inicio
 */
public function index()
{
    try {
        $api = $this->smoobu; // Inyectado en el constructor

        // 1) Traer apartamentos (cache 5 min)
        $apartments = Cache::remember('smoobu.apartments', 300, function () use ($api) {
            return $api->apartments(); // [['id'=>..., 'name'=>...], ...]
        });

        // Hasta 12 para portada
        $apts = collect($apartments)->take(12)->values();
        $ids  = $apts->pluck('id')->all();

        // 2) Detalle por apartment (para bedrooms/bathrooms/location y fallback de precios)
        $details = [];
        foreach ($ids as $aid) {
            $details[$aid] = Cache::remember("smoobu.apartment.$aid", 300, function () use ($api, $aid) {
                try {
                    return $api->apartment((int)$aid);
                } catch (\Throwable $e) {
                    \Log::warning('No se pudo obtener detalle de apartment', ['id' => $aid, 'e' => $e->getMessage()]);
                    return [];
                }
            });
        }

        // 3) Rates: hoy -> +7 días  (tu shape: data[aptId][YYYY-MM-DD]['price'])
        $start = now()->toDateString();
        $end   = now()->addDays(7)->toDateString();

        $ratesByApt = [];
        try {
            if (!empty($ids)) {
                $resp   = $api->rates($start, $end, $ids);
                $matrix = $resp['data'] ?? [];

                foreach ($ids as $aid) {
                    $dateRows = $matrix[$aid] ?? [];
                    $prices   = [];
                    foreach ($dateRows as $row) {
                        $p = $row['price'] ?? null;
                        if (is_numeric($p) && $p > 0) $prices[] = (float)$p;
                    }
                    $ratesByApt[$aid] = count($prices)
                        ? round(array_sum($prices) / count($prices))
                        : null;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('No se pudieron obtener rates (7d)', ['e' => $e->getMessage()]);
            $ratesByApt = [];
        }

        // 4) Si todos quedaron null, intenta con +30 días
        if (!empty($ids) && empty(array_filter($ratesByApt, fn($v) => is_numeric($v) && $v > 0))) {
            try {
                $start2 = now()->toDateString();
                $end2   = now()->addDays(30)->toDateString();

                $resp2   = $api->rates($start2, $end2, $ids);
                $matrix2 = $resp2['data'] ?? [];

                foreach ($ids as $aid) {
                    if (isset($ratesByApt[$aid]) && $ratesByApt[$aid] > 0) continue;

                    $dateRows = $matrix2[$aid] ?? [];
                    $prices   = [];
                    foreach ($dateRows as $row) {
                        $p = $row['price'] ?? null;
                        if (is_numeric($p) && $p > 0) $prices[] = (float)$p;
                    }
                    $avg = count($prices) ? round(array_sum($prices) / count($prices)) : null;
                    $ratesByApt[$aid] = $avg;
                }
            } catch (\Throwable $e) {
                \Log::warning('No se pudieron obtener rates (30d)', ['e' => $e->getMessage()]);
            }
        }

        // 5) Mapear a la estructura que tu Blade espera (con bedrooms/bathrooms reales)
        $featuredProperties = $apts->map(function ($apt) use ($details, $ratesByApt) {
            $id   = $apt['id'] ?? null;
            $name = trim($apt['name'] ?? 'Propiedad');

            $d      = $details[$id] ?? [];
            $loc    = $d['location'] ?? [];
            $rooms  = $d['rooms']    ?? [];
            // Fallbacks defensivos por si el esquema de tu cuenta difiere
            $bedrooms  = $rooms['bedrooms']      ?? $d['bedrooms']        ?? $d['numberOfBedrooms']   ?? 0;
            $bathrooms = $rooms['bathrooms']     ?? $d['bathrooms']       ?? $d['numberOfBathrooms']  ?? 0;

            // Imagen local opcional
            $img = $this->firstExistingAsset([
                "images/smoobu/{$id}.webp",
                "images/smoobu/{$id}.jpg",
                "images/smoobu/{$id}.png",
            ]) ?? asset('images/property-placeholder.jpg');

            // Precio final: rate promedio -> minimal -> maximal -> null si <= 0
            $raw   = $ratesByApt[$id] ?? ($d['price']['minimal'] ?? ($d['price']['maximal'] ?? null));
            $price = (is_numeric($raw) && $raw > 0) ? (int) round($raw) : null;

            return [
                '_id'      => $id,
                'title'    => $name,
                'picture'  => ['thumbnail' => $img],
                'address'  => [
                    'city'    => $loc['city'] ?? null,
                    'country' => $loc['country'] ?? 'República Dominicana',
                ],
                'bedrooms'  => $bedrooms,
                'bathrooms' => $bathrooms,
                'prices'    => ['basePrice' => $price],
                'rating'    => 5,
            ];
        })->values()->all();

        // 6) Imágenes destacadas para la sección "featured-property"
        $featuredImages = $this->getFeaturedImagesFromLocal($featuredProperties);

        // 7) Página/SEO
        $paginaModel = \App\Models\Pagina::with('meta')->find(1);
        if (!$paginaModel) {
            $pagina = new \App\Models\Pagina();
            $seo    = new \App\Models\PaginaMeta();
        } else {
            $pagina = $paginaModel;
            $seo    = $paginaModel->meta ?? new \App\Models\PaginaMeta();
        }

        return view('home', [
            'featuredProperties' => $featuredProperties,
            'featuredImages'     => $featuredImages,
            'pagina'             => $pagina,
            'seo'                => $seo,
        ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Error al obtener propiedades Smoobu', ['e' => $e->getMessage()]);

        $paginaModel = \App\Models\Pagina::with('meta')->find(1);
        if (!$paginaModel) {
            $pagina = new \App\Models\Pagina();
            $seo    = new \App\Models\PaginaMeta();
        } else {
            $pagina = $paginaModel;
            $seo    = $paginaModel->meta ?? new \App\Models\PaginaMeta();
        }

        return view('home', [
            'featuredProperties' => [],
            'featuredImages'     => [],
            'pagina'             => $pagina,
            'seo'                => $seo,
        ]);
    }
}



    /**
     * Devuelve hasta 2 imágenes para la sección "featured-property".
     * Busca primero imágenes locales por ID de Smoobu y si no hay, usa placeholder.
     */
    private function getFeaturedImagesFromLocal(array $featuredProperties): array
    {
        $images = [];

        // 1) Intentar imágenes por ID (images/smoobu/{id}.*)
        foreach ($featuredProperties as $p) {
            $id = $p['_id'] ?? null;
            if (!$id) continue;

            $candidate = $this->firstExistingAsset([
                "images/smoobu/{$id}-1.jpg",
                "images/smoobu/{$id}-1.png",
                "images/smoobu/{$id}-1.webp",
                "images/smoobu/{$id}.jpg",
                "images/smoobu/{$id}.png",
                "images/smoobu/{$id}.webp",
            ]);

            if ($candidate) $images[] = $candidate;
            if (count($images) >= 2) break;
        }

        // 2) Si no alcanzan, tomar de tu set genérico
        if (count($images) < 2) {
            $fallbacks = [
                'images/property-placeholder.jpg',
                'images/property-placeholder-2.jpg',
            ];
            foreach ($fallbacks as $f) {
                $images[] = asset($f);
                if (count($images) >= 2) break;
            }
        }

        return array_slice($images, 0, 2);
    }

    /**
     * Devuelve asset() del primer archivo existente en /public según lista de rutas relativas
     */
    private function firstExistingAsset(array $relativePaths): ?string
    {
        foreach ($relativePaths as $rel) {
            $full = public_path($rel);
            if (file_exists($full)) {
                return asset($rel);
            }
        }
        return null;
    }

    /**
     * Mostrar la página Acerca de
     */
    public function about()
    {
        $pagina = \App\Models\Pagina::find(1);
        $contenido = \App\Models\Pagina::find(3);
        return view('about', compact('pagina', 'contenido'));
    }

    /**
     * Mostrar la página de contacto
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Mostrar la página de servicios y experiencias
     */
    public function services()
    {
        return view('services');
    }

    /**
     * Mostrar la página para propietarios
     */
    public function forOwners()
    {
        return view('for-owners');
    }

    /**
     * Mostrar la página de FAQ
     */
    public function faq()
    {
        return view('faq');
    }
}
