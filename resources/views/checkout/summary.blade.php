@extends('layouts.app')
@section('title', 'Resumen de tu reserva')

@section('content')
@php $fmt = fn($n) => number_format($n, 0, ',', '.'); @endphp
<div class="container py-5">
  <h1 class="mb-4">Resumen de tu reserva</h1>

  <div class="row">
    <div class="col-md-7">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <h4 class="mb-3">{{ $checkout['apartment_title'] }}</h4>
          <p class="mb-1"><strong>Check-in:</strong> {{ $checkout['checkin'] }} a las {{ $checkout['checkin_hour'] }}</p>
          <p class="mb-1"><strong>Check-out:</strong> {{ $checkout['checkout'] }} a las {{ $checkout['checkout_hour'] }}</p>
          <p class="mb-1"><strong>Noches:</strong> {{ $checkout['nights'] }}</p>
          <p class="mb-1"><strong>Huéspedes:</strong> {{ $checkout['guests'] }}</p>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3">Detalle de precio</h5>
          <ul class="list-unstyled">
            <li class="d-flex justify-content-between">
              <span>{{ $checkout['nights'] }} noche{{ $checkout['nights'] > 1 ? 's' : '' }}</span>
              <strong>€{{ $fmt($checkout['subtotal']) }}</strong>
            </li>
            @if($checkout['tax'] > 0)
            <li class="d-flex justify-content-between">
              <span>Impuestos</span><strong>€{{ $fmt($checkout['tax']) }}</strong>
            </li>
            @endif
            @if($checkout['fees'] > 0)
            <li class="d-flex justify-content-between">
              <span>Cargos</span><strong>€{{ $fmt($checkout['fees']) }}</strong>
            </li>
            @endif
            <hr>
            <li class="d-flex justify-content-between">
              <span>Total</span><strong>€{{ $fmt($checkout['total']) }} {{ $checkout['currency'] }}</strong>
            </li>
          </ul>

          <form action="{{ route('paypal.pay') }}" method="POST" class="mt-3">
            @csrf
            <input type="hidden" name="apartment_id" value="{{ $checkout['apartment_id'] }}">
            <input type="hidden" name="apartment_title" value="{{ $checkout['apartment_title'] }}">
            <input type="hidden" name="checkin" value="{{ $checkout['checkin'] }}">
            <input type="hidden" name="checkout" value="{{ $checkout['checkout'] }}">
            <input type="hidden" name="checkin_hour" value="{{ $checkout['checkin_hour'] }}">
            <input type="hidden" name="checkout_hour" value="{{ $checkout['checkout_hour'] }}">
            <input type="hidden" name="guests" value="{{ $checkout['guests'] }}">
            <input type="hidden" name="total" value="{{ $checkout['total'] }}">
            <input type="hidden" name="currency" value="{{ $checkout['currency'] }}">
            <button class="btn btn-warning w-100 fw-bold">
              <i class="fab fa-paypal me-2"></i>
              Pagar €{{ $fmt($checkout['total']) }} con PayPal
            </button>
          </form>

          <a href="javascript:history.back()" class="btn btn-link mt-2">Cambiar fechas</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
