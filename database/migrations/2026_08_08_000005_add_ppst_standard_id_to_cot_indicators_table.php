<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_indicators', function (Blueprint $table) {
            $table->foreignId('ppst_standard_id')
                ->nullable()
                ->after('version_id')
                ->constrained('ppst_standards')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cot_indicators', function (Blueprint $table) {
            $table->dropForeign(['ppst_standard_id']);
            $table->dropColumn('ppst_standard_id');
        });
    }
};
