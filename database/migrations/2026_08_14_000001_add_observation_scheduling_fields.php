<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('observation_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('location')->nullable()->after('observation_mode');
        });

        Schema::table('post_conferences', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('conference_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('location')->nullable()->after('end_time');
            $table->string('mode')->default('in_person')->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'location']);
        });

        Schema::table('post_conferences', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'location', 'mode']);
        });
    }
};
