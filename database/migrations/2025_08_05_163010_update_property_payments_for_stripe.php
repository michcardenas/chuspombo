<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePropertyPaymentsForStripe extends Migration
{
public function up()
{
    Schema::table('property_payments', function (Blueprint $table) {
        // Opcional: si ya existe square_payment_id, lo puedes eliminar
        if (Schema::hasColumn('property_payments', 'square_payment_id')) {
            $table->dropColumn('square_payment_id');
        }

        // Agrega el nuevo campo de Stripe si no existe
        if (!Schema::hasColumn('property_payments', 'stripe_payment_id')) {
            $table->string('stripe_payment_id')->nullable()->after('currency');
        }
    });
}

public function down()
{
    Schema::table('property_payments', function (Blueprint $table) {
        $table->dropColumn('stripe_payment_id');
        $table->string('square_payment_id')->nullable();
    });
}

}
