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
        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->text('teacher_reflection')->nullable()->after('discussion_notes');
            $table->text('lesson_plan_review')->nullable()->after('teacher_reflection');
            $table->text('instructional_materials')->nullable()->after('lesson_plan_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->dropColumn(['teacher_reflection', 'lesson_plan_review', 'instructional_materials']);
        });
    }
};
