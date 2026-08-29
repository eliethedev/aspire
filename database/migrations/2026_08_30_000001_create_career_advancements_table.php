<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_advancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->string('from_career_stage', 40)->nullable();
            $table->string('to_career_stage', 40);
            $table->string('type', 20)->default('announce'); // allow | announce
            $table->string('status', 20)->default('recorded'); // recorded | acknowledged
            $table->text('remarks')->nullable();
            $table->date('acted_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_advancements');
    }
};
