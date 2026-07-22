<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\PreObservationPlanning;
use App\Models\Observation;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $lessonPlans = PreObservationPlanning::with([
                'observation.observee',
                'observation.observer',
            ])
            ->whereHas('observation', function ($q) use ($user) {
                $q->where('observee_type', Teacher::class)
                  ->whereHasMorph('observee', [Teacher::class], function ($q) use ($user) {
                      $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
                  });
            })
            ->whereNotNull('lesson_plan_file')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('observation', function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('school-head.lesson-plans.index', compact('lessonPlans'));
    }

    public function show($id)
    {
        $user = Auth::user();

        $plan = PreObservationPlanning::with([
                'observation.observee.user',
                'observation.observer',
            ])
            ->findOrFail($id);

        $observation = $plan->observation;

        if ($observation->observee_type !== Teacher::class) {
            abort(403, 'Invalid lesson plan.');
        }

        $teacher = $observation->observee;
        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This lesson plan does not belong to your school.');
        }

        return view('school-head.lesson-plans.show', compact('plan', 'observation'));
    }
}
