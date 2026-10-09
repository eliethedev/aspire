<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Co-observe schedule confirmation by the assigned school head.
 *
 * Mirrors the teacher confirmation fields (confirmation_status, etc.): when a
 * supervisor assigns a school head to be present during a teacher observation
 * (observations.school_head_id), the school head confirms or rejects the
 * schedule from their own co-observation view. The supervisor's
 * pre-observation page highlights both confirmations side by side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('school_head_confirmation_status', ['pending', 'confirmed', 'rejected'])
                ->default('pending')
                ->after('teacher_confirmed_at');
            $table->string('school_head_rejection_reason')->nullable()->after('school_head_confirmation_status');
            $table->text('school_head_rejection_notes')->nullable()->after('school_head_rejection_reason');
            $table->timestamp('school_head_confirmed_at')->nullable()->after('school_head_rejection_notes');
            $table->timestamp('school_head_rejected_at')->nullable()->after('school_head_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn([
                'school_head_confirmation_status',
                'school_head_rejection_reason',
                'school_head_rejection_notes',
                'school_head_confirmed_at',
                'school_head_rejected_at',
            ]);
        });
    }
};
