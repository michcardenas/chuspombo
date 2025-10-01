<?php

$modeRaw = env('PAYPAL_MODE', 'sandbox');
$mode = strtolower($modeRaw);
$normalizedMode = in_array($mode, ['live', 'production', 'prod'], true) ? 'live' : 'sandbox';

$apiBase = $normalizedMode === 'live'
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';

$appUrl = rtrim(env('APP_URL', ''), '/');

return [
    // Credenciales
    'client_id' => trim((string) env('PAYPAL_CLIENT_ID', '')),
    'secret'    => trim((string) env('PAYPAL_SECRET', '')),

    // Modo normalizado: 'sandbox' | 'live'
    'mode'      => $normalizedMode,

    // Base API por entorno (útil si haces llamadas manuales / webhooks)
    'api_base'  => $apiBase,

    // Webhooks (LIVE/SANDBOX según el modo que uses)
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    // Ajustes por defecto (puedes sobreescribirlos en runtime)
    'default_currency'     => env('PAYPAL_CURRENCY', 'EUR'),
    'brand_name'           => env('PAYPAL_BRAND_NAME', env('APP_NAME', 'Chuspombo')),
    'return_url'           => env('PAYPAL_RETURN_URL', $appUrl ? $appUrl . '/paypal/success' : null),
    'cancel_url'           => env('PAYPAL_CANCEL_URL', $appUrl ? $appUrl . '/paypal/cancel'  : null),
    'user_action'          => env('PAYPAL_USER_ACTION', 'PAY_NOW'),
    'shipping_preference'  => env('PAYPAL_SHIPPING_PREFERENCE', 'NO_SHIPPING'),

    // Idempotencia opcional
    'idempotency' => [
        'enabled' => (bool) env('PAYPAL_IDEMPOTENCY', false),
        'prefix'  => env('PAYPAL_IDEMPOTENCY_PREFIX', 'pp-order-'),
    ],

    // Timeout (informativo si quieres usarlo en tu cliente HTTP)
    'timeout' => (int) env('PAYPAL_TIMEOUT', 30),
];
