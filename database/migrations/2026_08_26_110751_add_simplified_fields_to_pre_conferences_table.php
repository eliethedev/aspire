<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('conference_date');
            $table->text('learning_objectives')->nullable()->after('topic');
            $table->text('teaching_strategies')->nullable()->after('learning_objectives');
            $table->text('assessment_activity')->nullable()->after('teaching_strategies');
            $table->text('expected_challenges')->nullable()->after('assessment_activity');
            $table->text('feedback_areas')->nullable()->after('expected_challenges');
        });
    }

    public function down(): void
    {
        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->dropColumn([
                'topic',
                'learning_objectives',
                'teaching_strategies',
                'assessment_activity',
                'expected_challenges',
                'feedback_areas',
            ]);
        });
    }
};
