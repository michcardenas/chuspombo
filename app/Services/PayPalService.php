<?php

namespace App\Services;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class PayPalService
{
    private $client;

    public function __construct()
    {
        $clientId = config('paypal.client_id');
        $clientSecret = config('paypal.secret');
        $mode = config('paypal.mode', 'sandbox');

        $environment = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder($amount, $currency = 'EUR', $customId = null, $description = null)
    {
        $req = new OrdersCreateRequest();
        $req->prefer('return=representation');
        $req->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => (string)$amount,
                ],
                'custom_id'   => $customId ? substr($customId, 0, 127) : null,
                'description' => $description ? substr($description, 0, 127) : null,
            ]],
            'application_context' => [
                'cancel_url' => url('/paypal/cancel'),
                'return_url' => url('/paypal/success'),
            ]
        ];
        return $this->client->execute($req);
    }

    public function captureOrder($orderId)
    {
        $req = new OrdersCaptureRequest($orderId);
        $req->prefer('return=representation');
        return $this->client->execute($req);
    }
}
