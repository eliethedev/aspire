<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Some school-head profiles were created before (or without) a school
     * assignment, leaving school_id NULL even though the linked user has a
     * school. Displays already fall back to the user's school, but backfilling
     * keeps the data itself consistent. Only NULLs are touched — existing
     * explicit assignments are never overwritten.
     *
     * Written as select-then-update (no joined UPDATE) so it runs on both
     * MySQL and SQLite.
     */
    public function up(): void
    {
        $rows = DB::table('school_head_profiles as shp')
            ->join('users as u', 'u.id', '=', 'shp.user_id')
            ->whereNull('shp.school_id')
            ->whereNotNull('u.school_id')
            ->select('shp.id', 'u.school_id')
            ->get();

        foreach ($rows->groupBy('school_id') as $schoolId => $group) {
            DB::table('school_head_profiles')
                ->whereIn('id', $group->pluck('id')->all())
                ->update(['school_id' => $schoolId]);
        }
    }

    public function down(): void
    {
        // Data backfill is intentionally irreversible.
    }
};
