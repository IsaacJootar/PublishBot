<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voice_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('product_type', ['book', 'digital_product']);
            $table->string('topic')->nullable();
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->integer('current_step')->default(1);
            $table->integer('total_steps')->default(6);
            $table->enum('book_format', [
                'childrens_educational',
                'childrens_story',
                'parenting_guide',
                'educational_nonfiction',
            ])->nullable();
            $table->string('target_age')->nullable();
            $table->string('target_reader')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
