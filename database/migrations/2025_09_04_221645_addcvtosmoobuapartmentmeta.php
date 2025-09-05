<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('smoobu_apartment_meta', function (Blueprint $table) {
            $table->string('calendar_verification', 191)->nullable()->after('base_price_override');
        });
    }

    public function down(): void
    {
        Schema::table('smoobu_apartment_meta', function (Blueprint $table) {
            $table->dropColumn('calendar_verification');
        });
    }
};
