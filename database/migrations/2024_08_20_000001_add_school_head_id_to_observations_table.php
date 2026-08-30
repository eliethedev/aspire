<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            if (! Schema::hasColumn('observations', 'school_head_id')) {
                // Use no explicit `after()` to avoid missing-column failures across
                // fresh/test databases where polymorphic columns are added later.
                $table->foreignId('school_head_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['school_head_id']);
            $table->dropColumn('school_head_id');
        });
    }
};
