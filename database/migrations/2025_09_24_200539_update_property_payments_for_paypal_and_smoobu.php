<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePropertyPaymentsForPaypalAndSmoobu extends Migration
{
    public function up()
    {
        Schema::table('property_payments', function (Blueprint $table) {
            // Cambiar nombre de columnas existentes para consistencia
            if (Schema::hasColumn('property_payments', 'listing_id')) {
                $table->renameColumn('listing_id', 'apartment_id');
            }
            if (Schema::hasColumn('property_payments', 'check_in')) {
                $table->renameColumn('check_in', 'checkin');
            }
            if (Schema::hasColumn('property_payments', 'check_out')) {
                $table->renameColumn('check_out', 'checkout');
            }
            if (Schema::hasColumn('property_payments', 'guests_count')) {
                $table->renameColumn('guests_count', 'guests');
            }
            if (Schema::hasColumn('property_payments', 'total_price')) {
                $table->renameColumn('total_price', 'amount');
            }

            // Nuevas columnas para PayPal y Smoobu
            if (!Schema::hasColumn('property_payments', 'payment_method')) {
                $table->string('payment_method')->default('stripe')->after('currency');
            }
            if (!Schema::hasColumn('property_payments', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('property_payments', 'status')) {
                $table->string('status')->default('pending')->after('payment_id');
            }
            if (!Schema::hasColumn('property_payments', 'reservation_id')) {
                $table->string('reservation_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('property_payments', 'nights')) {
                $table->integer('nights')->nullable()->after('reservation_id');
            }

            // Hacer algunas columnas opcionales para PayPal
            if (Schema::hasColumn('property_payments', 'guest_name')) {
                $table->string('guest_name')->nullable()->change();
            }
            if (Schema::hasColumn('property_payments', 'guest_email')) {
                $table->string('guest_email')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('property_payments', function (Blueprint $table) {
            // Revertir nombres de columnas
            if (Schema::hasColumn('property_payments', 'apartment_id')) {
                $table->renameColumn('apartment_id', 'listing_id');
            }
            if (Schema::hasColumn('property_payments', 'checkin')) {
                $table->renameColumn('checkin', 'check_in');
            }
            if (Schema::hasColumn('property_payments', 'checkout')) {
                $table->renameColumn('checkout', 'check_out');
            }
            if (Schema::hasColumn('property_payments', 'guests')) {
                $table->renameColumn('guests', 'guests_count');
            }
            if (Schema::hasColumn('property_payments', 'amount')) {
                $table->renameColumn('amount', 'total_price');
            }

            // Eliminar nuevas columnas
            $table->dropColumn(['payment_method', 'payment_id', 'status', 'reservation_id', 'nights']);

            // Hacer columnas requeridas de nuevo
            $table->string('guest_name')->nullable(false)->change();
            $table->string('guest_email')->nullable(false)->change();
        });
    }
}
