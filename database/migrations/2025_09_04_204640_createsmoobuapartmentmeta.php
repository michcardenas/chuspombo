<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('smoobu_apartment_meta', function (Blueprint $t) {
            $t->unsignedBigInteger('apartment_id')->primary(); // ID de Smoobu
            $t->string('title')->nullable();
            $t->string('slug')->nullable()->unique();
            $t->text('description')->nullable();

            $t->string('city')->nullable();
            $t->string('country')->nullable();

            $t->unsignedTinyInteger('bedrooms')->nullable();   // 0..255
            $t->decimal('bathrooms', 3, 1)->nullable();        // ej. 1.5

            // Imagen de portada subida a storage/app/public/... (se sirve por /storage/..)
            $t->string('cover_image_path')->nullable();

            // Precio manual para override del precio obtenido de Smoobu
            $t->decimal('base_price_override', 10, 2)->nullable();

            $t->boolean('is_published')->default(true)->index();
            $t->unsignedSmallInteger('sort_order')->default(0)->index();

            $t->json('extras')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smoobu_apartment_meta');
    }
};