<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\CareerAdvancement;
use App\Services\CareerStageResolver;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerAdvancementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * List career advancement recommendations pending the school head's
     * approval, along with previously approved / rejected records.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $schoolId = $user->school_id;

        $query = CareerAdvancement::query()
            ->with(['teacher.user', 'supervisor', 'schoolHead'])
            ->whereHas('teacher.user', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });

        $tab = $request->input('tab', 'pending');

        if ($tab === 'pending') {
            $advancements = $query
                ->where('status', CareerAdvancement::STATUS_PENDING_APPROVAL)
                ->latest('created_at')
                ->paginate(15)
                ->withQueryString();
        } else {
            $advancements = $query
                ->whereIn('status', [CareerAdvancement::STATUS_APPROVED, CareerAdvancement::STATUS_REJECTED])
                ->latest('created_at')
                ->paginate(15)
                ->withQueryString();
        }

        return view('school-head.career.advancements', compact('advancements', 'tab'));
    }

    /**
     * Approve a supervisor's career advancement recommendation. This is the
     * point at which the teacher's career_stage is actually advanced and the
     * teacher receives a congratulation notification.
     */
    public function approve(CareerAdvancement $advancement, Request $request)
    {
        $user = Auth::user();

        $this->authorizeSchool($advancement, $user);

        if (! $advancement->isPendingApproval()) {
            return back()->with('error', 'This career advancement has already been reviewed.');
        }

        $remarks = trim((string) $request->input('remarks'));

        $advancement->update([
            'school_head_id' => $user->id,
            'status' => CareerAdvancement::STATUS_APPROVED,
            'school_head_approved_at' => now(),
            'school_head_remarks' => $remarks ?: null,
        ]);

        // Advance the teacher's career stage only after approval.
        $teacher = $advancement->teacher;
        if ($teacher->career_stage !== $advancement->to_career_stage) {
            $teacher->career_stage = $advancement->to_career_stage;
            $teacher->save();
        }

        $stageLabel = $this->stageLabel($advancement->to_career_stage);

        // Congratulate the teacher.
        $this->notificationService->notifyCareerAdvancementApproved(
            $teacher->user,
            $stageLabel,
            route('school-head.teachers.show', $teacher),
        );

        // Notify the supervisor that the recommendation was approved.
        if ($advancement->supervisor) {
            $this->notificationService->notify(
                $advancement->supervisor,
                \App\Enums\NotificationType::ACHIEVEMENT,
                'Career advancement approved',
                "School head approved {$teacher->user->name}'s advancement to the {$stageLabel} career stage.",
                null,
                route('supervisor.teachers.show', $teacher).'#readiness',
            );
        }

        return back()->with('success', "Advancement approved. {$teacher->user->name} has been advanced to the {$stageLabel} career stage and notified.");
    }

    /**
     * Reject a supervisor's career advancement recommendation. The teacher
     * stays at their current career stage and is not congratulated.
     */
    public function reject(CareerAdvancement $advancement, Request $request)
    {
        $user = Auth::user();

        $this->authorizeSchool($advancement, $user);

        if (! $advancement->isPendingApproval()) {
            return back()->with('error', 'This career advancement has already been reviewed.');
        }

        $remarks = trim((string) $request->input('remarks'));

        $advancement->update([
            'school_head_id' => $user->id,
            'status' => CareerAdvancement::STATUS_REJECTED,
            'school_head_rejected_at' => now(),
            'school_head_remarks' => $remarks ?: null,
        ]);

        $teacher = $advancement->teacher;
        $stageLabel = $this->stageLabel($advancement->to_career_stage);

        // Notify the supervisor (and the teacher). The teacher is always
        // notified that the advancement was not approved.
        $supervisor = $advancement->supervisor;
        $this->notificationService->notifyCareerAdvancementRejected(
            $supervisor ?? $user,
            $teacher->user,
            $stageLabel,
            $remarks ?: null,
        );

        return back()->with('success', "Advancement rejected. {$teacher->user->name} remains at their current career stage.");
    }

    private function authorizeSchool(CareerAdvancement $advancement, $user): void
    {
        $schoolId = $advancement->teacher->user->school_id;

        if ($user->school_id !== $schoolId) {
            abort(403, 'This advancement does not belong to your school.');
        }
    }

    private function stageLabel(?string $key): string
    {
        return app(CareerStageResolver::class)->stageLabel($key) ?: $key;
    }
}
