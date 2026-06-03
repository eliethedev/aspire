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
            // Add polymorphic fields for observer
            $table->unsignedBigInteger('observer_id')->nullable()->after('id');
            $table->string('observer_type')->nullable()->after('observer_id');
            
            // Add polymorphic fields for observee
            $table->unsignedBigInteger('observee_id')->nullable()->after('observer_type');
            $table->string('observee_type')->nullable()->after('observee_id');
            
            // Add observation type field
            $table->enum('observation_type', ['teacher_observation', 'school_head_observation'])->default('teacher_observation')->after('observee_type');
            
            // Add indexes for polymorphic relationships
            $table->index(['observer_id', 'observer_type']);
            $table->index(['observee_id', 'observee_type']);
            
            // Migrate existing data
            // Set observer to supervisor and observee to teacher for existing records
            $table->foreign('observer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('observee_id')->references('id')->on('teachers')->onDelete('cascade');
        });
        
        // Migrate existing data
        \Illuminate\Support\Facades\DB::statement('UPDATE observations SET observer_id = supervisor_id, observer_type = "App\\\Models\\\User", observee_id = teacher_id, observee_type = "App\\\Models\\\Teacher"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropIndex(['observer_id', 'observer_type']);
            $table->dropIndex(['observee_id', 'observee_type']);
            $table->dropForeign(['observer_id']);
            $table->dropForeign(['observee_id']);
            $table->dropColumn(['observer_id', 'observer_type', 'observee_id', 'observee_type', 'observation_type']);
        });
    }
};
