<?php

namespace App\Http\Controllers\Supervisor\Concerns;

use App\Models\Observation;
use Illuminate\Support\Facades\Auth;

trait AuthorizesObservations
{
    /**
     * Intentionally strict: supervisor routes are role:supervisor only and
     * each observation is owned by its creator (observer_id). Cross-supervisor
     * visibility is provided via read-only aggregates (teachers list,
     * termCheck, reports), not by opening individual observation workflows.
     */
    protected function authorizeObservation(Observation $observation): void
    {
        if ($observation->observer_id !== Auth::id()) {
            abort(403, 'You are not authorized to access this observation.');
        }
    }
}
