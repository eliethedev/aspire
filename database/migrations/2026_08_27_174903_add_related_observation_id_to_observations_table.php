<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->unsignedBigInteger('related_observation_id')->nullable()->after('school_head_id');
            $table->index(['related_observation_id']);
            $table->foreign('related_observation_id')->references('id')->on('observations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['related_observation_id']);
            $table->dropIndex(['related_observation_id']);
            $table->dropColumn('related_observation_id');
        });
    }
};
