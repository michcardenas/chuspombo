@extends('layouts.app')

@section('title', 'Apartamentos - Chuspombo')

@section('content')

@php
    use Illuminate\Support\Arr;

    // ===== Banner con fallback =====
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

    // ===== Helpers para imágenes =====
    $validExt   = '/\.(jpe?g|png|webp|avif)(\?.*)?$/i';
    $skipSubstr = ['placeholder','default','noimage','missing','image-not-found','dummy'];

    $priorityRank = function (string $u) {
        if (preg_match('/banner|hero|cover|main|original|large/i', $u)) return 0;
        if (preg_match('/thumb|thumbnail|small|icon/i', $u))       return 2;
        return 1;
    };

    $extractFromArray = function (array $p) use ($validExt, $skipSubstr, $priorityRank) {
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

    $findLocalImageCandidates = function ($id) use ($validExt, $priorityRank, $skipSubstr) {
        $candidates = collect();
        $dir = public_path("images/smoobu/{$id}");
        if ($id && is_dir($dir)) {
            $files = glob($dir . '/*.{webp,avif,jpg,jpeg,png}', GLOB_BRACE) ?: [];
            foreach ($files as $abs) {
                $rel = 'images/smoobu/' . $id . '/' . basename($abs);
                $candidates->push(asset($rel));
            }
        }
        foreach (["images/smoobu/{$id}.webp","images/smoobu/{$id}.avif","images/smoobu/{$id}.jpg","images/smoobu/{$id}.jpeg","images/smoobu/{$id}.png"] as $rel) {
            if ($id && file_exists(public_path($rel))) $candidates->push(asset($rel));
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

    $mainImageForProperty = function (array $prop) use ($extractFromArray, $findLocalImageCandidates) {
        $id = $prop['_id'] ?? null;
        if ($id) {
            $local = $findLocalImageCandidates($id);
            if ($local->isNotEmpty()) return $local->first();
        }
        $fromArray = $extractFromArray($prop);
        if ($fromArray->isNotEmpty()) return $fromArray->first();
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
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($properties as $property)
                @php
                    $title = $property['title'] ?? 'Apartamento';
                    $thumb = Arr::get($property, 'picture.thumbnail');
                    $img   = $thumb ?: $mainImageForProperty($property);

                    $city    = $property['address']['city'] ?? 'Galicia';
                    $country = $property['address']['country'] ?? 'España';
                    $location = trim(($city ? $city : '') . ($city ? ', ' : '') . $country);

                    $bedrooms  = (int)($property['bedrooms']  ?? 0);
                    $bathrooms = (int)($property['bathrooms'] ?? 0);

                    $price = $property['prices']['basePrice'] ?? null;
                    $priceStr = is_numeric($price)
                        ? '€' . number_format((float)$price, 0, ',', '.') . '/noche'
                        : 'Consultar';

                    $pid = (int)($property['_id'] ?? 0);
                @endphp

                <div class="col">
                    <div class="card h-100 shadow-sm property-card">
                        <img src="{{ $img }}" class="card-img-top property-img" alt="{{ $title }}" loading="lazy" width="1280" height="800">
                        <div class="card-body">
                            <h5 class="card-title">{{ $title }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i> {{ $location }}
                            </p>
                            <div class="d-flex justify-content-between text-muted">
                                <span><i class="fas fa-bed me-1"></i> {{ $bedrooms }}</span>
                                <span><i class="fas fa-bath me-1"></i> {{ $bathrooms }}</span>
                            </div>
                            <div class="mt-3 text-end">
                                <strong>{{ $priceStr }}</strong>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center border-0">
                            @if($pid > 0)
                                {{-- Importante: aquí pasamos id porque la ruta es propiedades/{id} --}}
                                <a href="{{ route('properties.show', ['id' => $pid]) }}"
                                   class="btn btn-primary w-100"
                                   data-id="{{ $pid }}">
                                    Ver disponibilidad
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
.property-card{display:flex;flex-direction:column;}
.property-card .card-body{flex:1 1 auto;}
.property-img{width:100%;aspect-ratio:16/10;object-fit:cover;}
@media (max-width:576px){
  .chuspombo-hero-banner{height:360px}
  .property-img{aspect-ratio:4/3;}
}
</style>

@endsection
