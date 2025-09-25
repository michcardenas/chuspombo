<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaypalCheckoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'paypal_order_id',
        'checkout_data',
    ];

    protected $casts = [
        'checkout_data' => 'array',
    ];

    /**
     * Store checkout data for a PayPal order
     */
    public static function store(string $paypalOrderId, array $checkoutData): self
    {
        return self::updateOrCreate(
            ['paypal_order_id' => $paypalOrderId],
            ['checkout_data' => $checkoutData]
        );
    }

    /**
     * Retrieve checkout data for a PayPal order
     */
    public static function retrieve(string $paypalOrderId): ?array
    {
        $session = self::where('paypal_order_id', $paypalOrderId)->first();
        return $session ? $session->checkout_data : null;
    }

    /**
     * Clean up old sessions (older than 2 hours)
     */
    public static function cleanup(): int
    {
        return self::where('created_at', '<', Carbon::now()->subHours(2))->delete();
    }
}
