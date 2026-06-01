<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Teacher-Specific Fields
            $table->enum('grade_level', ['elementary', 'junior_high', 'senior_high'])->nullable();
            $table->string('subject_area_taught')->nullable();
            $table->enum('teaching_position', [
                'teacher_i',
                'teacher_ii',
                'teacher_iii',
                'master_teacher_i',
                'master_teacher_ii',
                'master_teacher_iii',
                'master_teacher_iv',
                'master_teacher_v'
            ])->nullable();
            $table->string('strand_specialization')->nullable(); // For SHS: STEM, ABM, HUMSS, etc.
            $table->boolean('has_advisory_class')->default(false);
            $table->string('advisory_section')->nullable();
            $table->integer('teacher_load')->default(0); // Number of classes
            $table->string('certification_training')->nullable(); // LET/PBET passer, seminars
            $table->string('department')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('grade_level');
            $table->index('teaching_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
