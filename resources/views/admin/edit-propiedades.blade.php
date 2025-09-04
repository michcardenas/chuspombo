@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Editar Página de Propiedades</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pagina.propiedades.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="h1" class="form-label">Título principal (H1)</label>
            <input type="text" name="h1" id="h1" class="form-control" value="{{ old('h1', $paginaPropiedades->h1) }}">
        </div>

        <div class="mb-4">
            <label for="h2_1" class="form-label">Subtítulo (H2)</label>
            <input type="text" name="h2_1" id="h2_1" class="form-control" value="{{ old('h2_1', $paginaPropiedades->h2_1) }}">
        </div>

        <div class="mb-3">
            <label for="card2_image_4" class="form-label">Imagen Card 2</label>
            <input type="file" name="card2_image_4" id="card2_image_4" class="form-control">

            @if($paginaPropiedades->card2_image_4)
    <div class="mt-2">
        <p>Imagen actual:</p>
        <img src="{{ asset('images/' . $paginaPropiedades->card2_image_4) }}" alt="Imagen actual" style="max-width: 200px;">
    </div>
@endif

        </div>

        <hr class="my-4">
        <h4 class="mb-3">Metadatos para SEO</h4>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="meta_title" class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $paginaPropiedades->meta->meta_title ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="meta_description" class="form-label">Meta Description</label>
                <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description', $paginaPropiedades->meta->meta_description ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $paginaPropiedades->meta->meta_keywords ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="canonical_url" class="form-label">Canonical URL</label>
                <input type="text" name="canonical_url" class="form-control" value="{{ old('canonical_url', $paginaPropiedades->meta->canonical_url ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="robots" class="form-label">Robots</label>
                <input type="text" name="robots" class="form-control" value="{{ old('robots', $paginaPropiedades->meta->robots ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" class="form-control" value="{{ old('author', $paginaPropiedades->meta->author ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="language" class="form-label">Language</label>
                <input type="text" name="language" class="form-control" value="{{ old('language', $paginaPropiedades->meta->language ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="viewport" class="form-label">Viewport</label>
                <input type="text" name="viewport" class="form-control" value="{{ old('viewport', $paginaPropiedades->meta->viewport ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="charset" class="form-label">Charset</label>
                <input type="text" name="charset" class="form-control" value="{{ old('charset', $paginaPropiedades->meta->charset ?? '') }}">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
