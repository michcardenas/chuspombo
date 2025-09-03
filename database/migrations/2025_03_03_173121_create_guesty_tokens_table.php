<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestyTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('guesty_tokens', function (Blueprint $table) {
        $table->id();
        $table->text('access_token');
        $table->string('token_type')->default('Bearer');
        $table->integer('expires_at')->nullable(); // Timestamp Unix
        $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('guesty_tokens');
    }
}
