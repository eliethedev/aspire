<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix linked PPSSH observations created before the co-observe confirmation
 * carry-over.
 *
 * 1. Children whose parent co-observation was already confirmed by the school
 *    head inherit that confirmation (same schedule, already agreed).
 * 2. Linked children never have a co-observer of their own: clear the
 *    duplicated school-head assignment (which rendered the same person twice
 *    in Schedule Confirmations) and reset its unused co-observe columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        $confirmedParentIds = DB::table('observations')
            ->where('school_head_confirmation_status', 'confirmed')
            ->pluck('id');

        if ($confirmedParentIds->isNotEmpty()) {
            DB::table('observations')
                ->whereIn('related_observation_id', $confirmedParentIds)
                ->where('confirmation_status', 'pending')
                ->update([
                    'confirmation_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);
        }

        DB::table('observations')
            ->whereNotNull('related_observation_id')
            ->update([
                'school_head_id' => null,
                'school_head_confirmation_status' => 'pending',
                'school_head_rejection_reason' => null,
                'school_head_rejection_notes' => null,
                'school_head_confirmed_at' => null,
                'school_head_rejected_at' => null,
            ]);
    }

    public function down(): void
    {
        // Intentionally a no-op: the cleared duplicate assignment cannot be
        // faithfully reconstructed.
    }
};
