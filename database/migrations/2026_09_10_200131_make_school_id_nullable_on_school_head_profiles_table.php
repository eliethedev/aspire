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
        Schema::table('school_head_profiles', function (Blueprint $table) {
            // A school head may be invited before a school is assigned,
            // matching the nullable school_id already used on users,
            // invitations, teachers, and supervisors tables.
            $table->foreignId('school_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_head_profiles', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable(false)->change();
        });
    }
};
