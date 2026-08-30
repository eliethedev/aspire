<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            if (! Schema::hasColumn('cot_ratings', 'rated_by')) {
                $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            if (Schema::hasColumn('cot_ratings', 'rated_by')) {
                try { $table->dropForeign(['rated_by']); } catch (\Throwable $e) {}
                $table->dropColumn('rated_by');
            }
        });
    }
};
