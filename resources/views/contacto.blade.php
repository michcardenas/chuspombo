@extends('layouts.app')

@section('title', 'Contacto · Chuspombo')
@section('meta_description', 'Ponte en contacto con Chuspombo para reservas y consultas sobre apartamentos en Galicia, España.')

@section('content')

@php
    // ===== Datos principales desde contact_pages =====
    $h1       = $contact->h1 ?? 'Hablemos 🤝';
    $subtitle = $contact->h2 ?? 'Estamos aquí para ayudarte a planear tu próxima estancia en Galicia.';
    $intro    = $contact->intro_text ?? 'Cuéntanos fechas, dudas o necesidades especiales. Respondemos rápido.';

    // Medios de contacto
    $phonePrimary   = $contact->phone_primary   ?? null;
    $phoneSecondary = $contact->phone_secondary ?? null;
    $emailPrimary   = $contact->email_primary   ?? null;
    $emailSecondary = $contact->email_secondary ?? null;
    $whatsapp       = $contact->whatsapp        ?? null;
    $website        = $contact->website         ?? null;

    // Dirección compuesta
    $addressParts = array_filter([
        $contact->address_line1 ?? null,
        $contact->address_line2 ?? null,
        $contact->city ?? null,
        $contact->region ?? null,
        $contact->postal_code ?? null,
        $contact->country ?? null,
    ]);
    $address = count($addressParts) ? implode(', ', $addressParts) : 'Galicia, España';

    // Imagen hero
    $heroImage = ($heroUrl ?? null)
        ?: (!empty($contact->hero_image) ? asset('images/'.$contact->hero_image) : asset('images/galicia-placeholder.webp'));

    // Horarios
    $hoursTitle = 'Horario de atención';
    $hoursList = null;
    if (!empty($contact->business_hours)) {
        $decoded = is_array($contact->business_hours)
            ? $contact->business_hours
            : json_decode($contact->business_hours, true);
        if (is_array($decoded)) {
            $hoursList = $decoded;
        }
    }
    $fallbackHours = [
        ['label' => 'Lun–Vie', 'from' => '09:00', 'to' => '19:00'],
        ['label' => 'Sáb',     'from' => '10:00', 'to' => '14:00'],
        ['label' => 'Dom',     'from' => 'Cerrado', 'to' => ''],
    ];

    // ===== FAQs desde BD (con fallback en el primero) =====
    $faqs = [
        [
            'id'   => 'faq1',
            'q'    => trim($contact->faq1_q ?? '') ?: '¿Cómo reservo un apartamento?',
            'a'    => trim($contact->faq1_a ?? '') ?: 'Envíanos tus fechas y preferencia; te confirmamos disponibilidad y precio.',
            'show' => true,
        ],
        [
            'id'   => 'faq2',
            'q'    => trim($contact->faq2_q ?? ''),
            'a'    => trim($contact->faq2_a ?? ''),
            'show' => false,
        ],
        [
            'id'   => 'faq3',
            'q'    => trim($contact->faq3_q ?? ''),
            'a'    => trim($contact->faq3_a ?? ''),
            'show' => false,
        ],
    ];
    // Ocultar FAQs totalmente vacíos (excepto el 1 que siempre tiene fallback)
    $faqs = array_values(array_filter($faqs, fn($f) => $f['q'] !== '' || $f['a'] !== ''));

    // Mapa: guardamos src o iframe completo
    $mapHtml = null;
    if (!empty($contact->map_embed_url)) {
        $raw = trim($contact->map_embed_url);
        if (str_contains($raw, '<iframe')) {
            $mapHtml = $raw;
        } else {
            $mapHtml = '<iframe src="'.e($raw).'" width="100%" height="340" style="border:0;border-radius:18px" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        }
    }
@endphp

<style>
/* ====== Encapsulamos TODO dentro de .contact-page para no tocar footer ni otras vistas ====== */
.contact-page{
  --hostella-primary:#1a1a1a;
  --hostella-secondary:#FFD700;
  --hostella-light:#f8f9fa;
  --hostella-dark:#000000;
  --hostella-accent:#D4AF37;
}

/* Botones solo dentro de la página de contacto */
.contact-page .btn-primary{
  background-color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}
.contact-page .btn-primary:hover{
  background-color:var(--hostella-dark)!important;
  border-color:var(--hostella-dark)!important;
}
.contact-page .btn-outline-primary{
  color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}
.contact-page .btn-outline-primary:hover{
  color:#fff!important;
  background-color:var(--hostella-primary)!important;
  border-color:var(--hostella-primary)!important;
}

/* NO tocamos enlaces globales: solo los de esta página */
.contact-page a,
.contact-page .text-primary{
  color:var(--hostella-primary)!important;
}

/* ====== Contact Page ====== */
.contact-page .contact-hero{
  position:relative; min-height:52vh; display:flex; align-items:center; overflow:hidden;
  background: linear-gradient(45deg, rgba(26,26,26,.75), rgba(212,175,55,.25)), url('{{ $heroImage }}') center/cover no-repeat;
}
.contact-page .contact-hero .lead{ color: #fff; opacity:.95; }

.contact-page .contact-card{
  background:#fff; border:1px solid #eee; border-radius:18px; box-shadow: 0 10px 30px rgba(0,0,0,.08);
}
.contact-page .contact-icon{
  width:48px;height:48px;border-radius:14px; display:flex; align-items:center; justify-content:center;
  background: rgba(212,175,55,.12); color: var(--hostella-primary);
}

.contact-page .form-control,
.contact-page .form-select{
  border-radius:12px; padding:.8rem .9rem; border-color:#e6e6e6;
}
.contact-page .form-control:focus,
.contact-page .form-select:focus{
  border-color: var(--hostella-accent); box-shadow: 0 0 0 .2rem rgba(212,175,55,.15);
}
.contact-page .label-sm{ font-size:.9rem; color:#6c757d; }

.contact-page .badge-soft{
  background: rgba(212,175,55,.12); color: var(--hostella-accent); border-radius:10px; padding:.35rem .6rem;
}

.contact-page .accordion-button:focus{ box-shadow:none; }
.contact-page .accordion-button:not(.collapsed){ background:#fff; color:var(--hostella-primary); }

.contact-page .map-wrapper iframe{ border:0; border-radius:18px; width:100%; min-height:340px; }

@media (max-width: 576px){
  .contact-page .contact-hero h1{ font-size:1.9rem; }
  .contact-page .contact-hero .lead{ font-size:1.05rem; }
}
</style>

<div class="contact-page"><!-- SCOPED WRAPPER -->

  <!-- ====== HERO ====== -->
  <section class="contact-hero text-white">
    <div class="container py-5">
      <div class="row justify-content-center text-center">
        <div class="col-xl-8 col-lg-9">
          <span class="badge-soft mb-3 d-inline-block">Contacto</span>
          <h1 class="fw-bold mb-3">{{ $h1 }}</h1>
          <p class="lead mb-4">{{ $subtitle }}</p>
          <p class="mb-0">{{ $intro }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== CONTENT ====== -->
  <section class="py-5">
    <div class="container">
      <div class="row g-4">

        <!-- Columna: Formulario -->
        <div class="col-lg-7">
          <div class="contact-card p-4 p-md-5">
            <h3 class="fw-bold mb-1">Escríbenos</h3>
            <p class="text-muted mb-4">Te responderemos a la brevedad.</p>

            @if(session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}">
              @csrf

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="label-sm mb-1">Nombre</label>
                  <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>
                <div class="col-md-6">
                  <label class="label-sm mb-1">Email</label>
                  <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                  <label class="label-sm mb-1">Teléfono (opcional)</label>
                  <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                  <label class="label-sm mb-1">Tema</label>
                  <select name="topic" class="form-select">
                    <option value="Reserva">Reserva</option>
                    <option value="Disponibilidad">Disponibilidad</option>
                    <option value="Atención al huésped">Atención al huésped</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="label-sm mb-1">Llegada (opcional)</label>
                  <input type="date" name="checkin" class="form-control" value="{{ old('checkin') }}">
                </div>
                <div class="col-md-6">
                  <label class="label-sm mb-1">Salida (opcional)</label>
                  <input type="date" name="checkout" class="form-control" value="{{ old('checkout') }}">
                </div>
                <div class="col-12">
                  <label class="label-sm mb-1">Mensaje</label>
                  <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                </div>
                {{-- Honeypot anti-spam simple --}}
                <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off">
              </div>

              <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary px-4">Enviar</button>
                @if($whatsapp)
                  <a href="https://wa.me/{{ preg_replace('/\D+/','',$whatsapp) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                  </a>
                @endif
              </div>
            </form>
          </div>
        </div>

        <!-- Columna: Info + Mapa + FAQ -->
        <div class="col-lg-5">
          <div class="contact-card p-4 p-md-5 mb-4">
            <h4 class="fw-bold mb-4">Información de contacto</h4>

            @if($phonePrimary)
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="contact-icon"><i class="fas fa-phone"></i></div>
              <div>
                <div class="text-muted small">Teléfono</div>
                <a href="tel:{{ preg_replace('/\D+/','',$phonePrimary) }}" class="fw-semibold d-block">{{ $phonePrimary }}</a>
                @if($phoneSecondary)
                  <small class="d-block text-muted mt-1">Alt: <a href="tel:{{ preg_replace('/\D+/','',$phoneSecondary) }}">{{ $phoneSecondary }}</a></small>
                @endif
              </div>
            </div>
            @endif

            @if($emailPrimary)
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="contact-icon"><i class="fas fa-envelope"></i></div>
              <div>
                <div class="text-muted small">Email</div>
                <a href="mailto:{{ $emailPrimary }}" class="fw-semibold d-block">{{ $emailPrimary }}</a>
                @if($emailSecondary)
                  <small class="d-block text-muted mt-1">Alt: <a href="mailto:{{ $emailSecondary }}">{{ $emailSecondary }}</a></small>
                @endif
              </div>
            </div>
            @endif

            <div class="d-flex align-items-start gap-3 mb-0">
              <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <div class="text-muted small">Dirección</div>
                <span class="fw-semibold d-block">{{ $address }}</span>
                @if($website)
                  <small class="d-block mt-2"><i class="fas fa-globe me-1"></i><a href="{{ $website }}" target="_blank" rel="noopener">{{ $website }}</a></small>
                @endif
              </div>
            </div>

            <hr class="my-4">

            <div>
              <div class="text-muted small mb-1">{{ $hoursTitle }}</div>
              @if($contact->is_24_hours ?? false)
                {{-- Mostrar horario 24 horas --}}
                <div class="d-flex align-items-center">
                  <span class="badge-soft fs-6 px-3 py-2 fw-semibold">
                    <i class="fas fa-clock me-2"></i>Lunes-Domingo 24H
                  </span>
                </div>
              @elseif($hoursList)
                <ul class="list-unstyled mb-0">
                  @foreach($hoursList as $h)
                    <li>
                      🗓️ {{ $h['label'] ?? '' }}:
                      @if(($h['from'] ?? '') !== '' && ($h['to'] ?? '') !== '')
                        {{ $h['from'] }}–{{ $h['to'] }}
                      @else
                        {{ $h['from'] ?? 'Cerrado' }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              @else
                <ul class="list-unstyled mb-0">
                  @foreach($fallbackHours as $h)
                    <li>
                      🗓️ {{ $h['label'] }}:
                      @if($h['to'])
                        {{ $h['from'] }}–{{ $h['to'] }}
                      @else
                        {{ $h['from'] }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          </div>

          @if($mapHtml)
            <div class="map-wrapper contact-card p-2">
              {!! $mapHtml !!}
            </div>
          @endif

          {{-- ===== FAQs ===== --}}
          @if(count($faqs))
          <div class="accordion mt-4" id="faqs">
            @foreach($faqs as $i => $f)
              <div class="accordion-item contact-card {{ $i>0 ? 'mt-3' : '' }}">
                <h2 class="accordion-header">
                  <button class="accordion-button fw-semibold {{ $f['show'] ? '' : 'collapsed' }}"
                          type="button" data-bs-toggle="collapse"
                          data-bs-target="#{{ $f['id'] }}">
                    {{ $f['q'] }}
                  </button>
                </h2>
                <div id="{{ $f['id'] }}" class="accordion-collapse collapse {{ $f['show'] ? 'show' : '' }}" data-bs-parent="#faqs">
                  <div class="accordion-body">{{ $f['a'] }}</div>
                </div>
              </div>
            @endforeach
          </div>
          @endif

        </div>
      </div>
    </div>
  </section>

</div><!-- /.contact-page -->

@endsection
