<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cot_ratings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('observation_id')->constrained('observations')->onDelete('cascade');
            $table->string('domain');
            $table->string('indicator');
            $table->decimal('rating', 3, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cot_ratings');
    }
};