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
        Schema::table('post_conferences', function (Blueprint $table) {
            $table->text('star_notes')->nullable()->after('conference_date');
            $table->text('areas_for_improvement')->nullable()->after('star_notes');
            $table->text('challenges_facing_teacher')->nullable()->after('areas_for_improvement');
            $table->text('ideas_for_addressing_challenges')->nullable()->after('challenges_facing_teacher');
            $table->text('prioritized_next_steps')->nullable()->after('ideas_for_addressing_challenges');
            $table->text('teacher_reflection')->nullable()->after('prioritized_next_steps');
            $table->text('supervisor_notes')->nullable()->after('teacher_reflection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_conferences', function (Blueprint $table) {
            $table->dropColumn([
                'star_notes',
                'areas_for_improvement',
                'challenges_facing_teacher',
                'ideas_for_addressing_challenges',
                'prioritized_next_steps',
                'teacher_reflection',
                'supervisor_notes',
            ]);
        });
    }
};
