<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\CoachingAgreement;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CoachingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $agreements = CoachingAgreement::with([
                'teacher.user',
                'supervisor',
                'observation',
            ])
            ->whereHas('teacher', function ($q) use ($user) {
                $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => CoachingAgreement::whereHas('teacher', function ($q) use ($user) {
                $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
            })->count(),
            'active' => CoachingAgreement::whereHas('teacher', function ($q) use ($user) {
                $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
            })->where('status', 'active')->count(),
            'completed' => CoachingAgreement::whereHas('teacher', function ($q) use ($user) {
                $q->whereHas('user', fn($q) => $q->where('school_id', $user->school_id));
            })->where('status', 'completed')->count(),
        ];

        return view('school-head.coaching.index', compact('agreements', 'stats'));
    }

    public function show(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->teacher->user->school_id !== $user->school_id) {
            abort(403, 'This coaching agreement does not belong to your school.');
        }

        $agreement->load([
            'teacher.user',
            'supervisor',
            'observation',
        ]);

        return view('school-head.coaching.show', compact('agreement'));
    }
}
