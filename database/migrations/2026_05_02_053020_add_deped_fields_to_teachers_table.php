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
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->unique()->after('years_of_service');
            $table->string('mobile_number')->nullable()->after('employee_number');
            $table->string('prc_license_number')->nullable()->unique()->after('mobile_number');
            $table->string('position')->nullable()->after('prc_license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['employee_number', 'mobile_number', 'prc_license_number', 'position']);
        });
    }
};
