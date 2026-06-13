<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_feedback', function (Blueprint $table) {
            $table->dropForeign(['cot_rating_id']);
            $table->foreignId('cot_rating_id')->nullable()->change();
            $table->foreign('cot_rating_id')->references('id')->on('cot_ratings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ai_feedback', function (Blueprint $table) {
            $table->dropForeign(['cot_rating_id']);
            $table->foreignId('cot_rating_id')->nullable(false)->change();
            $table->foreign('cot_rating_id')->references('id')->on('cot_ratings')->onDelete('cascade');
        });
    }
};
