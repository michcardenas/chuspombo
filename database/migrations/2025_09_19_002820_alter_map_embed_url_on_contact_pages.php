<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            // Si era string(255), cámbiala a TEXT
            $table->text('map_embed_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            // Revertir (si quieres volver a 255)
            $table->string('map_embed_url', 255)->nullable()->change();
        });
    }
};

