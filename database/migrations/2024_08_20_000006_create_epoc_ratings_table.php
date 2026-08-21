<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epoc_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epoc_evaluation_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->string('indicator');
            $table->integer('rating')->nullable();
            $table->timestamps();

            $table->index('epoc_evaluation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epoc_ratings');
    }
};
