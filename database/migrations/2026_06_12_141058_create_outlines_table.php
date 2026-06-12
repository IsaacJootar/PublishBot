<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->integer('chapter_number');
            $table->string('chapter_title');
            $table->text('chapter_summary')->nullable();
            $table->integer('page_count_est')->nullable();
            $table->text('learning_obj')->nullable();
            $table->text('illustration_note')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlines');
    }
};
