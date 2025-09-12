@extends('layouts.admin')

@section('title', 'Editar Página de Propiedades')

@section('content')
<div class="container py-3">

    <h1 class="mb-3">Editar Página de Propiedades</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.pagina.propiedades.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="accordion" id="propsEditor">

            {{-- Encabezados (H1 / H2) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-headings">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-headings" aria-expanded="true" aria-controls="sec-headings">
                        Encabezados <span class="badge text-bg-success ms-2">Visible en Propiedades</span>
                    </button>
                </h2>
                <div id="sec-headings" class="accordion-collapse collapse show" aria-labelledby="hdr-headings" data-bs-parent="#propsEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="h1" class="form-label">Título principal (H1)</label>
                                <input type="text" name="h1" id="h1" class="form-control"
                                       placeholder="Ej: Descubre Galicia, España"
                                       value="{{ old('h1', $paginaPropiedades->h1) }}">
                                <small class="text-muted">Se muestra como título principal en la página de Propiedades.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="h2_1" class="form-label">Subtítulo (H2)</label>
                                <input type="text" name="h2_1" id="h2_1" class="form-control"
                                       placeholder="Ej: Apartamentos exclusivos"
                                       value="{{ old('h2_1', $paginaPropiedades->h2_1) }}">
                                <small class="text-muted">Se muestra bajo el H1, en el banner.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Banner / Imagen superior (card2_image_4) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-banner">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-banner" aria-expanded="false" aria-controls="sec-banner">
                        Banner (imagen superior) <span class="badge text-bg-success ms-2">Visible en Propiedades</span>
                    </button>
                </h2>
                <div id="sec-banner" class="accordion-collapse collapse" aria-labelledby="hdr-banner" data-bs-parent="#propsEditor">
                    <div class="accordion-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label for="card2_image_4" class="form-label">Imagen del banner</label>
                                <input type="file" name="card2_image_4" id="card2_image_4" class="form-control" accept=".jpg,.jpeg,.png,.webp,.avif">
                                <small class="text-muted d-block mt-1">Usada como fondo del hero en la página de Propiedades.</small>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted mb-1">Previsualización</div>
                                <div id="bannerPreview" class="border rounded d-flex align-items-center justify-content-center" style="height:120px; overflow:hidden;">
                                    @if($paginaPropiedades->card2_image_4)
                                        <img src="{{ asset('images/' . $paginaPropiedades->card2_image_4) }}" alt="Imagen actual" class="img-fluid">
                                    @else
                                        <span class="small">Sin imagen seleccionada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($paginaPropiedades->card2_image_4)
                            <div class="mt-2">
                                <small class="text-muted">Actual: </small>
                                <img src="{{ asset('images/' . $paginaPropiedades->card2_image_4) }}" alt="Imagen actual" style="max-height:60px" class="ms-1 rounded">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SEO (no visible) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-seo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-seo" aria-expanded="false" aria-controls="sec-seo">
                        Metadatos SEO <span class="badge text-bg-secondary ms-2">No visible</span>
                    </button>
                </h2>
                <div id="sec-seo" class="accordion-collapse collapse" aria-labelledby="hdr-seo" data-bs-parent="#propsEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control"
                                       value="{{ old('meta_title', $paginaPropiedades->meta->meta_title ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <input type="text" name="meta_description" id="meta_description" class="form-control"
                                       value="{{ old('meta_description', $paginaPropiedades->meta->meta_description ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                                       value="{{ old('meta_keywords', $paginaPropiedades->meta->meta_keywords ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="canonical_url" class="form-label">Canonical URL</label>
                                <input type="text" name="canonical_url" id="canonical_url" class="form-control"
                                       value="{{ old('canonical_url', $paginaPropiedades->meta->canonical_url ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="robots" class="form-label">Robots</label>
                                <input type="text" name="robots" id="robots" class="form-control"
                                       value="{{ old('robots', $paginaPropiedades->meta->robots ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" name="author" id="author" class="form-control"
                                       value="{{ old('author', $paginaPropiedades->meta->author ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="language" class="form-label">Language</label>
                                <input type="text" name="language" id="language" class="form-control"
                                       value="{{ old('language', $paginaPropiedades->meta->language ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="viewport" class="form-label">Viewport</label>
                                <input type="text" name="viewport" id="viewport" class="form-control"
                                       value="{{ old('viewport', $paginaPropiedades->meta->viewport ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="charset" class="form-label">Charset</label>
                                <input type="text" name="charset" id="charset" class="form-control"
                                       value="{{ old('charset', $paginaPropiedades->meta->charset ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- /accordion --}}

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'card2_image_4' && e.target.files && e.target.files[0]) {
        const url = URL.createObjectURL(e.target.files[0]);
        const box = document.getElementById('bannerPreview');
        if (box) box.innerHTML = `<img src="${url}" class="img-fluid">`;
    }
});
</script>
@endpush
