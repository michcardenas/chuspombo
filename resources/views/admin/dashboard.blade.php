@extends('layouts.admin')

@section('title', 'Editar Página de Inicio')

@section('content')
<div class="container py-3">

    <h1 class="mb-3">Editar Página de Inicio</h1>

    <form action="{{ route('admin.pagina.update', $pagina->id ?? 1) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="pagina_id" value="{{ $pagina->id }}">

        <div class="accordion" id="homeEditor">

            {{-- Galería del Carrusel (Hero) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-gallery">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-gallery" aria-expanded="true" aria-controls="sec-gallery">
                        Galería del Carrusel (Hero) <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-gallery" class="accordion-collapse collapse show" aria-labelledby="hdr-gallery" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Galería del carrusel.</strong> Selecciona múltiples imágenes para el fondo del carrusel principal.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-images me-2"></i>Seleccionar imágenes
                                </label>
                                <input type="file" name="gallery_images[]" id="gallery-upload"
                                       class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Puedes seleccionar múltiples imágenes a la vez</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Acciones</label>
                                <div class="d-grid gap-2">
                                    <button type="button" id="clear-gallery" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Limpiar todo
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Galería actual --}}
                        @php
                            $galleryImages = json_decode($pagina->gallery_images ?? '[]', true);
                            if (!is_array($galleryImages)) $galleryImages = [];
                        @endphp

                        <div id="gallery-preview" class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-layer-group me-2"></i>Imágenes actuales
                                    <span class="badge bg-secondary" id="image-count">{{ count($galleryImages) }}</span>
                                </h6>
                                <div class="small text-muted">Arrastra para reordenar</div>
                            </div>

                            <div id="gallery-grid" class="row g-2">
                                @foreach($galleryImages as $index => $img)
                                    <div class="col-md-2 col-4 gallery-item" data-index="{{ $index }}">
                                        <div class="card h-100">
                                            <div class="position-relative">
                                                <img src="{{ asset('images/' . $img['image']) }}"
                                                     class="card-img-top" style="height: 100px; object-fit: cover;" alt="Imagen {{ $index + 1 }}">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-image"
                                                        data-index="{{ $index }}" style="padding: 2px 6px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <div class="position-absolute bottom-0 start-0 m-1">
                                                    <span class="badge bg-dark bg-opacity-75">{{ $index + 1 }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-2">
                                                <input type="text" name="gallery_titles[]" class="form-control form-control-sm"
                                                       placeholder="Título opcional" value="{{ $img['title'] ?? '' }}">
                                                <input type="hidden" name="existing_images[]" value="{{ $img['image'] }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="no-images" class="text-center py-4 text-muted" style="{{ count($galleryImages) > 0 ? 'display: none;' : '' }}">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <div>No hay imágenes en la galería</div>
                                <div class="small">Selecciona archivos arriba para comenzar</div>
                            </div>
                        </div>

                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <i class="fas fa-expand-arrows-alt text-primary"></i>
                                    <div class="small"><strong>1920x900px</strong></div>
                                    <div class="text-muted">Recomendado</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-file-image text-success"></i>
                                    <div class="small"><strong>JPG, PNG, WEBP</strong></div>
                                    <div class="text-muted">Formatos</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-weight-hanging text-warning"></i>
                                    <div class="small"><strong>Max 4MB</strong></div>
                                    <div class="text-muted">Por imagen</div>
                                </div>
                                <div class="col-md-3">
                                    <i class="fas fa-sort text-info"></i>
                                    <div class="small"><strong>Reordenable</strong></div>
                                    <div class="text-muted">Arrastra y suelta</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Encabezados (Hero) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-hero">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-hero" aria-expanded="false" aria-controls="sec-hero">
                        Encabezados (Hero) <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-hero" class="accordion-collapse collapse" aria-labelledby="hdr-hero" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="h1" class="form-label">Título (H1)</label>
                                <input type="text" name="h1" id="h1" class="form-control" placeholder="Ej: Descubre Propiedades Exclusivas" value="{{ old('h1', $pagina->h1) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="h2_1" class="form-label">Subtítulo (H2)</label>
                                <input type="text" name="h2_1" id="h2_1" class="form-control" placeholder="Ej: Apartamentos de lujo en Galicia" value="{{ old('h2_1', $pagina->h2_1) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Propiedades (listado destacado) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-props">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-props" aria-expanded="false" aria-controls="sec-props">
                        Propiedades (listado destacado) <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-props" class="accordion-collapse collapse" aria-labelledby="hdr-props" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="h2_propiedades" class="form-label">Título (H2)</label>
                                <input type="text" name="h2_propiedades" id="h2_propiedades" class="form-control" placeholder="Ej: Propiedades destacadas en Galicia" value="{{ old('h2_propiedades', $pagina->h2_propiedades) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="p_propiedades" class="form-label">Texto</label>
                                <input type="text" name="p_propiedades" id="p_propiedades" class="form-control" placeholder="Resumen corto…" value="{{ old('p_propiedades', $pagina->p_propiedades) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Experiencias / Chuspombo (mantiene name: h2_hostella / p_hostella) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-exp">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-exp" aria-expanded="false" aria-controls="sec-exp">
                        Experiencias / Chuspombo <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-exp" class="accordion-collapse collapse" aria-labelledby="hdr-exp" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="h2_hostella" class="form-label">Título (H2)</label>
                                <input type="text" name="h2_hostella" id="h2_hostella" class="form-control" placeholder="Ej: Experiencias de lujo en Galicia" value="{{ old('h2_hostella', $pagina->h2_hostella) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="p_hostella" class="form-label">Texto</label>
                                <input type="text" name="p_hostella" id="p_hostella" class="form-control" placeholder="Resumen corto…" value="{{ old('p_hostella', $pagina->p_hostella) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjetas sección 1 (1–3) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-cards1">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-cards1" aria-expanded="false" aria-controls="sec-cards1">
                        Tarjetas sección 1 (1–3) <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-cards1" class="accordion-collapse collapse" aria-labelledby="hdr-cards1" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-4">
                            @for ($i = 1; $i <= 3; $i++)
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label" for="card1_title_{{ $i }}">Título {{ $i }}</label>
                                                <input type="text" id="card1_title_{{ $i }}" name="card1_title_{{ $i }}" class="form-control" value="{{ old("card1_title_$i", $pagina["card1_title_$i"]) }}" placeholder="Ej: Ubicación Premium">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" for="card1_content_{{ $i }}">Texto {{ $i }}</label>
                                                <input type="text" id="card1_content_{{ $i }}" name="card1_content_{{ $i }}" class="form-control" value="{{ old("card1_content_$i", $pagina["card1_content_$i"]) }}" placeholder="Breve descripción">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" for="card1_image_{{ $i }}">Imagen (opcional)</label>
                                                <input type="file" id="card1_image_{{ $i }}" name="card1_image_{{ $i }}" class="form-control">
                                                @php $imageField = "card1_image_$i"; @endphp
                                                @if (!empty($pagina->$imageField))
                                                    <img src="{{ asset('images/' . $pagina->$imageField) }}" class="mt-2 img-fluid rounded" style="max-height:100px" alt="Card {{ $i }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lugar favorito (selector de propiedad) --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-fav">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-fav" aria-expanded="false" aria-controls="sec-fav">
                        Lugar favorito <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-fav" class="accordion-collapse collapse" aria-labelledby="hdr-fav" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label for="featured_property_id" class="form-label">Propiedad a destacar</label>
                                <select name="featured_property_id" id="featured_property_id" class="form-select">
                                    <option value="">— Selecciona una propiedad —</option>
                                    @foreach(($featuredProperties ?? []) as $p)
                                        @php
                                            $id   = $p['_id'] ?? null;
                                            $tit  = $p['title'] ?? 'Sin título';
                                            $city = $p['address']['city'] ?? null;
                                            $ctry = $p['address']['country'] ?? null;
                                            $loc  = trim(($city ? $city : '') . ($city && $ctry ? ', ' : '') . ($ctry ?: ''));
                                            $thumb = $p['picture']['thumbnail'] ?? '';
                                        @endphp
                                        @if($id)
                                            <option value="{{ $id }}"
                                                    data-thumb="{{ $thumb }}"
                                                    {{ (string)old('featured_property_id', $pagina->featured_property_id) === (string)$id ? 'selected' : '' }}>
                                                {{ $tit }}@if($loc) — {{ $loc }}@endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div id="favPreview">
                                    @php
                                        $sel = collect($featuredProperties ?? [])->firstWhere('_id', old('featured_property_id', $pagina->featured_property_id));
                                        $selThumb = $sel['picture']['thumbnail'] ?? null;
                                    @endphp
                                    @if($selThumb)
                                        <img src="{{ $selThumb }}" class="img-thumbnail" style="max-height:64px" alt="Preview">
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="p_lugar_favorito" class="form-label">Texto</label>
                                <input type="text" name="p_lugar_favorito" id="p_lugar_favorito" class="form-control" placeholder="Texto breve…" value="{{ old('p_lugar_favorito', $pagina->p_lugar_favorito) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ¿Por qué confían? + tarjetas 4–7 --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-trust">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-trust" aria-expanded="false" aria-controls="sec-trust">
                        ¿Por qué confían? (incluye 4 tarjetas) <span class="badge text-bg-success ms-2">Home</span>
                    </button>
                </h2>
                <div id="sec-trust" class="accordion-collapse collapse" aria-labelledby="hdr-trust" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="h2_confiar" class="form-label">Título (H2)</label>
                                <input type="text" name="h2_confiar" id="h2_confiar" class="form-control" placeholder="Ej: ¿Por qué elegir Chuspombo?" value="{{ old('h2_confiar', $pagina->h2_confiar) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="p_confiar" class="form-label">Texto</label>
                                <input type="text" name="p_confiar" id="p_confiar" class="form-control" placeholder="Resumen corto…" value="{{ old('p_confiar', $pagina->p_confiar) }}">
                            </div>
                        </div>

                        <div class="row g-4">
                            @for ($i = 4; $i <= 7; $i++)
                                <div class="col-md-6 col-xl-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label" for="card2_title_{{ $i }}">Título {{ $i }}</label>
                                                <input type="text" id="card2_title_{{ $i }}" name="card2_title_{{ $i }}" class="form-control" value="{{ old("card2_title_$i", $pagina["card2_title_$i"]) }}" placeholder="Ej: Limpieza impecable">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" for="card2_content_{{ $i }}">Texto {{ $i }}</label>
                                                <input type="text" id="card2_content_{{ $i }}" name="card2_content_{{ $i }}" class="form-control" value="{{ old("card2_content_$i", $pagina["card2_content_$i"]) }}" placeholder="Breve descripción">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" for="card2_image_{{ $i }}">Imagen (opcional)</label>
                                                <input type="file" id="card2_image_{{ $i }}" name="card2_image_{{ $i }}" class="form-control">
                                                @php $imageField = "card2_image_$i"; @endphp
                                                @if (!empty($pagina->$imageField))
                                                    <img src="{{ asset('images/' . $pagina->$imageField) }}" class="mt-2 img-fluid rounded" style="max-height:100px" alt="Card {{ $i }}">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información general --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-info">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-info" aria-expanded="false" aria-controls="sec-info">
                        Información general <span class="badge text-bg-primary ms-2">Admin</span>
                    </button>
                </h2>
                <div id="sec-info" class="accordion-collapse collapse" aria-labelledby="hdr-info" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="facebook" class="form-label">Facebook</label>
                                <input type="url" name="facebook" id="facebook" class="form-control" placeholder="https://facebook.com/..." value="{{ old('facebook', $pagina->facebook) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="instagram" class="form-label">Instagram</label>
                                <input type="url" name="instagram" id="instagram" class="form-control" placeholder="https://instagram.com/..." value="{{ old('instagram', $pagina->instagram) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" id="whatsapp" class="form-control" placeholder="+57..." value="{{ old('whatsapp', $pagina->whatsapp) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" name="direccion" id="direccion" class="form-control"
                                    placeholder="Ej: España, Galicia"
                                    value="{{ old('direccion', $pagina->direccion) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo de contacto</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="info@chuspombo.com"
                                    value="{{ old('email', $pagina->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="logo" class="form-label">Logo</label>
                                <input type="file" name="logo" id="logo" class="form-control">
                                @if ($pagina->logo)
                                    <img src="{{ asset('images/' . $pagina->logo) }}" class="mt-2 img-fluid rounded" style="max-height:60px" alt="Logo actual">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-seo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-seo" aria-expanded="false" aria-controls="sec-seo">
                        SEO <span class="badge text-bg-secondary ms-2">No visible en Home</span>
                    </button>
                </h2>
                <div id="sec-seo" class="accordion-collapse collapse" aria-labelledby="hdr-seo" data-bs-parent="#homeEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="meta_title" class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title', $pagina->meta->meta_title ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="meta_description" class="form-label">Meta Description</label>
                                <input type="text" name="meta_description" id="meta_description" class="form-control" value="{{ old('meta_description', $pagina->meta->meta_description ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="{{ old('meta_keywords', $pagina->meta->meta_keywords ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="canonical_url" class="form-label">Canonical URL</label>
                                <input type="text" name="canonical_url" id="canonical_url" class="form-control" value="{{ old('canonical_url', $pagina->meta->canonical_url ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="robots" class="form-label">Robots</label>
                                <input type="text" name="robots" id="robots" class="form-control" value="{{ old('robots', $pagina->meta->robots ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" name="author" id="author" class="form-control" value="{{ old('author', $pagina->meta->author ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="language" class="form-label">Language</label>
                                <input type="text" name="language" id="language" class="form-control" value="{{ old('language', $pagina->meta->language ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="viewport" class="form-label">Viewport</label>
                                <input type="text" name="viewport" id="viewport" class="form-control" value="{{ old('viewport', $pagina->meta->viewport ?? '') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="charset" class="form-label">Charset</label>
                                <input type="text" name="charset" id="charset" class="form-control" value="{{ old('charset', $pagina->meta->charset ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- /accordion --}}

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const galleryUpload = document.getElementById('gallery-upload');
  const galleryGrid = document.getElementById('gallery-grid');
  const noImages = document.getElementById('no-images');
  const imageCount = document.getElementById('image-count');
  const clearGallery = document.getElementById('clear-gallery');

  // Actualizar contador de imágenes
  function updateImageCount() {
    const count = galleryGrid.children.length;
    imageCount.textContent = count;
    noImages.style.display = count === 0 ? 'block' : 'none';
  }

  // Crear elemento de imagen para la galería
  function createImageElement(src, title = '', isExisting = false, imageName = '') {
    const index = galleryGrid.children.length;
    const colDiv = document.createElement('div');
    colDiv.className = 'col-md-2 col-4 gallery-item';
    colDiv.setAttribute('data-index', index);

    colDiv.innerHTML = `
      <div class="card h-100">
        <div class="position-relative">
          <img src="${src}" class="card-img-top" style="height: 100px; object-fit: cover;" alt="Imagen ${index + 1}">
          <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-image"
                  data-index="${index}" style="padding: 2px 6px;">
            <i class="fas fa-times"></i>
          </button>
          <div class="position-absolute bottom-0 start-0 m-1">
            <span class="badge bg-dark bg-opacity-75">${index + 1}</span>
          </div>
        </div>
        <div class="card-body p-2">
          <input type="text" name="gallery_titles[]" class="form-control form-control-sm"
                 placeholder="Título opcional" value="${title}">
          ${isExisting ? `<input type="hidden" name="existing_images[]" value="${imageName}">` : ''}
        </div>
      </div>
    `;

    return colDiv;
  }

  // Manejar selección múltiple de archivos
  galleryUpload.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);

    files.forEach(file => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const imageElement = createImageElement(e.target.result);
          galleryGrid.appendChild(imageElement);
          updateImageCount();
          updateIndices();
        };
        reader.readAsDataURL(file);
      }
    });

    // Limpiar el input para permitir seleccionar los mismos archivos de nuevo
    e.target.value = '';
  });

  // Manejar eliminación de imágenes
  document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-image')) {
      e.preventDefault();
      const button = e.target.closest('.remove-image');
      const galleryItem = button.closest('.gallery-item');
      galleryItem.remove();
      updateImageCount();
      updateIndices();
    }
  });

  // Limpiar toda la galería
  clearGallery.addEventListener('click', function() {
    if (confirm('¿Estás seguro de que quieres eliminar todas las imágenes?')) {
      galleryGrid.innerHTML = '';
      updateImageCount();
    }
  });

  // Actualizar índices después de cambios
  function updateIndices() {
    const items = galleryGrid.querySelectorAll('.gallery-item');
    items.forEach((item, index) => {
      item.setAttribute('data-index', index);
      item.querySelector('.remove-image').setAttribute('data-index', index);
      item.querySelector('.badge').textContent = index + 1;
    });
  }

  // Hacer la galería sortable (drag & drop)
  new Sortable(galleryGrid, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    onEnd: function() {
      updateIndices();
    }
  });

  // Inicializar contador
  updateImageCount();
});

// Preview para propiedad destacada (mantener funcionalidad existente)
document.addEventListener('change', function(e){
  if (e.target && e.target.id === 'featured_property_id') {
    const opt = e.target.options[e.target.selectedIndex];
    const thumb = opt.getAttribute('data-thumb');
    const box = document.getElementById('favPreview');
    box.innerHTML = thumb ? `<img src="${thumb}" class="img-thumbnail" style="max-height:64px" alt="Preview">` : '';
  }
});
</script>

{{-- Incluir SortableJS para drag & drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<style>
.sortable-ghost {
  opacity: 0.4;
}
.gallery-item {
  cursor: move;
}
.gallery-item:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}
</style>
@endpush
