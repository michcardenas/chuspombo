@extends('layouts.app')

@section('title', 'Confirmar Reserva')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Confirmar Reserva</h4>
                </div>
                <div class="card-body">
                    <!-- Resumen de la Propiedad -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            @if(isset($property['pictures']) && count($property['pictures']) > 0)
                                <img src="{{ $property['pictures'][0]['original'] ?? asset('images/property-placeholder.jpg') }}" 
                                     class="img-fluid rounded" alt="{{ $property['title'] }}">
                            @else
                                <img src="{{ asset('images/property-placeholder.jpg') }}" class="img-fluid rounded" alt="Imagen no disponible">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5>{{ $property['title'] ?? 'Propiedad sin título' }}</h5>
                            <p class="text-muted mb-1"><i class="fas fa-map-marker-alt"></i> {{ $property['address']['full'] ?? 'Ubicación no disponible' }}</p>
                            <div class="d-flex my-2">
                                <small class="me-2"><i class="fas fa-bed"></i> {{ $property['bedrooms'] ?? '0' }} habitaciones</small>
                                <small class="me-2"><i class="fas fa-bath"></i> {{ $property['bathrooms'] ?? '0' }} baños</small>
                                <small><i class="fas fa-user"></i> Hasta {{ $property['accommodates'] ?? '0' }} huéspedes</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Resumen de la Reserva -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Detalles de la Reserva</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <p class="mb-1"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($checkIn)->format('d/m/Y') }}</p>
                                        <p class="mb-1"><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($checkOut)->format('d/m/Y') }}</p>
                                        <p class="mb-0"><strong>Huéspedes:</strong> {{ $guestsCount }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="mb-2">Desglose de Precios</h6>
                                        @if(isset($quoteData['money']))
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Alojamiento:</span>
                                                <span>${{ $quoteData['money']['fareAccommodation'] ?? '0' }} {{ $quoteData['money']['currency'] ?? 'USD' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Limpieza:</span>
                                                <span>${{ $quoteData['money']['fareCleaning'] ?? '0' }} {{ $quoteData['money']['currency'] ?? 'USD' }}</span>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total:</span>
                                                <span>${{ $quoteData['money']['subTotalPrice'] ?? '0' }} {{ $quoteData['money']['currency'] ?? 'USD' }}</span>
                                            </div>
                                        @else
                                            <p class="text-muted">Información de precios no disponible</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Formulario de Información del Huésped -->
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="mb-3">Información del Huésped</h5>
                            <form action="{{ route('properties.payment-form', ['propertyId' => $property['_id'], 'quoteId' => $quoteId]) }}" method="POST">
                                @csrf
                                <!-- Campos ocultos para datos de la reserva -->
                                <input type="hidden" name="listingId" value="{{ $property['_id'] }}">
                                <input type="hidden" name="checkIn" value="{{ $checkIn }}">
                                <input type="hidden" name="checkOut" value="{{ $checkOut }}">
                                <input type="hidden" name="guestsCount" value="{{ $guestsCount }}">
                                <input type="hidden" name="quoteId" value="{{ $quoteId }}">
                                <input type="hidden" name="totalPrice" value="{{ $quoteData['money']['subTotalPrice'] ?? '0' }}">
                                <input type="hidden" name="currency" value="{{ $quoteData['money']['currency'] ?? 'USD' }}">
                                <input type="hidden" name="reservationData" value="{{ json_encode($quoteData) }}">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control @error('guestName') is-invalid @enderror" 
                                                   id="guestName" name="guestName" placeholder="Nombre Completo" required>
                                            <label for="guestName">Nombre Completo</label>
                                            @error('guestName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control @error('guestEmail') is-invalid @enderror" 
                                                   id="guestEmail" name="guestEmail" placeholder="Email" required>
                                            <label for="guestEmail">Correo Electrónico</label>
                                            @error('guestEmail')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control @error('guestPhone') is-invalid @enderror" 
                                                   id="guestPhone" name="guestPhone" placeholder="Teléfono">
                                            <label for="guestPhone">Teléfono de Contacto (Opcional)</label>
                                            @error('guestPhone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">Proceder al Pago</button>
                                    <a href="{{ route('properties.show', $property['_id']) }}" class="btn btn-outline-secondary">Volver a la Propiedad</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection