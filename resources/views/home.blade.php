@extends('layouts.app')

@section('title', 'Chuspombo Apartamentos')

@section('meta_description', 'Descubre apartamentos en Galicia, España con Chuspombo, tu socio confiable para experiencias de lujo inolvidables.')

@push('head')
    <meta name="google-site-verification" content="2jdAvl84H3j_D5bxpnQr-4fBXKrM4poobpPPTDT5fTU" />
@endpush

@section('content')


<style>

/* Compresión SOLO en móviles */
@media (max-width: 576px){
  /* Titulares del hero */
  .carousel-overlay h1{
    font-size: 1.6rem;      /* antes muy grande */
    line-height: 1.2;
    margin-bottom: .5rem;
  }
  .carousel-overlay h2{
    font-size: 1rem;
    line-height: 1.35;
    margin-bottom: .75rem;
    opacity: .95;
  }

  /* Caja de búsqueda (filtro) */
  .search-box{
    padding: 1rem !important;        /* menos padding */
    border-radius: .6rem;
  }
  .search-box .form-label{
    margin-bottom: .25rem;
    font-size: .9rem;
  }
  .search-box .form-control,
  .search-box .form-select{
    padding: .375rem .5rem;           /* inputs más compactos */
    font-size: .9rem;
  }
  .search-box .btn{
    padding: .5rem .75rem;            /* botón más bajo */
    font-size: .95rem;
  }
}

</style>

<!-- Hero Section -->
@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;

    // ===== PRIORIZAR GALERÍA INDEPENDIENTE =====
    $carouselImages = collect();

    // 1. Intentar usar la galería independiente del home
    if (!empty($pagina->gallery_images)) {
        $galleryData = is_string($pagina->gallery_images)
            ? json_decode($pagina->gallery_images, true)
            : $pagina->gallery_images;

        if (is_array($galleryData) && count($galleryData) > 0) {
            $carouselImages = collect($galleryData)->map(function ($item) {
                return [
                    'url' => asset('images/' . $item['image']),
                    'title' => $item['title'] ?? '',
                    'type' => 'gallery'
                ];
            });
        }
    }

    // 2. Fallback: usar imágenes de propiedades (código original)
    if ($carouselImages->isEmpty()) {
        $imagesPerProperty = 3;

        // Extensiones válidas y strings a evitar (placeholders)
        $validExt   = '/\.(jpe?g|png|webp|avif)(\?.*)?$/i';
        $skipSubstr = ['placeholder','default','noimage','missing','image-not-found'];

        // Ranking: banner/hero/cover/main/original/large primero; thumbs al final
        $priorityRank = function (string $u) {
            if (preg_match('/banner|hero|cover|main|original|large/i', $u)) return 0;
            if (preg_match('/thumb|thumbnail|small|icon/i', $u))       return 2;
            return 1;
        };

        // Extraer URLs con extensión desde cualquier nivel del array de la propiedad
        $extractFromArray = function ($p) use ($validExt, $skipSubstr, $priorityRank) {
            $flat = collect(Arr::dot((array) $p))
                ->filter(fn ($v) => is_string($v))
                ->map(fn ($v) => trim($v))
                ->values();

            return $flat
                ->filter(fn ($u) => $u !== '' && !str_starts_with($u, 'data:') && preg_match($validExt, $u))
                ->reject(function ($u) use ($skipSubstr) {
                    $lu = strtolower($u);
                    foreach ($skipSubstr as $s) { if (str_contains($lu, $s)) return true; }
                    return false;
                })
                ->unique()
                ->sortBy(fn ($u) => $priorityRank($u))
                ->values();
        };

        // IDs de propiedades para consultar galería en DB
        $apartmentIds = collect($featuredProperties ?? [])->pluck('_id')->filter()->unique()->values();

        // Galería por propiedad desde DB
        $galleryByApartment = collect();
        if ($apartmentIds->isNotEmpty()) {
            $rows = DB::table('smoobu_apartment_images')
                ->select('apartment_id', 'path', 'sort_order', 'is_active')
                ->whereIn('apartment_id', $apartmentIds)
                ->where('is_active', 1)
                ->orderBy('apartment_id')
                ->orderBy('sort_order')
                ->get();

            $galleryByApartment = collect($rows)->groupBy('apartment_id')->map(function ($rows) use ($validExt, $skipSubstr) {
                return collect($rows)->pluck('path')->map(function ($p) {
                    $p = is_string($p) ? trim($p) : '';
                    if ($p === '') return null;
                    // Si es relativo, conviértelo a URL absoluta
                    return str_starts_with($p, 'http') ? $p : asset($p);
                })
                ->filter(fn ($u) => is_string($u) && preg_match('/\.(jpe?g|png|webp|avif)(\?.*)?$/i', $u))
                ->reject(function ($u) use ($skipSubstr) {
                    $lu = strtolower($u);
                    foreach ($skipSubstr as $s) { if (str_contains($lu, $s)) return true; }
                    return false;
                })
                ->values();
            });
        }

        // Hasta 3 imágenes por propiedad (array + DB). Omitir propiedades sin fotos.
        $randomImages = collect($featuredProperties ?? [])
            ->flatMap(function ($p) use ($extractFromArray, $galleryByApartment, $imagesPerProperty, $priorityRank) {
                $pid     = $p['_id'] ?? null;
                $arrImgs = $extractFromArray($p);
                $dbImgs  = ($pid !== null && $galleryByApartment->has($pid)) ? $galleryByApartment->get($pid) : collect();

                $merged = $arrImgs
                    ->merge($dbImgs)
                    ->unique()
                    ->sortBy(fn ($u) => $priorityRank($u))
                    ->take($imagesPerProperty)
                    ->values();

                return $merged;
            })
            ->unique()
            ->values();

        // Convertir a formato consistente
        $carouselImages = $randomImages->map(function ($url) {
            return [
                'url' => $url,
                'title' => '',
                'type' => 'property'
            ];
        });
    }
@endphp

@if($carouselImages->count() > 0)
    <div class="carousel-container position-relative">
        <!-- Carrusel -->
        <div id="carouselProperties" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($carouselImages as $index => $imageData)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $imageData['url'] }}" class="d-block w-100 carousel-image"
                             alt="{{ $imageData['title'] ?: 'Chuspombo Apartamentos' }}">
                        @if(!empty($imageData['title']) && $imageData['type'] === 'gallery')
                            <div class="carousel-caption d-none d-md-block">
                                <p class="fs-5 fw-semibold">{{ $imageData['title'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Solo mostrar controles si hay más de una imagen --}}
            @if($carouselImages->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselProperties" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselProperties" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            @endif

            {{-- Indicadores solo si hay más de una imagen --}}
            @if($carouselImages->count() > 1)
                <div class="carousel-indicators">
                    @foreach($carouselImages as $index => $imageData)
                        <button type="button" data-bs-target="#carouselProperties"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"
                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Contenido superpuesto -->
        <div class="carousel-overlay text-center">
            <h1 class="display-4 fw-bold text-white">
                {{ $pagina->h1 ?? 'Descubre Propiedades Exclusivas' }}
            </h1>

            <h2 class="lead text-white">
                {{ $pagina->h2_1 ?? 'Explora apartamentos de lujo en Galicia, España' }}
            </h2>

            <div class="search-box-overlay">
                <div class="container">
                    <div class="search-box p-4 shadow rounded">
                        <form action="{{ route('properties.index') }}" method="GET">
                            <div class="row g-3 justify-content-center">

                                {{-- Select: Apartamento (indexa como en featured properties) --}}
                                <div class="col-md-4">
                                    <label for="apartment" class="form-label">Apartamento</label>
                                    <select id="apartment" name="Apartamento" class="form-select">
                                        <option value="">Todos los apartamentos</option>
                                        @foreach(($featuredProperties ?? []) as $property)
                                            @php
                                                $id   = $property['_id'] ?? null;
                                                $tit  = $property['title'] ?? 'Sin título';
                                                $city = $property['address']['city'] ?? null;
                                                $ctry = $property['address']['country'] ?? null;
                                                $loc  = trim(($city ? $city : '') . ($city && $ctry ? ', ' : '') . ($ctry ? $ctry : ''));
                                            @endphp
                                            @if($id)
                                                <option value="{{ $id }}">
                                                    {{ $tit }}@if($loc) — {{ $loc }}@endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="checkin" class="form-label">Llegada</label>
                                    <input type="date" class="form-control" id="checkin" name="checkin" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="checkout" class="form-label">Salida</label>
                                    <input type="date" class="form-control" id="checkout" name="checkout" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- /overlay -->
    </div><!-- /carousel-container -->
@else
    <p class="text-center text-danger">No se encontraron imágenes de Chuspombo Apartamentos.</p>
@endif

<!-- Featured Properties -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">
                    {{ $pagina->h2_propiedades ?? 'Propiedades destacadas en Galicia, España' }}
                </h2>
                <p class="text-muted">
                    {{ $pagina->p_propiedades ?? 'Descubre nuestras propiedades exclusivas en los destinos más deseados de Galicia.' }}
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('properties.index') }}" class="btn btn-outline-primary">Ver todos</a>
            </div>
        </div>

        <div class="row g-4 justify-content-center">

            @forelse($featuredProperties as $property)
                @php
                    $thumb = $property['picture']['thumbnail'] ?? asset('images/property-placeholder.jpg');
                    $city = $property['address']['city'] ?? null;
                    $country = $property['address']['country'] ?? null;
                    $location = trim(($city ? $city : '') . ($city && $country ? ', ' : '') . ($country ? $country : ''));
                    $bedrooms = $property['bedrooms'] ?? 0;
                    $bathrooms = $property['bathrooms'] ?? 0;
                    $price = $property['prices']['basePrice'] ?? null;
                @endphp

<div class="col-lg-3 col-md-6 mb-4 d-flex">
    <div class="card h-100 property-card w-100">

                        <img src="{{ $thumb }}" class="card-img-top" alt="{{ $property['title'] }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $property['title'] }}</h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $location !== '' ? $location : 'Galicia, España' }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-bed me-1"></i> {{ $bedrooms }}
                                    <i class="fas fa-bath ms-2 me-1"></i> {{ $bathrooms }}
                                </div>
                                <strong>
                                    @if(is_numeric($price))
                                        €{{ $price }}/noche
                                    @else
                                        Consultar
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="{{ route('properties.show', $property['_id']) }}"
                               class="btn btn-outline-primary w-100">Ver disponibilidad</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p>No hay propiedades disponibles en este momento.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5" style="background-color: #f8f9fa; background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-lg-8 mx-auto">
                <h2 class="fw-bold" style="color: #1c2d41;">
                    {{ $pagina->h2_hostella ?? 'Experiencias de lujo en Galicia, España' }}
                </h2>
                <p class="text-muted">
                    {{ $pagina->p_hostella ?? '¿Por qué elegir nuestras propiedades en Galicia, España?' }}
                </p>
            </div>
        </div>

        <div class="row g-4">
            @for ($i = 1; $i <= 3; $i++)
                <div class="col-lg-4">
                    <div class="benefit-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            @php
                                $imageField = 'card1_image_' . $i;
                                $titleField = 'card1_title_' . $i;
                                $contentField = 'card1_content_' . $i;

                                $defaultTitles = [
                                    1 => 'Ubicación Premium',
                                    2 => 'Confort Moderno',
                                    3 => 'Experiencia Auténtica'
                                ];

                                $defaultContent = [
                                    1 => 'En las mejores zonas turísticas de la región, cerca de playas, centros históricos y gastronomía de alto nivel.',
                                    2 => 'Propiedades equipadas con todas las comodidades modernas para una estancia perfecta.',
                                    3 => 'Vive como local con acceso a cultura, tradiciones y paisajes únicos de Galicia, España.'
                                ];
                            @endphp

                            @if (!empty($pagina->$imageField))
                                <img
                                    src="{{ asset('images/' . $pagina->$imageField) }}"
                                    alt="Imagen tarjeta {{ $i }}"
                                    class="mb-4 img-fluid"
                                    style="max-height: 120px; object-fit: contain; width: 100%; max-width: 100%;">
                            @else
                                <div class="feature-icon text-white rounded-circle mb-4">
                                    @if ($i === 1)
                                        <i class="fas fa-map-marker-alt fa-2x"></i>
                                    @elseif ($i === 2)
                                        <i class="fas fa-home fa-2x"></i>
                                    @else
                                        <i class="fas fa-star fa-2x"></i>
                                    @endif
                                </div>
                            @endif

                            <h4 class="card-title">
                                {{ $pagina->$titleField ?? $defaultTitles[$i] }}
                            </h4>
                            <p class="text-muted">
                                {{ $pagina->$contentField ?? $defaultContent[$i] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- Sección de Propiedad Destacada -->
@if(count($featuredProperties) > 0)
    @php
        $property = $featuredProperties[0];
    @endphp

    <section class="featured-property py-5">
        <div class="container">
            <div class="row align-items-center">
                <!-- Columna de texto -->
                <div class="col-md-6 text-section">
                    <p class="text-muted">{{ $pagina->p_lugar_favorito ?? 'La favorita de nuestros huéspedes en Galicia, España.' }}</p>
                    <div class="stars">
                        @php $rating = $property['rating'] ?? 5; @endphp
                        @for ($i = 0; $i < $rating; $i++) ★ @endfor
                    </div>
                    <h2 class="property-title">{{ $property['title'] ?? 'Propiedad Premium' }}</h2>
                    <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-primary">Ver disponibilidad</a>
                </div>

                <!-- Columna de imágenes -->
                <div class="col-md-6 images-section">
                    <div class="image-wrapper">
                        <img src="{{ $featuredImages[0] ?? asset('images/property-placeholder.jpg') }}"
                             class="small-image" alt="Interior propiedad">
                        <img src="{{ $featuredImages[1] ?? asset('images/property-placeholder.jpg') }}"
                             class="large-image" alt="Vista exterior">
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

<!-- Por qué los huéspedes confían en Chuspombo -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-lg-8 mx-auto">
                <h2 class="fw-bold">
                    {{ $pagina->h2_confiar ?? '¿Por qué elegir Chuspombo?' }}
                </h2>
                <p class="text-muted">
                    {{ $pagina->p_confiar ?? 'Valores que nos convierten en tu mejor opción en Galicia, España.' }}
                </p>
            </div>
        </div>

        <div class="row g-4 text-center">
            @for ($i = 4; $i <= 7; $i++)
                <div class="col-lg-3 col-md-6">
                    @php
                        $title = "card2_title_$i";
                        $content = "card2_content_$i";
                        $image = "card2_image_$i";

                        $defaultTitles2 = [
                            4 => 'Limpieza Impecable',
                            5 => 'Atención Personalizada',
                            6 => 'Ubicación Estratégica',
                            7 => 'Precios Justos'
                        ];

                        $defaultContent2 = [
                            4 => 'Mantenemos altos estándares de limpieza y desinfección en todas nuestras propiedades.',
                            5 => 'Atención ágil y dedicada para que tu estancia sea memorable.',
                            6 => 'Ubicaciones ideales cerca de playas, atracciones y servicios.',
                            7 => 'Tarifas competitivas sin comprometer calidad y confort.'
                        ];
                    @endphp

                    <div class="feature-card h-100 p-4 border-0 shadow-sm bg-white rounded">
                        @if (!empty($pagina->{$image}))
                            <img src="{{ asset('images/' . $pagina->{$image}) }}" alt="Imagen tarjeta {{ $i }}"
                                 style="max-height: 100px; object-fit: contain; width: 100%; max-width: 100%;" class="mb-4">
                        @else
                            <div class="feature-icon rounded-circle text-white mb-4 bg-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                @switch($i)
                                    @case(4) <i class="fas fa-broom fa-2x"></i> @break
                                    @case(5) <i class="fas fa-concierge-bell fa-2x"></i> @break
                                    @case(6) <i class="fas fa-map-marked-alt fa-2x"></i> @break
                                    @case(7) <i class="fas fa-euro-sign fa-2x"></i> @break
                                @endswitch
                            </div>
                        @endif

                        <h5 class="feature-title">{{ $pagina->{$title} ?? $defaultTitles2[$i] }}</h5>
                        <p class="feature-text text-muted">{{ $pagina->{$content} ?? $defaultContent2[$i] }}</p>
                    </div>
                </div>
            @endfor
        </div>

        <div class="row text-center mt-5">
            <div class="col-lg-12">
                <a href="{{ route('about') }}" class="animated-button">
                    <span>Descubre más sobre Galicia</span>
                    <span></span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
