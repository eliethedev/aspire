<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('role')->nullable()->after('user_id');
            $table->string('module')->nullable()->after('action');
            $table->string('record_id')->nullable()->after('module');
            $table->string('status')->nullable()->after('record_id');
            $table->json('old_values')->nullable()->after('metadata');
            $table->json('new_values')->nullable()->after('old_values');

            $table->index('module');
            $table->index('status');
            $table->index('role');
            $table->index(['module', 'action']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropIndex(['status']);
            $table->dropIndex(['role']);
            $table->dropIndex(['module', 'action']);
            $table->dropIndex(['user_id', 'created_at']);

            $table->dropColumn(['role', 'module', 'record_id', 'status', 'old_values', 'new_values']);
        });
    }
};
