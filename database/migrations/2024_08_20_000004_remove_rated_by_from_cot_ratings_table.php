<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->dropForeign(['rated_by']);
            $table->dropColumn('rated_by');
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            if (! Schema::hasColumn('cot_ratings', 'rated_by')) {
                $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }
};
