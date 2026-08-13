<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->string('career_stage', 40)->nullable()->after('ratee_role');
            $table->json('rating_scale')->nullable()->after('observer_roles');
            $table->json('rating_scale_css')->nullable()->after('rating_scale');
            $table->index('career_stage');
        });
    }

    public function down(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->dropIndex(['career_stage']);
            $table->dropColumn(['career_stage', 'rating_scale', 'rating_scale_css']);
        });
    }
};
