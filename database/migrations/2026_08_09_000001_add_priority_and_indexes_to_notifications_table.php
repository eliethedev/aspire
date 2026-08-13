<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add priority support and indexes to the notifications table.
     *
     * Soft deletion is intentionally NOT added: notification history is
     * small and short-lived, and users never need to restore a deleted
     * notification. Hard deletes keep the table lean.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('type');

            // Frequently queried filter/ordering columns
            $table->index('type');
            $table->index('read_at');
            $table->index('created_at');

            // Unread-count lookups per user
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
            $table->dropIndex(['type']);
            $table->dropIndex(['read_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('priority');
        });
    }
};
