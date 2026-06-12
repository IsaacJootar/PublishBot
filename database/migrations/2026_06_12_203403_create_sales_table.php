<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('run_id')->nullable()->constrained('pipeline_runs')->nullOnDelete();
            $table->string('topic');
            $table->enum('product_type', ['ebook', 'workbook', 'printable', 'bundle']);
            $table->enum('platform', ['amazon', 'etsy', 'gumroad']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('sale_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
