<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_head_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            
            // School Head-Specific Fields
            $table->enum('position_level', [
                'principal_i',
                'principal_ii',
                'principal_iii',
                'principal_iv',
                'head_teacher',
                'assistant_principal'
            ])->nullable();
            $table->integer('administrative_experience_years')->default(0);
            $table->string('leadership_training')->nullable(); // NQESH result, etc.
            $table->enum('current_designation', [
                'principal',
                'officer_in_charge',
                'head_teacher',
                'assistant_principal'
            ])->nullable();
            $table->integer('number_of_teachers_supervised')->default(0);
            $table->enum('school_type', ['elementary', 'secondary', 'integrated', 'senior_high'])->nullable();
            $table->text('additional_roles')->nullable(); // District Supervisor concurrent, ALS Coordinator
            $table->string('position')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('school_id');
            $table->index('position_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_head_profiles');
    }
};
