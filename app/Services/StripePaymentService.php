<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripePaymentService
{
    public function __construct()
    {
        // Configura la clave secreta de Stripe desde el archivo .env
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crear un pago con tarjeta usando un PaymentIntent
     *
     * @param string $paymentMethodId ID del método de pago obtenido desde Stripe.js
     * @param int $amountCents Monto en centavos (ej. 1000 = $10.00)
     * @param string $currency Código de moneda (ej. 'USD')
     * @param string|null $description Descripción opcional del pago
     * @return PaymentIntent
     * @throws Exception
     */
    public function createCardPayment(string $paymentMethodId, int $amountCents, string $currency = 'USD', ?string $description = null): PaymentIntent
    {
        try {
            return PaymentIntent::create([
                'amount' => $amountCents,
                'currency' => $currency,
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'description' => $description,
                'idempotency_key' => Str::uuid()->toString(),
            ]);
        } catch (\Exception $e) {
            throw new Exception('Error al procesar el pago con Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar manualmente un PaymentIntent (opcional si se requiere segundo paso)
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws Exception
     */
    public function confirmPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
            return $intent->confirm();
        } catch (\Exception $e) {
            throw new Exception('Error al confirmar el pago con Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Obtener un PaymentIntent por ID
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws Exception
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (\Exception $e) {
            throw new Exception('Error al obtener el PaymentIntent: ' . $e->getMessage());
        }
    }
}
