<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('name');
            $table->string('pen_name')->nullable()->after('author_name');
            $table->string('anthropic_api_key')->nullable()->after('pen_name');
            $table->string('openai_api_key')->nullable()->after('anthropic_api_key');
            $table->boolean('onboarding_complete')->default(false)->after('openai_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'pen_name', 'anthropic_api_key', 'openai_api_key', 'onboarding_complete']);
        });
    }
};
