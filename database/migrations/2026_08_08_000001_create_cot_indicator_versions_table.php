<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cot_indicator_versions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('school_year', 20)->unique();
            $table->string('label');
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cot_indicator_versions');
    }
};
