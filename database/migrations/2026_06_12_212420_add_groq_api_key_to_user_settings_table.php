<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('groq_api_key')->nullable()->after('api_key_encrypted');
            $table->string('groq_model')->default('llama-3.3-70b-versatile')->after('groq_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['groq_api_key', 'groq_model']);
        });
    }
};
