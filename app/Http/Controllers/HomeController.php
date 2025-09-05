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

        // 1) Traer apartamentos desde Smoobu (cache 5 min), como hacías originalmente
        $apartments = Cache::remember('smoobu.apartments', 300, fn () => $api->apartments());

        // Limitar a 12 para portada
        $apts = collect($apartments)->take(12)->values();
        $ids  = $apts->pluck('id')->filter()->map(fn($i) => (int)$i)->all();

        // 2) Cargar detalles de Smoobu (fallbacks para location/rooms/prices)
        $details = [];
        foreach ($ids as $aid) {
            $details[$aid] = Cache::remember("smoobu.apartment.$aid", 300, function () use ($api, $aid) {
                try {
                    return $api->apartment((int)$aid);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo obtener detalle de apartment', ['id' => $aid, 'e' => $e->getMessage()]);
                    return [];
                }
            });
        }

        // 3) Rates promedio 7 días (fallback 30 días) — como antes
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
            Log::warning('No se pudieron obtener rates (7d)', ['e' => $e->getMessage()]);
            $ratesByApt = [];
        }

        // Fallback: 30 días si todo quedó null/0
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
                Log::warning('No se pudieron obtener rates (30d)', ['e' => $e->getMessage()]);
            }
        }

        // 4) Cargar overrides locales (meta + imágenes) para esos IDs
        $metas = SmoobuApartmentMeta::with(['images' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->whereIn('apartment_id', $ids)
            ->get()
            ->keyBy('apartment_id');

        // 5) Mapear al shape que espera la vista: API + overrides locales
        $featuredProperties = $apts->map(function ($apt) use ($details, $ratesByApt, $metas) {
            $id = (int)($apt['id'] ?? 0);
            $d  = $details[$id] ?? [];
            /** @var SmoobuApartmentMeta|null $m */
            $m  = $metas->get($id);

            // Título
            $name = trim($m->title ?? ($d['name'] ?? ($apt['name'] ?? 'Propiedad')));

            // Ubicación
            $loc     = $d['location'] ?? [];
            $city    = $m->city ?? ($loc['city'] ?? null);
            $country = $m->country ?? ($loc['country'] ?? 'República Dominicana');

            // Rooms (preferir valores locales si están seteados)
            $roomsApi           = $d['rooms'] ?? [];
            $bedroomsFromApi    = $roomsApi['bedrooms']      ?? ($d['bedrooms']       ?? $d['numberOfBedrooms']   ?? 0);
            $bathroomsFromApi   = $roomsApi['bathrooms']     ?? ($d['bathrooms']      ?? $d['numberOfBathrooms']  ?? 0);
            $bedrooms           = isset($m) && $m->bedrooms  !== null ? (int)$m->bedrooms  : (int)$bedroomsFromApi;
            $bathrooms          = isset($m) && $m->bathrooms !== null ? (float)$m->bathrooms : (float)$bathroomsFromApi;

            // Imagen (galería local -> cover local -> archivo local por convención -> placeholder)
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

            // Precio (override local > rates promedio > minimal/maximal API > null)
            $priceOverride = $m?->base_price_override;
            $apiMinimal    = data_get($d, 'price.minimal');
            $apiMaximal    = data_get($d, 'price.maximal');

            $raw = (is_numeric($priceOverride) && $priceOverride > 0)
                ? (float)$priceOverride
                : ($ratesByApt[$id] ?? $apiMinimal ?? $apiMaximal ?? null);

            $price = (is_numeric($raw) && $raw > 0) ? (int)round($raw) : null;

            return [
                '_id'      => $id,
                'title'    => $name,
                'picture'  => ['thumbnail' => $thumb],
                'address'  => [
                    'city'    => $city,
                    'country' => $country,
                ],
                'bedrooms'  => $bedrooms,
                'bathrooms' => $bathrooms,
                'prices'    => ['basePrice' => $price],
                'rating'    => 5,
            ];
        })->values()->all();

        // 6) Imágenes destacadas para "featured-property" desde DB
        $featuredImages = $this->getFeaturedImagesFromMetas($metas);

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
        Log::error('Error al cargar portada (API + DB)', ['e' => $e->getMessage()]);

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
