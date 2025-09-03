<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaginasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paginas', function (Blueprint $table) {
            $table->id();
            $table->string('h1')->nullable();
            $table->string('h2_1')->nullable();
            $table->string('h2_propiedades')->nullable();
            $table->text('p_propiedades')->nullable();
            $table->string('h2_hostella')->nullable();
            $table->text('p_hostella')->nullable();
            $table->text('p_lugar_favorito')->nullable();
            $table->string('h2_confiar')->nullable();
            $table->text('p_confiar')->nullable();
        
            // Tarjetas sección 1
            $table->string('card1_title_1')->nullable();
            $table->text('card1_content_1')->nullable();
            $table->string('card1_title_2')->nullable();
            $table->text('card1_content_2')->nullable();
            $table->string('card1_title_3')->nullable();
            $table->text('card1_content_3')->nullable();
        
            // Tarjetas sección 2
            $table->string('card2_title_1')->nullable();
            $table->text('card2_content_1')->nullable();
            $table->string('card2_title_2')->nullable();
            $table->text('card2_content_2')->nullable();
            $table->string('card2_title_3')->nullable();
            $table->text('card2_content_3')->nullable();
        
            // Información general
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('logo')->nullable();
        
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
        Schema::dropIfExists('paginas');
    }
}
