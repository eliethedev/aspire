<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            // Guest sender (prospective teacher / school head / supervisor).
            $table->string('name', 120);
            $table->string('email', 190);
            $table->enum('role', ['teacher', 'school_head', 'supervisor', 'other'])->default('other');
            $table->string('school_name', 190)->nullable();
            $table->enum('topic', ['account_access', 'demo', 'partnership', 'feedback', 'other'])->default('other');
            $table->string('subject', 255);
            $table->text('message');
            $table->enum('status', ['new', 'in_progress', 'resolved'])->default('new');
            $table->text('admin_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('role');
            $table->index('topic');
            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
