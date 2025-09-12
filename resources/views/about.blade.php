@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Arr;

    /**
     * Tomamos imágenes reales de las PROPIEDADES (como en los códigos anteriores):
     * - Buscamos en /public/images/smoobu/{ID}/ y archivos sueltos por ID.
     * - También recogemos URLs de picture/pictures/gallery/images del array.
     * - Filtramos placeholders y priorizamos banner/hero/cover/main/original/large.
     * - Seleccionamos 4 aleatorias para las secciones de la página.
     */

    $imagesNeeded = 4;

    $validExt   = '/\.(jpe?g|png|webp|avif)(\?.*)?$/i';
    $skipSubstr = ['placeholder','default','noimage','missing','image-not-found','dummy','coming-soon'];

    $priorityRank = function (string $u) {
        if (preg_match('/banner|hero|cover|main|original|large/i', $u)) return 0;
        if (preg_match('/thumb|thumbnail|small|icon/i', $u))       return 2;
        return 1;
    };

    // Preferimos $properties, si no, caemos a $featuredProperties.
    $sourceProps = collect($properties ?? ($featuredProperties ?? []));

    // Candidatos locales: /public/images/smoobu/{ID}/... y archivos sueltos por ID
    $localCandidates = function ($id) use ($validExt, $skipSubstr, $priorityRank) {
        $c = collect();

        // Carpeta por ID (nivel 1)
        $dir = public_path("images/smoobu/{$id}");
        if (is_dir($dir)) {
            $files = glob($dir.'/*.{webp,avif,jpg,jpeg,png}', GLOB_BRACE) ?: [];
            foreach ($files as $abs) {
                $rel = 'images/smoobu/'.$id.'/'.basename($abs);
                $c->push(asset($rel));
            }
        }

        // Archivos directos por ID
        foreach (["images/smoobu/{$id}.webp","images/smoobu/{$id}.avif","images/smoobu/{$id}.jpg","images/smoobu/{$id}.jpeg","images/smoobu/{$id}.png"] as $rel) {
            if (file_exists(public_path($rel))) {
                $c->push(asset($rel));
            }
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

    // Candidatos desde el array de la propiedad (picture/pictures/gallery/images)
    $arrayCandidates = function ($p) use ($validExt, $skipSubstr, $priorityRank) {
        $urls = collect();

        // picture puede ser string o array
        if (!empty($p['picture'])) {
            if (is_array($p['picture'])) {
                foreach (['banner','hero','cover','main','original','large','url','full','thumbnail'] as $k) {
                    if (!empty($p['picture'][$k]) && is_string($p['picture'][$k])) {
                        $urls->push($p['picture'][$k]);
                    }
                }
            } elseif (is_string($p['picture'])) {
                $urls->push($p['picture']);
            }
        }

        foreach (['pictures','gallery','images'] as $key) {
            if (!empty($p[$key]) && is_array($p[$key])) {
                foreach ($p[$key] as $u) {
                    if (is_string($u)) $urls->push($u);
                }
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

    // Construir POOL de imágenes (omite propiedades sin fotos)
    $pool = $sourceProps->flatMap(function ($p) use ($localCandidates, $arrayCandidates) {
                $id  = $p['_id'] ?? ($p['id'] ?? null);
                $loc = $id ? $localCandidates($id) : collect();
                $arr = $arrayCandidates($p);
                $merged = $loc->merge($arr)->unique()->values();

                // Log breve por propiedad (útil para depurar)
                if (($id ?? null) !== null) {
                    logger()->info('[Landing Pool] Imágenes por propiedad', [
                        'property_id' => $id,
                        'title'       => $p['title'] ?? ($p['name'] ?? null),
                        'local'       => $loc->count(),
                        'array'       => $arr->count(),
                        'merged'      => $merged->count(),
                        'sample'      => $merged->take(3)->all(),
                    ]);
                }

                return $merged;
            })
            ->unique()
            ->values();

    logger()->info('[Landing Pool] Total imágenes disponibles', ['total' => $pool->count()]);

    // Seleccionar 4 aleatorias; si hay menos de 4, repetimos de las existentes (evitamos placeholders)
    $sectionImages = $pool->shuffle()->take($imagesNeeded)->values()->all();
    if (count($sectionImages) < $imagesNeeded && $pool->isNotEmpty()) {
        while (count($sectionImages) < $imagesNeeded) {
            $sectionImages[] = $pool->random();
        }
    }
    if (empty($sectionImages)) {
        $sectionImages = [
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
            asset('images/galicia-placeholder.webp'),
        ];
    }
@endphp

{{-- ====== Hostella palette / overrides locales para esta vista ====== --}}
<style>
:root{
  --hostella-primary:#1a1a1a;
  --hostella-secondary:#FFD700;
  --hostella-light:#f8f9fa;
  --hostella-dark:#000000;
  --hostella-accent:#D4AF37;
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
  background: linear-gradient(45deg, var(--hostella-accent), #B8860B);
  border:none;
  color:var(--hostella-dark);
  font-weight:600;
  border-radius:30px;
}
.btn-accent:hover{
  filter: brightness(.95);
  color:var(--hostella-dark);
}
/* Mapear utilidades de Bootstrap a paleta Hostella (sólo en esta vista) */
.bg-primary{ background-color:var(--hostella-primary)!important; }
.text-primary{ color:var(--hostella-primary)!important; }
.badge.bg-primary{
  background-color:var(--hostella-secondary)!important;
  color:var(--hostella-dark)!important;
}
.text-accent{ color:var(--hostella-accent)!important; }

/* Pequeños detalles visuales */
.shadow-soft{ box-shadow:0 10px 30px rgba(0,0,0,.08); }
.round-3{ border-radius:1rem; }
</style>

<div class="container-fluid p-0">
    <!-- Hero Section Premium -->
    <section class="position-relative overflow-hidden" style="height: 70vh; min-height: 600px;">
        <div class="position-absolute w-100 h-100"
             style="background: linear-gradient(45deg, rgba(26,26,26,0.8), rgba(212,175,55,0.3)), url('{{ $sectionImages[0] }}'); background-size: cover; background-position: center;"></div>
        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center">
            <div class="container text-center text-white">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-2 fw-bold mb-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            Chuspombo
                        </h1>
                        <p class="fs-3 mb-4 text-accent" style="font-weight: 300;">
                            Apartamentos
                        </p>
                        <p class="lead fs-4 mb-5 px-3" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.7);">
                            Donde el lujo se encuentra con la autenticidad gallega.<br>
                            <em>Dos apartamentos únicos en el corazón de Galicia.</em>
                        </p>
                        <a href="{{ route('contact') }}" class="btn btn-lg px-5 py-3 btn-accent">
                            Descubre Tu Refugio Gallego
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestra Historia -->
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
                            Más que alojamiento,<br>
                            <span class="text-accent">una experiencia auténtica</span>
                        </h2>
                        <p class="fs-5 text-muted mb-4 lh-lg">
                            Chuspombo nació del amor por Galicia y la pasión por la hospitalidad. Nuestros dos apartamentos no son solo espacios de lujo, sino ventanas abiertas a la verdadera esencia gallega.
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

    <!-- La Experiencia Chuspombo -->
    <section class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="badge bg-dark px-3 py-2 mb-3">La Experiencia Chuspombo</span>
                <h2 class="display-5 fw-bold">
                    Vive Galicia como nunca antes
                </h2>
                <p class="lead text-muted col-lg-8 mx-auto">
                    Cada detalle está pensado para que te sumerjas en la cultura gallega mientras disfrutas del máximo confort.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-utensils text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Gastronomía Auténtica</h4>
                            <p class="text-muted">Cocinas completamente equipadas y guías personalizadas para descubrir los mejores mariscos, vinos y empanadas gallegas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-map-marked-alt text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Ubicación Privilegiada</h4>
                            <p class="text-muted">A pasos de la Catedral, el casco histórico y los mejores rincones que solo los locales conocen.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center p-4">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-concierge-bell text-primary fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Atención Personalizada</h4>
                            <p class="text-muted">Somos tus anfitriones locales. Te acompañamos para que vivas Galicia como un verdadero gallego.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Por qué Chuspombo -->
    <section class="py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
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
                        La diferencia está en los detalles
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
                                    <h5 class="fw-bold mb-1">Exclusividad Garantizada</h5>
                                    <p class="text-muted mb-0">Solo dos apartamentos significa atención completamente personalizada.</p>
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
                                    <h5 class="fw-bold mb-1">Pasión Local</h5>
                                    <p class="text-muted mb-0">Conocemos cada rincón, cada historia, cada sabor de Galicia.</p>
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
                                    <h5 class="fw-bold mb-1">Lujo Auténtico</h5>
                                    <p class="text-muted mb-0">Comodidades modernas en espacios que respiran historia gallega.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final Premium -->
    <section class="py-5 position-relative overflow-hidden">
        <div class="position-absolute w-100 h-100"
             style="background: linear-gradient(rgba(26,26,26,0.85), rgba(26,26,26,0.85)), url('{{ $sectionImages[3] }}'); background-size: cover; background-position: center;"></div>
        <div class="container position-relative py-5">
            <div class="row justify-content-center text-center text-white">
                <div class="col-lg-8">
                    <h2 class="display-4 fw-bold mb-4">
                        Tu hogar gallego te espera
                    </h2>
                    <p class="lead mb-5">
                        Dos apartamentos únicos, infinitas experiencias. Descubre por qué nuestros huéspedes se enamoran de Galicia... y regresan.
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="mailto:info@chuspombo.com" class="btn btn-lg px-5 py-3 btn-accent">
                            <i class="fas fa-envelope me-2"></i>
                            Reserva Ahora
                        </a>
                        <a href="tel:+34123456789" class="btn btn-outline-light btn-lg px-5 py-3" style="border-radius: 30px; font-weight: 600;">
                            <i class="fas fa-phone me-2"></i>
                            Llámanos
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
