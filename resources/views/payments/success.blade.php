@extends('layouts.app')

@section('title', 'Pago Exitoso - Hostella')

@section('content')
<section class="py-5 text-center" style="min-height: 70vh;">
    <div class="container">
        <h1 class="text-success mb-4"><i class="fas fa-check-circle"></i> ¡Pago Exitoso!</h1>
        <p class="lead">Gracias {{ $payment->guest_name }}, tu pago se ha procesado correctamente.</p>

        <div class="card shadow-sm mx-auto mt-4" style="max-width: 600px;">
            <div class="card-body text-start">
                <h5 class="card-title">Resumen del Pago</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Propiedad ID:</strong> {{ $payment->listing_id }}</li>
                    <li class="list-group-item"><strong>Check-in:</strong> {{ $payment->check_in }}</li>
                    <li class="list-group-item"><strong>Check-out:</strong> {{ $payment->check_out }}</li>
                    <li class="list-group-item"><strong>Huéspedes:</strong> {{ $payment->guests_count }}</li>
                    <li class="list-group-item"><strong>Correo:</strong> {{ $payment->guest_email }}</li>
                    <li class="list-group-item"><strong>Teléfono:</strong> {{ $payment->guest_phone ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Total:</strong> ${{ number_format($payment->total_price, 2) }} {{ strtoupper($payment->currency) }}</li>
                    <li class="list-group-item"><strong>Método:</strong> {{ strtoupper($payment->card_brand) }} ****{{ $payment->last_4 }}</li>
                </ul>
            </div>
        </div>

        <a href="{{ route('home') }}" class="btn btn-outline-primary mt-4">Volver al inicio</a>
    </div>
</section>
@endsection
