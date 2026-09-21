<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Offline-first sync support (Architecture B).
     *
     * - client_id: UUID generated on the tablet for idempotent POST /api/sync/push.
     * - sync_source: online | offline (how the row first reached the server).
     * - sync_status: synced | conflict (server-side resolution flag).
     * - device_updated_at: last-write-wins clock from the client.
     * - ai_status: pending | processing | done | failed (deferred cloud AI).
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            if (! Schema::hasColumn('observations', 'client_id')) {
                $table->uuid('client_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('observations', 'sync_source')) {
                $table->string('sync_source', 20)->default('online')->after('client_id');
            }
            if (! Schema::hasColumn('observations', 'sync_status')) {
                $table->string('sync_status', 20)->default('synced')->after('sync_source');
            }
            if (! Schema::hasColumn('observations', 'device_updated_at')) {
                $table->timestamp('device_updated_at')->nullable()->after('sync_status');
            }
            if (! Schema::hasColumn('observations', 'ai_status')) {
                $table->string('ai_status', 20)->default('none')->after('device_updated_at');
            }
            if (! Schema::hasColumn('observations', 'server_version')) {
                $table->unsignedInteger('server_version')->default(1)->after('ai_status');
            }
        });

        Schema::table('cot_ratings', function (Blueprint $table) {
            if (! Schema::hasColumn('cot_ratings', 'client_id')) {
                $table->uuid('client_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('cot_ratings', 'device_updated_at')) {
                $table->timestamp('device_updated_at')->nullable()->after('client_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->dropUnique(['client_id']);
            $table->dropColumn(['client_id', 'device_updated_at']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->dropUnique(['client_id']);
            $table->dropColumn([
                'client_id',
                'sync_source',
                'sync_status',
                'device_updated_at',
                'ai_status',
                'server_version',
            ]);
        });
    }
};
