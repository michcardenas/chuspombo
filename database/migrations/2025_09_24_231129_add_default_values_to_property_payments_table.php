<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultValuesToPropertyPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_payments', function (Blueprint $table) {
            // Añadir valores por defecto para evitar errores de campos requeridos
            if (Schema::hasColumn('property_payments', 'payment_status')) {
                $table->string('payment_status')->default('pending')->change();
            }
            if (Schema::hasColumn('property_payments', 'card_brand')) {
                $table->string('card_brand')->nullable()->change();
            }
            if (Schema::hasColumn('property_payments', 'last_4')) {
                $table->string('last_4')->nullable()->change();
            }
            if (Schema::hasColumn('property_payments', 'payment_method_type')) {
                $table->string('payment_method_type')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_payments', function (Blueprint $table) {
            // Revertir cambios
            if (Schema::hasColumn('property_payments', 'payment_status')) {
                $table->string('payment_status')->default(null)->change();
            }
        });
    }
}
