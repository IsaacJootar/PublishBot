<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->string('slug');
            $table->enum('status', ['pending', 'running', 'paused', 'completed', 'failed'])->default('pending');
            $table->integer('current_stage')->default(1);
            $table->string('validation_result')->nullable(); // go / no_go
            $table->longText('validation_report')->nullable();
            $table->boolean('user_confirmed_continue')->default(false);
            $table->string('output_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_runs');
    }
};
