<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->json('focus_areas')->nullable();
            $table->json('action_steps')->nullable();
            $table->text('resources_needed')->nullable();
            $table->text('success_indicators')->nullable();
            $table->string('timeline')->nullable();
            $table->text('supervisor_notes')->nullable();
            $table->text('teacher_notes')->nullable();
            $table->timestamp('teacher_signed_at')->nullable();
            $table->timestamp('supervisor_signed_at')->nullable();
            $table->string('teacher_signature')->nullable();
            $table->string('supervisor_signature')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_agreements');
    }
};
