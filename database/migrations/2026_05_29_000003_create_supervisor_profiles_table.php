<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Supervisor-Specific Fields
            $table->string('division_district_assigned')->nullable();
            $table->string('area_of_specialization')->nullable(); // Math, English, Science, Curriculum
            $table->enum('supervisory_level', ['division', 'district', 'regional'])->nullable();
            $table->integer('previous_teaching_experience_years')->default(0);
            $table->integer('administrative_experience_years')->default(0);
            $table->text('key_responsibilities')->nullable(); // Optional for admin notes
            $table->string('position')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('supervisory_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_profiles');
    }
};
