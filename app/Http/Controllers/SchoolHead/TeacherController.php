<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Observation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $teachers = Teacher::query()
            ->with(['user', 'school'])
            ->withCount('observations')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('(SELECT name FROM users WHERE users.id = teachers.user_id)')
            ->paginate(15)
            ->withQueryString();

        return view('school-head.teachers.index', compact('teachers'));
    }

    public function show(Teacher $teacher)
    {
        $user = Auth::user();

        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This teacher does not belong to your school.');
        }

        $teacher->load(['user', 'school']);

        $observations = Observation::with(['observer'])
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)->count(),
            'completed' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->where('status', 'completed')->count(),
            'in_progress' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereIn('status', ['scheduled', 'in_progress'])->count(),
            'avg_score' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereNotNull('overall_score')
                ->avg('overall_score'),
        ];

        return view('school-head.teachers.show', compact('teacher', 'observations', 'stats'));
    }
}
