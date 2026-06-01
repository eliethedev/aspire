<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Core Fields (Common to All Roles)
            $table->string('mobile_number', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address_barangay')->nullable();
            $table->string('address_municipality')->nullable();
            $table->string('address_province')->nullable();
            $table->string('employee_id', 50)->nullable()->unique();
            $table->string('prc_license_number', 50)->nullable()->unique();
            $table->string('highest_educational_attainment')->nullable();
            $table->string('major_specialization')->nullable();
            $table->integer('years_of_teaching_experience')->default(0);
            $table->date('date_of_entry_to_deped')->nullable();
            $table->enum('employment_status', ['permanent', 'provisional', 'contractual', 'substitute'])->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('employee_id');
            $table->index('prc_license_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
