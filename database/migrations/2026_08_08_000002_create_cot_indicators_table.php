<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cot_indicators', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('version_id')
                ->constrained('cot_indicator_versions')
                ->onDelete('cascade');
            $table->string('code');
            $table->text('description');
            $table->string('domain');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['version_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cot_indicators');
    }
};
