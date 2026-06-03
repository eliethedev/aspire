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
            $table->string('subject')->nullable()->after('additional_roles');
            $table->string('grade_level')->nullable()->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_head_profiles', function (Blueprint $table) {
            $table->dropColumn(['subject', 'grade_level']);
        });
    }
};
