<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();

            // ===== HERO =====
            $table->string('hero_title')->nullable();        // Título grande (h1)
            $table->string('hero_subtitle')->nullable();     // Subtítulo
            $table->string('hero_cta_text')->nullable();     // Texto del botón
            $table->string('hero_cta_url')->nullable();      // URL del botón
            $table->string('hero_image')->nullable();        // Nombre/relpath de imagen

            // ===== NUESTRA HISTORIA =====
            $table->string('story_title')->nullable();
            $table->string('story_subtitle')->nullable();
            $table->text('story_text')->nullable();

            // ===== LA EXPERIENCIA (3 tarjetas) =====
            $table->string('exp_title')->nullable();
            $table->string('exp_subtitle')->nullable();
            $table->string('exp_card1_title')->nullable();
            $table->text('exp_card1_text')->nullable();
            $table->string('exp_card2_title')->nullable();
            $table->text('exp_card2_text')->nullable();
            $table->string('exp_card3_title')->nullable();
            $table->text('exp_card3_text')->nullable();

            // ===== POR QUÉ ELEGIRNOS (3 items) =====
            $table->string('why_title')->nullable();
            $table->string('why_item1_title')->nullable();
            $table->text('why_item1_text')->nullable();
            $table->string('why_item2_title')->nullable();
            $table->text('why_item2_text')->nullable();
            $table->string('why_item3_title')->nullable();
            $table->text('why_item3_text')->nullable();

            // ===== CTA FINAL =====
            $table->string('cta_title')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->string('cta_phone_label')->nullable();      // p.ej. "Llámanos"
            $table->string('cta_phone_number')->nullable();     // p.ej. +34...
            $table->string('cta_background_image')->nullable(); // fondo del bloque CTA

            // Estado
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            // $table->softDeletes(); // <- Descomenta si quieres borrado lógico
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
