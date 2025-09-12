@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Arr;

    /**
     * ===== Pool de imágenes desde PROPIEDADES =====
     * - Busca en /public/images/smoobu/{ID}/ y variantes sueltas por ID
     * - Lee URLs en picture/pictures/gallery/images
     * - Filtra placeholders, prioriza banner/hero/cover/main/original/large
     * - Selecciona 4 para secciones; usa placeholders si no hay pool
     */

    $imagesNeeded = 4;

    $validExt   = '/\.(jpe?g|png|webp|avif)(\?.*)?$/i';
    $skipSubstr = ['placeholder','default','noimage','missing','image-not-found','dummy','coming-soon'];

    $priorityRank = function (string $u) {
        if (preg_match('/banner|hero|cover|main|original|large/i', $u)) return 0;
        if (preg_match('/thumb|thumbnail|small|icon/i', $u))       return 2;
        return 1;
    };

    // Preferimos $properties (si la página las recibe), luego $featuredProperties.
    $sourceProps = collect($properties ?? ($featuredProperties ?? []));

    $localCandidates = function ($id) use ($validExt, $skipSubstr, $priorityRank) {
        $c = collect();

        $dir = public_path("images/smoobu/{$id}");
        if (is_dir($dir)) {
            $files = glob($dir.'/*.{webp,avif,jpg,jpeg,png}', GLOB_BRACE) ?: [];
            foreach ($files as $abs) {
                $rel = 'images/smoobu/'.$id.'/'.basename($abs);
                $c->push(asset($rel));
            }
        }

        foreach (["images/smoobu/{$id}.webp","images/smoobu/{$id}.avif","images/smoobu/{$id}.jpg","images/smoobu/{$id}.jpeg","images/smoobu/{$id}.png"] as $rel) {
            if (file_exists(public_path($rel))) $c->push(asset($rel));
        }

        return $c->filter(fn($u) => is_string($u) && preg_match($validExt, $u))
                ->reject(function ($u) use ($skipSubstr) {
                    $lu = strtolower($u);
                    foreach ($skipSubstr as $s) { if (str_contains($lu, $s)) return true; }
                    return false;
                })
                ->unique()
                ->sortBy(fn($u) => $priorityRank($u))
                ->values();
    };

    $arrayCandidates = function ($p) use ($validExt, $skipSubstr, $priorityRank) {
        $urls = collect();

        if (!empty($p['picture'])) {
            if (is_array($p['picture'])) {
                foreach (['banner','hero','cover','main','original','large','url','full','thumbnail'] as $k) {
                    if (!empty($p['picture'][$k]) && is_string($p['picture'][$k])) $urls->push($p['picture'][$k]);
                }
            } elseif (is_string($p['picture'])) {
                $urls->push($p['picture']);
            }
        }

        foreach (['pictures','gallery','images'] as $key) {
            if (!empty($p[$key]) && is_array($p[$key])) {
                foreach ($p[$key] as $u) if (is_string($u)) $urls->push($u);
            }
        }

        return $urls->filter(fn($u) => $u !== '' && !str_starts_with($u,'data:') && preg_match($validExt,$u))
                    ->reject(function ($u) use ($skipSubstr) {
                        $lu = strtolower($u);
                        foreach ($skipSubstr as $s) { if (str_contains($lu, $s)) return true; }
                        return false;
                    })
                    ->unique()
                    ->sortBy(fn($u) => $priorityRank($u))
                    ->values();
    };

    $pool = $sourceProps->flatMap(function ($p) use ($localCandidates, $arrayCandidates) {
                $id  = $p['_id'] ?? ($p['id'] ?? null);
                $loc = $id ? $localCandidates($id) : collect();
                $arr = $arrayCandidates($p);
                return $loc->merge($arr)->unique()->values();
            })
            ->unique()
            ->values();

    $sectionImages = $pool->shuffle()->take($imagesNeeded)->values()->all();
    if (count($sectionImages) < $imagesNeeded && $pool->isNotEmpty()) {
        while (count($sectionImages) < $imagesNeeded) $sectionImages[] = $pool->random();
    }
    if (empty($sectionImages)) {
        $sectionImages = [
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
        ];
    }

    /**
     * ===== Textos desde $about (AboutPage) con fallbacks =====
     * El controlador debe pasar: $about = AboutPage::active()->first() ?? AboutPage::first() ?? new AboutPage();
     */
    $heroH1   = $about->hero_title     ?? 'Chuspombo';
    $heroH2   = $about->hero_subtitle  ?? 'Apartamentos';
    // Lead opcional: usamos story_subtitle o exp_subtitle como lead si existen.
    $heroLead = $about->story_subtitle
                ?? $about->exp_subtitle
                ?? 'Donde el lujo se encuentra con la autenticidad gallega.';

    $heroBtnText = $about->hero_cta_text ?? 'Descubre Tu Refugio Gallego';
    $heroBtnUrl  = $about->hero_cta_url  ?? (function_exists('route') && Route::has('contact') ? route('contact') : url('/contacto'));

    // Nuestra Historia
    $historiaH2 = $about->story_title ?? 'Más que alojamiento, una experiencia auténtica';
    $historiaP  = $about->story_text  ?? 'Chuspombo nació del amor por Galicia y la pasión por la hospitalidad.';

    // Experiencia
    $expH2   = $about->exp_title     ?? 'Vive Galicia como nunca antes';
    $expLead = $about->exp_subtitle  ?? 'Cada detalle está pensado para que te sumerjas en la cultura gallega.';
    $c1t1 = $about->exp_card1_title ?? 'Gastronomía Auténtica';
    $c1p1 = $about->exp_card1_text  ?? 'Cocinas equipadas y guías de mariscos, vinos y empanadas.';
    $c1t2 = $about->exp_card2_title ?? 'Ubicación Privilegiada';
    $c1p2 = $about->exp_card2_text  ?? 'A pasos de los mejores rincones que solo los locales conocen.';
    $c1t3 = $about->exp_card3_title ?? 'Atención Personalizada';
    $c1p3 = $about->exp_card3_text  ?? 'Te acompañamos para vivir Galicia como un verdadero gallego.';

    // Por qué elegirnos
    $whyH2 = $about->why_title ?? 'La diferencia está en los detalles';
    $c2t1 = $about->why_item1_title ?? 'Exclusividad Garantizada';
    $c2p1 = $about->why_item1_text  ?? 'Solo dos apartamentos, atención totalmente personalizada.';
    $c2t2 = $about->why_item2_title ?? 'Pasión Local';
    $c2p2 = $about->why_item2_text  ?? 'Conocemos cada rincón, historia y sabor de Galicia.';
    $c2t3 = $about->why_item3_title ?? 'Lujo Auténtico';
    $c2p3 = $about->why_item3_text  ?? 'Comodidades modernas en espacios con alma gallega.';

    // CTA final
    $ctaH2   = $about->cta_title       ?? 'Tu hogar gallego te espera';
    $ctaP    = $about->cta_text        ?? 'Dos apartamentos únicos, infinitas experiencias.';
    $cta1Txt = $about->cta_button_text ?? 'Reserva Ahora';
    $cta1Url = $about->cta_button_url  ?? 'mailto:info@chuspombo.com';
    $cta2Txt = $about->cta_phone_label ?? 'Llámanos';
    $cta2Url = $about->cta_phone_number?? 'tel:+34123456789';

    // Overrides de imágenes desde AboutPage (si subiste imágenes fijas)
    $heroBg = $about->hero_image_url ?: $sectionImages[0];
    $ctaBg  = $about->cta_background_image_url ?: $sectionImages[3];
@endphp

{{-- ====== Paleta / estilos locales ====== --}}
<style>
:root{
  --hostella-primary:#1a1a1a;
  --hostella-secondary:#FFD700;
  --hostella-light:#f8f9fa;
  --hostella-dark:#000000;
  --hostella-accent:#D4AF37;

  /* helpers para usar alpha en gradientes */
  --hostella-primary-rgb: 26,26,26;
  --hostella-accent-rgb: 212,175,55;
}

/* Botones base con paleta Hostella */
.btn-primary{
  background-color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}
.btn-primary:hover{
  background-color:var(--hostella-dark)!important;
  border-color:var(--hostella-dark)!important;
}
.btn-outline-primary{
  color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}
.btn-outline-primary:hover{
  color:#fff!important;
  background-color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}

/* Botón acento dorado */
.btn-accent{
  background: linear-gradient(45deg, var(--hostella-accent), var(--hostella-accent));
  border:none;
  color:var(--hostella-dark);
  font-weight:600;
  border-radius:30px;
}
.btn-accent:hover{
  filter: brightness(.95);
  color:var(--hostella-dark);
}

.text-accent{ color:var(--hostella-accent)!important; }
.shadow-soft{ box-shadow:0 10px 30px rgba(0,0,0,.08); }
.round-3{ border-radius:1rem; }

/* Mapear utilidades Bootstrap a la paleta */
.bg-primary{ background-color:var(--hostella-primary)!important; }
.text-primary{ color:var(--hostella-primary)!important; }

/* Badges con la paleta */
.badge.bg-primary{
  background-color:var(--hostella-secondary)!important;
  color:var(--hostella-dark)!important;
}
.badge.bg-dark{
  background-color:var(--hostella-primary)!important;
  color:#fff!important;
}
</style>


<div class="container-fluid p-0">
    {{-- HERO --}}
    <section class="position-relative overflow-hidden" style="height: 70vh; min-height: 600px;">
        <div class="position-absolute w-100 h-100"
             style="background: linear-gradient(45deg, rgba(var(--hostella-primary-rgb), 0.8), rgba(var(--hostella-accent-rgb), 0.3)), url('{{ $heroBg }}'); background-size: cover; background-position: center;"></div>
        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center">
            <div class="container text-center text-white">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-2 fw-bold mb-2" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            {{ $heroH1 }}
                        </h1>
                        <p class="fs-3 mb-3 text-accent" style="font-weight: 300;">
                            {{ $heroH2 }}
                        </p>
                        <p class="lead fs-5 mb-5 px-3" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.7);">
                            {{ $heroLead }}
                        </p>
                        <a href="{{ $heroBtnUrl }}" class="btn btn-lg px-5 py-3 btn-accent">
                            {{ $heroBtnText }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Nuestra Historia --}}
    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="{{ $sectionImages[1] }}" alt="Apartamento Chuspombo" class="img-fluid rounded-3 shadow-lg" loading="lazy">
                        <div class="position-absolute bottom-0 end-0 bg-dark text-white p-3 rounded-3 m-3">
                            <small class="text-muted">Galicia, España</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="pe-lg-4">
                        <span class="badge bg-primary px-3 py-2 mb-3">Nuestra Historia</span>
                        <h2 class="display-5 fw-bold mb-4">
                            {{ $historiaH2 }}
                        </h2>
                        <p class="fs-5 text-muted mb-4 lh-lg">
                            {{ $historiaP }}
                        </p>
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-home text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 fw-bold">2 Apartamentos</h6>
                                        <small class="text-muted">Exclusivos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-star text-white"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 fw-bold">100% Local</h6>
                                        <small class="text-muted">Auténtico</small>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- La Experiencia Chuspombo --}}
    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="badge bg-dark px-3 py-2 mb-3">La Experiencia Chuspombo</span>
                <h2 class="display-5 fw-bold">
                    {{ $expH2 }}
                </h2>
                <p class="lead text-muted col-lg-8 mx-auto">
                    {{ $expLead }}
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-utensils text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ $c1t1 }}</h4>
                            <p class="text-muted">{{ $c1p1 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-map-marked-alt text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ $c1t2 }}</h4>
                            <p class="text-muted">{{ $c1p2 }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-concierge-bell text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">{{ $c1t3 }}</h4>
                            <p class="text-muted">{{ $c1p3 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Por qué Chuspombo --}}
    <section class="py-5" style="background: linear-gradient(135deg, var(--hostella-light) 0%, var(--hostella-light) 100%);">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-lg-2">
                    <div class="position-relative">
                        <img src="{{ $sectionImages[2] }}" alt="Interior Chuspombo" class="img-fluid rounded-3 shadow-lg" loading="lazy">
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <span class="badge bg-dark px-3 py-2 mb-3">Por qué Elegirnos</span>
                    <h2 class="display-6 fw-bold mb-4">
                        {{ $whyH2 }}
                    </h2>
                    
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1">{{ $c2t1 }}</h5>
                                    <p class="text-muted mb-0">{{ $c2p1 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-heart text-white"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1">{{ $c2t2 }}</h5>
                                    <p class="text-muted mb-0">{{ $c2p2 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-gem text-white"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="fw-bold mb-1">{{ $c2t3 }}</h5>
                                    <p class="text-muted mb-0">{{ $c2p3 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Final --}}
    <section class="py-5 position-relative overflow-hidden">
        <div class="position-absolute w-100 h-100"
            style="background: linear-gradient(rgba(var(--hostella-primary-rgb), 0.85), rgba(var(--hostella-primary-rgb), 0.85)), url('{{ $ctaBg }}'); background-size: cover; background-position: center;"></div>
        <div class="container position-relative py-5">
            <div class="row justify-content-center text-center text-white">
                <div class="col-lg-8">
                    <h2 class="display-4 fw-bold mb-4">
                        {{ $ctaH2 }}
                    </h2>
                    <p class="lead mb-5">
                        {{ $ctaP }}
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="{{ $cta1Url }}" class="btn btn-lg px-5 py-3 btn-accent">
                            <i class="fas fa-envelope me-2"></i>
                            {{ $cta1Txt }}
                        </a>
                        <a href="{{ $cta2Url }}" class="btn btn-outline-light btn-lg px-5 py-3" style="border-radius: 30px; font-weight: 600;">
                            <i class="fas fa-phone me-2"></i>
                            {{ $cta2Txt }}
                        </a>
                    </div>
                    <p class="mt-4 text-muted">
                        <small>Respuesta garantizada en menos de 2 horas</small>
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
