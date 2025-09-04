@extends('layouts.admin')

@section('title', 'Editar Página de Inicio')

@section('content')
<div class="container">

    <h1 class="mb-4">Editar Página de Inicio</h1>

    <form action="{{ route('admin.pagina.update', $pagina->id ?? 1) }}" method="POST" enctype="multipart/form-data">
    @csrf
        @method('PUT')

        <input type="hidden" name="pagina_id" value="{{ $pagina->id }}">

        {{-- Encabezados --}}
        <div class="card mb-4">
            <div class="card-header">Encabezados</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="h1" class="form-label">Título principal (h1)</label>
                    <input type="text" name="h1" class="form-control" value="{{ old('h1', $pagina->h1) }}">
                </div>

                <div class="mb-3">
                    <label for="h2_1" class="form-label">Subtítulo (h2)</label>
                    <input type="text" name="h2_1" class="form-control" value="{{ old('h2_1', $pagina->h2_1) }}">
                </div>
            </div>
        </div>

        {{-- Sección Propiedades --}}
        <div class="card mb-4">
            <div class="card-header">Sección: Propiedades</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="h2_propiedades" class="form-label">Título h2</label>
                    <input type="text" name="h2_propiedades" class="form-control" value="{{ old('h2_propiedades', $pagina->h2_propiedades) }}">
                </div>

                <div class="mb-3">
                    <label for="p_propiedades" class="form-label">Contenido</label>
                    <textarea name="p_propiedades" class="form-control" rows="3">{{ old('p_propiedades', $pagina->p_propiedades) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Sección Propiedad con Hostella --}}
        <div class="card mb-4">
            <div class="card-header">Sección: Propiedad con Hostella</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="h2_hostella" class="form-label">Título h2</label>
                    <input type="text" name="h2_hostella" class="form-control" value="{{ old('h2_hostella', $pagina->h2_hostella) }}">
                </div>

                <div class="mb-3">
                    <label for="p_hostella" class="form-label">Contenido</label>
                    <textarea name="p_hostella" class="form-control" rows="3">{{ old('p_hostella', $pagina->p_hostella) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Tarjetas sección 1 --}}
        <div class="card mb-4">
            <div class="card-header">Tarjetas - Sección 1</div>
            <div class="card-body">
                @for ($i = 1; $i <= 3; $i++)
                    <div class="mb-3">
                        <label for="card1_title_{{ $i }}">Título tarjeta {{ $i }}</label>
                        <input type="text" name="card1_title_{{ $i }}" class="form-control" value="{{ old("card1_title_$i", $pagina["card1_title_$i"]) }}">
                    </div>
                    <div class="mb-3">
                        <label for="card1_content_{{ $i }}">Contenido tarjeta {{ $i }}</label>
                        <textarea name="card1_content_{{ $i }}" class="form-control" rows="2">{{ old("card1_content_$i", $pagina["card1_content_$i"]) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="card1_image_{{ $i }}">Imagen tarjeta {{ $i }}</label>
                        <input type="file" name="card1_image_{{ $i }}" class="form-control">

                        @php
                            $imageField = "card1_image_$i";
                        @endphp

                        @if (!empty($pagina->$imageField))
                            <div class="mt-2">
                            <img src="{{ url('images/' . $pagina->$imageField) }}" alt="Imagen tarjeta {{ $i }}" style="max-height: 100px;">
                            </div>
                        @endif
                    </div>

                    <hr>
                @endfor
            </div>
        </div>


        {{-- Lugar favorito --}}
        <div class="card mb-4">
            <div class="card-header">Lugar Favorito</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="p_lugar_favorito" class="form-label">Texto</label>
                    <textarea name="p_lugar_favorito" class="form-control" rows="3">{{ old('p_lugar_favorito', $pagina->p_lugar_favorito) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Confianza en Hostella --}}
        <div class="card mb-4">
            <div class="card-header">¿Por qué confían en Hostella?</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="h2_confiar" class="form-label">Título h2</label>
                    <input type="text" name="h2_confiar" class="form-control" value="{{ old('h2_confiar', $pagina->h2_confiar) }}">
                </div>

                <div class="mb-3">
                    <label for="p_confiar" class="form-label">Contenido</label>
                    <textarea name="p_confiar" class="form-control" rows="3">{{ old('p_confiar', $pagina->p_confiar) }}</textarea>
                </div>

                <hr>
<h6 class="mt-4">Tarjetas adicionales</h6>

@for ($i = 4; $i <= 7; $i++)
    <div class="mb-3">
        <label for="card2_title_{{ $i }}">Título tarjeta {{ $i }}</label>
        <input type="text" name="card2_title_{{ $i }}" class="form-control" value="{{ old("card2_title_$i", $pagina["card2_title_$i"]) }}">
    </div>

    <div class="mb-3">
        <label for="card2_content_{{ $i }}">Contenido tarjeta {{ $i }}</label>
        <textarea name="card2_content_{{ $i }}" class="form-control" rows="2">{{ old("card2_content_$i", $pagina["card2_content_$i"]) }}</textarea>
    </div>

    <div class="mb-3">
        <label for="card2_image_{{ $i }}">Imagen tarjeta {{ $i }}</label>
        <input type="file" name="card2_image_{{ $i }}" class="form-control">

        @php
            $imageField = "card2_image_$i";
        @endphp

        @if (!empty($pagina->$imageField))
            <div class="mt-2">
                <img src="{{ asset('images/' . $pagina->$imageField) }}" alt="Imagen tarjeta {{ $i }}" style="max-height: 100px;">
            </div>
        @endif
    </div>

    <hr>
@endfor
            </div>
        </div>

        {{-- Redes y logo --}}
        <div class="card mb-4">
            <div class="card-header">Información General</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="facebook" class="form-label">Facebook</label>
                    <input type="url" name="facebook" class="form-control" value="{{ old('facebook', $pagina->facebook) }}">
                </div>

                <div class="mb-3">
                    <label for="instagram" class="form-label">Instagram</label>
                    <input type="url" name="instagram" class="form-control" value="{{ old('instagram', $pagina->instagram) }}">
                </div>

                <div class="mb-3">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $pagina->whatsapp) }}">
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control">
                    @if ($pagina->logo)
                    <img src="{{ asset('images/' . $pagina->logo) }}" height="60" class="mt-2" alt="Logo actual">
                    @endif
                </div>
            </div>
        </div>
        {{-- SEO --}}
<div class="card mb-4">
    <div class="card-header">Metadatos para SEO</div>
    <div class="card-body">
        <div class="mb-3">
            <label for="meta_title" class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $pagina->meta->meta_title ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="meta_description" class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $pagina->meta->meta_description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="meta_keywords" class="form-label">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $pagina->meta->meta_keywords ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="canonical_url" class="form-label">Canonical URL</label>
            <input type="text" name="canonical_url" class="form-control" value="{{ old('canonical_url', $pagina->meta->canonical_url ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="robots" class="form-label">Robots</label>
            <input type="text" name="robots" class="form-control" value="{{ old('robots', $pagina->meta->robots ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Author</label>
            <input type="text" name="author" class="form-control" value="{{ old('author', $pagina->meta->author ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="language" class="form-label">Language</label>
            <input type="text" name="language" class="form-control" value="{{ old('language', $pagina->meta->language ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="viewport" class="form-label">Viewport</label>
            <input type="text" name="viewport" class="form-control" value="{{ old('viewport', $pagina->meta->viewport ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="charset" class="form-label">Charset</label>
            <input type="text" name="charset" class="form-control" value="{{ old('charset', $pagina->meta->charset ?? '') }}">
        </div>
    </div>
</div>


        {{-- Botón guardar --}}
        <div class="text-end">
            <button type="submit" class="btn btn-success">Guardar cambios</button>
        </div>
    </form>

</div>
@endsection
