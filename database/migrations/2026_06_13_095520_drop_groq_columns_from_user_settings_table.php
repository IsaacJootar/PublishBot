<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings', 'groq_api_key')) {
                $table->dropColumn('groq_api_key');
            }
            if (Schema::hasColumn('user_settings', 'groq_model')) {
                $table->dropColumn('groq_model');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->text('groq_api_key')->nullable();
            $table->string('groq_model')->nullable();
        });
    }
};
