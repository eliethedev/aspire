<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify status ENUM to include 'cancelled'
        DB::statement("ALTER TABLE observations MODIFY COLUMN status ENUM('pending', 'scheduled', 'in_progress', 'cot_completed', 'completed', 'cancelled') DEFAULT 'pending'");

        Schema::table('observations', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE observations MODIFY COLUMN status ENUM('pending', 'scheduled', 'in_progress', 'cot_completed', 'completed') DEFAULT 'pending'");

        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancellation_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
