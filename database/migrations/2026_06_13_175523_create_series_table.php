<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('emoji', 8)->default('📚');
            $table->string('color', 16)->default('#6C3CE1');
            $table->string('format')->nullable(); // non_fiction | children | guide etc
            $table->text('description')->nullable();
            $table->json('characters')->nullable(); // array of {name, age_appearance, personality, key_detail}
            $table->text('art_style')->nullable();
            $table->text('writing_tone')->nullable();
            $table->text('recurring_themes')->nullable();
            $table->text('never_do')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
