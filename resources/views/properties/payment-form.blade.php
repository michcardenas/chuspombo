@extends('layouts.app')

@section('title', 'Formulario de Pago')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header text-white" style="background-color: #02006a;">
                    <h4 class="mb-0">💳 Formulario de Pago</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Estás a punto de realizar un pago seguro por
                        <strong>${{ number_format($totalPrice, 2) }} {{ $currency }}</strong>.
                        Completa los datos de tu tarjeta para continuar.
                    </p>

                    <!-- Elemento de Stripe -->
                    <form id="payment-form">
                        <div id="payment-element" class="mb-4">
                            <!-- Stripe injectará aquí el formulario -->
                        </div>

                        <div class="d-grid gap-2">
                            <button id="submit" class="btn text-white" style="background-color: #02006a;">
                                Pagar ahora
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>

                        <div id="payment-message" class="alert alert-info mt-3 d-none"></div>
                    </form>

                    <div class="border rounded p-3 mt-4 bg-light">
                        <h6 class="mb-2 text-dark">🔍 Datos de la reserva:</h6>
                        <ul class="list-unstyled small mb-0">
                            <li><strong>Monto:</strong> ${{ $totalPrice }} {{ $currency }}</li>
                            <li><strong>Nombre:</strong> {{ $guestName ?? '—' }}</li>
                            <li><strong>Email:</strong> {{ $guestEmail ?? '—' }}</li>
                            <li><strong>Teléfono:</strong> {{ $guestPhone ?? '—' }}</li>
                            <li><strong>Check-In:</strong> {{ $checkIn ?? '—' }}</li>
                            <li><strong>Check-Out:</strong> {{ $checkOut ?? '—' }}</li>
                            <li><strong>Huéspedes:</strong> {{ $guestsCount ?? '—' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ $stripeKey }}");
    const clientSecret = "{{ $clientSecret }}";

    let elements;

    document.addEventListener("DOMContentLoaded", async function () {
        elements = stripe.elements({ clientSecret });

        const paymentElement = elements.create("payment", {
            layout: "tabs" // puedes usar accordion o auto
        });

        paymentElement.mount("#payment-element");

        const form = document.getElementById('payment-form');
        const messageContainer = document.getElementById('payment-message');
        const submitButton = document.getElementById('submit');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            submitButton.disabled = true;

            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: "{{ route('stripe.redirect') }}"
                }
            });

            if (error) {
                showMessage(error.message || "Error en el pago");
                submitButton.disabled = false;
            }
        });

        function showMessage(message) {
            messageContainer.textContent = message;
            messageContainer.classList.remove("d-none");

            setTimeout(() => {
                messageContainer.classList.add("d-none");
                messageContainer.textContent = "";
            }, 5000);
        }
    });
</script>
@endsection
