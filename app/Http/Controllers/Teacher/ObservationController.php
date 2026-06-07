<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\PreObservationPlanning;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')->with('error', 'Teacher profile not found.');
        }

        $query = Observation::with(['observer'])
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
                  ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $observations = $query
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->stage, fn($q, $s) => $q->where('stage', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)->count(),
            'upcoming' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereIn('status', ['scheduled'])->count(),
            'completed' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->where('status', 'completed')->count(),
        ];

        return view('teacher.observations.index', compact('observations', 'stats'));
    }

    public function show(Observation $observation)
    {
        $teacher = Auth::user()->teacher;

        if ($observation->observee_id !== $teacher?->id || $observation->observee_type !== Teacher::class) {
            abort(403, 'You are not authorized to view this observation.');
        }

        $observation->load([
            'observer',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
        ]);

        return view('teacher.observations.show', compact('observation'));
    }

    public function uploadLessonPlan(Request $request, Observation $observation)
    {
        $teacher = Auth::user()->teacher;

        if ($observation->observee_id !== $teacher?->id || $observation->observee_type !== Teacher::class) {
            abort(403);
        }

        if ($observation->stage !== 'pre_observation_planning') {
            return back()->with('error', 'Lesson plan can only be uploaded during the Pre-Observation Planning stage.');
        }

        $validated = $request->validate([
            'lesson_plan_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $filePath = $request->file('lesson_plan_file')->store('lesson_plans', 'public');

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['lesson_plan_file' => $filePath]
        );

        return back()->with('success', 'Lesson plan uploaded successfully.');
    }
}
