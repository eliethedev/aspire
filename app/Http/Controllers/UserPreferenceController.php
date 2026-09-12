<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    /**
     * Persist the "don't show again" preference for the observation
     * rating sheet tip. Can also be used to re-enable the tip.
     */
    public function dismissObservationTip(Request $request)
    {
        $request->validate(['dismissed' => ['required', 'boolean']]);

        auth()->user()->dismissObservationTip($request->boolean('dismissed'));

        return response()->json(['ok' => true]);
    }
}