<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('pipeline_runs')->cascadeOnDelete();
            $table->string('stage_number'); // 1, 2, 3a, 3b, 4, 5
            $table->string('stage_name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->string('output_filename')->nullable();
            $table->text('error_message')->nullable();
            $table->string('progress_note')->nullable(); // e.g. "Writing chapter 4 of 8"
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
