<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->foreignId('cot_indicator_version_id')
                ->nullable()
                ->after('form_template_id')
                ->constrained('cot_indicator_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['cot_indicator_version_id']);
            $table->dropColumn('cot_indicator_version_id');
        });
    }
};
