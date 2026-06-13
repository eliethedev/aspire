<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $feedbacks = AiFeedback::with(['observation' => function ($q) {
            $q->with('observer');
        }])
        ->whereHas('observation', function ($q) use ($teacher) {
            $q->where('observee_id', $teacher->id)
              ->where('observee_type', get_class($teacher));
        })
        ->where('status', 'published')
        ->latest()
        ->paginate(20);

        return view('teacher.feedback.index', compact('feedbacks'));
    }

    public function show(AiFeedback $feedback)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if ($feedback->observation->observee_id !== $teacher->id ||
            $feedback->observation->observee_type !== get_class($teacher)) {
            abort(403);
        }

        if ($feedback->status !== 'published') {
            abort(404);
        }

        $feedback->load(['observation.observee.user', 'observation.observer', 'observation.cotRatings']);

        return view('teacher.feedback.show', compact('feedback'));
    }
}
