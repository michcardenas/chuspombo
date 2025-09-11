@extends('layouts.app')

@section('title', 'Chuspombo - Apartamentos y villas en Galicia, España')

@section('meta_description', 'Descubre apartamentos y villas exclusivas en Galicia, España con Chuspombo, tu socio confiable para experiencias de lujo inolvidables.')

@section('content')
<!-- Hero Section -->
@php
    $imagesToShow = 5;

    // Validaciones de URL de imagen
    $validExt = '/\.(jpe?g|png|webp|avif)(\?.*)?$/i';
    $skipSubstr = ['placeholder', 'default', 'noimage', 'missing', 'image-not-found'];

    // Helper: extraer URLs de imágenes válidas desde una propiedad
    $extractImages = function ($p) use ($validExt, $skipSubstr) {
        $urls = [];

        if (!empty($p['picture']) && is_array($p['picture'])) {
            foreach (['banner','large','url','original','full','thumbnail'] as $k) {
                if (!empty($p['picture'][$k]) && is_string($p['picture'][$k])) {
                    $urls[] = $p['picture'][$k];
                }
            }
        }

        foreach (['pictures','gallery','images'] as $listKey) {
            if (!empty($p[$listKey]) && is_array($p[$listKey])) {
                foreach ($p[$listKey] as $u) {
                    if (is_string($u)) $urls[] = $u;
                }
            }
        }

        return collect($urls)
            ->filter(fn ($u) => is_string($u) && $u !== '' && !str_starts_with($u, 'data:') && preg_match($validExt, $u))
            ->reject(function ($u) use ($skipSubstr) {
                $lu = strtolower($u);
                foreach ($skipSubstr as $s) {
                    if (str_contains($lu, $s)) return true; // descarta placeholders
                }
                return false;
            })
            ->values()
            ->all();
    };

    // 1) Solo considerar propiedades que tengan al menos una imagen válida
    $propsWithImages = collect($featuredProperties ?? [])->filter(
        fn ($p) => count($extractImages($p)) > 0
    );

    // 2) Construir la lista de candidatos SOLO con imágenes válidas
    $candidates = $propsWithImages
        ->flatMap(function ($p) use ($extractImages) {
            $title = $p['title'] ?? 'Propiedad Chuspombo';
            return collect($extractImages($p))->map(fn ($u) => ['url' => $u, 'alt' => $title]);
        })
        ->unique('url')
        ->sortBy(function ($img) {
            $u = $img['url'];
            // Prioriza posibles banners/hero
            return preg_match('/banner|hero|cover/i', $u) ? 0 : 1;
        })
        ->values();

    // 3) Selección final para el carrusel
    $propertyImages = $candidates->take(20)->shuffle()->take($imagesToShow)->values();

    // 4) Fallback a /public_html/images si no se consiguió nada
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


@if($propertyImages->count() > 0)
<section class="pb-4 pb-md-5">
  {{-- Carrusel sin CSS custom: cada slide es un <img> responsive --}}
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
      @foreach($propertyImages as $index => $img)
        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
          <img
            src="{{ $img['url'] }}"
            alt="{{ $img['alt'] ?? 'Banner' }}"
            class="d-block w-100 img-fluid"
            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
            decoding="async"
            draggable="false">
        </div>
      @endforeach
    </div>

    @if($propertyImages->count() > 1)
      {{-- Controles: ocultos en pantallas chicas para evitar solapes --}}
      <button class="carousel-control-prev d-none d-lg-flex" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Anterior">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      </button>
      <button class="carousel-control-next d-none d-lg-flex" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Siguiente">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
      </button>

      {{-- Indicadores (opcionales) --}}
      <div class="carousel-indicators d-none d-md-flex">
        @foreach($propertyImages as $i => $img)
          <button type="button"
                  data-bs-target="#heroCarousel"
                  data-bs-slide-to="{{ $i }}"
                  class="{{ $i === 0 ? 'active' : '' }}"
                  aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Contenido del “hero” debajo del carrusel (sin overlay, sin CSS) --}}
  <div class="container mt-3 mt-md-4">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 text-center">
        <h1 class="fw-bold">{{ $pagina->h1 ?? 'Descubre Propiedades Exclusivas' }}</h1>
        <p class="lead text-muted mb-3 mb-md-4">{{ $pagina->h2_1 ?? 'Explora villas y apartamentos de lujo en Galicia, España' }}</p>

        <div class="card border-0 shadow-lg">
          <div class="card-body p-3 p-md-4">
            <form action="{{ route('properties.index') }}" method="GET">
              <div class="row g-3 justify-content-center">
                <div class="col-xl-3 col-lg-4 col-md-6">
                  <label class="form-label fw-semibold d-block">Tipo de propiedad</label>
                  <span class="badge bg-primary fs-6 px-3 py-2">Apartamento</span>
                  <input type="hidden" name="property_type" value="apartment">
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                  <label for="checkin" class="form-label fw-semibold">Fecha de llegada</label>
                  <input type="date" class="form-control form-control-lg" id="checkin" name="checkin" min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6">
                  <label for="checkout" class="form-label fw-semibold">Fecha de salida</label>
                  <input type="date" class="form-control form-control-lg" id="checkout" name="checkout" min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-xl-3 col-lg-12 col-md-6 d-grid">
                  <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Buscar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
@else
  <div class="container py-5">
    <div class="alert alert-warning text-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      No se encontraron imágenes para el banner.
    </div>
  </div>
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
                <a href="{{ route('properties.index', ['property_type' => 'apartment']) }}" class="btn btn-outline-primary">Ver todos</a>
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
                    <div class="card h-100 property-card border-0 shadow-sm">
                        <div class="position-relative">
                            <img src="{{ $thumb }}" class="card-img-top" alt="{{ $property['title'] }}" style="height: 250px; object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $property['title'] }}</h5>
                            <p class="card-text text-muted mb-3">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $location !== '' ? $location : 'Galicia, España' }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    <i class="fas fa-bed me-1"></i> {{ $bedrooms }}
                                    <i class="fas fa-bath ms-2 me-1"></i> {{ $bathrooms }}
                                </div>
                                <div class="fw-bold text-primary">
                                    @if(is_numeric($price))
                                        €{{ number_format($price, 0, ',', '.') }}/noche
                                    @else
                                        Consultar
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-outline-primary w-100">Ver disponibilidad</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-home fa-3x mb-3"></i>
                        <p>No hay propiedades disponibles en este momento.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
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
                    <div class="card h-100 border-0 shadow-sm">
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
                                     class="mb-4 img-fluid" style="max-height: 120px; object-fit: contain;">
                            @else
                                <div class="feature-icon bg-primary text-white rounded-circle mb-4 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    @if ($i === 1)
                                        <i class="fas fa-map-marker-alt fa-2x"></i>
                                    @elseif ($i === 2)
                                        <i class="fas fa-home fa-2x"></i>
                                    @else
                                        <i class="fas fa-star fa-2x"></i>
                                    @endif
                                </div>
                            @endif

                            <h4 class="card-title mb-3">{{ $pagina->$titleField ?? $defaultTitles[$i] }}</h4>
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
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <p class="text-muted mb-2">{{ $pagina->p_lugar_favorito ?? 'La favorita de nuestros huéspedes en Galicia, España.' }}</p>
                    <div class="mb-3">
                        @php $rating = $property['rating'] ?? 5; @endphp
                        @for ($i = 0; $i < $rating; $i++) 
                            <i class="fas fa-star text-warning"></i>
                        @endfor
                    </div>
                    <h2 class="fw-bold mb-4">{{ $property['title'] ?? 'Propiedad Premium' }}</h2>
                    <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-primary btn-lg">Ver disponibilidad</a>
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <img src="{{ $featuredImages[0] ?? asset('images/property-placeholder.jpg') }}" 
                                 class="img-fluid rounded shadow" 
                                 alt="Interior propiedad"
                                 style="height: 200px; width: 100%; object-fit: cover;">
                        </div>
                        <div class="col-6">
                            <img src="{{ $featuredImages[1] ?? asset('images/property-placeholder.jpg') }}" 
                                 class="img-fluid rounded shadow" 
                                 alt="Vista exterior"
                                 style="height: 200px; width: 100%; object-fit: cover;">
                        </div>
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

        <div class="row g-4">
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

                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            @if (!empty($pagina->{$image}))
                                <img src="{{ asset('images/' . $pagina->{$image}) }}" alt="Imagen tarjeta {{ $i }}"
                                     style="max-height: 100px; object-fit: contain;" class="mb-4">
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

                            <h5 class="fw-bold mb-3">{{ $pagina->{$title} ?? $defaultTitles2[$i] }}</h5>
                            <p class="text-muted">{{ $pagina->{$content} ?? $defaultContent2[$i] }}</p>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="row text-center mt-5">
            <div class="col-12">
                <a href="{{ route('about') }}" class="btn btn-outline-primary btn-lg">
                    Descubre más sobre Galicia
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
