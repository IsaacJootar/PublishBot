<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voice_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_type'); // prompt_library | sop_pack | email_sequence_vault
            $table->string('niche');
            $table->string('product_title')->nullable();
            $table->text('buyer_description');
            $table->text('buyer_problem');
            $table->json('brief_options')->nullable();
            $table->integer('current_stage')->default(1);
            $table->string('status')->default('draft'); // draft|researching|structuring|writing|publishing|complete|failed
            $table->json('research_output')->nullable();
            $table->json('structure_output')->nullable();
            $table->longText('content_output')->nullable();
            $table->json('publish_pack')->nullable();
            $table->string('recommended_platform')->nullable();
            $table->decimal('recommended_price', 8, 2)->nullable();
            $table->string('error_message')->nullable();
            $table->string('progress_note')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
