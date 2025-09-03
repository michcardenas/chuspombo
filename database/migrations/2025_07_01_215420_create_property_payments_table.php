<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_payments', function (Blueprint $table) {
            $table->id();

            $table->string('listing_id');
            $table->string('quote_id')->nullable();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('guests_count');
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 10);

            // Campos del pago
            $table->string('square_payment_id');
            $table->string('payment_status');
            $table->string('card_brand')->nullable();
            $table->string('last_4')->nullable();
            $table->string('payment_method_type')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_payments');
    }
}
