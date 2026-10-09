<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase out the pre-conference step of the observation cycle.
 *
 * The workflow is now strictly three stages:
 *   pre_observation_planning -> observation -> post_conference
 *
 * Any observations still sitting in the retired `pre_conference` stage are
 * moved forward to `observation` so they stay actionable (their saved
 * pre-conference records are left untouched for history).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('observations')
            ->where('stage', 'pre_conference')
            ->update(['stage' => 'observation']);
    }

    public function down(): void
    {
        // Intentionally a no-op: the pre-conference step no longer exists,
        // so there is nothing faithful to roll back to.
    }
};
