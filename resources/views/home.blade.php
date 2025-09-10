@extends('layouts.app')

@section('title', 'Chuspombo - Apartamentos y villas en Galicia, España')

@section('meta_description', 'Descubre apartamentos y villas exclusivas en Galicia, España con Chuspombo, tu socio confiable para experiencias de lujo inolvidables.')

@section('content')
<!-- Hero Section -->
@php
    $imagesToShow = 5;

    // 1) Buscar imágenes en featuredProperties
    $candidates = collect($featuredProperties ?? [])
        ->flatMap(function ($p) {
            $title = $p['title'] ?? 'Propiedad Chuspombo';
            $urls = [];

            if (!empty($p['picture']) && is_array($p['picture'])) {
                foreach (['banner','large','url','original','full','thumbnail'] as $k) {
                    if (!empty($p['picture'][$k])) $urls[] = $p['picture'][$k];
                }
            }

            foreach (['pictures','gallery','images'] as $listKey) {
                if (!empty($p[$listKey]) && is_array($p[$listKey])) {
                    foreach ($p[$listKey] as $u) $urls[] = $u;
                }
            }

            return collect($urls)
                ->filter(fn ($u) => is_string($u) && preg_match('/\.(jpe?g|png|webp|avif)$/i', $u))
                ->map(fn ($u) => ['url' => $u, 'alt' => $title]);
        })
        ->unique('url')
        ->sortBy(function ($img) {
            $u = $img['url'];
            return preg_match('/banner|hero|cover/i', $u) ? 0 : 1;
        })
        ->values();

    $propertyImages = $candidates->take(20)->shuffle()->take($imagesToShow)->values();

    // 2) Fallback a public_html/images si no hay
    if ($propertyImages->isEmpty()) {
        $fallback = collect();
        for ($i = 1; $i <= 100; $i++) {
            $path = base_path("public_html/images/CHUSPOMBO-APARTAMENTOS-{$i}.webp");
            if (file_exists($path)) {
                $fallback->push([
                    'url' => asset("images/CHUSPOMBO-APARTAMENTOS-{$i}.webp"),
                    'alt' => 'Chuspombo Apartamentos'
                ]);
            }
        }
        $propertyImages = $fallback->shuffle()->take($imagesToShow)->values();
    }
@endphp

<style>
  /* Contenedor del hero: fija una relación o altura para todos los slides */
  .hero-aspect {
    position: relative;
    width: 100%;
    aspect-ratio: 21 / 9;   /* usa 16/9 si prefieres; también puedes quitar y usar height fija */
    max-height: 720px;      /* opcional */
    min-height: 360px;      /* opcional */
    overflow: hidden;
  }

  /* Cada slide se pinta como background */
  .hero-slide {
    width: 100%;
    height: 100%;
    background-size: cover;       /* clave: ignora el aspect ratio de origen */
    background-position: center;
    background-repeat: no-repeat;
  }

  .carousel-overlay {
    position: absolute; inset: 0;
    display: grid; place-items: center;
    padding: 1.5rem;
    background: linear-gradient(to top, rgba(0,0,0,.35), rgba(0,0,0,.12));
  }
  .search-box-overlay .search-box { background: rgba(255,255,255,.92); }
</style>

@if($propertyImages->count() > 0)
    <style>
        .carousel-image {
            height: clamp(320px, 55vh, 680px);
            object-fit: cover;
            object-position: center;
        }
        .carousel-overlay {
            position: absolute; inset: 0;
            display: grid; place-items: center;
            padding: 1.5rem;
            background: linear-gradient(to top, rgba(0,0,0,.45), rgba(0,0,0,.15));
        }
        .search-box-overlay .search-box { background: rgba(255,255,255,.9); }
    </style>

    <div class="carousel-container position-relative">
        <div id="carouselProperties" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($propertyImages as $index => $img)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $img['url'] }}" class="d-block w-100 carousel-image" alt="{{ $img['alt'] }}">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProperties" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselProperties" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>

        <div class="carousel-overlay text-center">
            <div class="container">
                <h1 class="display-4 fw-bold text-white mb-2">
                    {{ $pagina->h1 ?? 'Descubre Propiedades Exclusivas' }}
                </h1>

                <h2 class="lead text-white mb-4">
                    {{ $pagina->h2_1 ?? 'Explora villas y apartamentos de lujo en Galicia, España' }}
                </h2>

                <div class="search-box-overlay">
                    <div class="container">
                        <div class="search-box p-4 shadow rounded">
                            <form action="{{ route('properties.index') }}" method="GET">
                                <div class="row g-3 justify-content-center">
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
            </div>
        </div>
    </div>
@else
    <p class="text-center text-danger">No se encontraron imágenes para el banner.</p>
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

        <div class="row">
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

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 property-card">
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
                            <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-outline-primary w-100">Ver disponibilidad</a>
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
                    {{ $pagina->h2_chuspombo ?? 'Experiencias de lujo en Galicia, España' }}
                </h2>
                <p class="text-muted">
                    {{ $pagina->p_chuspombo ?? '¿Por qué elegir nuestras propiedades en Galicia, España?' }}
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
                                <img src="{{ asset('images/' . $pagina->$imageField) }}" alt="Imagen tarjeta {{ $i }}"
                                     class="mb-4 img-fluid" style="max-height: 120px; object-fit: contain; width: 100%; max-width: 100%;">
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

                            <h4 class="card-title">{{ $pagina->$titleField ?? $defaultTitles[$i] }}</h4>
                            <p class="text-muted">{{ $pagina->$contentField ?? $defaultContent[$i] }}</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>

<!-- Sección de Propiedad Destacada -->
@if(count($featuredProperties) > 0)
    @php $property = $featuredProperties[0]; @endphp
    <section class="featured-property py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-section">
                    <p class="text-muted">{{ $pagina->p_lugar_favorito ?? 'La favorita de nuestros huéspedes en Galicia, España.' }}</p>
                    <div class="stars">
                        @php $rating = $property['rating'] ?? 5; @endphp
                        @for ($i = 0; $i < $rating; $i++) ★ @endfor
                    </div>
                    <h2 class="property-title">{{ $property['title'] ?? 'Propiedad Premium' }}</h2>
                    <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-primary">Ver disponibilidad</a>
                </div>
                <div class="col-md-6 images-section">
                    <div class="image-wrapper">
                        <img src="{{ $featuredImages[0] ?? asset('images/property-placeholder.jpg') }}" class="small-image" alt="Interior propiedad">
                        <img src="{{ $featuredImages[1] ?? asset('images/property-placeholder.jpg') }}" class="large-image" alt="Vista exterior">
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
                <h2 class="fw-bold">{{ $pagina->h2_confiar ?? '¿Por qué elegir Chuspombo?' }}</h2>
                <p class="text-muted">{{ $pagina->p_confiar ?? 'Valores que nos convierten en tu mejor opción en Galicia, España.' }}</p>
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
