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
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('product_type', ['prompt_pack', 'notion_template', 'pdf_guide', 'swipe_file', 'toolkit'])->nullable();
            $table->enum('platform', ['gumroad', 'selar', 'payhip'])->nullable();
            $table->decimal('price_usd', 8, 2)->nullable();
            $table->json('sections')->nullable();
            $table->string('sales_page_title')->nullable();
            $table->longText('sales_page_body')->nullable();
            $table->string('tagline')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
