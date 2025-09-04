@extends('layouts.admin')

@section('title', 'Pagos')

<style>
    td, th {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    td.text-break {
        word-break: break-word;
        max-width: 250px;
    }

    .table td, .table th {
        padding: 0.75rem 1rem;
    }
</style>


@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-lg border-0">
                <div class="card-header text-white" style="background-color: #02006a;">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Listado de Pagos Recibidos</h5>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center table-hover w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Propiedad</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Total</th>
                                    <th>Moneda</th>
                                    <th>Método</th>
                                    <th>Últimos 4</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pagos as $pago)
                                    <tr>
                                        <td>{{ $pago->guest_email }}</td>
                                        <td>{{ $pago->guest_phone ?? '—' }}</td>
                                        <td class="text-break">{{ $pago->listing_id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($pago->check_in)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($pago->check_out)->format('d/m/Y') }}</td>
                                        <td>${{ number_format($pago->total_price, 2) }}</td>
                                        <td>{{ $pago->currency }}</td>
                                        <td>{{ strtoupper($pago->payment_method_type ?? '—') }}</td>
                                        <td>{{ $pago->last_4 }}</td>
                                        <td>
                                            @if ($pago->payment_status === 'COMPLETED')
                                                <span class="badge bg-success">Confirmado</span>
                                            @else
                                                <span class="badge bg-danger">Rechazado</span>
                                            @endif
                                        </td>
                                        <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11">No se han recibido pagos aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
