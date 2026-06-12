<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->text('title_angle');
            $table->string('book_format')->nullable();
            $table->integer('buyer_intent_score')->default(0);
            $table->enum('competition_level', ['Low', 'Medium', 'High'])->default('Medium');
            $table->text('reason')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_results');
    }
};
