@extends('layouts.app')

@section('title', 'Apartamentos - Chuspombo')

@section('content')

@php
use Illuminate\Support\Arr;

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
    $testImage="CHUSPOMBO-APARTAMENTOS-{$i}.png" ;
    if (file_exists(public_path("images/{$testImage}"))) {
    $foundImage=asset("images/{$testImage}");
    break;
    }
    }
    $bannerImage=$foundImage ?? asset('images/property-placeholder.jpg');
    }
    }

    //=====Helpers para IMÁGENES DE PROPIEDAD=====$validExt='/\.(jpe?g|png|webp|avif)(\?.*)?$/i' ;
    $skipSubstr=['placeholder','default','noimage','missing','image-not-found','dummy'];

    $priorityRank=function (string $u) {
    if (preg_match('/banner|hero|cover|main|original|large/i', $u)) return 0;
    if (preg_match('/thumb|thumbnail|small|icon/i', $u)) return 2;
    return 1;
    };

    // Extrae posibles URLs del array de la propiedad
    $extractFromArray=function (array $p) use ($validExt, $skipSubstr, $priorityRank) {
    return collect(Arr::dot($p))
    ->values()
    ->filter(fn ($v) => is_string($v))
    ->map(fn ($u) => trim($u))
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

    // Busca en /public/images/smoobu/{ID}/ y variantes directas
    $findLocalImageCandidates = function ($id) use ($validExt, $priorityRank, $skipSubstr) {
    $candidates = collect();

    // 1) Carpeta por ID
    $dir = public_path("images/smoobu/{$id}");
    if (is_dir($dir)) {
    $files = glob($dir . '/*.{webp,avif,jpg,jpeg,png}', GLOB_BRACE) ?: [];
    foreach ($files as $abs) {
    $rel = 'images/smoobu/' . $id . '/' . basename($abs);
    $candidates->push(asset($rel));
    }
    }

    // 2) Archivos sueltos por ID
    foreach (["images/smoobu/{$id}.webp", "images/smoobu/{$id}.avif", "images/smoobu/{$id}.jpg", "images/smoobu/{$id}.jpeg", "images/smoobu/{$id}.png"] as $rel) {
    if (file_exists(public_path($rel))) {
    $candidates->push(asset($rel));
    }
    }

    return $candidates
    ->filter(fn ($u) => is_string($u) && preg_match($validExt, $u))
    ->reject(function ($u) use ($skipSubstr) {
    $lu = strtolower($u);
    foreach ($skipSubstr as $s) { if (str_contains($lu, $s)) return true; }
    return false;
    })
    ->unique()
    ->sortBy(fn ($u) => $priorityRank($u))
    ->values();
    };

    // Imagen principal para tarjeta
    $mainImageForProperty = function (array $prop) use ($extractFromArray, $findLocalImageCandidates) {
    $id = $prop['_id'] ?? null;

    // 1) Locales
    if ($id) {
    $local = $findLocalImageCandidates($id);
    if ($local->isNotEmpty()) return $local->first();
    }

    // 2) URLs que vengan en el array (picture/gallery/…)
    $fromArray = $extractFromArray($prop);
    if ($fromArray->isNotEmpty()) return $fromArray->first();

    // 3) Fallback
    return asset('images/property-placeholder.jpg');
    };
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
            $title = $property['title'] ?? 'Apartamento';

            // ID Smoobu de la propiedad
            $pid = (int)($property['_id'] ?? 0);

            // 1) Imagen local con sort_order = 1 (activa)
            $localPath = null;
            if ($pid) {
            $localPath = \App\Models\SmoobuApartmentImage::query()
            ->where('apartment_id', $pid)
            ->where('is_active', 1)
            ->where('sort_order', 1)
            ->value('path');

            // 2) Si no hay sort_order=1, tomar la primera por sort_order
            if (!$localPath) {
            $localPath = \App\Models\SmoobuApartmentImage::query()
            ->where('apartment_id', $pid)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->value('path');
            }
            }

            // 3) Resultado final con fallbacks:
            // - Local (si existe)
            // - La que ya usabas de Smoobu ($property['picture']['thumbnail'] o tu helper)
            $img = $localPath
            ? asset($localPath)
            : ($property['picture']['thumbnail'] ?? $mainImageForProperty($property));

            // Resto de datos igual que ya tenías...
            $city = $property['address']['city'] ?? 'Galicia';
            $country = $property['address']['country'] ?? 'España';
            $location = trim(($city ? $city : '') . ($city ? ', ' : '') . $country);

            $bedrooms = (int)($property['bedrooms'] ?? 0);
            $bathrooms = (int)($property['bathrooms'] ?? 0);

            $price = $property['prices']['basePrice'] ?? null;
            $priceStr = is_numeric($price) ? '€' . number_format((float)$price, 0, ',', '.') . '/noche' : 'Consultar';
            @endphp


            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="{{ $img }}" class="card-img-top property-img" alt="{{ $title }}" loading="lazy">
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
        .chuspombo-hero-banner {
            position: relative;
            height: 500px;
            background-image:url('{{ $bannerImage }}');
            background-size: cover;
            background-position: center;
            margin-bottom: 50px;
            overflow: hidden
        }

        .chuspombo-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, .35), rgba(0, 0, 0, .55))
        }

        .chuspombo-hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: end;
            height: 100%;
            padding-bottom: 40px;
            color: #fff
        }

        .property-img {
            height: 250px;
            object-fit: cover
        }

        @media (max-width:576px) {
            .chuspombo-hero-banner {
                height: 360px
            }

            .property-img {
                height: 210px
            }
        }
    </style>

    @endsection