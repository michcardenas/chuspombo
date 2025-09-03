<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaginaMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagina_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagina_id')->constrained('paginas')->onDelete('cascade');

            // SEO Básico
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // SEO Adicional
            $table->string('canonical_url')->nullable();
            $table->string('robots')->nullable(); // Ej: "index, follow"
            $table->string('author')->nullable();
            $table->string('language')->nullable(); // Ej: "es"
            $table->string('viewport')->nullable()->default('width=device-width, initial-scale=1');
            $table->string('charset')->nullable()->default('utf-8');

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
        Schema::dropIfExists('pagina_metas');
    }
}
