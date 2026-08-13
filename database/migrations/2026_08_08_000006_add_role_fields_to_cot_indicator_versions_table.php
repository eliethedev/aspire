<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->string('ratee_role')->default('teacher')->after('is_default');
            $table->json('observer_roles')->nullable()->after('ratee_role');
        });
    }

    public function down(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->dropColumn(['ratee_role', 'observer_roles']);
        });
    }
};
