<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPayment extends Model
{
    protected $fillable = [
        // Datos básicos
        'apartment_id',
        'quote_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'checkin',
        'checkout',
        'guests',
        'nights',
        'amount',
        'currency',

        // Campos de pago
        'payment_method',
        'payment_id',
        'status',
        'reservation_id',

        // Campos específicos de Stripe/legacy
        'stripe_payment_id',
        'payment_status',  // campo legacy pero necesario
        'card_brand',
        'last_4',
        'payment_method_type',
    ];
}
