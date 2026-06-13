<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('emoji', 8)->default('✍️');
            $table->string('color', 16)->default('#6C3CE1');
            $table->string('description')->nullable();
            $table->longText('raw_content')->nullable();
            $table->longText('extracted_style')->nullable();
            $table->integer('word_count')->default(0);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['draft', 'training', 'ready', 'failed'])->default('draft');
            $table->string('error_message')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_profiles');
    }
};
