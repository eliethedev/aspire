<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_progression_assessments', function (Blueprint $table) {
            $table->id();
            $table->morphs('ratee');
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->date('assessed_at')->nullable();
            $table->string('position')->nullable();
            $table->string('career_stage')->nullable();
            $table->string('framework')->nullable();
            $table->timestamps();

            $table->index(['ratee_type', 'ratee_id', 'assessed_at'], 'cpa_ratee_assessed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_progression_assessments');
    }
};
