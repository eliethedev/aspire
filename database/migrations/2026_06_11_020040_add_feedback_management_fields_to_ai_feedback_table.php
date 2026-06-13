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
        Schema::table('ai_feedback', function (Blueprint $table) {
            $table->foreignId('observation_id')->nullable()->after('id')->constrained('observations')->onDelete('cascade');
            $table->string('feedback_type')->nullable()->after('cot_rating_id')->comment('pre_observation, post_observation, post_conference, final_summary');
            $table->string('generated_by')->default('ai')->after('model_version')->comment('supervisor, ai, system');
            $table->string('status')->default('draft')->after('generated_by')->comment('draft, published, archived');
            $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->index(['observation_id', 'feedback_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_feedback', function (Blueprint $table) {
            $table->dropForeign(['observation_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['observation_id', 'feedback_type']);
            $table->dropIndex(['status']);
            $table->dropColumn(['observation_id', 'feedback_type', 'generated_by', 'status', 'reviewed_by', 'reviewed_at']);
        });
    }
};
