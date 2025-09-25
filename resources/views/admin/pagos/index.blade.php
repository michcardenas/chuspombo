@extends('layouts.admin')

@section('title', 'Pagos PayPal')

<style>
    .filter-card {
        border-left: 4px solid #007bff;
    }
    .table-clean {
        font-size: 0.95rem;
    }
    .table-clean th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    .table-clean td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f4;
    }
    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }
    .badge-status {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
</style>

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fab fa-paypal text-primary me-2"></i>
            Pagos PayPal
        </h2>
        <div class="d-flex gap-3">
            <span class="badge bg-success fs-6">
                {{ $pagos->where('status', 'completed')->count() }} completados
            </span>
            <span class="badge bg-info fs-6">
                €{{ number_format($pagos->sum('amount') + $pagos->sum('total_price'), 2) }} total
            </span>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card filter-card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.pagos.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Buscar huésped</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nombre o email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completados</option>
                        <option value="requires_manual_booking" {{ request('status') == 'requires_manual_booking' ? 'selected' : '' }}>Revisar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Propiedad</label>
                    <select name="apartment_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($apartments as $apt)
                            <option value="{{ $apt['id'] }}" {{ request('apartment_id') == $apt['id'] ? 'selected' : '' }}>
                                {{ $apt['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Fecha desde</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Fecha hasta</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">{{ $pagos->total() }} pagos encontrados</h6>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-clean table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Huésped</th>
                            <th>Propiedad</th>
                            <th>Fechas</th>
                            <th>Importe</th>
                            <th>Estado</th>
                            <th>Fecha pago</th>
                            <th class="pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagos as $pago)
                            <tr>
                                <td class="ps-4">
                                    <div>
                                        <strong class="d-block">{{ $pago->guest_name ?? 'Cliente PayPal' }}</strong>
                                        <small class="text-muted">{{ $pago->guest_email ?? 'N/A' }}</small>
                                        @if($pago->guest_phone)
                                            <br><small class="text-success"><i class="fas fa-phone me-1"></i>{{ $pago->guest_phone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $pago->apartment_name ?? 'Apartamento #' . ($pago->apartment_id ?? $pago->listing_id) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $pago->guests ?? $pago->guests_count ?? 1 }} huésped{{ ($pago->guests ?? $pago->guests_count ?? 1) > 1 ? 'es' : '' }}</small>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>{{ \Carbon\Carbon::parse($pago->checkin ?? $pago->check_in)->format('d M Y') }}</strong>
                                        <br>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($pago->checkout ?? $pago->check_out)->format('d M Y') }}</span>
                                        @if($pago->nights)
                                            <br><span class="badge badge-status bg-light text-dark">{{ $pago->nights }} noche{{ $pago->nights > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success">€{{ number_format($pago->amount ?? $pago->total_price, 2) }}</strong>
                                    <br>
                                    <span class="badge bg-primary badge-status">
                                        <i class="fab fa-paypal me-1"></i>PayPal
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $status = $pago->status ?? $pago->payment_status ?? 'unknown';
                                    @endphp
                                    @if (in_array(strtolower($status), ['completed', 'confirmed']))
                                        <span class="badge bg-success badge-status">
                                            <i class="fas fa-check me-1"></i>Completado
                                        </span>
                                        @if($pago->reservation_id)
                                            <br><small class="text-muted">Reserva #{{ $pago->reservation_id }}</small>
                                        @endif
                                    @elseif (in_array(strtolower($status), ['requires_manual_booking']))
                                        <span class="badge bg-warning badge-status">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Revisar
                                        </span>
                                    @else
                                        <span class="badge bg-secondary badge-status">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $pago->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $pago->created_at->format('H:i') }}</small>
                                </td>
                                <td class="pe-4">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.pagos.show', $pago->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                            Ver
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="confirmarEliminacion({{ $pago->id }}, '{{ $pago->guest_name }}', '{{ $pago->payment_id }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Form oculto para DELETE -->
                                    <form id="delete-form-{{ $pago->id }}" method="POST" action="{{ route('admin.pagos.destroy', $pago->id) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fab fa-paypal text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <p class="text-muted mb-0">No se encontraron pagos PayPal</p>
                                    <small class="text-muted">Los pagos aparecerán aquí cuando se completen las transacciones</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pagos->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $pagos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(pagoId, guestName, paypalId) {
    const message = `¿Estás seguro de que quieres eliminar este pago?

Huésped: ${guestName}
PayPal ID: ${paypalId}

Esta acción no se puede deshacer.`;

    if (confirm(message)) {
        document.getElementById('delete-form-' + pagoId).submit();
    }
}

// Mostrar mensajes de éxito/error
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
