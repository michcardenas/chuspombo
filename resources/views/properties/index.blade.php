@extends('layouts.app')

@section('title', 'Apartamentos - Chuspombo')

@section('content')

@php
    // Generar imagen random para el banner
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
            // Fallback
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
                    <h1 class="chuspombo-main-title">{{ $paginapropiedades->h1 ?? 'Descubre República Dominicana' }}</h1>
                    <h2 class="chuspombo-subtitle">{{ $paginapropiedades->h2_1 ?? 'Villas y apartamentos exclusivos' }}</h2>
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
                    $thumb = $property['picture']['thumbnail'] ?? asset('images/property-placeholder.jpg');
                    $title = $property['title'] ?? 'Apartamento';
                    $city  = $property['address']['city'] ?? null;
                    $country = $property['address']['country'] ?? 'República Dominicana';
                    $location = trim(($city ? $city : '') . ($city ? ', ' : '') . $country);
                    $bedrooms = $property['bedrooms'] ?? 0;
                    $bathrooms = $property['bathrooms'] ?? 0;
                    $price = $property['prices']['basePrice'] ?? null;
                @endphp

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $thumb }}" class="card-img-top property-img" alt="{{ $title }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $title }}</h5>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i> {{ $location }}
                            </p>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-bed"></i> {{ $bedrooms }} Hab.</span>
                                <span><i class="fas fa-bath"></i> {{ $bathrooms }} Baños</span>
                            </div>
                            <div class="mt-2 text-end">
                                <strong>
                                    @if(is_numeric($price))
                                        US${{ $price }}/noche
                                    @else
                                        Consultar
                                    @endif
                                </strong>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center">
                            {{-- Para Smoobu usamos el ID del apartment --}}
                            <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-primary w-100">
                                Ver Apartamento
                            </a>
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
</style>

@endsection
