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
                   value="{{ old('h1', $contact->h1 ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="h2" class="form-label">Subtítulo (H2)</label>
            <input type="text" name="h2" id="h2" class="form-control"
                   value="{{ old('h2', $contact->h2 ?? '') }}">
        </div>

        <div class="mb-3">
            <label for="intro_text" class="form-label">Introducción</label>
            <textarea name="intro_text" id="intro_text" rows="3" class="form-control">{{ old('intro_text', $contact->intro_text ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label for="side_text" class="form-label">Texto lateral / apoyo (opcional)</label>
            <textarea name="side_text" id="side_text" rows="3" class="form-control">{{ old('side_text', $contact->side_text ?? '') }}</textarea>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Imagen Hero</label>
                <input type="file" name="hero_image" class="form-control" accept="image/*">
                @if(!empty($contact->hero_image))
                    <div class="mt-2">
                        <img src="{{ asset('images/'.$contact->hero_image) }}" style="max-width:240px;height:auto" alt="Hero">
                    </div>
                @endif
                <small class="text-muted">Sugerido: 1920×900 (JPG/WEBP/AVIF)</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Imagen Banner (opcional)</label>
                <input type="file" name="banner_image" class="form-control" accept="image/*">
                @if(!empty($contact->banner_image))
                    <div class="mt-2">
                        <img src="{{ asset('images/'.$contact->banner_image) }}" style="max-width:240px;height:auto" alt="Banner">
                    </div>
                @endif
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Datos de Contacto ===== --}}
        <h4 class="mb-3">Datos de contacto</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email principal</label>
                <input type="email" name="email_primary" class="form-control"
                       value="{{ old('email_primary', $contact->email_primary) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email secundario</label>
                <input type="email" name="email_secondary" class="form-control"
                       value="{{ old('email_secondary', $contact->email_secondary) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Teléfono principal</label>
                <input type="text" name="phone_primary" class="form-control"
                       value="{{ old('phone_primary', $contact->phone_primary) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Teléfono secundario</label>
                <input type="text" name="phone_secondary" class="form-control"
                       value="{{ old('phone_secondary', $contact->phone_secondary) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control"
                       value="{{ old('whatsapp', $contact->whatsapp) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Sitio web</label>
                <input type="url" name="website" class="form-control"
                       value="{{ old('website', $contact->website) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Dirección (línea 1)</label>
                <input type="text" name="address_line1" class="form-control"
                       value="{{ old('address_line1', $contact->address_line1) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Dirección (línea 2)</label>
                <input type="text" name="address_line2" class="form-control"
                       value="{{ old('address_line2', $contact->address_line2) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" name="city" class="form-control"
                       value="{{ old('city', $contact->city) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Provincia/Región</label>
                <input type="text" name="region" class="form-control"
                       value="{{ old('region', $contact->region) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Código Postal</label>
                <input type="text" name="postal_code" class="form-control"
                       value="{{ old('postal_code', $contact->postal_code) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">País</label>
                <input type="text" name="country" class="form-control"
                       value="{{ old('country', $contact->country) }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Horario (business_hours JSON) ===== --}}
        <h4 class="mb-3">Horario de atención</h4>
        @php
            $rows = $contact->business_hours_array ?? [];
            if (count($rows) === 0) {
                $rows = [
                    ['label' => 'Lun–Vie', 'from' => '09:00', 'to' => '19:00'],
                    ['label' => 'Sáb', 'from' => '10:00', 'to' => '14:00'],
                    ['label' => 'Dom', 'from' => 'Cerrado', 'to' => ''],
                ];
            }
        @endphp

        <div id="hours-rows" class="mb-3">
            @foreach($rows as $i => $r)
                <div class="row g-2 align-items-end mb-2 hours-row">
                    <div class="col-md-4">
                        <label class="form-label">Etiqueta</label>
                        <input type="text" name="business_hours[{{ $i }}][label]" class="form-control" value="{{ $r['label'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="text" name="business_hours[{{ $i }}][from]" class="form-control" placeholder="09:00" value="{{ $r['from'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="text" name="business_hours[{{ $i }}][to]" class="form-control" placeholder="19:00" value="{{ $r['to'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100 remove-row">Quitar</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-4">
            <button type="button" id="add-hour-row" class="btn btn-outline-primary">Añadir fila</button>
            <small class="text-muted ms-2">Deja “Hasta” vacío para “Cerrado”.</small>
        </div>

        <hr class="my-4">

        {{-- ===== Mapa ===== --}}
        <h4 class="mb-3">Mapa</h4>
        <div class="mb-3">
            <label class="form-label">URL del mapa (iframe src)</label>
            <input type="text" name="map_embed_url" class="form-control"
                   value="{{ old('map_embed_url', $contact->map_embed_url) }}"
                   placeholder="https://www.google.com/maps/embed?...">
            <small class="text-muted">Pega el <strong>src</strong> del iframe de Google Maps (no el iframe completo).</small>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Latitud</label>
                <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $contact->latitude) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Longitud</label>
                <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $contact->longitude) }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- ===== Redes Sociales ===== --}}
        <h4 class="mb-3">Redes sociales</h4>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Facebook</label><input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $contact->facebook_url) }}"></div>
            <div class="col-md-6"><label class="form-label">Instagram</label><input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $contact->instagram_url) }}"></div>
            <div class="col-md-6"><label class="form-label">X / Twitter</label><input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $contact->twitter_url) }}"></div>
            <div class="col-md-6"><label class="form-label">TikTok</label><input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $contact->tiktok_url) }}"></div>
            <div class="col-md-6"><label class="form-label">YouTube</label><input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $contact->youtube_url) }}"></div>
            <div class="col-md-6"><label class="form-label">LinkedIn</label><input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $contact->linkedin_url) }}"></div>
        </div>

        <hr class="my-4">

        {{-- ===== Envío de Formulario / Legal ===== --}}
        <h4 class="mb-3">Formulario y legal</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Enviar formularios a (To)</label>
                <input type="email" name="form_recipient" class="form-control" value="{{ old('form_recipient', $contact->form_recipient) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">CC (opcional, separar por coma)</label>
                <input type="text" name="form_cc" class="form-control" value="{{ old('form_cc', $contact->form_cc) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Mensaje de éxito</label>
                <input type="text" name="success_message" class="form-control" value="{{ old('success_message', $contact->success_message ?? '¡Gracias! Te responderemos muy pronto.') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Label checkbox legal</label>
                <input type="text" name="legal_checkbox_label" class="form-control" value="{{ old('legal_checkbox_label', $contact->legal_checkbox_label) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">URL política/aviso legal</label>
                <input type="url" name="legal_link_url" class="form-control" value="{{ old('legal_link_url', $contact->legal_link_url) }}">
            </div>
        </div>

        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $contact->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Página activa</label>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>

{{-- JS mínimo para horario --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const box = document.getElementById('hours-rows');
  const add = document.getElementById('add-hour-row');
  const tpl = (i) => `
    <div class="row g-2 align-items-end mb-2 hours-row">
      <div class="col-md-4">
        <label class="form-label">Etiqueta</label>
        <input type="text" name="business_hours[${i}][label]" class="form-control" value="">
      </div>
      <div class="col-md-3">
        <label class="form-label">Desde</label>
        <input type="text" name="business_hours[${i}][from]" class="form-control" placeholder="09:00" value="">
      </div>
      <div class="col-md-3">
        <label class="form-label">Hasta</label>
        <input type="text" name="business_hours[${i}][to]" class="form-control" placeholder="19:00" value="">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-outline-danger w-100 remove-row">Quitar</button>
      </div>
    </div>`;
  add?.addEventListener('click', () => {
    const idx = box.querySelectorAll('.hours-row').length;
    box.insertAdjacentHTML('beforeend', tpl(idx));
  });
  box?.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-row')) {
      e.target.closest('.hours-row')?.remove();
    }
  });
});
</script>
@endsection
