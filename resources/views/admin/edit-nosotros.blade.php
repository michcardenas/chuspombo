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

            {{-- ===== NOTAS ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-notas">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-notas" aria-expanded="false" aria-controls="sec-notas">
                        Notas
                        <span class="badge text-bg-secondary ms-2">No visible</span>
                    </button>
                </h2>
                <div id="sec-notas" class="accordion-collapse collapse" aria-labelledby="hdr-notas" data-bs-parent="#aboutEditor">
                    <div class="accordion-body">
                        <small class="text-muted">
                            Esta página usa imágenes reales de tus propiedades (como la portada y “Propiedades”).  
                            Si prefieres subir imágenes fijas para cada sección, avísame y agregamos los campos y la lógica de guardado.
                        </small>
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
