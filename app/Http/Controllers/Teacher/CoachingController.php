<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Enums\NotificationType;
use App\Models\CoachingAgreement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachingController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
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

        $supervisor = $agreement->supervisor;
        if ($supervisor) {
            $showRoute = $supervisor->role === 'school_head'
                ? route('school-head.coaching.show', $agreement)
                : route('supervisor.coaching.show', $agreement);
            $this->notificationService->notify(
                $supervisor,
                NotificationType::PROFESSIONAL_DEVELOPMENT,
                'Teacher signed the improvement plan',
                ($user->name ?? 'The teacher') . ' signed the improvement plan for the ' . ($agreement->observation->subject ?? 'recent') . ' observation.' . ($agreement->isFullySigned() ? ' The plan is now active.' : ''),
                null,
                $showRoute,
            );
        }

        return redirect()->route('teacher.coaching.show', $agreement)
            ->with('success', 'Agreement signed successfully.');
    }
}
