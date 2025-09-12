@extends('layouts.app')

@section('title', "Editar propiedad #$apartmentId")

@section('content')
<div class="container py-5">
    <h1 class="fw-bold mb-4">Editar propiedad (ID: {{ $apartmentId }})</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Info de Smoobu (solo lectura, opcional) --}}
    @if(!empty($detail))
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-2">Información desde Smoobu</h5>
                <p class="mb-1"><strong>Nombre:</strong> {{ $detail['name'] ?? '-' }}</p>
                <p class="mb-1"><strong>Ubicación:</strong> {{ $detail['location']['city'] ?? '' }} {{ isset($detail['location']['country']) ? '('.$detail['location']['country'].')' : '' }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.apartments.update', $apartmentId) }}" method="POST" enctype="multipart/form-data" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $meta->title) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $meta->slug) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" rows="5" class="form-control">{{ old('description', $meta->description) }}</textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $meta->city) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">País</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $meta->country) }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Habitaciones</label>
                    <input type="number" name="bedrooms" class="form-control" min="0" value="{{ old('bedrooms', $meta->bedrooms) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Baños</label>
                    <input type="number" step="0.5" name="bathrooms" class="form-control" min="0" value="{{ old('bathrooms', $meta->bathrooms) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Precio base (override)</label>
                    <input type="number" step="0.01" name="base_price_override" class="form-control" value="{{ old('base_price_override', $meta->base_price_override) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Token data-verification (Single Calendar)</label>
                    <input type="text" name="calendar_verification" class="form-control" value="{{ old('calendar_verification', $meta->calendar_verification) }}" placeholder="e.g. ca10b78b23...">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_published" id="is_published" class="form-check-input" value="1" {{ old('is_published', $meta->is_published) ? 'checked' : '' }}>
                        <label for="is_published" class="form-check-label">Publicado</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- Galería actual --}}
            <h5 class="mb-3">Imágenes actuales</h5>
            @if($images->count())
                <div class="row g-3">
                    @foreach($images as $img)
                        <div class="col-md-3">
                            <div class="card h-100">
                                <img src="{{ asset($img->path) }}" class="card-img-top" alt="img">
                                <div class="card-body small">
                                    <div class="mb-2">
                                        <label class="form-label">Orden</label>
                                        <input type="number" name="sort_order[{{ $img->id }}]" class="form-control form-control-sm" value="{{ $img->sort_order }}">
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="radio" name="cover_image_id" class="form-check-input" value="{{ $img->id }}" {{ $meta->cover_image_path === $img->path ? 'checked' : '' }}>
                                        <label class="form-check-label">Usar como portada</label>
                                    </div>

                                    <div class="form-check">
                                        <input type="checkbox" name="delete_images[]" class="form-check-input" value="{{ $img->id }}" id="del{{ $img->id }}">
                                        <label class="form-check-label text-danger" for="del{{ $img->id }}">Eliminar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Aún no hay imágenes.</p>
            @endif

            <hr class="my-4">

            {{-- Subir nuevas imágenes --}}
            <div class="mb-3">
                <label class="form-label">Agregar imágenes</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <small class="text-muted">Puedes subir varias.</small>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('properties.show', $apartmentId) }}" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </form>
</div>
@endsection
