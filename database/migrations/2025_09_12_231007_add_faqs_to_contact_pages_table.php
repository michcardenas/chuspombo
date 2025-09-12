<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->string('faq1_q')->nullable()->after('linkedin_url');
            $table->text('faq1_a')->nullable()->after('faq1_q');
            $table->string('faq2_q')->nullable()->after('faq1_a');
            $table->text('faq2_a')->nullable()->after('faq2_q');
            $table->string('faq3_q')->nullable()->after('faq2_a');
            $table->text('faq3_a')->nullable()->after('faq3_q');
        });
    }

    public function down(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->dropColumn(['faq1_q','faq1_a','faq2_q','faq2_a','faq3_q','faq3_a']);
        });
    }
};
