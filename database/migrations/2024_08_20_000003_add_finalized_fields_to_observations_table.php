<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            if (! Schema::hasColumn('observations', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable();
            }
            if (! Schema::hasColumn('observations', 'finalized_by')) {
                $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            $table->dropColumn(['finalized_at', 'finalized_by']);
        });
    }
};
