<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('social_post_linkedin')->nullable();
            $table->text('social_post_twitter')->nullable();
            $table->text('social_post_instagram')->nullable();
            $table->text('social_post_pinterest')->nullable();
            $table->text('social_post_whatsapp')->nullable();
            $table->string('email_1_subject')->nullable();
            $table->longText('email_1_body')->nullable();
            $table->string('email_2_subject')->nullable();
            $table->longText('email_2_body')->nullable();
            $table->string('email_3_subject')->nullable();
            $table->longText('email_3_body')->nullable();
            $table->text('review_request')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_packs');
    }
};
