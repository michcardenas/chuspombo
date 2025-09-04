@extends('layouts.app')

@section('title', $property['title'] ?? 'Propiedad')

@section('meta_description', $property['description'] ?? 'Descubre esta propiedad exclusiva.')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="fw-bold">{{ $property['title'] }}</h1>
            <p class="text-muted">
                <i class="fas fa-map-marker-alt"></i> {{ $property['address']['city'] ?? 'Ubicación no disponible' }},
                {{ $property['address']['country'] ?? 'País no disponible' }}
            </p>

            <img src="{{ $property['picture']['large'] ?? asset('images/property-placeholder.jpg') }}" class="img-fluid rounded mb-4" alt="{{ $property['title'] }}">

            <h3>Descripción</h3>
            <p>{{ $property['description'] ?? 'No hay descripción disponible.' }}</p>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h3 class="fw-bold">${{ $property['prices']['basePrice'] ?? '0' }}/noche</h3>
                <p><i class="fas fa-bed"></i> {{ $property['bedrooms'] ?? 0 }} habitaciones</p>
                <p><i class="fas fa-bath"></i> {{ $property['bathrooms'] ?? 0 }} baños</p>

                <a href="{{ route('properties.reservation', $property['_id']) }}" class="btn btn-primary btn-lg w-100">
    Reservar Ahora
</a>
            </div>
        </div>
    </div>
</div>
@endsection
