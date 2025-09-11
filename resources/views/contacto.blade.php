@extends('layouts.app')

@section('title', 'Contacto · Chuspombo')
@section('meta_description', 'Ponte en contacto con Chuspombo para reservas y consultas sobre apartamentos en Galicia, España.')

@section('content')

@php
    // === Contenidos editables (desde $pagina, con fallback) ===
    $h1         = $pagina->contact_h1        ?? 'Hablemos 🤝';
    $subtitle   = $pagina->contact_subtitle  ?? 'Estamos aquí para ayudarte a planear tu próxima estancia en Galicia.';
    $intro      = $pagina->contact_intro     ?? 'Cuéntanos fechas, dudas o necesidades especiales. Respondemos rápido.';

    $phone      = $pagina->contact_phone     ?? '+34 000 000 000';
    $email      = $pagina->contact_email     ?? 'info@chuspombo.com';
    $address    = $pagina->contact_address   ?? 'Galicia, España';
    $whatsapp   = $pagina->contact_whatsapp  ?? null;

    // Imagen del hero (archivo dentro de /public/images). Ej: contact-hero.webp
    $heroImage  = !empty($pagina->contact_hero) ? asset('images/'.$pagina->contact_hero) : asset('images/galicia-placeholder.webp');

    // Horarios (opcionalmente editables)
    $hoursTitle = $pagina->contact_hours_title ?? 'Horario de atención';
    $hoursMon   = $pagina->contact_hours_mon   ?? 'Lun–Vie: 09:00–19:00';
    $hoursSat   = $pagina->contact_hours_sat   ?? 'Sáb: 10:00–14:00';
    $hoursSun   = $pagina->contact_hours_sun   ?? 'Dom: Cerrado';

    // FAQs simples (se pueden extender)
    $faq1_q     = $pagina->faq_q1 ?? '¿Cómo reservo un apartamento?';
    $faq1_a     = $pagina->faq_a1 ?? 'Envíanos tus fechas y preferencia; te confirmamos disponibilidad y precio.';
    $faq2_q     = $pagina->faq_q2 ?? '¿Se permite cancelación?';
    $faq2_a     = $pagina->faq_a2 ?? 'Sí, según políticas del alojamiento y el tiempo previo a la llegada.';
    $faq3_q     = $pagina->faq_q3 ?? '¿Ofrecen check-in sin contacto?';
    $faq3_a     = $pagina->faq_a3 ?? 'Podemos gestionar check-in autónomo bajo solicitud.';

    // Map embed (pega el iframe completo en contact_map_embed)
    $mapEmbed   = $pagina->contact_map_embed ?? null;
@endphp

<style>
/* ====== Paleta Hostella (override mínimo Bootstrap) ====== */
:root{
  --hostella-primary:#1a1a1a;
  --hostella-secondary:#FFD700;
  --hostella-light:#f8f9fa;
  --hostella-dark:#000000;
  --hostella-accent:#D4AF37;
}
.btn-primary{ background-color:var(--hostella-primary)!important; border-color:var(--hostella-primary)!important; }
.btn-primary:hover{ background-color:var(--hostella-dark)!important; border-color:var(--hostella-dark)!important; }
.btn-outline-primary{ color:var(--hostella-primary)!important; border-color:var(--hostella-primary)!important; }
.btn-outline-primary:hover{ color:#fff!important; background-color:var(--hostella-primary)!important; border-color:var(--hostella-primary)!important; }
a,.text-primary{ color:var(--hostella-primary)!important; }

/* ====== Contact Page ====== */
.contact-hero{
  position:relative; min-height:52vh; display:flex; align-items:center; overflow:hidden;
  background: linear-gradient(45deg, rgba(26,26,26,.75), rgba(212,175,55,.25)), url('{{ $heroImage }}') center/cover no-repeat;
}
.contact-hero .lead{ color: #fff; opacity:.95; }

.contact-card{
  background:#fff; border:1px solid #eee; border-radius:18px; box-shadow: 0 10px 30px rgba(0,0,0,.08);
}
.contact-icon{
  width:48px;height:48px;border-radius:14px; display:flex; align-items:center; justify-content:center;
  background: rgba(212,175,55,.12); color: var(--hostella-primary);
}

.form-control, .form-select{
  border-radius:12px; padding:.8rem .9rem; border-color:#e6e6e6;
}
.form-control:focus, .form-select:focus{
  border-color: var(--hostella-accent); box-shadow: 0 0 0 .2rem rgba(212,175,55,.15);
}
.label-sm{ font-size:.9rem; color:#6c757d; }

.badge-soft{
  background: rgba(212,175,55,.12); color: var(--hostella-accent); border-radius:10px; padding:.35rem .6rem;
}

.accordion-button:focus{ box-shadow:none; }
.accordion-button:not(.collapsed){ background:#fff; color:var(--hostella-primary); }

.map-wrapper iframe{ border:0; border-radius:18px; width:100%; min-height:340px; }

@media (max-width: 576px){
  .contact-hero h1{ font-size:1.9rem; }
  .contact-hero .lead{ font-size:1.05rem; }
}
</style>

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

          <form method="POST" action="{{ route('landing.contact') }}">
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

          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="contact-icon"><i class="fas fa-phone"></i></div>
            <div>
              <div class="text-muted small">Teléfono</div>
              <a href="tel:{{ preg_replace('/\D+/','',$phone) }}" class="fw-semibold d-block">{{ $phone }}</a>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
            <div>
              <div class="text-muted small">Email</div>
              <a href="mailto:{{ $email }}" class="fw-semibold d-block">{{ $email }}</a>
            </div>
          </div>

          <div class="d-flex align-items-start gap-3 mb-0">
            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div>
              <div class="text-muted small">Dirección</div>
              <span class="fw-semibold d-block">{{ $address }}</span>
            </div>
          </div>

          <hr class="my-4">

          <div>
            <div class="text-muted small mb-1">{{ $hoursTitle }}</div>
            <ul class="list-unstyled mb-0">
              <li>🗓️ {{ $hoursMon }}</li>
              <li>🗓️ {{ $hoursSat }}</li>
              <li>🗓️ {{ $hoursSun }}</li>
            </ul>
          </div>
        </div>

        @if($mapEmbed)
          <div class="map-wrapper contact-card p-2">
            {!! $mapEmbed !!}
          </div>
        @endif

        <div class="accordion mt-4" id="faqs">
          <div class="accordion-item contact-card">
            <h2 class="accordion-header">
              <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                {{ $faq1_q }}
              </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqs">
              <div class="accordion-body">{{ $faq1_a }}</div>
            </div>
          </div>
          <div class="accordion-item contact-card mt-3">
            <h2 class="accordion-header">
              <button class="accordion-button fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                {{ $faq2_q }}
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqs">
              <div class="accordion-body">{{ $faq2_a }}</div>
            </div>
          </div>
          <div class="accordion-item contact-card mt-3">
            <h2 class="accordion-header">
              <button class="accordion-button fw-semibold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                {{ $faq3_q }}
              </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqs">
              <div class="accordion-body">{{ $faq3_a }}</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

@endsection
