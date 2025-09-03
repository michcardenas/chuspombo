<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            $table->string('card1_image_1')->nullable();
            $table->string('card1_image_2')->nullable();
            $table->string('card1_image_3')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            $table->dropColumn(['card1_image_1', 'card1_image_2', 'card1_image_3']);
        });
    }
};
