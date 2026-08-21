<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epoc_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained()->cascadeOnDelete();
            $table->string('school_head_name')->nullable();
            $table->date('observation_date')->nullable();
            $table->text('narrative_observation')->nullable();
            $table->text('agreement')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->timestamps();

            $table->index('observation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epoc_evaluations');
    }
};
