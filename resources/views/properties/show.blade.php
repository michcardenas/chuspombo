@extends('layouts.app')

@section('title', $property['title'] ?? 'Detalle de Propiedad')


<style>
/* ======== Calendario Smoobu - modo compacto (más horizontal) ======== */
.calendarWidget--compact {
  --cal-scale: .85;                   /* Ajusta .80–.90 según te guste */
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
</style>


@section('content')
@php
    // ===== Helpers de datos =====
    // Galería
    $images = [];
    if (!empty($property['gallery']) && is_array($property['gallery'])) {
        $images = $property['gallery'];
    } elseif (!empty($property['pictures']) && is_array($property['pictures'])) {
        $images = collect($property['pictures'])
            ->map(fn($p) => $p['original'] ?? $p['thumbnail'] ?? null)
            ->filter()->values()->all();
    }
    if (empty($images)) $images = [asset('images/property-placeholder.jpg')];

    // Dirección compacta
    $city        = $property['address']['city'] ?? null;
    $country     = $property['address']['country'] ?? null;
    $addressFull = $property['address']['full'] ?? trim(($city ?: '').(($city && $country) ? ', ' : '').($country ?: ''));

    // Precio
    $price    = $property['prices']['basePrice'] ?? null;
    $currency = $property['prices']['currency'] ?? 'USD';

    // Atributos
    $bedrooms     = $property['bedrooms']     ?? 0;
    $bathrooms    = $property['bathrooms']    ?? 0;
    $beds         = $property['beds']         ?? null;
    $accommodates = $property['accommodates'] ?? null;

    // Descripción y otras secciones
    $description  = $property['description'] ?? ($property['publicDescription']['summary'] ?? '');
    $space        = $property['publicDescription']['space'] ?? '';
    $neighborhood = $property['publicDescription']['neighborhood'] ?? '';
    $houseRules   = $property['publicDescription']['houseRules'] ?? '';

    // ID del apartamento
    $aptId = $property['_id'] ?? null;

    // Booking Tool URL (snippet que me pasaste). Si quieres centralizarlo, pásalo por config/services.php
    $bookingToolUrl = config('services.smoobu.booking_tool_url', 'https://login.smoobu.com/en/booking-tool/iframe/1380421');

    // Token de verificación del calendario
    $calVerification = $property['calendar_verification']
        ?? (config('services.smoobu.calendar_verifications')[$aptId] ?? null)
        ?? config('services.smoobu.default_calendar_verification');
@endphp

<div class="container py-5">
    <!-- Título y ubicación -->
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

    <!-- Galería principal -->
    <div class="gallery-container position-relative">
        <div class="row g-2">
            @if(count($images) >= 4)
                <div class="col-md-6">
                    <img src="{{ $images[0] }}" class="img-fluid rounded w-100 h-100 object-fit-cover" alt="Imagen 1">
                </div>
                <div class="col-md-3">
                    <img src="{{ $images[1] }}" class="img-fluid rounded w-100 mb-2 object-fit-cover" alt="Imagen 2">
                    <img src="{{ $images[2] }}" class="img-fluid rounded w-100 object-fit-cover" alt="Imagen 3">
                </div>
                <div class="col-md-3">
                    <img src="{{ $images[3] }}" class="img-fluid rounded w-100 h-100 object-fit-cover" alt="Imagen 4">
                </div>
            @else
                <div class="col-12">
                    <img src="{{ $images[0] }}" class="img-fluid rounded w-100" alt="Imagen portada">
                </div>
            @endif
        </div>

        <button class="btn btn-primary view-images-btn" data-bs-toggle="modal" data-bs-target="#imageModal">
            Ver todas las imágenes
        </button>
    </div>

    <div class="row mt-4">
        <!-- Columna izquierda: info -->
        <div class="col-md-7">
            <div class="property-pricing bg-white p-3 rounded mb-3 shadow-sm">
                <h3 class="fw-bold text-blue">
                    @if(is_numeric($price) && $price > 0)
                        ${{ number_format($price, 0) }} <small class="text-dark">/ noche</small>
                    @else
                        Consultar <small class="text-dark">/ noche</small>
                    @endif
                </h3>
                <p class="text-muted">Moneda: {{ $currency }}</p>

                <div class="property-details bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <i class="fas fa-bed text-blue me-2"></i> {{ (int)$bedrooms }} Habitaciones
                        </div>
                        <div class="col-6 d-flex align-items-center">
                            <i class="fas fa-bath text-blue me-2"></i> {{ (float)$bathrooms }} Baños
                        </div>
                        @if(!is_null($beds))
                            <div class="col-6 d-flex align-items-center mt-2">
                                <i class="fas fa-procedures text-blue me-2"></i> {{ (int)$beds }} Camas
                            </div>
                        @endif
                        @if(!is_null($accommodates))
                            <div class="col-6 d-flex align-items-center mt-2">
                                <i class="fas fa-users text-blue me-2"></i> Capacidad máxima: {{ (int)$accommodates }} huéspedes
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Descripción con ver más/menos --}}
            @php
                $shortDescription = \Illuminate\Support\Str::limit(strip_tags($description), 250);
            @endphp

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

            @if(!empty($space))
                <div class="mt-5">
                    <h4 class="fw-bold text-blue">Espacio</h4>
                    <p style="line-height: 1.7;">{!! nl2br(e($space)) !!}</p>
                    <hr>
                </div>
            @endif

            @if(!empty($property['amenities']) && is_array($property['amenities']))
                <div class="mt-5">
                    <h4 class="fw-bold text-blue">Qué encontrarás en este lugar</h4>
                    <div class="bg-light p-3 rounded mt-3">
                        <div class="row amenities-container">
                            @foreach ($property['amenities'] as $index => $amenity)
                                <div class="col-md-6 mb-2 amenity-item {{ $index >= 6 ? 'd-none extra-amenity' : '' }}">
                                    <i class="fas fa-check-circle text-blue me-2"></i>{{ $amenity }}
                                </div>
                            @endforeach
                        </div>
                        @if(count($property['amenities']) > 6)
                            <a href="#" class="text-blue fw-semibold see-more-amenities d-block mt-2">Ver más &gt;&gt;</a>
                        @endif
                    </div>
                </div>
            @endif

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
                                            <div>
                                                <strong>Invitado</strong><br>
                                                <small class="text-muted">Huésped verificado</small>
                                            </div>
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

        <!-- Columna derecha: Calendario + Booking Tool -->
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
                            Asigna <code>$property['calendar_verification']</code> en el controlador
                            o configura <code>services.smoobu.calendar_verifications[{{ $aptId }}]</code>
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
                        <div class="alert alert-info">
                            No se encontró el <code>apartmentId</code> para cargar el calendario.
                        </div>
                    @endif

                    {{-- Booking Tool Iframe (snippet que me pasaste) --}}
                    <div id="apartmentIframeAll" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal con todas las imágenes -->
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

{{-- Scripts de interacción + carga del Booking Tool --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ver más/menos descripción
    const descLink = document.querySelector('.see-more-link');
    if (descLink) {
        descLink.addEventListener('click', function(e) {
            e.preventDefault();
            const block = document.querySelector('.description-section');
            const shortText = block.querySelector('.short-text');
            const fullText  = block.querySelector('.full-text');
            const showingFull = !fullText.classList.contains('d-none');
            shortText.classList.toggle('d-none', !showingFull);
            fullText.classList.toggle('d-none', showingFull);
            this.textContent = showingFull ? 'Ver más >>' : 'Ver menos <<';
        });
    }

    // Ver más amenities
    const moreAmenities = document.querySelector('.see-more-amenities');
    if (moreAmenities) {
        moreAmenities.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.extra-amenity').forEach(el => el.classList.toggle('d-none'));
            this.textContent = this.textContent.includes('más') ? 'Ver menos <<' : 'Ver más >>';
        });
    }

    // Ver más/menos en reviews
    document.querySelectorAll('.see-more-review').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const container = this.closest('.review-text');
            const shortText = container.querySelector('.short-text');
            const fullText = container.querySelector('.full-text');
            const hidden = fullText.classList.contains('d-none');
            shortText.classList.toggle('d-none', hidden);
            fullText.classList.toggle('d-none', !hidden);
            this.textContent = hidden ? 'Ver menos <<' : 'Ver más >>';
        });
    });

    // ===== Booking Tool Iframe =====
    (function initBookingTool() {
        const BASE = 'https://login.smoobu.com';
        const url  = @json($bookingToolUrl); // p.ej. 'https://login.smoobu.com/en/booking-tool/iframe/1380421'
        const target = '#apartmentIframeAll';
        function start() {
            if (window.BookingToolIframe && typeof window.BookingToolIframe.initialize === 'function') {
                window.BookingToolIframe.initialize({ url, baseUrl: BASE, target });
            }
        }
        if (!window.BookingToolIframe) {
            const s = document.createElement('script');
            s.src = BASE + '/js/Settings/BookingToolIframe.js';
            s.onload = start;
            document.head.appendChild(s);
        } else {
            start();
        }
    })();
});
</script>
@endsection
