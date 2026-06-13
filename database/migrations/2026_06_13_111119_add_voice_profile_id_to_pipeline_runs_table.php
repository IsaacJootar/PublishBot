<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->foreignId('voice_profile_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voice_profile_id');
        });
    }
};
