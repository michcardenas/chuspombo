@extends('layouts.app')

@section('title', 'Pago Fallido - Hostella')

@section('content')
<section class="py-5 text-center" style="min-height: 70vh;">
    <div class="container">
        <h1 class="text-danger mb-4"><i class="fas fa-times-circle"></i> Pago Fallido</h1>
        <p class="lead">Lo sentimos, tu pago no se pudo completar.</p>

        @if(session('error'))
            <div class="alert alert-danger mx-auto mt-4" style="max-width: 600px;">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-4">Intentar de nuevo</a>
    </div>
</section>
@endsection
