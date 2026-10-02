<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI observation-assistant approval gate: an offline observation may only
 * proceed once the observer has reviewed and approved the AI-suggested
 * result (pre-observation prompts) and downloaded the bundle containing it.
 * Works on MySQL and SQLite (tests) alike.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->timestamp('ai_suggestions_approved_at')->nullable()->after('lesson_plan_reviewed_by');
            $table->foreignId('ai_suggestions_approved_by')->nullable()->after('ai_suggestions_approved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_suggestions_approved_by');
            $table->dropColumn('ai_suggestions_approved_at');
        });
    }
};
