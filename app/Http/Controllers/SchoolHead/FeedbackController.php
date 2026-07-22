<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use App\Models\Observation;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $aiFeedbacks = AiFeedback::with([
                'observation.observee.user',
                'observation.observer',
            ])
            ->whereHas('observation', function ($q) use ($user) {
                $q->whereHasMorph('observee', [Teacher::class], function ($q) use ($user) {
                    $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('observation', function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_feedback' => AiFeedback::whereHas('observation', function ($q) use ($user) {
                $q->whereHasMorph('observee', [Teacher::class], function ($q) use ($user) {
                    $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
                });
            })->count(),
            'recent_feedback' => AiFeedback::whereHas('observation', function ($q) use ($user) {
                $q->whereHasMorph('observee', [Teacher::class], function ($q) use ($user) {
                    $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
                });
            })->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('school-head.feedback.index', compact('aiFeedbacks', 'stats'));
    }
}
