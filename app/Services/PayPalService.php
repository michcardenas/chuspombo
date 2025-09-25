<?php

namespace App\Services;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;

class PayPalService
{
    private PayPalHttpClient $client;
    private bool $isLive = false;

    public function __construct()
    {
        $clientId     = trim((string) config('paypal.client_id'));
        $clientSecret = trim((string) config('paypal.secret'));
        $modeRaw      = (string) config('paypal.mode', 'sandbox');
        $mode         = strtolower($modeRaw);

        // Log inicial (no expone secretos)
        \Log::info('PayPal init', [
            'mode' => $mode,
            'client_id_prefix' => substr($clientId, 0, 8),
        ]);

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('PayPal: Client ID/Secret vacíos. Revisa .env y limpia la caché de config.');
        }

        $this->isLive = in_array($mode, ['live', 'production', 'prod'], true);

        if ($this->isLive) {
            if (!class_exists(\PayPalCheckoutSdk\Core\ProductionEnvironment::class)) {
                throw new \RuntimeException('PayPal SDK no instalado. Ejecuta: composer require paypal/paypal-checkout-sdk:^1.0');
            }
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        } else {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        }

        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Crea una orden de PayPal (Orders API v2).
     *
     * @param float|string $amount
     * @param string       $currency
     * @param string|null  $customId        (<=127 chars) *evita JSON grande*
     * @param string|null  $description     (<=127 chars)
     * @param string|null  $brandName       (opcional)
     * @param string|null  $cancelUrl       (fallback a url('/paypal/cancel'))
     * @param string|null  $returnUrl       (fallback a url('/paypal/success'))
     * @param string|null  $idempotencyKey  (opcional)
     */
    public function createOrder(
        $amount,
        string $currency = 'EUR',
        ?string $customId = null,
        ?string $description = null,
        ?string $brandName = null,
        ?string $cancelUrl = null,
        ?string $returnUrl = null,
        ?string $idempotencyKey = null
    ) {
        $value = $this->formatAmount($amount);

        $purchaseUnit = [
            'amount' => [
                'currency_code' => $currency,
                'value' => $value,
            ],
        ];

        if (!empty($customId)) {
            $purchaseUnit['custom_id'] = $this->clip($customId, 127);
        }
        if (!empty($description)) {
            $purchaseUnit['description'] = $this->clip($description, 127);
        }

        $req = new OrdersCreateRequest();
        $req->prefer('return=representation');

        if (!empty($idempotencyKey)) {
            $req->headers['PayPal-Request-Id'] = $idempotencyKey;
        }

        $req->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [ $purchaseUnit ],
            'application_context' => array_filter([
                'cancel_url' => $cancelUrl ?: url('/paypal/cancel'),
                'return_url' => $returnUrl ?: url('/paypal/success'),
                'brand_name' => $brandName ?: config('app.name'),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                // 'landing_page' => 'NO_PREFERENCE', // opcional
            ]),
        ];

        try {
            return $this->client->execute($req);
        } catch (HttpException $e) {
            \Log::error('PayPal createOrder error', [
                'status_code' => $e->statusCode ?? null,
                'message'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Captura una orden aprobada.
     *
     * @param string      $orderId
     * @param string|null $idempotencyKey (opcional)
     */
    public function captureOrder(string $orderId, ?string $idempotencyKey = null)
    {
        $req = new OrdersCaptureRequest($orderId);
        $req->prefer('return=representation');

        if (!empty($idempotencyKey)) {
            $req->headers['PayPal-Request-Id'] = $idempotencyKey;
        }

        try {
            return $this->client->execute($req);
        } catch (HttpException $e) {
            \Log::error('PayPal captureOrder error', [
                'order_id'   => $orderId,
                'status_code'=> $e->statusCode ?? null,
                'message'    => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /** Formatea a "0.00" como string. */
    private function formatAmount($amount): string
    {
        if (is_string($amount)) {
            $normalized = str_replace(',', '.', $amount);
            if (is_numeric($normalized)) {
                return number_format((float) $normalized, 2, '.', '');
            }
        }
        return number_format((float) $amount, 2, '.', '');
    }

    /** Recorta cadenas a la longitud permitida. */
    private function clip(string $value, int $max): string
    {
        return mb_strimwidth($value, 0, $max, '', 'UTF-8');
    }
}
