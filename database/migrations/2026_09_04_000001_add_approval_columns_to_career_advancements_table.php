<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add school-head approval columns to the career_advancements table so a
     * supervisor's allow/announce decision is held pending until the school
     * head reviews and approves (or rejects) it.
     */
    public function up(): void
    {
        Schema::table('career_advancements', function (Blueprint $table) {
            $table->foreignId('school_head_id')->nullable()->after('supervisor_id')->constrained('users')->nullOnDelete();
            $table->timestamp('school_head_approved_at')->nullable()->after('acknowledged_at');
            $table->timestamp('school_head_rejected_at')->nullable()->after('school_head_approved_at');
            $table->text('school_head_remarks')->nullable()->after('school_head_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('career_advancements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_head_id');
            $table->dropColumn(['school_head_approved_at', 'school_head_rejected_at', 'school_head_remarks']);
        });
    }
};
