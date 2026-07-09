<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('school_year');
            $table->boolean('is_active')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        Schema::create('form_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('template_id')->constrained('form_templates')->onDelete('cascade');
            $table->string('key');
            $table->string('label');
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'key']);
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('section_id')->constrained('form_sections')->onDelete('cascade');
            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('required')->default(false);
            $table->string('column_map')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_sections');
        Schema::dropIfExists('form_templates');
    }
};
