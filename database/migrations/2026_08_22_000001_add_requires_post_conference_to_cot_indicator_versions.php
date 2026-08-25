<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->boolean('requires_post_conference')->default(true)->after('career_stage');
        });
    }

    public function down(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->dropColumn('requires_post_conference');
        });
    }
};
