@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Editar Página de Contacto</h1>

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

    <form method="POST" action="{{ route('admin.pagina.contacto.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ===== Encabezados / Hero ===== --}}
        <div class="mb-3">
            <label for="h1" class="form-label">Título principal (H1)</label>
            <input type="text" name="h1" id="h1" class="form-control"
                   value="{{ old('h1', $paginaContacto->h1 ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="h2_1" class="form-label">Subtítulo (H2)</label>
            <input type="text" name="h2_1" id="h2_1" class="form-control"
                   value="{{ old('h2_1', $paginaContacto->h2_1 ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="intro" class="form-label">Introducción / texto breve</label>
            <textarea name="intro" id="intro" rows="3" class="form-control"
                      placeholder="Texto que aparece bajo el título.">{{ old('intro', $paginaContacto->intro ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="contact_hero_image" class="form-label">Imagen Hero (cabecera)</label>
            <input type="file" name="contact_hero_image" id="contact_hero_image" class="form-control" accept="image/*">
            @if(!empty($paginaContacto->contact_hero_image))
                <div class="mt-2">
                    <p class="mb-1">Imagen actual:</p>
                    <img src="{{ asset('images/' . $paginaContacto->contact_hero_image) }}"
                         alt="Hero actual" style="max-width: 240px; height:auto;">
                </div>
            @endif
            <small class="text-muted d-block mt-1">Sugerido: 1920×900px (JPG/WEBP).</small>
        </div>

        <hr class="my-4">

        {{-- ===== Datos de Contacto ===== --}}
        <h4 class="mb-3">Datos de contacto</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="contact_email" class="form-label">Email de contacto</label>
                <input type="email" name="contact_email" id="contact_email" class="form-control"
                       value="{{ old('contact_email', $paginaContacto->contact_email ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="contact_phone" class="form-label">Teléfono</label>
                <input type="text" name="contact_phone" id="contact_phone" class="form-control"
                       value="{{ old('contact_phone', $paginaContacto->contact_phone ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="contact_whatsapp" class="form-label">WhatsApp</label>
                <input type="text" name="contact_whatsapp" id="contact_whatsapp" class="form-control"
                       value="{{ old('contact_whatsapp', $paginaContacto->contact_whatsapp ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="contact_address_1" class="form-label">Dirección (línea 1)</label>
                <input type="text" name="contact_address_1" id="contact_address_1" class="form-control"
                       value="{{ old('contact_address_1', $paginaContacto->contact_address_1 ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="contact_address_2" class="form-label">Dirección (línea 2)</label>
                <input type="text" name="contact_address_2" id="contact_address_2" class="form-control"
                       value="{{ old('contact_address_2', $paginaContacto->contact_address_2 ?? '') }}">
            </div>
            <div class="col-md-4">
                <label for="contact_city" class="form-label">Ciudad</label>
                <input type="text" name="contact_city" id="contact_city" class="form-control"
                       value="{{ old('contact_city', $paginaContacto->contact_city ?? '') }}">
            </div>
            <div class="col-md-4">
                <label for="contact_region" class="form-label">Provincia/Región</label>
                <input type="text" name="contact_region" id="contact_region" class="form-control"
                       value="{{ old('contact_region', $paginaContacto->contact_region ?? '') }}">
            </div>
            <div class="col-md-4">
                <label for="contact_postal" class="form-label">Código Postal</label>
                <input type="text" name="contact_postal" id="contact_postal" class="form-control"
                       value="{{ old('contact_postal', $paginaContacto->contact_postal ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="contact_country" class="form-label">País</label>
                <input type="text" name="contact_country" id="contact_country" class="form-control"
                       value="{{ old('contact_country', $paginaContacto->contact_country ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="opening_hours" class="form-label">Horario de atención</label>
                <input type="text" name="opening_hours" id="opening_hours" class="form-control"
                       value="{{ old('opening_hours', $paginaContacto->opening_hours ?? '') }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Textos del Formulario ===== --}}
        <h4 class="mb-3">Textos del formulario</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="form_title" class="form-label">Título del formulario</label>
                <input type="text" name="form_title" id="form_title" class="form-control"
                       value="{{ old('form_title', $paginaContacto->form_title ?? 'Contáctanos') }}">
            </div>
            <div class="col-md-6">
                <label for="form_subtitle" class="form-label">Subtítulo del formulario</label>
                <input type="text" name="form_subtitle" id="form_subtitle" class="form-control"
                       value="{{ old('form_subtitle', $paginaContacto->form_subtitle ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="form_cta_label" class="form-label">Texto del botón (CTA)</label>
                <input type="text" name="form_cta_label" id="form_cta_label" class="form-control"
                       value="{{ old('form_cta_label', $paginaContacto->form_cta_label ?? 'Enviar mensaje') }}">
            </div>
            <div class="col-md-6">
                <label for="form_success_message" class="form-label">Mensaje de éxito</label>
                <input type="text" name="form_success_message" id="form_success_message" class="form-control"
                       value="{{ old('form_success_message', $paginaContacto->form_success_message ?? '¡Gracias! Te responderemos muy pronto.') }}">
            </div>
            <div class="col-12">
                <label for="form_error_message" class="form-label">Mensaje de error</label>
                <input type="text" name="form_error_message" id="form_error_message" class="form-control"
                       value="{{ old('form_error_message', $paginaContacto->form_error_message ?? 'No se pudo enviar. Intenta nuevamente más tarde.') }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Mapa ===== --}}
        <h4 class="mb-3">Mapa</h4>
        <div class="mb-3">
            <label for="map_iframe" class="form-label">Iframe del mapa (Google Maps)</label>
            <textarea name="map_iframe" id="map_iframe" rows="4" class="form-control"
                      placeholder='Pega aquí el iframe de Google Maps'>{{ old('map_iframe', $paginaContacto->map_iframe ?? '') }}</textarea>
            <small class="text-muted">Ejemplo: &lt;iframe src="https://www.google.com/maps/embed?..." ...&gt;&lt;/iframe&gt;</small>
        </div>

        <hr class="my-4">

        {{-- ===== Redes Sociales ===== --}}
        <h4 class="mb-3">Redes sociales</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="facebook_url" class="form-label">Facebook</label>
                <input type="url" name="facebook_url" id="facebook_url" class="form-control"
                       value="{{ old('facebook_url', $paginaContacto->facebook_url ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="instagram_url" class="form-label">Instagram</label>
                <input type="url" name="instagram_url" id="instagram_url" class="form-control"
                       value="{{ old('instagram_url', $paginaContacto->instagram_url ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="tiktok_url" class="form-label">TikTok</label>
                <input type="url" name="tiktok_url" id="tiktok_url" class="form-control"
                       value="{{ old('tiktok_url', $paginaContacto->tiktok_url ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="twitter_url" class="form-label">X / Twitter</label>
                <input type="url" name="twitter_url" id="twitter_url" class="form-control"
                       value="{{ old('twitter_url', $paginaContacto->twitter_url ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="youtube_url" class="form-label">YouTube</label>
                <input type="url" name="youtube_url" id="youtube_url" class="form-control"
                       value="{{ old('youtube_url', $paginaContacto->youtube_url ?? '') }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Metadatos SEO ===== --}}
        <h4 class="mb-3">Metadatos para SEO</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="meta_title" class="form-label">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" class="form-control"
                       value="{{ old('meta_title', $paginaContacto->meta->meta_title ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="meta_description" class="form-label">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" class="form-control"
                       value="{{ old('meta_description', $paginaContacto->meta->meta_description ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control"
                       value="{{ old('meta_keywords', $paginaContacto->meta->meta_keywords ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="canonical_url" class="form-label">Canonical URL</label>
                <input type="text" name="canonical_url" id="canonical_url" class="form-control"
                       value="{{ old('canonical_url', $paginaContacto->meta->canonical_url ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="robots" class="form-label">Robots</label>
                <input type="text" name="robots" id="robots" class="form-control"
                       value="{{ old('robots', $paginaContacto->meta->robots ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" id="author" class="form-control"
                       value="{{ old('author', $paginaContacto->meta->author ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="language" class="form-label">Language</label>
                <input type="text" name="language" id="language" class="form-control"
                       value="{{ old('language', $paginaContacto->meta->language ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="viewport" class="form-label">Viewport</label>
                <input type="text" name="viewport" id="viewport" class="form-control"
                       value="{{ old('viewport', $paginaContacto->meta->viewport ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="charset" class="form-label">Charset</label>
                <input type="text" name="charset" id="charset" class="form-control"
                       value="{{ old('charset', $paginaContacto->meta->charset ?? '') }}">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
