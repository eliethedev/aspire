<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['supervisor_id']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->change();
            $table->unsignedBigInteger('supervisor_id')->nullable()->change();
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['supervisor_id']);
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable(false)->change();
            $table->unsignedBigInteger('supervisor_id')->nullable(false)->change();
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
