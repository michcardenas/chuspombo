@extends('layouts.app')
@section('title', 'Pago exitoso')
@section('content')
<div class="container py-5">
  <h1>✅ ¡Pago exitoso!</h1>
  <p class="mb-1"><strong>Order ID:</strong> {{ $order->id }}</p>
  <p class="mb-1"><strong>Estatus:</strong> {{ $order->status }}</p>
  <a href="{{ url('/') }}" class="btn btn-primary mt-3">Volver al inicio</a>
</div>
@endsection
