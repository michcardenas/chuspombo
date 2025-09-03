<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPayment extends Model
{
    protected $fillable = [
        'listing_id',
        'quote_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'check_in',
        'check_out',
        'guests_count',
        'total_price',
        'currency',

        // Stripe-specific fields
        'stripe_payment_id',         // reemplaza a square_payment_id
        'payment_status',
        'card_brand',
        'last_4',
        'payment_method_type',
    ];
}
