<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
            $table->string('token')->unique();
            $table->string('email');
            $table->enum('role', ['teacher', 'supervisor', 'school_head', 'admin']);
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->integer('resend_count')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('email');
            $table->index('expires_at');
            $table->index('is_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
