@extends('layouts.admin')

@section('title', 'Editar Página de Contacto')

@section('content')
<div class="container py-3">
    <h1 class="mb-3">Editar Página de Contacto</h1>

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

        <div class="accordion" id="contactEditor">

            {{-- ===== Encabezados / Intro ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-encabezados">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sec-encabezados" aria-expanded="true" aria-controls="sec-encabezados">
                        Encabezados y texto intro
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-encabezados" class="accordion-collapse collapse show" aria-labelledby="hdr-encabezados" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="h1" class="form-label">Título principal (H1)</label>
                                <input type="text" name="h1" id="h1" class="form-control" value="{{ old('h1', $contact->h1 ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="h2" class="form-label">Subtítulo (H2)</label>
                                <input type="text" name="h2" id="h2" class="form-control" value="{{ old('h2', $contact->h2 ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label for="intro_text" class="form-label">Introducción</label>
                                <textarea name="intro_text" id="intro_text" rows="3" class="form-control">{{ old('intro_text', $contact->intro_text ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="side_text" class="form-label">Texto lateral / apoyo (opcional)</label>
                                <textarea name="side_text" id="side_text" rows="3" class="form-control">{{ old('side_text', $contact->side_text ?? '') }}</textarea>
                                <small class="text-muted">Se muestra en bloques informativos junto al formulario (si aplica).</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Imágenes (Hero / Banner) ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-imagenes">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-imagenes" aria-expanded="false" aria-controls="sec-imagenes">
                        Imágenes (Hero / Banner)
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-imagenes" class="accordion-collapse collapse" aria-labelledby="hdr-imagenes" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <div class="row g-4 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Imagen Hero</label>
                                <input type="file" name="hero_image" id="hero_image" class="form-control" accept="image/*">
                                <small class="text-muted d-block mt-1">Sugerido: 1920×900 (JPG/WEBP/AVIF)</small>
                                <div class="mt-2">
                                    <div class="text-muted mb-1">Previsualización</div>
                                    <div id="heroPreview" class="border rounded d-flex align-items-center justify-content-center" style="height:140px; overflow:hidden;">
                                        @if(!empty($contact->hero_image))
                                            <img src="{{ asset('images/'.$contact->hero_image) }}" class="img-fluid" alt="Hero">
                                        @else
                                            <span class="small">Sin imagen seleccionada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Imagen Banner (opcional)</label>
                                <input type="file" name="banner_image" id="banner_image" class="form-control" accept="image/*">
                                <div class="mt-2">
                                    <div class="text-muted mb-1">Previsualización</div>
                                    <div id="bannerPreview" class="border rounded d-flex align-items-center justify-content-center" style="height:140px; overflow:hidden;">
                                        @if(!empty($contact->banner_image))
                                            <img src="{{ asset('images/'.$contact->banner_image) }}" class="img-fluid" alt="Banner">
                                        @else
                                            <span class="small">Sin imagen seleccionada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!empty($contact->hero_image) || !empty($contact->banner_image))
                            <div class="mt-3 small text-muted">
                                @if(!empty($contact->hero_image))
                                    <div>Hero actual: <img src="{{ asset('images/'.$contact->hero_image) }}" style="max-height:50px" class="ms-1 rounded" alt=""></div>
                                @endif
                                @if(!empty($contact->banner_image))
                                    <div>Banner actual: <img src="{{ asset('images/'.$contact->banner_image) }}" style="max-height:50px" class="ms-1 rounded" alt=""></div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== Datos de Contacto ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-datos">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-datos" aria-expanded="false" aria-controls="sec-datos">
                        Datos de contacto
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-datos" class="accordion-collapse collapse" aria-labelledby="hdr-datos" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email principal</label>
                                <input type="email" name="email_primary" class="form-control" value="{{ old('email_primary', $contact->email_primary) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email secundario</label>
                                <input type="email" name="email_secondary" class="form-control" value="{{ old('email_secondary', $contact->email_secondary) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono principal</label>
                                <input type="text" name="phone_primary" class="form-control" value="{{ old('phone_primary', $contact->phone_primary) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono secundario</label>
                                <input type="text" name="phone_secondary" class="form-control" value="{{ old('phone_secondary', $contact->phone_secondary) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $contact->whatsapp) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sitio web</label>
                                <input type="url" name="website" class="form-control" value="{{ old('website', $contact->website) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Dirección (línea 1)</label>
                                <input type="text" name="address_line1" class="form-control" value="{{ old('address_line1', $contact->address_line1) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dirección (línea 2)</label>
                                <input type="text" name="address_line2" class="form-control" value="{{ old('address_line2', $contact->address_line2) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $contact->city) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Provincia/Región</label>
                                <input type="text" name="region" class="form-control" value="{{ old('region', $contact->region) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Código Postal</label>
                                <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $contact->postal_code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <input type="text" name="country" class="form-control" value="{{ old('country', $contact->country) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Horario de atención ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-horario">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-horario" aria-expanded="false" aria-controls="sec-horario">
                        Horario de atención
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-horario" class="accordion-collapse collapse" aria-labelledby="hdr-horario" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
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

                        <button type="button" id="add-hour-row" class="btn btn-outline-primary">Añadir fila</button>
                        <small class="text-muted ms-2">Deja “Hasta” vacío para “Cerrado”.</small>
                    </div>
                </div>
            </div>

            {{-- ===== Mapa ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-mapa">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-mapa" aria-expanded="false" aria-controls="sec-mapa">
                        Mapa
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-mapa" class="accordion-collapse collapse" aria-labelledby="hdr-mapa" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
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
                    </div>
                </div>
            </div>

            {{-- ===== Redes Sociales ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-redes">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-redes" aria-expanded="false" aria-controls="sec-redes">
                        Redes Sociales
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-redes" class="accordion-collapse collapse" aria-labelledby="hdr-redes" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Facebook</label><input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $contact->facebook_url) }}"></div>
                            <div class="col-md-6"><label class="form-label">Instagram</label><input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $contact->instagram_url) }}"></div>
                            <div class="col-md-6"><label class="form-label">X / Twitter</label><input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $contact->twitter_url) }}"></div>
                            <div class="col-md-6"><label class="form-label">TikTok</label><input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $contact->tiktok_url) }}"></div>
                            <div class="col-md-6"><label class="form-label">YouTube</label><input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url', $contact->youtube_url) }}"></div>
                            <div class="col-md-6"><label class="form-label">LinkedIn</label><input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $contact->linkedin_url) }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== FAQs ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-faqs">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-faqs" aria-expanded="false" aria-controls="sec-faqs">
                        Preguntas frecuentes (FAQ)
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-faqs" class="accordion-collapse collapse" aria-labelledby="hdr-faqs" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <div class="row g-3">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="mb-3">FAQ {{ $i }}</h6>
                                            <div class="mb-2">
                                                <label class="form-label" for="faq{{ $i }}_q">Pregunta</label>
                                                <input type="text"
                                                       id="faq{{ $i }}_q"
                                                       name="faq{{ $i }}_q"
                                                       class="form-control"
                                                       value="{{ old("faq{$i}_q", $contact?->{"faq{$i}_q"} ) }}">
                                            </div>
                                            <div>
                                                <label class="form-label" for="faq{{ $i }}_a">Respuesta</label>
                                                <textarea id="faq{{ $i }}_a"
                                                          name="faq{{ $i }}_a"
                                                          rows="3"
                                                          class="form-control">{{ old("faq{$i}_a", $contact?->{"faq{$i}_a"}) }}</textarea>
                                                <small class="text-muted">Puedes usar líneas nuevas para separar ideas.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Formulario & Legal ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-form-legal">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-form-legal" aria-expanded="false" aria-controls="sec-form-legal">
                        Formulario y legal
                        <span class="badge text-bg-success ms-2">Visible en Contacto</span>
                    </button>
                </h2>
                <div id="sec-form-legal" class="accordion-collapse collapse" aria-labelledby="hdr-form-legal" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
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

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $contact->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Página activa</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Notas SEO (solo referencia) ===== --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="hdr-nota-seo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sec-nota-seo" aria-expanded="false" aria-controls="sec-nota-seo">
                        Notas
                        <span class="badge text-bg-secondary ms-2">No visible</span>
                    </button>
                </h2>
                <div id="sec-nota-seo" class="accordion-collapse collapse" aria-labelledby="hdr-nota-seo" data-bs-parent="#contactEditor">
                    <div class="accordion-body">
                        <small class="text-muted">
                            Esta página no usa metadatos específicos aquí (los puedes gestionar globalmente si lo prefieres).
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

@push('scripts')
<script>
/* Previews de imágenes */
document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'hero_image' && e.target.files && e.target.files[0]) {
        const url = URL.createObjectURL(e.target.files[0]);
        const box = document.getElementById('heroPreview');
        if (box) box.innerHTML = `<img src="${url}" class="img-fluid">`;
    }
    if (e.target && e.target.id === 'banner_image' && e.target.files && e.target.files[0]) {
        const url = URL.createObjectURL(e.target.files[0]);
        const box = document.getElementById('bannerPreview');
        if (box) box.innerHTML = `<img src="${url}" class="img-fluid">`;
    }
});

/* Gestor de horarios */
document.addEventListener('DOMContentLoaded', () => {
  const box = document.getElementById('hours-rows');
  const addBtn = document.getElementById('add-hour-row');
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
  addBtn?.addEventListener('click', () => {
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
@endpush
