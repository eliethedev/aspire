<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('ai_feedback');
    }
};