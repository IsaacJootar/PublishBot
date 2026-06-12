<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('api_key_encrypted')->nullable();
            $table->string('model')->default('claude-sonnet-4-6');
            $table->string('web_search_tool_version')->default('web_search_20250305');
            $table->integer('max_tokens')->default(2000);
            $table->integer('chapter_count')->default(8);
            $table->integer('words_per_chapter')->default(500);
            $table->integer('pin_count')->default(15);
            $table->string('default_tone')->nullable();
            $table->string('author_name')->nullable();
            $table->json('quick_topics')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
