@extends('layouts.app')
@section('title', 'Reservación confirmada')
@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body text-center">
          @if(!isset($booking_error))
            <div class="text-success mb-4">
              <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
            </div>
            <h1 class="text-success mb-3">¡Reservación confirmada!</h1>
            <p class="lead">Tu pago ha sido procesado exitosamente y tu reservación ha sido creada.</p>
          @else
            <div class="text-warning mb-4">
              <i class="fas fa-exclamation-triangle" style="font-size: 4rem;"></i>
            </div>
            <h1 class="text-warning mb-3">Pago procesado</h1>
            <div class="alert alert-warning">
              <strong>{{ $error_message }}</strong>
            </div>
          @endif

          <hr class="my-4">

          <div class="row text-start">
            <div class="col-md-6">
              <h5>Detalles del pago</h5>
              <p class="mb-1"><strong>Order ID:</strong> {{ $order->id }}</p>
              <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-success">{{ $order->status }}</span></p>
              @if(isset($payment))
                <p class="mb-1"><strong>Monto:</strong> €{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
                <p class="mb-1"><strong>Método:</strong> PayPal</p>
              @endif
            </div>
            @if(isset($payment))
            <div class="col-md-6">
              <h5>Detalles de la reservación</h5>
              <p class="mb-1"><strong>Check-in:</strong> {{ $payment->checkin }}</p>
              <p class="mb-1"><strong>Check-out:</strong> {{ $payment->checkout }}</p>
              <p class="mb-1"><strong>Noches:</strong> {{ $payment->nights }}</p>
              <p class="mb-1"><strong>Huéspedes:</strong> {{ $payment->guests }}</p>
              @if($payment->guest_name && $payment->guest_name !== 'Cliente PayPal')
                <p class="mb-1"><strong>Nombre:</strong> {{ $payment->guest_name }}</p>
              @endif
              @if($payment->guest_email && $payment->guest_email !== 'noreply@paypal.com')
                <p class="mb-1"><strong>Email:</strong> {{ $payment->guest_email }}</p>
              @endif
              @if($payment->guest_phone)
                <p class="mb-1"><strong>Teléfono:</strong> {{ $payment->guest_phone }}</p>
              @endif
              @if(isset($reservation) && isset($reservation['id']))
                <p class="mb-1"><strong>ID Reservación:</strong> #{{ $reservation['id'] }}</p>
              @endif
            </div>
            @endif
          </div>

          @if(!isset($booking_error))
            <div class="alert alert-info mt-4">
              <strong>¿Qué sigue?</strong><br>
              Recibirás un email de confirmación con todos los detalles de tu reservación.
              Si tienes alguna pregunta, no dudes en contactarnos.
            </div>
          @endif

          <div class="mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary btn-lg me-2">Volver al inicio</a>
            <a href="{{ url('/propiedades') }}" class="btn btn-outline-primary btn-lg">Ver más propiedades</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
