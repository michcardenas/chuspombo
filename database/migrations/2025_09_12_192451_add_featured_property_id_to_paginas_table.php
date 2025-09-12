<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            // ID de apartamento en Smoobu seleccionado para "Lugar favorito"
            $table->string('featured_property_id', 100)
                  ->nullable()
                  ->comment('Smoobu apartment ID for featured property');

            // Si prefieres index para filtrar rápidamente por este campo:
            // $table->index('featured_property_id');
        });
    }

    public function down(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            $table->dropColumn('featured_property_id');

            // Si agregaste índice arriba:
            // $table->dropIndex(['featured_property_id']);
        });
    }
};
