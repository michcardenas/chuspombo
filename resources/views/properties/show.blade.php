@extends('layouts.app')

@section('title', $property['title'] ?? 'Detalle de Propiedad')

<style>
/* ======== Calendario Smoobu - modo compacto (más horizontal) ======== */
.calendarWidget--compact {
  --cal-scale: .85;
  position: relative;
  overflow: hidden;
  max-height: calc(700px * var(--cal-scale));
}
.calendarWidget--compact .calendarContent {
  transform: scale(var(--cal-scale));
  transform-origin: top left;
  width: calc(100% / var(--cal-scale)); /* evita corte a la derecha */
}
@media (max-width: 576px) {
  .calendarWidget--compact { --cal-scale: .9; max-height: calc(650px * var(--cal-scale)); }
}

/* ======== Galería - Fix para imágenes verticales ======== */
.gallery-container {
  --gallery-height: 400px;
}
.gallery-container .row {
  height: var(--gallery-height);
}
.gallery-container .col-md-6 {
  height: 100%;
}
.gallery-container .col-md-3 {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.gallery-container .col-md-3 > img:first-child,
.gallery-container .col-md-3 > img:last-child {
  flex: 1;
  min-height: 0;
}
.gallery-container img {
  object-fit: cover;
  object-position: center;
  height: 100%;
  width: 100%;
}
@media (max-width: 768px) {
  .gallery-container {
    --gallery-height: 300px;
  }
  .gallery-container .col-md-3 {
    margin-top: 8px;
  }
}
</style>

@section('content')
@php
    // Datos base
    $images = [];
    if (!empty($property['gallery']) && is_array($property['gallery'])) {
        $images = $property['gallery'];
    } elseif (!empty($property['pictures']) && is_array($property['pictures'])) {
        $images = collect($property['pictures'])
            ->map(fn($p) => $p['original'] ?? $p['thumbnail'] ?? null)
            ->filter()->values()->all();
    }
    if (empty($images)) $images = [asset('images/property-placeholder.jpg')];

    $city        = $property['address']['city'] ?? null;
    $country     = $property['address']['country'] ?? null;
    $addressFull = $property['address']['full'] ?? trim(($city ?: '').(($city && $country) ? ', ' : '').($country ?: ''));

    $price    = $property['prices']['basePrice'] ?? null;
    $currency = 'EUR';

    $bedrooms     = $property['bedrooms']     ?? 0;
    $bathrooms    = $property['bathrooms']    ?? 0;
    $beds         = $property['beds']         ?? null;
    $accommodates = $property['accommodates'] ?? null;

    $description  = $property['description'] ?? ($property['publicDescription']['summary'] ?? '');
    $space        = $property['publicDescription']['space'] ?? '';
    $reviews      = $reviews ?? [];
    $aptId        = $property['_id'] ?? null;

    // Token de verificación del calendario (requerido por el widget)
    $calVerification = $property['calendar_verification']
        ?? (config('services.smoobu.calendar_verifications')[$aptId] ?? null)
        ?? config('services.smoobu.default_calendar_verification');
@endphp

<div class="container py-5">
    <h1 class="fw-bold text-center">{{ $property['title'] ?? 'Sin título' }}</h1>

    @if(auth()->check() && $aptId)
        <div class="text-end mb-3">
            <a href="{{ route('admin.apartments.edit', $aptId) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i> Editar propiedad
            </a>
        </div>
    @endif

    <p class="text-muted text-center">
        <i class="fas fa-map-marker-alt"></i>
        {{ $addressFull !== '' ? $addressFull : 'Ubicación no disponible' }}
    </p>

    <!-- Galería -->
    <div class="gallery-container position-relative">
        <div class="row g-2">
            @if(count($images) >= 4)
                <div class="col-md-6">
                    <img src="{{ $images[0] }}" class="img-fluid rounded" alt="Imagen 1">
                </div>
                <div class="col-md-3">
                    <img src="{{ $images[1] }}" class="img-fluid rounded" alt="Imagen 2">
                    <img src="{{ $images[2] }}" class="img-fluid rounded" alt="Imagen 3">
                </div>
                <div class="col-md-3">
                    <img src="{{ $images[3] }}" class="img-fluid rounded" alt="Imagen 4">
                </div>
            @else
                <div class="col-12">
                    <img src="{{ $images[0] }}" class="img-fluid rounded w-100" alt="Imagen portada">
                </div>
            @endif
        </div>
        <button class="btn btn-primary view-images-btn mt-2" data-bs-toggle="modal" data-bs-target="#imageModal">
            Ver todas las imágenes
        </button>
    </div>

    <div class="row mt-4">
        <div class="col-md-7">
            <div class="property-pricing bg-white p-3 rounded mb-3 shadow-sm">
                <h3 class="fw-bold text-blue">
                    @if(is_numeric($price) && $price > 0)
                        €{{ number_format($price, 0, ',', '.') }} <small class="text-dark">/ noche</small>
                    @else
                        Consultar <small class="text-dark">/ noche</small>
                    @endif
                </h3>
                <p class="text-muted">Moneda: {{ $currency }}</p>

                <div class="property-details bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center"><i class="fas fa-bed text-blue me-2"></i> {{ (int)$bedrooms }} Habitaciones</div>
                        <div class="col-6 d-flex align-items-center"><i class="fas fa-bath text-blue me-2"></i> {{ (float)$bathrooms }} Baños</div>
                        @if(!is_null($beds))
                            <div class="col-6 d-flex align-items-center mt-2"><i class="fas fa-procedures text-blue me-2"></i> {{ (int)$beds }} Camas</div>
                        @endif
                        @if(!is_null($accommodates))
                            <div class="col-6 d-flex align-items-center mt-2"><i class="fas fa-users text-blue me-2"></i> Capacidad: {{ (int)$accommodates }} huéspedes</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Descripción --}}
            @php $shortDescription = \Illuminate\Support\Str::limit(strip_tags($description), 250); @endphp
            @if(!empty($description))
                <div class="mb-4 description-section">
                    <p class="description-text" style="line-height: 1.7;">
                        <span class="short-text">{{ $shortDescription }}</span>
                        <span class="full-text d-none">{!! nl2br(e($description)) !!}</span>
                    </p>
                    @if(\Illuminate\Support\Str::length(strip_tags($description)) > 250)
                        <a href="#" class="text-blue fw-semibold see-more-link">Ver más &gt;&gt;</a>
                    @endif
                </div>
            @endif

            {{-- Reviews --}}
            @if (!empty($reviews))
                <div class="mt-5">
                    <h4 class="fw-bold text-primary mb-4">Qué dicen los demás...</h4>
                    <div class="row g-4">
                        @foreach (array_values($reviews) as $index => $review)
                            @php
                                $text      = $review['rawReview']['public_review'] ?? null;
                                $rating    = $review['rawReview']['overall_rating'] ?? 0;
                                $avatarId  = ($index % 70) + 1;
                                $imgUrl    = "https://i.pravatar.cc/150?img={$avatarId}";
                                $shortText = $text ? \Illuminate\Support\Str::limit($text, 200) : null;
                            @endphp
                            @if($text)
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm border-0 p-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="{{ $imgUrl }}" class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                                            <div><strong>Invitado</strong><br><small class="text-muted">Huésped verificado</small></div>
                                        </div>
                                        <div class="review-text mb-3" style="font-size: 0.95rem; color: #555;">
                                            <span class="short-text">{{ $shortText }}</span>
                                            <span class="full-text d-none">{{ $text }}</span>
                                            @if(strlen($text) > 200)
                                                <a href="#" class="text-blue fw-semibold see-more-review d-block mt-1" style="font-size: 0.9rem;">Ver más &gt;&gt;</a>
                                            @endif
                                        </div>
                                        <div>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $rating ? 'text-warning' : 'text-secondary' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Calendario + Selector con selects -->
        <div class="col-md-5">
          <div class="sticky-form">
            <div class="calendar-container text-center">
              {{-- Calendario: Single Calendar Widget (compacto) --}}
              @if($aptId && $calVerification)
                <div id="smoobuApartment{{ $aptId }}de" class="calendarWidget calendarWidget--compact mb-3">
                  <div class="calendarContent"
                       data-load-calendar-url="https://login.smoobu.com/de/cockpit/widget/single-calendar/{{ $aptId }}"
                       data-verification="{{ $calVerification }}"
                       data-baseUrl="https://login.smoobu.com"
                       data-disable-css="false">
                  </div>
                  <script type="text/javascript" src="https://login.smoobu.com/js/Apartment/CalendarWidget.js"></script>
                </div>
              @elseif($aptId && !$calVerification)
                <div class="alert alert-warning text-start">
                  <strong>Falta el token del calendario.</strong><br>
                  Asigna <code>$property['calendar_verification']</code> o configura
                  <code>services.smoobu.calendar_verifications[{{ $aptId }}]</code>
                  (o <code>services.smoobu.default_calendar_verification</code>) en <code>config/services.php</code>.
                  @if(auth()->check())
                    <div class="mt-2">
                      <a href="{{ route('admin.apartments.edit', $aptId) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-key me-1"></i> Añadir token ahora
                      </a>
                    </div>
                  @endif
                </div>
              @else
                <div class="alert alert-info">No se encontró el <code>apartmentId</code> para cargar el calendario.</div>
              @endif

              {{-- ===== Selector con selects ===== --}}
              <form id="selectCheckoutForm" action="{{ route('checkout.start') }}" method="POST" class="card shadow-sm p-3 mt-3 text-start">
                @csrf
                <input type="hidden" name="apartment_id"    value="{{ $aptId }}">
                <input type="hidden" name="apartment_title" value="{{ $property['title'] ?? '' }}">
                <input type="hidden" name="base_price" value="{{ $price ?? 0 }}">

                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label mb-1">Check-in</label>
                    <select id="ciSelect" name="checkin" class="form-select" required>
                      <option value="">Cargando fechas libres…</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Check-out</label>
                    <select id="coSelect" name="checkout" class="form-select" required disabled>
                      <option value="">Selecciona check-in primero</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Hora Check-in</label>
                    <select id="ciHourSelect" name="checkin_hour" class="form-select">
                      {{-- Check-in permitido desde 1 PM (13:00) en adelante --}}
                      @for($h = 13; $h <= 23; $h++)
                        <option value="{{ sprintf('%02d:00', $h) }}" {{ $h == 15 ? 'selected' : '' }}>
                          {{ sprintf('%02d:00', $h) }}
                        </option>
                      @endfor
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label mb-1">Hora Check-out</label>
                    <select id="coHourSelect" name="checkout_hour" class="form-select">
                      {{-- Check-out permitido hasta 11 AM (11:00) --}}
                      @for($h = 0; $h <= 11; $h++)
                        <option value="{{ sprintf('%02d:00', $h) }}" {{ $h == 11 ? 'selected' : '' }}>
                          {{ sprintf('%02d:00', $h) }}
                        </option>
                      @endfor
                    </select>
                  </div>

                  {{-- Información de horarios de reserva --}}
                  <div class="col-12 mt-2">
                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 0.9rem;">
                      <i class="fas fa-info-circle me-2"></i>
                      <strong>Información de horarios:</strong> La disponibilidad horaria puede variar según la reserva.
                      Si necesita horarios diferentes, puede comunicarse con nosotros para coordinar su llegada y salida.
                    </div>
                  </div>

                  <div class="col-6 mt-2">
                    <label class="form-label mb-1">Huéspedes</label>
                    <select id="guestsSelect" name="guests" class="form-select">
                      @php $maxGuests = max(1, (int)($accommodates ?? 10)); @endphp
                      @for ($i=1; $i<= $maxGuests; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                      @endfor
                    </select>
                  </div>
                  <div class="col-6 mt-2 d-flex align-items-end">
                    <button type="button" id="btnQuote" class="btn btn-outline-primary w-100">Calcular precio</button>
                  </div>
                </div>

                <div id="quoteBox" class="alert mt-3 d-none"></div>

                <div class="d-grid mt-2">
                  <button type="submit" id="btnProceed" class="btn btn-primary" disabled>Continuar al resumen</button>
                </div>
              </form>
              {{-- ===== /Selector con selects ===== --}}
            </div>
          </div>
        </div>
    </div>
</div>

<!-- Modal imágenes -->
<div class="modal fade" id="imageModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Todas las imágenes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    @foreach($images as $img)
                        <div class="col-6">
                            <img src="{{ $img }}" class="img-fluid rounded" alt="Imagen">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // ===== Referencias UI =====
  const aptId      = {{ (int)($aptId ?? 0) }};
  const basePrice  = {{ (float)($price ?? 0) }};
  const ciSelect   = document.getElementById('ciSelect');
  const coSelect   = document.getElementById('coSelect');
  const guestsSel  = document.getElementById('guestsSelect');
  const btnQuote   = document.getElementById('btnQuote');
  const btnProceed = document.getElementById('btnProceed');
  const quoteBox   = document.getElementById('quoteBox');

  // ===== Utilidades fecha =====
  function pad(n){ return String(n).padStart(2,'0'); }
  function fmtDate(d){ return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }
  function addDays(d, n){ const x = new Date(d.getTime()); x.setDate(x.getDate()+n); return x; }
  function addDaysStr(ds, n){ const d = new Date(ds+'T00:00:00'); d.setDate(d.getDate()+n); return fmtDate(d); }

  // UI helpers
  function setBox(type, html) {
    quoteBox.classList.remove('d-none', 'alert-info', 'alert-danger', 'alert-success', 'alert-warning');
    quoteBox.classList.add('alert', `alert-${type}`);
    quoteBox.innerHTML = html;
  }
  function clearBox(){ quoteBox.classList.add('d-none'); quoteBox.innerHTML=''; }
  function disableProceed(){ btnProceed.disabled = true; }
  function enableProceed(){ btnProceed.disabled = false; }

  // ===== Estado =====
  const today = new Date();
  const end   = new Date(); end.setFullYear(end.getFullYear() + 1);

  const busySet = new Set();     // días ocupados "YYYY-MM-DD"

  function ingestAvailabilityPayload(data) {
    busySet.clear();

    // 1) 'dates': ['YYYY-MM-DD', ...]
    if (Array.isArray(data?.dates)) {
      data.dates.forEach(ds => { if (typeof ds === 'string') busySet.add(ds.slice(0,10)); });
    }

    // 2) 'ranges': [{from,to}]
    const ranges = Array.isArray(data?.ranges) ? data.ranges : [];
    for (const r of ranges) {
      if (!r || !r.from || !r.to) continue;
      let cur = r.from.slice(0,10);
      const to = r.to.slice(0,10);
      while (cur <= to) { busySet.add(cur); cur = addDaysStr(cur, 1); }
    }
  }

  function isDisabled(ds){ return busySet.has(ds); }

  // Construcción de selects
  function buildCheckinOptions(){
    const start = new Date();
    const stop  = new Date(); stop.setFullYear(stop.getFullYear()+1);
    const opts  = [];
    for (let dte = new Date(start); dte <= stop; dte = addDays(dte, 1)) {
      const ds = fmtDate(dte);
      if (!isDisabled(ds)) opts.push(ds);
    }
    ciSelect.innerHTML = `<option value="">Selecciona…</option>` + opts.map(v => `<option value="${v}">${v}</option>`).join('');
  }

  function buildCheckoutOptions(ciStr){
    if (!ciStr) {
      coSelect.innerHTML = `<option value="">Selecciona check-in primero</option>`;
      coSelect.disabled = true;
      return;
    }
    const ci = new Date(ciStr + 'T00:00:00');
    const maxNights = 60;
    const opts = [];
    for (let n=1; n<=maxNights; n++){
      const nightStr = fmtDate(addDays(ci, n-1)); // noche n
      if (isDisabled(nightStr)) break;
      opts.push(fmtDate(addDays(ci, n)));         // checkout al día siguiente
    }
    coSelect.innerHTML = opts.length
      ? `<option value="">Selecciona…</option>` + opts.map(v => `<option value="${v}">${v}</option>`).join('')
      : `<option value="">No hay salidas válidas</option>`;
    coSelect.disabled = (opts.length === 0);
  }

  // Carga de disponibilidad
  async function loadAvailability(){
    if (!aptId) return;
    try {
      const url = new URL(@json(route('checkout.availability')), window.location.origin);
      url.searchParams.set('apartment_id', aptId);
      url.searchParams.set('from', fmtDate(today));
      url.searchParams.set('to',   fmtDate(end));

      const res  = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
      const data = await res.json().catch(() => ({}));
      ingestAvailabilityPayload(data || {});
    } catch(_) {
      busySet.clear(); // si falla, no deshabilitamos nada adicional
    } finally {
      buildCheckinOptions();
      coSelect.innerHTML = `<option value="">Selecciona check-in primero</option>`;
      coSelect.disabled = true;
    }
  }

  // Eventos selects
  ciSelect.addEventListener('change', () => { clearBox(); disableProceed(); buildCheckoutOptions(ciSelect.value); });
  coSelect.addEventListener('change', () => { clearBox(); disableProceed(); });
  guestsSel.addEventListener('change', () => { clearBox(); disableProceed(); });

  // Cotizar
  btnQuote.addEventListener('click', async () => {
    disableProceed();
    const checkin  = ciSelect.value;
    const checkout = coSelect.value;
    const guests   = guestsSel.value || 1;

    if (!aptId || !checkin || !checkout) {
      setBox('warning', 'Selecciona check-in y check-out.');
      return;
    }

    try {
      setBox('info', 'Calculando precio…');

      const res = await fetch(@json(route('checkout.quote')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': @json(csrf_token()),
        },
        body: JSON.stringify({ apartment_id: aptId, checkin, checkout, guests, base_price: basePrice })
      });

      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error('Quote error');

      const fmt = n => (Number(n || 0)).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      let lines = '';
      if (data.breakdown) {
        const days = Object.keys(data.breakdown).sort();
        lines = days.map(dy => `<div class="d-flex justify-content-between"><span>${dy}</span><span>€ ${fmt(data.breakdown[dy])}</span></div>`).join('');
      }

      setBox('success', `
        <div><strong>Noches:</strong> ${data.nights}</div>
        <div class="mt-2">
          <strong>Total:</strong> € ${fmt(data.total)} EUR
          <div class="small text-muted">Subtotal: € ${fmt(data.subtotal)}${data.tax ? ` · Imp.: € ${fmt(data.tax)}` : ''}${data.fees ? ` · Cargos: € ${fmt(data.fees)}` : ''}</div>
        </div>
        ${lines ? `<hr><div class="small">${lines}</div>` : ``}
      `);
      enableProceed();
    } catch (_) {
      setBox('danger', 'No se pudo calcular el precio.');
    }
  });

  // Inicio
  loadAvailability();
});
</script>
<script>
document.addEventListener('click', function (e) {
  // Toggle de la descripción principal
  const moreDesc = e.target.closest('.see-more-link');
  if (moreDesc) {
    e.preventDefault();
    const wrapper = moreDesc.closest('.description-section');
    if (!wrapper) return;

    const shortEl = wrapper.querySelector('.short-text');
    const fullEl  = wrapper.querySelector('.full-text');
    if (!shortEl || !fullEl) return;

    const expanded = !fullEl.classList.contains('d-none');
    // Alternar visibilidad
    fullEl.classList.toggle('d-none',  expanded);
    shortEl.classList.toggle('d-none', !expanded);

    // Cambiar texto del enlace
    moreDesc.textContent = expanded ? 'Ver más >>' : 'Ver menos <<';
    return;
  }

  // Toggle de cada reseña
  const moreReview = e.target.closest('.see-more-review');
  if (moreReview) {
    e.preventDefault();
    const card = moreReview.closest('.card');
    if (!card) return;

    const shortEl = card.querySelector('.review-text .short-text');
    const fullEl  = card.querySelector('.review-text .full-text');
    if (!shortEl || !fullEl) return;

    const expanded = !fullEl.classList.contains('d-none');
    fullEl.classList.toggle('d-none',  expanded);
    shortEl.classList.toggle('d-none', !expanded);

    moreReview.textContent = expanded ? 'Ver más >>' : 'Ver menos <<';
  }
});
</script>


@endsection
