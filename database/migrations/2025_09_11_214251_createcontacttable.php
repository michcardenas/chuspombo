<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_pages', function (Blueprint $table) {
            $table->id();

            // Textos principales
            $table->string('h1')->nullable();
            $table->string('h2')->nullable();
            $table->text('intro_text')->nullable();      // párrafo introductorio
            $table->text('side_text')->nullable();       // texto lateral / ayuda

            // Datos de contacto
            $table->string('email_primary')->nullable();
            $table->string('email_secondary')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('website')->nullable();

            // Dirección
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            // Mapa
            $table->string('map_embed_url')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Redes sociales
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();

            // Imágenes / media
            $table->string('hero_image')->nullable();     // ruta en /public/images
            $table->string('banner_image')->nullable();   // ruta en /public/images

            // Config formulario
            $table->string('form_recipient')->nullable(); // correo destino para envíos
            $table->string('form_cc')->nullable();        // CC opcional
            $table->string('success_message')->nullable();// mensaje éxito
            $table->string('legal_checkbox_label')->nullable();
            $table->string('legal_link_url')->nullable();

            // Horarios (JSON) => [{day:"Lun",open:"09:00",close:"18:00"}, ...]
            $table->json('business_hours')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices útiles
            $table->index(['email_primary']);
            $table->index(['city', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_pages');
    }
};
