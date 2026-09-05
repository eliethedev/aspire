<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->boolean('not_applicable')->default(false)->after('not_observed');
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->dropColumn('not_applicable');
        });
    }
};