@extends('layouts.app')

@section('title', 'Apartamentos - Chuspombo')

@section('content')

@php
    // ===== Banner (igual que tenías, con fallback) =====
    $totalImages = 100;
    $randomNumber = rand(1, $totalImages);

    if (!empty($paginapropiedades->card2_image_4)) {
        $bannerImage = asset('images/' . $paginapropiedades->card2_image_4);
    } else {
        $randomImage = "CHUSPOMBO-APARTAMENTOS-{$randomNumber}.png";
        $imagePath = public_path("images/{$randomImage}");

        if (file_exists($imagePath)) {
            $bannerImage = asset("images/{$randomImage}");
        } else {
            $foundImage = null;
            for ($i = 1; $i <= $totalImages; $i++) {
                $testImage = "CHUSPOMBO-APARTAMENTOS-{$i}.png";
                if (file_exists(public_path("images/{$testImage}"))) {
                    $foundImage = asset("images/{$testImage}");
                    break;
                }
            }
            $bannerImage = $foundImage ?? asset('images/property-placeholder.jpg');
        }
    }
@endphp

<!-- Banner hero -->
<div class="chuspombo-hero-banner">
    <div class="chuspombo-overlay"></div>
    <div class="chuspombo-hero-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-10">
                    <h1 class="chuspombo-main-title">{{ $paginapropiedades->h1 ?? 'Descubre Galicia, España' }}</h1>
                    <h2 class="chuspombo-subtitle">{{ $paginapropiedades->h2_1 ?? 'Apartamentos exclusivos' }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h1 class="fw-bold text-center mb-4">Propiedades disponibles</h1>

    @if(isset($properties) && count($properties) > 0)
        <div class="row">
            @foreach($properties as $property)
                @php
                    // Datos base (vienen del controlador en array)
                    $title = $property['title'] ?? 'Apartamento';
                    $pid   = (int)($property['_id'] ?? 0);

                    // ===== Portada desde BD: sort_order = 1 (fallback: menor sort_order) =====
                    $imgPath = null;
                    if ($pid) {
                        $imgPath = Cache::remember("apt.mainimg.$pid", 300, function () use ($pid) {
                            $q = \App\Models\SmoobuApartmentImage::query()
                                ->where('apartment_id', $pid)
                                ->where('is_active', 1);

                            // Primero intentamos exactamente sort_order = 1
                            $exact = (clone $q)->where('sort_order', 1)->value('path');
                            if ($exact) return $exact;

                            // Si no existe, tomamos la primera por sort_order
                            return $q->orderBy('sort_order')->value('path');
                        });
                    }

                    // Fallbacks: cover local del mapping -> archivos locales por ID -> placeholder
                    $thumb = $imgPath
                        ? asset($imgPath)
                        : ($property['picture']['thumbnail'] ?? null);

                    if (!$thumb) {
                        $thumb = $pid && file_exists(public_path("images/smoobu/{$pid}.webp")) ? asset("images/smoobu/{$pid}.webp")
                            : ($pid && file_exists(public_path("images/smoobu/{$pid}.jpg")) ? asset("images/smoobu/{$pid}.jpg")
                            : ($pid && file_exists(public_path("images/smoobu/{$pid}.png")) ? asset("images/smoobu/{$pid}.png")
                            : asset('images/property-placeholder.jpg')));
                    }

                    // Ubicación
                    $city     = $property['address']['city'] ?? 'Galicia';
                    $country  = $property['address']['country'] ?? 'España';
                    $location = trim(($city ?: '') . ($city ? ', ' : '') . $country);

                    // Habitaciones / baños
                    $bedrooms  = (int)($property['bedrooms']  ?? 0);
                    $bathrooms = (int)($property['bathrooms'] ?? 0);

                    // Precio
                    $price   = $property['prices']['basePrice'] ?? null;
                    $priceStr = is_numeric($price) ? '€' . number_format((float)$price, 0, ',', '.') . '/noche' : 'Consultar';
                @endphp

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $thumb }}" class="card-img-top property-img" alt="{{ $title }}" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">{{ $title }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i> {{ $location }}
                            </p>

                            @if($bedrooms > 0 || $bathrooms > 0)
                                <div class="d-flex justify-content-between text-muted">
                                    @if($bedrooms > 0)
                                        <span><i class="fas fa-bed"></i> {{ $bedrooms }} Hab.</span>
                                    @else
                                        <span></span>
                                    @endif
                                    @if($bathrooms > 0)
                                        <span><i class="fas fa-bath"></i> {{ $bathrooms }} Baños</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 text-end">
                                <strong>{{ $priceStr }}</strong>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center">
                            @if($pid)
                                <a href="{{ route('properties.show', $pid) }}" class="btn btn-primary w-100">
                                    Ver Apartamento
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-home fa-3x text-muted mb-3"></i>
            <p class="text-muted fs-5">No hay propiedades disponibles por ahora.</p>
            <p class="text-muted">Explora pronto para encontrar tu estancia perfecta.</p>
        </div>
    @endif
</div>

<style>
.chuspombo-hero-banner{
    position:relative;height:500px;background-image:url('{{ $bannerImage }}');
    background-size:cover;background-position:center;margin-bottom:50px;overflow:hidden
}
.chuspombo-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.35),rgba(0,0,0,.55))}
.chuspombo-hero-content{position:relative;z-index:2;display:flex;align-items:end;height:100%;padding-bottom:40px;color:#fff}
.property-img{height:250px;object-fit:cover}
@media (max-width:576px){
  .chuspombo-hero-banner{height:360px}
  .property-img{height:210px}
}
</style>

@endsection
