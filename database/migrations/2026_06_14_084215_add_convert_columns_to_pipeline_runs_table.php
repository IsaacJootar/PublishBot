<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->string('creation_mode')->default('generated')->after('series_id'); // generated | converted
            $table->string('original_filename')->nullable()->after('creation_mode');
            $table->longText('raw_uploaded_content')->nullable()->after('original_filename');
            $table->longText('cleaned_content')->nullable()->after('raw_uploaded_content');
            $table->boolean('cleaning_approved')->default(false)->after('cleaned_content');
            $table->json('convert_listing')->nullable()->after('cleaning_approved');
            $table->json('convert_launch')->nullable()->after('convert_listing');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->dropColumn([
                'creation_mode', 'original_filename', 'raw_uploaded_content',
                'cleaned_content', 'cleaning_approved', 'convert_listing', 'convert_launch',
            ]);
        });
    }
};
