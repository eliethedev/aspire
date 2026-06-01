<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\CotRating;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        // Get teacher-specific statistics
        $stats = [
            'total_observations' => $teacher ? Observation::where('teacher_id', $teacher->id)->count() : 0,
            'average_cot_score' => $teacher ? CotRating::whereHas('observation', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })->avg('rating') ?? 0 : 0,
        ];

        return view('teacher.dashboard', compact('stats'));
    }
}
