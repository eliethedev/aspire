<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->foreignId('rated_by')->nullable()->after('comments')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->dropForeign(['rated_by']);
            $table->dropColumn('rated_by');
        });
    }
};
