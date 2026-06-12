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
            $table->string('domain')->nullable();
            $table->text('domain_description')->nullable();
            $table->string('color')->default('#6C3CE1');
            $table->string('emoji')->default('✍️');
            $table->longText('raw_content')->nullable();
            $table->longText('extracted_style')->nullable();
            $table->integer('word_count')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_profiles');
    }
};
