<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key constraint for users table only (others already have constraints)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Drop foreign key from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });
    }
};
