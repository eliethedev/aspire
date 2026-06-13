<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CoachingAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $agreements = CoachingAgreement::with(['observation', 'supervisor'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.coaching.index', compact('agreements'));
    }

    public function show(CoachingAgreement $agreement)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if ($agreement->teacher_id !== $teacher->id) {
            abort(403);
        }

        $agreement->load(['observation.observee.user', 'observation.observer', 'supervisor']);

        return view('teacher.coaching.show', compact('agreement'));
    }

    public function sign(CoachingAgreement $agreement, Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if ($agreement->teacher_id !== $teacher->id) {
            abort(403);
        }

        if ($agreement->isCompleted()) {
            return back()->with('error', 'This agreement is already completed.');
        }

        $data = $request->validate([
            'signature' => 'required|string|max:255',
        ]);

        $agreement->update([
            'teacher_signature' => $data['signature'],
            'teacher_signed_at' => now(),
            'teacher_notes' => $request->input('teacher_notes'),
        ]);

        if ($agreement->isFullySigned()) {
            $agreement->update(['status' => 'active']);
        }

        return redirect()->route('teacher.coaching.show', $agreement)
            ->with('success', 'Agreement signed successfully.');
    }
}
