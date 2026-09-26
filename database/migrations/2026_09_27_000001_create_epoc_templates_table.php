<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epoc_templates', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('school_year');
            $table->boolean('is_active')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('epoc_indicators', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('template_id')->constrained('epoc_templates')->onDelete('cascade');
            $table->string('domain');
            $table->text('indicator');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['template_id', 'order']);
        });

        Schema::table('epoc_evaluations', function (Blueprint $table) {
            $table->foreignId('epoc_template_id')->nullable()->after('observation_id')->constrained('epoc_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('epoc_evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('epoc_template_id');
        });

        Schema::dropIfExists('epoc_indicators');
        Schema::dropIfExists('epoc_templates');
    }
};
