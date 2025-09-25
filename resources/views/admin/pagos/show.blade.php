@extends('layouts.admin')

@section('title', 'Detalle del Pago #' . $pago->id)

<style>
    .detail-card {
        border-left: 4px solid #007bff;
    }
    .info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f1f3f4;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 140px;
    }
</style>

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.pagos.index') }}">Pagos PayPal</a></li>
                    <li class="breadcrumb-item active">Pago #{{ $pago->id }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">
                <i class="fab fa-paypal text-primary me-2"></i>
                Detalle del Pago #{{ $pago->id }}
            </h2>
        </div>

        <div class="d-flex gap-2">
            @php
                $status = $pago->status ?? $pago->payment_status ?? 'unknown';
            @endphp
            @if (in_array(strtolower($status), ['completed', 'confirmed']))
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="fas fa-check me-1"></i>Pago Completado
                </span>
            @elseif (in_array(strtolower($status), ['requires_manual_booking']))
                <span class="badge bg-warning fs-6 px-3 py-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>Requiere Revisión
                </span>
            @else
                <span class="badge bg-secondary fs-6 px-3 py-2">{{ ucfirst($status) }}</span>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Información del Huésped -->
        <div class="col-lg-6 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Información del Huésped
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex">
                        <span class="info-label">Nombre:</span>
                        <span>{{ $pago->guest_name ?? 'No disponible' }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Email:</span>
                        <span>{{ $pago->guest_email ?? 'No disponible' }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Teléfono:</span>
                        <span>
                            @if($pago->guest_phone)
                                <span class="text-success">
                                    <i class="fas fa-phone me-1"></i>{{ $pago->guest_phone }}
                                </span>
                            @else
                                <span class="text-muted">No recolectado</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de la Reservación -->
        <div class="col-lg-6 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>Información de la Reservación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex">
                        <span class="info-label">Propiedad:</span>
                        <span>
                            <strong>{{ $pago->apartment_name ?? 'Apartamento #' . ($pago->apartment_id ?? $pago->listing_id) }}</strong>
                        </span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Check-in:</span>
                        <span>{{ \Carbon\Carbon::parse($pago->checkin ?? $pago->check_in)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Check-out:</span>
                        <span>{{ \Carbon\Carbon::parse($pago->checkout ?? $pago->check_out)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Noches:</span>
                        <span>{{ $pago->nights ?? 'N/A' }} noche{{ ($pago->nights ?? 0) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Huéspedes:</span>
                        <span>{{ $pago->guests ?? $pago->guests_count ?? 'N/A' }}</span>
                    </div>
                    @if($pago->reservation_id)
                    <div class="info-row d-flex">
                        <span class="info-label">Reserva Smoobu:</span>
                        <span>
                            <span class="badge bg-success">
                                <i class="fas fa-calendar-check me-1"></i>#{{ $pago->reservation_id }}
                            </span>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información del Pago -->
        <div class="col-lg-6 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Información del Pago
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex">
                        <span class="info-label">Importe:</span>
                        <span class="text-success fw-bold fs-5">€{{ number_format($pago->amount ?? $pago->total_price, 2) }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Moneda:</span>
                        <span>{{ $pago->currency }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Método:</span>
                        <span>
                            <span class="badge bg-primary">
                                <i class="fab fa-paypal me-1"></i>PayPal
                            </span>
                        </span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">ID PayPal:</span>
                        <span class="font-monospace small">{{ $pago->payment_id ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Fecha:</span>
                        <span>{{ $pago->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Técnica -->
        <div class="col-lg-6 mb-4">
            <div class="card detail-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Información Técnica
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row d-flex">
                        <span class="info-label">ID interno:</span>
                        <span>#{{ $pago->id }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Estado pago:</span>
                        <span>{{ $pago->status ?? $pago->payment_status ?? 'unknown' }}</span>
                    </div>
                    @if($pago->quote_id)
                    <div class="info-row d-flex">
                        <span class="info-label">Quote ID:</span>
                        <span class="font-monospace small">{{ $pago->quote_id }}</span>
                    </div>
                    @endif
                    <div class="info-row d-flex">
                        <span class="info-label">Creado:</span>
                        <span>{{ $pago->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="info-row d-flex">
                        <span class="info-label">Actualizado:</span>
                        <span>{{ $pago->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(in_array(strtolower($status), ['requires_manual_booking']))
        <!-- Acciones Requeridas -->
        <div class="col-12 mb-4">
            <div class="alert alert-warning border-warning">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading">Acción Manual Requerida</h5>
                        <p class="mb-2">
                            Este pago se procesó correctamente en PayPal, pero la reservación en Smoobu no se pudo crear automáticamente.
                        </p>
                        <p class="mb-0">
                            <strong>Pasos a seguir:</strong>
                        </p>
                        <ol class="mb-0 mt-2">
                            <li>Crear manualmente la reservación en Smoobu con los datos mostrados arriba</li>
                            <li>Usar el PayPal ID <code>{{ $pago->payment_id }}</code> como referencia</li>
                            <li>Incluir las horas de check-in/out en las notas</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al listado
                </a>

                <div class="d-flex gap-2">
                    @if($pago->reservation_id)
                        <a href="#" class="btn btn-success disabled">
                            <i class="fas fa-check me-2"></i>Reservación Creada
                        </a>
                    @endif

                    <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminacion()">
                        <i class="fas fa-trash me-2"></i>Eliminar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para DELETE -->
<form id="delete-form" method="POST" action="{{ route('admin.pagos.destroy', $pago->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmarEliminacion() {
    const message = `¿Estás seguro de que quieres eliminar este pago?

Huésped: {{ $pago->guest_name }}
PayPal ID: {{ $pago->payment_id }}
Importe: €{{ number_format($pago->amount ?? $pago->total_price, 2) }}

Esta acción no se puede deshacer y eliminará permanentemente:
- El registro del pago
- Toda la información asociada

¿Continuar con la eliminación?`;

    if (confirm(message)) {
        document.getElementById('delete-form').submit();
    }
}

// Mostrar mensajes de éxito/error si vienen de redirect
@if(session('success'))
    setTimeout(() => {
        alert('✅ {{ session('success') }}');
    }, 100);
@endif

@if(session('error'))
    setTimeout(() => {
        alert('❌ {{ session('error') }}');
    }, 100);
@endif
</script>
@endsection