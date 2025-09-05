<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('smoobu_apartment_images', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('apartment_id')->index();
            $t->string('path');                 // p.ej. 'storage/smoobu/2884371/portada.jpg'
            $t->string('alt_text')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0)->index();
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();

            $t->foreign('apartment_id')
              ->references('apartment_id')
              ->on('smoobu_apartment_meta')
              ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smoobu_apartment_images');
    }
};
