<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\SmoobuClient;
use App\Models\SmoobuApartmentMeta;
use App\Models\SmoobuApartmentImage;

class HomeController extends Controller
{
    protected SmoobuClient $smoobu;

    public function __construct(SmoobuClient $smoobu)
    {
        $this->smoobu = $smoobu;
    }

   public function index()
{
    try {
        $api = $this->smoobu;

        // === Página / featured_property_id (lo necesitamos desde el inicio)
        $paginaModel = \App\Models\Pagina::with('meta')->find(1);
        $featuredId  = (int) ($paginaModel->featured_property_id ?? 0);

        // 1) Traer apartamentos desde Smoobu (cache 5 min)
        $apartments = \Illuminate\Support\Facades\Cache::remember(
            'smoobu.apartments',
            300,
            fn () => $api->apartments()
        );

        // Base: 12 para portada
        $apts = collect($apartments)->take(12)->values();

        // Asegurar que el "featuredId" esté presente y primero
        if ($featuredId) {
            $featInList = collect($apartments)->first(function ($a) use ($featuredId) {
                $aid = (int)($a['id'] ?? $a['apartmentId'] ?? $a['apartment_id'] ?? 0);
                return $aid === $featuredId;
            });

            if ($featInList) {
                // Lo movemos al inicio y quitamos duplicado
                $apts = collect([$featInList])
                    ->concat($apts->reject(function ($a) use ($featuredId) {
                        $aid = (int)($a['id'] ?? $a['apartmentId'] ?? $a['apartment_id'] ?? 0);
                        return $aid === $featuredId;
                    }))
                    ->unique(function ($a) {
                        return (int)($a['id'] ?? $a['apartmentId'] ?? $a['apartment_id'] ?? 0);
                    })
                    ->take(12)
                    ->values();
            } else {
                // No vino en la lista base: creamos un stub para forzarlo
                $apts = collect([['id' => $featuredId]])
                    ->concat($apts)
                    ->unique(function ($a) {
                        return (int)($a['id'] ?? $a['apartmentId'] ?? $a['apartment_id'] ?? 0);
                    })
                    ->take(12)
                    ->values();
            }
        }

        // IDs finales (incluye featured)
        $ids = $apts->map(function ($a) {
                return (int)($a['id'] ?? $a['apartmentId'] ?? $a['apartment_id'] ?? 0);
            })
            ->filter()
            ->values()
            ->all();

        // 2) Detalles de Smoobu (fallbacks para location/rooms/prices)
        $details = [];
        foreach ($ids as $aid) {
            $details[$aid] = \Illuminate\Support\Facades\Cache::remember(
                "smoobu.apartment.$aid",
                300,
                function () use ($api, $aid) {
                    try {
                        return $api->apartment((int)$aid);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('No se pudo obtener detalle de apartment', [
                            'id' => $aid,
                            'e'  => $e->getMessage()
                        ]);
                        return [];
                    }
                }
            );
        }

        // 3) Rates promedio 7 días (fallback 30 días)
        $ratesByApt = [];
        try {
            if (!empty($ids)) {
                $start = now()->toDateString();
                $end   = now()->addDays(7)->toDateString();

                $resp   = $api->rates($start, $end, $ids);
                $matrix = $resp['data'] ?? [];

                foreach ($ids as $aid) {
                    $rows   = $matrix[$aid] ?? [];
                    $prices = [];
                    foreach ($rows as $r) {
                        $p = $r['price'] ?? null;
                        if (is_numeric($p) && $p > 0) $prices[] = (float)$p;
                    }
                    $ratesByApt[$aid] = count($prices) ? round(array_sum($prices) / count($prices)) : null;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudieron obtener rates (7d)', ['e' => $e->getMessage()]);
            $ratesByApt = [];
        }

        if (!empty($ids) && empty(array_filter($ratesByApt, fn($v) => is_numeric($v) && $v > 0))) {
            try {
                $start2 = now()->toDateString();
                $end2   = now()->addDays(30)->toDateString();

                $resp2   = $api->rates($start2, $end2, $ids);
                $matrix2 = $resp2['data'] ?? [];

                foreach ($ids as $aid) {
                    if (isset($ratesByApt[$aid]) && $ratesByApt[$aid] > 0) continue;

                    $rows   = $matrix2[$aid] ?? [];
                    $prices = [];
                    foreach ($rows as $r) {
                        $p = $r['price'] ?? null;
                        if (is_numeric($p) && $p > 0) $prices[] = (float)$p;
                    }
                    $ratesByApt[$aid] = count($prices) ? round(array_sum($prices) / count($prices)) : null;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudieron obtener rates (30d)', ['e' => $e->getMessage()]);
            }
        }

        // 4) Overrides locales (meta + imágenes) para esos IDs
        $metas = \App\Models\SmoobuApartmentMeta::with(['images' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->whereIn('apartment_id', $ids)
            ->get()
            ->keyBy('apartment_id');

        // 5) Mapear al shape que espera la vista (API + overrides)
        $featuredProperties = $apts->map(function ($apt) use ($details, $ratesByApt, $metas, $featuredId) {
            $id = (int)($apt['id'] ?? $apt['apartmentId'] ?? $apt['apartment_id'] ?? 0);
            $d  = $details[$id] ?? [];
            /** @var \App\Models\SmoobuApartmentMeta|null $m */
            $m  = $metas->get($id);

            // Título
            $name = trim($m->title ?? ($d['name'] ?? ($apt['name'] ?? 'Propiedad')));

            // Ubicación (por defecto Galicia, España)
            $loc     = $d['location'] ?? [];
            $city    = $m->city ?? ($loc['city'] ?? 'Galicia');
            $country = $m->country ?? ($loc['country'] ?? 'España');

            // Rooms
            $roomsApi         = $d['rooms'] ?? [];
            $bedroomsFromApi  = $roomsApi['bedrooms']   ?? ($d['bedrooms'] ?? $d['numberOfBedrooms']  ?? 0);
            $bathroomsFromApi = $roomsApi['bathrooms']  ?? ($d['bathrooms'] ?? $d['numberOfBathrooms'] ?? 0);
            $bedrooms         = isset($m) && $m->bedrooms  !== null ? (int)$m->bedrooms   : (int)$bedroomsFromApi;
            $bathrooms        = isset($m) && $m->bathrooms !== null ? (float)$m->bathrooms : (float)$bathroomsFromApi;

            // Imagen (galería local -> cover local -> convención -> placeholder)
            $firstImagePath = $m?->images?->first()?->path;
            $thumb = $firstImagePath
                ? asset($firstImagePath)
                : ( $m?->cover_image_path
                    ? asset($m->cover_image_path)
                    : ($this->firstExistingAsset([
                        "images/smoobu/{$id}.webp",
                        "images/smoobu/{$id}.jpg",
                        "images/smoobu/{$id}.png",
                    ]) ?? asset('images/property-placeholder.jpg'))
                  );

            // Precio
            $priceOverride = $m?->base_price_override;
            $apiMinimal    = data_get($d, 'price.minimal');
            $apiMaximal    = data_get($d, 'price.maximal');

            $raw = (is_numeric($priceOverride) && $priceOverride > 0)
                ? (float)$priceOverride
                : ($ratesByApt[$id] ?? $apiMinimal ?? $apiMaximal ?? null);

            $price = (is_numeric($raw) && $raw > 0) ? (int)round($raw) : null;

            return [
                '_id'       => $id,
                'title'     => $name,
                'picture'   => ['thumbnail' => $thumb],
                'address'   => ['city' => $city, 'country' => $country],
                'bedrooms'  => $bedrooms,
                'bathrooms' => $bathrooms,
                'prices'    => ['basePrice' => $price],
                'rating'    => 5,
                'isFeatured'=> ($featuredId > 0 && $id === $featuredId), // ⬅️ útil en Blade
            ];
        })->values()->all();

        // 6) Imágenes destacadas para "featured-property" desde DB (puedes ajustarlo a usar $featuredId si quieres)
        $featuredImages = $this->getFeaturedImagesFromMetas($metas);

        // 7) Página/SEO (ya tenemos $paginaModel)
        $pagina = $paginaModel ?: new \App\Models\Pagina();
        $seo    = $paginaModel->meta ?? new \App\Models\PaginaMeta();

        return view('home', [
            'featuredProperties' => $featuredProperties,
            'featuredImages'     => $featuredImages,
            'pagina'             => $pagina,
            'seo'                => $seo,
            // 'featuredId'       => $featuredId, // opcional si quieres usarlo directo en Blade
        ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Error al cargar portada (API + DB)', ['e' => $e->getMessage()]);

        $paginaModel = \App\Models\Pagina::with('meta')->find(1);
        $pagina = $paginaModel ?: new \App\Models\Pagina();
        $seo    = $paginaModel->meta ?? new \App\Models\PaginaMeta();

        return view('home', [
            'featuredProperties' => [],
            'featuredImages'     => [],
            'pagina'             => $pagina,
            'seo'                => $seo,
        ]);
    }
}


    /**
     * Devuelve hasta 2 imágenes para la sección "featured-property" usando la galería de DB.
     * Si ninguna meta tiene imágenes, aplica fallbacks.
     *
     * @param \Illuminate\Support\Collection|array $metas
     */
    private function getFeaturedImagesFromMetas($metas): array
    {
        // Normaliza a colección
        $collection = collect($metas);

        // Toma la primera meta con imágenes activas
        $firstWithImages = $collection->first(function ($m) {
            return $m->images && $m->images->count() > 0;
        });

        $images = [];
        if ($firstWithImages) {
            $images = $firstWithImages->images->take(2)
                ->map(fn($img) => asset($img->path))
                ->values()
                ->all();
        }

        // Fallbacks si no alcanzan 2
        if (count($images) < 2) {
            $fallbacks = [
                asset('images/property-placeholder.jpg'),
                asset('images/property-placeholder-2.jpg'),
            ];
            foreach ($fallbacks as $f) {
                if (count($images) >= 2) break;
                $images[] = $f;
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
