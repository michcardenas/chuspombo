<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaypalCheckoutSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('paypal_order_id')->unique();
            $table->json('checkout_data');
            $table->timestamps();

            // Auto-eliminar después de 2 horas
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_checkout_sessions');
    }
}
