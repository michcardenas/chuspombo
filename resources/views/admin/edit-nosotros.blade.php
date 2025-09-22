@extends('layouts.admin')

@section('title', 'Editar Página "Nosotros"')

@section('content')
<div class="container py-3">
    <h1 class="mb-3">Editar Página “Nosotros”</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- NOTA: esta vista no sube imágenes. La página pública usa fotos reales de propiedades.
         Si quieres overrides por imagen, luego añadimos inputs de archivo y la lógica en el controller. --}}

    <form method="POST" action="{{ route('admin.pagina.nosotros.update') }}">
        @csrf

        <div class="accordion" id="aboutEditor">

            {{-- ===== HERO ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-hero">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-hero" aria-expanded="true" aria-controls="sec-hero">
                        Portada (Hero)
                        <span class="badge text-bg-success ms-2">Visible en “Nosotros”</span>
                    </button>
                </h2>
                <div id="sec-hero" class="accordion-collapse collapse show" aria-labelledby="hdr-hero" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título (H1)</label>
                                <input type="text" name="h1" class="form-control" value="{{ old('h1', $paginaNosotros->h1) }}" placeholder="Chuspombo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subtítulo / Bajada (H2)</label>
                                <input type="text" name="h2_1" class="form-control" value="{{ old('h2_1', $paginaNosotros->h2_1) }}" placeholder="Apartamentos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Texto botón principal</label>
                                <input type="text" name="cta_primary_text" class="form-control" value="{{ old('cta_primary_text', $paginaNosotros->cta_primary_text) }}" placeholder="Descubre Tu Refugio Gallego">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL botón principal</label>
                                <input type="url" name="cta_primary_url" class="form-control" value="{{ old('cta_primary_url', $paginaNosotros->cta_primary_url) }}" placeholder="{{ url('/contacto') }}">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Las imágenes del hero se toman automáticamente de tus propiedades (mejor calidad y variedad).</small>
                    </div>
                </div>
            </div>

            {{-- ===== NUESTRA HISTORIA ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-historia">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-historia" aria-expanded="false" aria-controls="sec-historia">
                        Nuestra Historia
                        <span class="badge text-bg-success ms-2">Visible</span>
                    </button>
                </h2>
                <div id="sec-historia" class="accordion-collapse collapse" aria-labelledby="hdr-historia" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" name="h2_historia" class="form-control" value="{{ old('h2_historia', $paginaNosotros->h2_historia) }}" placeholder="Más que alojamiento, una experiencia auténtica">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Texto / Cuerpo</label>
                                <textarea name="p_historia" rows="4" class="form-control" placeholder="Breve relato de origen, propósito y sello de la marca.">{{ old('p_historia', $paginaNosotros->p_historia) }}</textarea>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">La imagen de esta sección también se elige automáticamente de tu galería de propiedades.</small>
                    </div>
                </div>
            </div>

            {{-- ===== LA EXPERIENCIA CHUSPOMBO (3 highlights) ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-experiencia">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-experiencia" aria-expanded="false" aria-controls="sec-experiencia">
                        La Experiencia Chuspombo
                        <span class="badge text-bg-success ms-2">Visible</span>
                    </button>
                </h2>
                <div id="sec-experiencia" class="accordion-collapse collapse" aria-labelledby="hdr-experiencia" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" name="h2_experiencia" class="form-control" value="{{ old('h2_experiencia', $paginaNosotros->h2_experiencia) }}" placeholder="Vive Galicia como nunca antes">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Texto / lead</label>
                                <textarea name="p_experiencia" rows="3" class="form-control" placeholder="Breve explicación del enfoque de experiencia.">{{ old('p_experiencia', $paginaNosotros->p_experiencia) }}</textarea>
                            </div>

                            @for($i=1;$i<=3;$i++)
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <label class="form-label">Título highlight {{ $i }}</label>
                                        <input type="text" name="card1_title_{{ $i }}" class="form-control mb-2" value="{{ old("card1_title_$i", $paginaNosotros["card1_title_$i"] ?? '') }}" placeholder="Ej: Gastronomía auténtica">
                                        <label class="form-label">Texto highlight {{ $i }}</label>
                                        <textarea name="card1_content_{{ $i }}" rows="3" class="form-control" placeholder="Detalle corto del beneficio.">{{ old("card1_content_$i", $paginaNosotros["card1_content_$i"] ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== POR QUÉ ELEGIRNOS (3 bullets) ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-why">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-why" aria-expanded="false" aria-controls="sec-why">
                        Por qué elegirnos
                        <span class="badge text-bg-success ms-2">Visible</span>
                    </button>
                </h2>
                <div id="sec-why" class="accordion-collapse collapse" aria-labelledby="hdr-why" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" name="h2_why" class="form-control" value="{{ old('h2_why', $paginaNosotros->h2_why) }}" placeholder="La diferencia está en los detalles">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            @for($i=4;$i<=6;$i++)
                                <div class="col-md-4">
                                    <div class="border rounded p-3 h-100">
                                        <label class="form-label">Título punto {{ $i-3 }}</label>
                                        <input type="text" name="card2_title_{{ $i }}" class="form-control mb-2" value="{{ old("card2_title_$i", $paginaNosotros["card2_title_$i"] ?? '') }}" placeholder="Ej: Exclusividad garantizada">
                                        <label class="form-label">Texto punto {{ $i-3 }}</label>
                                        <textarea name="card2_content_{{ $i }}" rows="3" class="form-control" placeholder="Breve explicación del punto.">{{ old("card2_content_$i", $paginaNosotros["card2_content_$i"] ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <small class="text-muted d-block mt-2">La imagen de apoyo se selecciona automáticamente de tu biblioteca de propiedades.</small>
                    </div>
                </div>
            </div>

            {{-- ===== CTA FINAL ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-cta">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-cta" aria-expanded="false" aria-controls="sec-cta">
                        Llamado a la acción (CTA) final
                        <span class="badge text-bg-success ms-2">Visible</span>
                    </button>
                </h2>
                <div id="sec-cta" class="accordion-collapse collapse" aria-labelledby="hdr-cta" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" name="h2_cta" class="form-control" value="{{ old('h2_cta', $paginaNosotros->h2_cta) }}" placeholder="Tu hogar gallego te espera">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Texto / apoyo</label>
                                <textarea name="p_cta" rows="3" class="form-control" placeholder="Texto persuasivo breve.">{{ old('p_cta', $paginaNosotros->p_cta) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Botón primario — texto</label>
                                <input type="text" name="cta_primary_text_final" class="form-control" value="{{ old('cta_primary_text_final', $paginaNosotros->cta_primary_text_final) }}" placeholder="Reserva ahora">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Botón primario — URL</label>
                                <input type="url" name="cta_primary_url_final" class="form-control" value="{{ old('cta_primary_url_final', $paginaNosotros->cta_primary_url_final) }}" placeholder="mailto:info@chuspombo.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Botón secundario — texto</label>
                                <input type="text" name="cta_secondary_text_final" class="form-control" value="{{ old('cta_secondary_text_final', $paginaNosotros->cta_secondary_text_final) }}" placeholder="Llámanos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Botón secundario — URL</label>
                                <input type="url" name="cta_secondary_url_final" class="form-control" value="{{ old('cta_secondary_url_final', $paginaNosotros->cta_secondary_url_final) }}" placeholder="tel:+34123456789">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">El fondo del bloque se auto-rellena con una foto real de tus propiedades.</small>
                    </div>
                </div>
            </div>

            {{-- ===== SEO ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-seo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-seo" aria-expanded="false" aria-controls="sec-seo">
                        SEO y Metadatos
                        <span class="badge text-bg-secondary ms-2">Optimización</span>
                    </button>
                </h2>
                <div id="sec-seo" class="accordion-collapse collapse" aria-labelledby="hdr-seo" data-bs-parent="#nosotrosEditor">
                    <div class="accordion-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-search me-2"></i>
                            <strong>Optimización SEO:</strong> Estos campos mejoran la visibilidad en buscadores y redes sociales.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="meta_title" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Meta Title
                                </label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control"
                                       maxlength="60" placeholder="Título optimizado para SEO (max 60 caracteres)"
                                       value="{{ old('meta_title', $paginaNosotros->meta->meta_title ?? '') }}">
                                <small class="text-muted">Aparece en pestañas del navegador y resultados de Google</small>
                            </div>
                            <div class="col-md-6">
                                <label for="meta_description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Meta Description
                                </label>
                                <textarea name="meta_description" id="meta_description" class="form-control" rows="3"
                                          maxlength="160" placeholder="Descripción para resultados de búsqueda (max 160 caracteres)">{{ old('meta_description', $paginaNosotros->meta->meta_description ?? '') }}</textarea>
                                <small class="text-muted">Descripción que aparece en Google bajo el título</small>
                            </div>
                            <div class="col-md-6">
                                <label for="meta_keywords" class="form-label">
                                    <i class="fas fa-tags me-1"></i>Meta Keywords
                                </label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                                       placeholder="nosotros, apartamentos galicia, quienes somos"
                                       value="{{ old('meta_keywords', $paginaNosotros->meta->meta_keywords ?? '') }}">
                                <small class="text-muted">Palabras clave separadas por comas</small>
                            </div>
                            <div class="col-md-6">
                                <label for="canonical_url" class="form-label">
                                    <i class="fas fa-link me-1"></i>URL Canónica
                                </label>
                                <input type="url" name="canonical_url" id="canonical_url" class="form-control"
                                       placeholder="https://chuspomboapartamentos.com/nosotros"
                                       value="{{ old('canonical_url', $paginaNosotros->meta->canonical_url ?? '') }}">
                                <small class="text-muted">URL principal de esta página</small>
                            </div>
                            <div class="col-md-3">
                                <label for="robots" class="form-label">
                                    <i class="fas fa-robot me-1"></i>Robots
                                </label>
                                <select name="robots" id="robots" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="index,follow" {{ old('robots', $paginaNosotros->meta->robots ?? '') === 'index,follow' ? 'selected' : '' }}>index,follow</option>
                                    <option value="noindex,nofollow" {{ old('robots', $paginaNosotros->meta->robots ?? '') === 'noindex,nofollow' ? 'selected' : '' }}>noindex,nofollow</option>
                                    <option value="index,nofollow" {{ old('robots', $paginaNosotros->meta->robots ?? '') === 'index,nofollow' ? 'selected' : '' }}>index,nofollow</option>
                                    <option value="noindex,follow" {{ old('robots', $paginaNosotros->meta->robots ?? '') === 'noindex,follow' ? 'selected' : '' }}>noindex,follow</option>
                                </select>
                                <small class="text-muted">Instrucciones para buscadores</small>
                            </div>
                            <div class="col-md-3">
                                <label for="author" class="form-label">
                                    <i class="fas fa-user me-1"></i>Autor
                                </label>
                                <input type="text" name="author" id="author" class="form-control"
                                       placeholder="Chuspombo Apartamentos"
                                       value="{{ old('author', $paginaNosotros->meta->author ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="language" class="form-label">
                                    <i class="fas fa-language me-1"></i>Idioma
                                </label>
                                <select name="language" id="language" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="es" {{ old('language', $paginaNosotros->meta->language ?? '') === 'es' ? 'selected' : '' }}>Español (es)</option>
                                    <option value="en" {{ old('language', $paginaNosotros->meta->language ?? '') === 'en' ? 'selected' : '' }}>English (en)</option>
                                    <option value="pt" {{ old('language', $paginaNosotros->meta->language ?? '') === 'pt' ? 'selected' : '' }}>Português (pt)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="viewport" class="form-label">
                                    <i class="fas fa-mobile-alt me-1"></i>Viewport
                                </label>
                                <input type="text" name="viewport" id="viewport" class="form-control"
                                       placeholder="width=device-width, initial-scale=1"
                                       value="{{ old('viewport', $paginaNosotros->meta->viewport ?? 'width=device-width, initial-scale=1') }}">
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-2">📊 Consejos SEO:</h6>
                            <ul class="mb-0 small text-muted">
                                <li><strong>Meta Title:</strong> Incluye palabras clave principales al inicio</li>
                                <li><strong>Meta Description:</strong> Escribe una descripción atractiva que invite al clic</li>
                                <li><strong>Keywords:</strong> Usa términos que tus clientes buscarían</li>
                                <li><strong>URL Canónica:</strong> Evita contenido duplicado</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>

    </form>
</div>
@endsection
