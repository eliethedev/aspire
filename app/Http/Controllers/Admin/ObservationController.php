<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use Illuminate\Http\Request;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Observation::with(['observer', 'observee.user']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->observation_type) {
            $query->where('observation_type', $request->observation_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->stage) {
            $query->where('stage', $request->stage);
        }

        $observations = $query->latest('observation_date')->paginate(15)->withQueryString();

        $stats = [
            'total' => Observation::count(),
            'in_progress' => Observation::whereIn('status', ['scheduled', 'in_progress'])->count(),
            'completed' => Observation::where('status', 'completed')->count(),
            'cancelled' => Observation::where('status', 'cancelled')->count(),
        ];

        return view('admin.observations.index', compact('observations', 'stats'));
    }

    public function show(Observation $observation)
    {
        $observation->load([
            'observer',
            'observee.user',
            'cancelledBy',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
        ]);

        return view('admin.observations.show', compact('observation'));
    }
}
