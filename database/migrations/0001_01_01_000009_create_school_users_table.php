<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['teacher', 'supervisor', 'school_head', 'admin']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_users');
    }
};
