<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->integer('rating')->nullable()->change();
            $table->boolean('not_observed')->default(false)->after('rating');
            $table->string('indicator_code')->nullable()->after('indicator');
        });
    }

    public function down(): void
    {
        Schema::table('cot_ratings', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->change();
            $table->dropColumn('not_observed');
            $table->dropColumn('indicator_code');
        });
    }
};
