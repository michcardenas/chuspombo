<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            for ($i = 4; $i <= 7; $i++) {
                $table->string("card2_title_$i")->nullable();
                $table->text("card2_content_$i")->nullable();
                $table->string("card2_image_$i")->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            for ($i = 4; $i <= 7; $i++) {
                $table->dropColumn("card2_title_$i");
                $table->dropColumn("card2_content_$i");
                $table->dropColumn("card2_image_$i");
            }
        });
    }
};
