<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen school_type from an enum of education levels (elementary /
     * secondary / integrated / senior_high) to a plain string so it can hold
     * the ownership values (public / private) submitted by the profile form.
     * Education level is captured in the separate grade_level column.
     */
    public function up(): void
    {
        Schema::table('school_head_profiles', function (Blueprint $table) {
            $table->string('school_type', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_head_profiles', function (Blueprint $table) {
            $table->enum('school_type', [
                'elementary',
                'secondary',
                'integrated',
                'senior_high',
            ])->nullable()->change();
        });
    }
};
