@extends('layouts.app')

@section('title', $property['title'] ?? 'Detalle de Propiedad')

@section('content')
<div class="container py-5">
    <!-- Título de la propiedad -->
    <h1 class="fw-bold text-center">{{ $property['title'] ?? 'Sin título' }}</h1>
    <p class="text-muted text-center"><i class="fas fa-map-marker-alt"></i> {{ $property['address']['full'] ?? 'Ubicación no disponible' }}</p>

    <!-- Contenedor de la galería con posición relativa -->
    <div class="gallery-container position-relative">
        <div class="row g-2">
            @if(isset($property['pictures']) && count($property['pictures']) >= 4)
                <div class="col-md-6">
                    <img src="{{ $property['pictures'][0]['original'] ?? asset('images/property-placeholder.jpg') }}" 
                         class="img-fluid rounded w-100 h-100 object-fit-cover">
                </div>
                <div class="col-md-3">
                    <img src="{{ $property['pictures'][1]['original'] ?? asset('images/property-placeholder.jpg') }}" 
                         class="img-fluid rounded w-100 mb-2 object-fit-cover">
                    <img src="{{ $property['pictures'][2]['original'] ?? asset('images/property-placeholder.jpg') }}" 
                         class="img-fluid rounded w-100 object-fit-cover">
                </div>
                <div class="col-md-3">
                    <img src="{{ $property['pictures'][3]['original'] ?? asset('images/property-placeholder.jpg') }}" 
                         class="img-fluid rounded w-100 h-100 object-fit-cover">
                </div>
            @else
                <div class="col-12">
                    <img src="{{ asset('images/property-placeholder.jpg') }}" class="img-fluid rounded w-100">
                </div>
            @endif
        </div>

        <!-- Botón encima de las imágenes (inferior derecha) -->
        <button class="btn btn-primary view-images-btn" data-bs-toggle="modal" data-bs-target="#imageModal">
            Ver todas las imágenes
        </button>
    </div>


    <!-- Contenedor de información y formulario sticky -->
    <div class="row mt-4">
        <div class="col-md-7">
        <div class="property-pricing bg-white p-3 rounded mb-3 shadow-sm">
            <h3 class="fw-bold text-blue">${{ $property['prices']['basePrice'] ?? 'N/A' }} <small class="text-dark">/ noche</small></h3>
            <p class="text-muted">Moneda: {{ $property['prices']['currency'] ?? 'USD' }}</p>

            <!-- Características de la propiedad -->
            <div class="property-details bg-light p-3 rounded">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <i class="fas fa-bed text-blue me-2"></i> {{ $property['bedrooms'] ?? 'N/A' }} Habitaciones
                    </div>
                    <div class="col-6 d-flex align-items-center">
                        <i class="fas fa-bath text-blue me-2"></i> {{ $property['bathrooms'] ?? 'N/A' }} Baños
                    </div>
                    <div class="col-6 d-flex align-items-center mt-2">
                        <i class="fas fa-procedures text-blue me-2"></i> {{ $property['beds'] ?? 'N/A' }} Camas
                    </div>
                    <div class="col-6 d-flex align-items-center mt-2">
                        <i class="fas fa-users text-blue me-2"></i> Capacidad máxima: {{ $property['accommodates'] ?? 'N/A' }} huéspedes
                    </div>
                </div>
            </div>
        </div>

        @php
    $sections = [
        'summary' => 'Descripción',
        'space' => 'Espacio',
        'neighborhood' => 'Ubicación',
        'houseRules' => 'Reglas de la Casa'
    ];
@endphp

            <!-- Sección de detalles con "Ver más" -->
            @php
    $description = $property['publicDescription']['summary'] ?? '';
    $shortDescription = Str::limit($description, 250);
@endphp

@if(!empty($description))
    <div class="mb-4 description-section">
        <p class="description-text" style="line-height: 1.7;">
            <span class="short-text">{{ $shortDescription }}</span>
            <span class="full-text d-none">{{ $description }}</span>
        </p>
        @if(strlen($description) > 250)
            <a href="#" class="text-blue fw-semibold see-more-link" data-target="description">Ver más &gt;&gt;</a>
        @endif
    </div>
@endif
@php
    $space = $property['publicDescription']['space'] ?? '';
@endphp

@if(!empty($space))
    <div class="mt-5">
        <h4 class="fw-bold text-blue ">Espacio</h4>
        <p style="line-height: 1.7;">{{ $space }}</p>
        <hr>
    </div>
@endif
@if(!empty($property['amenities']))
    <div class="mt-5">
        <h4 class="fw-bold text-blue">Qué encontrarás en este lugar</h4>
        <div class="bg-light p-3 rounded mt-3">
            <div class="row amenities-container">
                @foreach ($property['amenities'] as $index => $amenity)
                    <div class="col-md-6 mb-2 amenity-item {{ $index >= 6 ? 'd-none extra-amenity' : '' }}">
                        <i class="fas fa-check-circle text-blue me-2" ></i>{{ $amenity }}
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
                    $text = $review['rawReview']['public_review'] ?? null;
                    $rating = $review['rawReview']['overall_rating'] ?? 0;
                    $avatarId = ($index % 70) + 1; // pravatar tiene 70 imágenes numeradas
                    $imgUrl = "https://i.pravatar.cc/150?img={$avatarId}";
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

                            @php
                            $shortText = Str::limit($text, 200);
                        @endphp

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
       <!-- 📌 FORMULARIO STICKY PARA RESERVAR -->
       <div class="col-md-5">
            <div class="sticky-form">
                <div class="calendar-container text-center">
                    <div id="calendar"></div>
                    <form id="reservationForm" action="{{ route('properties.confirm-reservation', $property['_id']) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" id="checkIn" name="checkIn">
                        <input type="hidden" id="checkOut" name="checkOut">
                        <input type="hidden" id="reservationData" name="reservationData">
                        <input type="hidden" id="quoteId" name="quoteId">
                        
                        <!-- Número de huéspedes -->
                        <div class="mb-3">
                            <label for="guestsCount" class="form-label">Número de huéspedes</label>
                            <select id="guestsCount" name="guestsCount" class="form-select" required>
                                @for ($i = 1; $i <= $property['accommodates']; $i++)
                                    <option value="{{ $i }}">{{ $i }} {{ $i == 1 ? 'huésped' : 'huéspedes' }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="mb-2 form-floating">
                            <input type="text" id="paymentAmount" name="payment[amount]" class="form-control" readonly placeholder="Monto Total">
                            <label for="paymentAmount">Monto Total</label>
                        </div>
                        
                        <div id="priceBreakdown" class="mt-3 mb-2"></div>
                        
                        <!-- Botón de Continuar -->
                        <button type="submit" id="reserveBtn" class="btn btn-primary w-100 mt-2" disabled>Continuar con la Reserva</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para todas las imágenes -->
<div class="modal fade" id="imageModal">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Todas las imágenes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    @if(isset($property['pictures']))
                        @foreach($property['pictures'] as $picture)
                            <div class="col-6">
                                <img src="{{ $picture['original'] ?? asset('images/property-placeholder.jpg') }}" class="img-fluid rounded">
                            </div>
                        @endforeach
                    @else
                        <p>No hay imágenes disponibles.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>

 document.addEventListener('DOMContentLoaded', function() {
       // Ver más/menos descripción (ya existente)
       document.querySelectorAll('.see-more-review').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const container = this.closest('.review-text');
                const shortText = container.querySelector('.short-text');
                const fullText = container.querySelector('.full-text');

                const isHidden = fullText.classList.contains('d-none');
                shortText.classList.toggle('d-none');
                fullText.classList.toggle('d-none');

                this.textContent = isHidden ? 'Ver menos <<' : 'Ver más >>';
            });
        });
       
       document.querySelectorAll('.see-more-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const descriptionBlock = document.querySelector('.description-section');
                const shortText = descriptionBlock.querySelector('.short-text');
                const fullText = descriptionBlock.querySelector('.full-text');
                if (fullText.classList.contains('d-none')) {
                    shortText.classList.add('d-none');
                    fullText.classList.remove('d-none');
                    this.textContent = 'Ver menos <<';
                } else {
                    shortText.classList.remove('d-none');
                    fullText.classList.add('d-none');
                    this.textContent = 'Ver más >>';
                }
            });
        });

        // Ver más amenities
        const seeMoreAmenities = document.querySelector('.see-more-amenities');
        if (seeMoreAmenities) {
            seeMoreAmenities.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelectorAll('.extra-amenity').forEach(el => el.classList.toggle('d-none'));
                this.textContent = this.textContent.includes('más') ? 'Ver menos <<' : 'Ver más >>';
            });
        }
    const bookedDates = @json($bookedDates);

    // Elemento para mostrar desglose de precios (ya creado en el HTML)
    const priceBreakdown = document.getElementById('priceBreakdown');
    
    // Selector de número de huéspedes
    const guestsCountSelect = document.getElementById('guestsCount');
    
    // Referencia al botón de reserva
    const reserveBtn = document.getElementById('reserveBtn');
    reserveBtn.textContent = 'Continuar con la Reserva';

    // Inicializar Flatpickr para el calendario
    flatpickr("#calendar", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: bookedDates,
        inline: true,
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                const checkIn = selectedDates[0].toISOString().split('T')[0];
                const checkOut = selectedDates[1].toISOString().split('T')[0];
                
                document.getElementById("checkIn").value = checkIn;
                document.getElementById("checkOut").value = checkOut;
                
                // Obtener número de huéspedes del selector
                const guestsCount = guestsCountSelect.value;
                
                // Usar el token CSRF del formulario
                const csrfToken = document.querySelector('input[name="_token"]').value;
                
                // Mostrar indicador de carga
                priceBreakdown.innerHTML = `
                    <div class="text-center py-3">
                        <div class="spinner-border text-blue" role="status">
                            <span class="visually-hidden">Calculando...</span>
                        </div>
                        <p class="mt-2">Calculando precio...</p>
                    </div>
                `;
                
                // Deshabilitar botón mientras se calcula
                reserveBtn.disabled = true;
                
                // Realizar petición AJAX para obtener la cotización
                fetch('{{ route('properties.calculatePrice') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        listingId: '{{ $property["_id"] }}',
                        checkIn: checkIn,
                        checkOut: checkOut,
                        guestsCount: guestsCount
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error en la respuesta:', data.error);
                        throw new Error(data.error);
                    }
                    
                    // Guardar datos completos en campo oculto
                    document.getElementById('reservationData').value = JSON.stringify(data);
                    
                    // Guardar ID de cotización si existe
                    if (data.quoteId) {
                        document.getElementById('quoteId').value = data.quoteId;
                        
                        // Intentar acceder a los datos de dinero desde la estructura anidada
                        if (data.money) {
                            // Asegurarse de que tenemos los datos necesarios
                            const fareAccommodation = data.money.fareAccommodation || 0;
                            const fareCleaning = data.money.fareCleaning || 0;
                            const subTotalPrice = data.money.subTotalPrice || (fareAccommodation + fareCleaning);
                            const currency = data.money.currency || 'USD';
                            
                            // Actualizar el campo de monto total
                            document.getElementById('paymentAmount').value = `$${subTotalPrice} ${currency}`;
                            
                            // Mostrar el desglose de precios
                            priceBreakdown.innerHTML = `
                                <div class="card p-3 mb-3">
                                    <h5 class="mb-3">Detalles de precio</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Alojamiento:</span>
                                        <span>$${fareAccommodation} ${currency}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Limpieza:</span>
                                        <span>$${fareCleaning} ${currency}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>$${subTotalPrice} ${currency}</span>
                                    </div>
                                </div>
                            `;
                            
                            // Habilitar el botón de reserva
                            reserveBtn.disabled = false;
                        } else {
                            console.error('No se encontraron datos de precio en la respuesta');
                        }
                    } else {
                        throw new Error('No se recibió un ID de cotización');
                    }
                })
                .catch(error => {
                    console.error('Error al obtener la cotización:', error);
                    priceBreakdown.innerHTML = `
                        <div class="alert alert-danger">
                            No se pudo calcular el precio. Por favor, intente nuevamente.
                            <p class="small mb-0 mt-1">${error.message || 'Error de conexión'}</p>
                        </div>
                    `;
                });
            }
        }
    });
    
    // Actualizar precio cuando cambia el número de huéspedes
    guestsCountSelect.addEventListener('change', function() {
        const checkInValue = document.getElementById("checkIn").value;
        const checkOutValue = document.getElementById("checkOut").value;
        
        if (checkInValue && checkOutValue) {
            // Simular un cambio en las fechas para recalcular
            const dates = flatpickr.instances[0].selectedDates;
            flatpickr.instances[0].onChange(dates, '', flatpickr.instances[0]);
        }
    });
});
</script>
@endsection
