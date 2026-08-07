<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            // Expand the status ENUM to include 'cancelled' using the schema
            // builder so it works on both MySQL and SQLite.
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'cot_completed', 'completed', 'cancelled'])->default('pending')->change();

            $table->text('cancellation_reason')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'scheduled', 'in_progress', 'cot_completed', 'completed'])->default('pending')->change();

            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
