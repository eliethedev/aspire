<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('stage', ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'])
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('stage', ['pre_conference', 'observation', 'post_conference'])
                ->change();
        });
    }
};
