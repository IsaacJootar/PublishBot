<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            // Stores parsed content per file: [{filename, content, word_count}]
            $table->json('uploaded_files')->nullable()->after('raw_uploaded_content');
            // Claude's inferred order: [{filename, title, position, reason}]
            $table->json('inferred_order')->nullable()->after('uploaded_files');
            // User-confirmed final order: [filename, ...]
            $table->json('confirmed_order')->nullable()->after('inferred_order');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->dropColumn(['uploaded_files', 'inferred_order', 'confirmed_order']);
        });
    }
};
