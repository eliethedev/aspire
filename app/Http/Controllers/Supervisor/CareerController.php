<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CareerAdvancement;
use App\Models\CareerProgressionAssessment;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CareerMonitorService;
use App\Services\CareerProgressionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CareerController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Record a career progression readiness assessment for a teacher.
     *
     * This is a support-only action: it never changes the teacher's position
     * or career stage. Each save is appended to the assessment history.
     */
    public function storeCareerAssessment(Teacher $teacher, Request $request)
    {
        $user = Auth::user();

        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This teacher does not belong to your school.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(CareerProgressionAssessment::STATUSES)],
            'target_career_stage' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'assessed_at' => ['nullable', 'date'],
        ]);

        app(CareerProgressionService::class)->recordAssessment($teacher, $user, $validated);

        $this->notifyCareerAssessment($teacher, $validated);

        return back()->with('success', 'Career progression readiness assessment saved.');
    }

    /**
     * Edit an existing career readiness assessment in place.
     */
    public function updateCareerAssessment(Teacher $teacher, CareerProgressionAssessment $assessment, Request $request)
    {
        $user = Auth::user();

        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This teacher does not belong to your school.');
        }

        if ($assessment->ratee_type !== $teacher->getMorphClass() || $assessment->ratee_id !== $teacher->id) {
            abort(403, 'This assessment does not belong to the given teacher.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(CareerProgressionAssessment::STATUSES)],
            'target_career_stage' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'assessed_at' => ['nullable', 'date'],
        ]);

        app(CareerProgressionService::class)->updateAssessment($assessment, $validated);

        $this->notifyCareerAssessment($teacher, $validated, true);

        return back()->with('success', 'Career progression readiness assessment updated.');
    }

    /**
     * Notify the teacher and school head(s) about a saved/edited assessment.
     */
    private function notifyCareerAssessment(Teacher $teacher, array $data, bool $edited = false): void
    {
        $this->notificationService->notifyCareerAssessment(
            $teacher->user,
            $teacher->user->school_id,
            CareerProgressionAssessment::statusLabelFor($data['status']),
            $edited
        );
    }

    /**
     * Browse the career progression readiness of all ratees in the school.
     *
     * Lists every teacher with their current career stage, latest readiness
     * assessment, target stage, and a compact COT evidence summary, with
     * filters by readiness status.
     */
    public function careerProgression(Request $request)
    {
        $user = Auth::user();
        $careerService = app(CareerProgressionService::class);

        $teachers = Teacher::query()
            ->with('user')
            ->whereHas('user', fn ($query) => $query->where('school_id', $user->school_id))
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->get();

        $latestAssessments = CareerProgressionAssessment::with('evaluator')
            ->where('ratee_type', Teacher::class)
            ->whereIn('ratee_id', $teachers->pluck('id'))
            ->latest('assessed_at')
            ->latest('id')
            ->get()
            ->groupBy('ratee_id')
            ->map->first();

        $evidenceStats = Observation::where('observee_type', Teacher::class)
            ->whereIn('observee_id', $teachers->pluck('id'))
            ->whereNotNull('overall_score')
            ->groupBy('observee_id')
            ->selectRaw('observee_id, count(*) as observations_count, avg(overall_score) as avg_score, max(observation_date) as last_observation_date')
            ->get()
            ->keyBy('observee_id');

        $rows = $teachers->map(function (Teacher $teacher) use ($latestAssessments, $evidenceStats, $careerService) {
            $context = $careerService->contextFor($teacher);
            $assessment = $latestAssessments->get($teacher->id);
            $stats = $evidenceStats->get($teacher->id);

            return [
                'teacher' => $teacher,
                'current_stage_label' => $context['career_stage_label'],
                'assessment' => $assessment,
                'status' => $assessment->status ?? 'not_yet_assessed',
                'target_stage_label' => $assessment?->targetStageLabel(),
                'observations_count' => (int) ($stats->observations_count ?? 0),
                'avg_score' => $stats ? round((float) $stats->avg_score, 2) : null,
                'last_observation_date' => $stats?->last_observation_date,
            ];
        });

        $statusCounts = $rows->countBy('status');
        $statusFilter = $request->status;

        if ($statusFilter && in_array($statusFilter, array_merge(CareerProgressionAssessment::STATUSES, ['not_yet_assessed']), true)) {
            $filtered = $rows->filter(fn (array $row) => $row['status'] === $statusFilter)->values();
        } else {
            $statusFilter = null;
            $filtered = $rows;
        }

        // Ready for consideration first, then for review, needs development,
        // not-yet-assessed; alphabetical within each group.
        $priority = array_flip(array_merge(
            [CareerProgressionAssessment::STATUS_READY_FOR_CONSIDERATION, CareerProgressionAssessment::STATUS_FOR_REVIEW, CareerProgressionAssessment::STATUS_NEEDS_DEVELOPMENT],
            ['not_yet_assessed']
        ));
        $sorted = $filtered->sort(function (array $a, array $b) use ($priority) {
            return [$priority[$a['status']] ?? 99, strtolower($a['teacher']->user->name)]
                <=> [$priority[$b['status']] ?? 99, strtolower($b['teacher']->user->name)];
        })->values();

        return view('supervisor.career.index', [
            'rows' => $sorted,
            'statusCounts' => [
                'all' => $rows->count(),
                CareerProgressionAssessment::STATUS_READY_FOR_CONSIDERATION => $statusCounts->get(CareerProgressionAssessment::STATUS_READY_FOR_CONSIDERATION, 0),
                CareerProgressionAssessment::STATUS_FOR_REVIEW => $statusCounts->get(CareerProgressionAssessment::STATUS_FOR_REVIEW, 0),
                CareerProgressionAssessment::STATUS_NEEDS_DEVELOPMENT => $statusCounts->get(CareerProgressionAssessment::STATUS_NEEDS_DEVELOPMENT, 0),
                'not_yet_assessed' => $statusCounts->get('not_yet_assessed', 0),
            ],
            'statusFilter' => $statusFilter,
            'statuses' => CareerProgressionAssessment::statusOptions(),
            'search' => $request->search,
        ]);
    }

    /**
     * Career Monitor: shows whether each teacher's performance aligns with
     * their position / career stage, and lets the supervisor allow or
     * announce an achieved higher stage.
     */
    public function careerMonitor(Request $request)
    {
        $user = Auth::user();
        $monitor = app(CareerMonitorService::class)->forSchool($user->school_id);

        $counts = [
            'aligned' => $monitor->where('alignment', CareerMonitorService::ALIGNED)->count(),
            'partial' => $monitor->where('alignment', CareerMonitorService::PARTIAL)->count(),
            'not_aligned' => $monitor->where('alignment', CareerMonitorService::NOT_ALIGNED)->count(),
            'insufficient' => $monitor->where('alignment', CareerMonitorService::INSUFFICIENT)->count(),
            'ready' => $monitor->where('next_stage', '!==', null)->where('alignment', CareerMonitorService::ALIGNED)->count(),
        ];

        $filter = $request->alignment;
        if ($filter && in_array($filter, [CareerMonitorService::ALIGNED, CareerMonitorService::PARTIAL, CareerMonitorService::NOT_ALIGNED, CareerMonitorService::INSUFFICIENT], true)) {
            $rows = $monitor->where('alignment', $filter)->values();
        } else {
            $filter = null;
            $rows = $monitor;
        }

        $rows = $rows->sort(function (array $a, array $b) {
            $order = [CareerMonitorService::NOT_ALIGNED => 0, CareerMonitorService::PARTIAL => 1, CareerMonitorService::ALIGNED => 2, CareerMonitorService::INSUFFICIENT => 3];
            return [$order[$a['alignment']] ?? 9, strtolower($a['teacher']->user->name)]
                <=> [$order[$b['alignment']] ?? 9, strtolower($b['teacher']->user->name)];
        })->values();

        return view('supervisor.career.monitor', [
            'rows' => $rows,
            'counts' => $counts,
            'filter' => $filter,
            'search' => $request->search,
            'alignmentOptions' => [
                CareerMonitorService::ALIGNED => 'Aligned with position',
                CareerMonitorService::PARTIAL => 'Partially aligned',
                CareerMonitorService::NOT_ALIGNED => 'Needs development',
                CareerMonitorService::INSUFFICIENT => 'Insufficient data',
            ],
        ]);
    }

    /**
     * Record that a supervisor allows a teacher to progress to the next
     * career stage and notify the teacher + school head.
     */
    public function allowCareerStage(Teacher $teacher, Request $request)
    {
        return $this->handleAdvancement($teacher, $request, CareerAdvancement::TYPE_ALLOW);
    }

    /**
     * Announce that a teacher has achieved a higher career stage and notify
     * the teacher + school head.
     */
    public function announceCareerStage(Teacher $teacher, Request $request)
    {
        return $this->handleAdvancement($teacher, $request, CareerAdvancement::TYPE_ANNOUNCE);
    }

    /**
     * Cancel a supervisor's pending career advancement recommendation
     * before the school head has reviewed it. The teacher stays at their
     * current stage and the school head is notified of the withdrawal.
     */
    public function cancelCareerAdvancement(CareerAdvancement $advancement, Request $request)
    {
        $user = Auth::user();

        if ($advancement->supervisor_id !== $user->id) {
            abort(403, 'You can only cancel your own career advancement recommendations.');
        }

        if (! $advancement->isPendingApproval()) {
            return back()->with('error', 'This career advancement has already been reviewed and cannot be cancelled.');
        }

        $advancement->update([
            'status' => CareerAdvancement::STATUS_CANCELLED,
            'school_head_remarks' => null,
        ]);

        $teacher = $advancement->teacher;
        $stageLabel = $this->stageLabel($advancement->to_career_stage);

        $this->notificationService->notifyCareerAdvancementCancelled($teacher->user, $stageLabel);

        return back()->with('success', "Career advancement recommendation for {$teacher->user->name} has been cancelled.");
    }

    private function handleAdvancement(Teacher $teacher, Request $request, string $type)
    {
        $user = Auth::user();

        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This teacher does not belong to your school.');
        }

        $context = app(CareerProgressionService::class)->contextFor($teacher);
        $currentStage = $context['career_stage'];
        $nextStage = app(CareerProgressionService::class)->nextStageKey($currentStage);

        if (! $nextStage) {
            return back()->with('error', 'This teacher is already at the top of their career track.');
        }

        if (CareerAdvancement::where('teacher_id', $teacher->id)
            ->where('status', CareerAdvancement::STATUS_PENDING_APPROVAL)
            ->exists()) {
            return back()->with('error', 'This teacher already has a career advancement awaiting school head approval.');
        }

        $remarks = $request->input('remarks');
        $remarks = is_string($remarks) ? trim($remarks) : null;

        $advancement = CareerAdvancement::create([
            'teacher_id' => $teacher->id,
            'supervisor_id' => $user->id,
            'from_career_stage' => $currentStage,
            'to_career_stage' => $nextStage,
            'type' => $type,
            'status' => CareerAdvancement::STATUS_PENDING_APPROVAL,
            'remarks' => $remarks ?: null,
            'acted_at' => now()->toDateString(),
        ]);

        // The teacher's career_stage is NOT advanced yet. It is held pending
        // until the school head reviews and approves this recommendation.
        $this->notificationService->notifyCareerAdvancementApprovalRequest(
            $teacher->user,
            $this->stageLabel($nextStage),
            $type,
            route('school-head.career.advancements.index'),
        );

        $message = $type === CareerAdvancement::TYPE_ALLOW
            ? 'Career progression recommended. Awaiting school head approval before the teacher advances.'
            : 'Career stage recommended. Awaiting school head approval before the teacher advances.';

        return back()->with('success', $message);
    }

    private function stageLabel(string $stageKey): string
    {
        return app(\App\Services\CareerStageResolver::class)->stageLabel($stageKey) ?: $stageKey;
    }
}
