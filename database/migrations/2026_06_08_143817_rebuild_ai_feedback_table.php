<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing ai_feedback table — it has observation_id/generated_text/edited_text/approved_by/status
        // but the model+service expect cot_rating_id + structured analysis fields.
        // Table is empty, so safe to drop.
        Schema::dropIfExists('ai_feedback');

        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('cot_rating_id')->constrained('cot_ratings')->onDelete('cascade');
            $table->text('analysis');
            $table->json('recommendations')->nullable();
            $table->json('strengths')->nullable();
            $table->json('areas_for_improvement')->nullable();
            $table->decimal('confidence_score', 4, 2)->default(0.00);
            $table->string('model_version')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_feedback');

        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('observation_id')->constrained('observations')->onDelete('cascade');
            $table->text('generated_text');
            $table->text('edited_text')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'approved', 'finalized']);
            $table->timestamps();
        });
    }
};
