<?php

namespace App\Http\Controllers;

use App\AI\Exceptions\AIRateLimitException;
use App\AI\Support\AIStatus;
use App\Enums\NotificationType;
use App\Jobs\GeneratePostObservationFeedback;
use App\Models\CareerAdvancement;
use App\Models\CareerProgressionAssessment;
use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\CotRating;
use App\Models\FormTemplate;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use App\Services\AIFeedbackService;
use App\Services\AISuggestionService;
use App\Services\AuditLogService;
use App\Services\CareerMonitorService;
use App\Services\CareerProgressionService;
use App\Services\CotDocumentService;use App\Services\CotIndicatorService;
use App\Services\FormTemplateService;
use App\Services\IndicatorTrendService;
use App\Services\NotificationService;
use App\Services\ObservationComparisonService;
use App\Services\ObservationReportService;
use App\Services\PDFReportService;
use App\Services\PHPMailerService;
use App\Services\ProfessionalDevelopmentService;
use App\Services\RateeProfileService;
use App\Services\TeacherAttentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SupervisorController extends Controller
{
    protected NotificationService $notificationService;

    protected PHPMailerService $mailerService;

    protected AIFeedbackService $aiFeedback;

    protected AISuggestionService $aiSuggestions;

    protected FormTemplateService $formTemplateService;

    protected CotIndicatorService $cotIndicatorService;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailerService, AIFeedbackService $aiFeedback, AISuggestionService $aiSuggestions, FormTemplateService $formTemplateService, CotIndicatorService $cotIndicatorService)
    {
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->aiFeedback = $aiFeedback;
        $this->aiSuggestions = $aiSuggestions;
        $this->formTemplateService = $formTemplateService;
        $this->cotIndicatorService = $cotIndicatorService;
    }

    /**
     * Display the supervisor dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $observationsQuery = Observation::where('observer_id', $user->id);
        $obs = (clone $observationsQuery)->get();
        $obsScored = $obs->whereNotNull('overall_score');

        $stats = [
            'total_teachers' => Teacher::whereHas('user', fn ($q) => $q->where('school_id', $user->school_id))->count(),
            'total_observations' => $obs->count(),
            'completed' => $obs->where('status', 'completed')->count(),
            'in_progress' => $obs->where('status', 'in_progress')->count(),
            'scheduled' => $obs->where('status', 'scheduled')->count(),
            'average_score' => round($obsScored->avg('overall_score') ?? 0, 2),
            'stage_pre_planning' => $obs->where('stage', 'pre_observation_planning')->count(),
            'stage_pre_conference' => $obs->where('stage', 'pre_conference')->count(),
            'stage_observation' => $obs->where('stage', 'observation')->count(),
            'stage_post_conference' => $obs->where('stage', 'post_conference')->count(),
        ];

        $recentObservations = (clone $observationsQuery)
            ->with('observee.user')
            ->latest()
            ->take(5)
            ->get();

        // Active observations awaiting the supervisor's next action.
        $todoObservations = (clone $observationsQuery)
            ->with('observee.user')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $cotScores = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->pluck('overall_score')
            ->toArray();

        $cotLabels = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->get()
            ->map(fn ($o, $i) => 'Obs '.($i + 1))
            ->toArray();

        $prevAvg = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->take(max(count($cotScores) - 1, 1))
            ->avg('overall_score');

        $trend = $prevAvg ? ($stats['average_score'] - round($prevAvg, 2)) : 0;

        // Teachers that need the supervisor's attention (dashboard card -> teachers list).
        $attention = app(TeacherAttentionService::class)->forSchool($user->school_id);
        $needsAttention = collect($attention)
            ->filter(fn ($row) => $row['level'] !== 'ok')
            ->sortByDesc('level')
            ->values();

        return view('supervisor.dashboard', compact(
            'stats', 'recentObservations', 'todoObservations', 'cotScores', 'cotLabels', 'trend',
            'attention', 'needsAttention'
        ));
    }

    /**
     * Display list of teachers supervised by the current supervisor.
     */
    public function teachers(Request $request)
    {
        $user = Auth::user();

        // Attention diagnostics for every teacher under the supervisor's school.
        $attention = app(TeacherAttentionService::class)->forSchool($user->school_id);
        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        $needsAttentionCount = collect($attention)->filter(fn ($row) => $row['level'] !== 'ok')->count();

        $attentionFilter = $request->get('attention');

        // Get teachers from the same school as the supervisor
        $teachers = Teacher::query()
            ->with(['user', 'school', 'subjects'])
            ->withCount('observations')
            ->whereHas('user', function ($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })
            ->when($attentionFilter === 'needs', function ($query) use ($attention) {
                $ids = collect($attention)->filter(fn ($row) => $row['level'] !== 'ok')->keys();
                $query->whereIn('id', $ids->isNotEmpty() ? $ids->all() : [0]);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);

        // Enrich each paginated teacher with its attention flags and summary.
        $teachers->getCollection()->transform(function (Teacher $teacher) use ($attention, $order) {
            $row = $attention[$teacher->id] ?? [
                'teacher' => $teacher,
                'flags' => [],
                'summary' => 'On track',
                'level' => 'ok',
            ];

            $teacher->attention_level = $row['level'];
            $teacher->attention_level_order = $order[$row['level']] ?? 3;
            $teacher->attention_flags = $row['flags'];
            $teacher->attention_summary = $row['summary'];

            return $teacher;
        });

        // Order the current page: attention severity first (high -> medium -> low -> ok), then name.
        $sorted = $teachers->getCollection()
            ->sortBy(fn ($t) => $t->attention_level_order)
            ->values();
        $teachers->setCollection($sorted);

        return view('supervisor.teachers.index', compact('teachers', 'needsAttentionCount', 'order'));
    }

    /**
     * Display a teacher's profile with their details and recent observations.
     */
    public function teacherProfile(Teacher $teacher)
    {
        $user = Auth::user();

        if ($teacher->user->school_id !== $user->school_id) {
            abort(403, 'This teacher does not belong to your school.');
        }

        $teacher->load(['user', 'school', 'subjects']);

        $observations = Observation::with(['preObservationPlanning', 'preConference', 'postConference', 'cotRatings', 'cotIndicatorVersion'])
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class)
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->count(),
            'completed' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->where('stage', 'post_conference')
                ->count(),
            'in_progress' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])
                ->count(),
            'avg_score' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->whereNotNull('overall_score')
                ->avg('overall_score'),
            'latest_observation' => Observation::where('observee_id', $teacher->id)
                ->where('observee_type', Teacher::class)
                ->latest()
                ->first(),
        ];

        $careerService = app(CareerProgressionService::class);
        $rateeProfile = app(RateeProfileService::class)->for($teacher);
        $careerContext = $careerService->contextFor($teacher);

        $attention = app(\App\Services\TeacherAttentionService::class)->forSchool($user->school_id);
        $teacherAttention = $attention[$teacher->id] ?? ['flags' => [], 'level' => 'ok', 'summary' => 'On track'];

        return view('supervisor.teachers.show', compact('teacher', 'observations', 'stats', 'rateeProfile', 'teacherAttention'))
            ->with('careerContext', $careerContext)
            ->with('careerNextStages', $careerService->nextStageOptions($careerContext['career_stage']))
            ->with('careerEvidence', $careerService->evidenceFor($teacher))
            ->with('careerReadiness', $careerService->readinessFor($teacher))
            ->with('careerRoute', route('supervisor.teachers.career-assessment', $teacher))
            ->with('canAssess', true);
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
            route('supervisor.teachers.show', $teacher).'#readiness',
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

    /**
     * Display list of school heads.
     */
    public function schoolHeads(Request $request)
    {
        $user = Auth::user();

        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user', 'school'])
            ->withCount(['observations as total_observations' => function ($q) {
                $q->where('observee_type', SchoolHeadProfile::class);
            }])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);

        return view('supervisor.teachers.school-heads', compact('schoolHeads'));
    }

    /**
     * Display observation history for a specific school head.
     */
    public function schoolHeadObservationHistory(SchoolHeadProfile $schoolHead)
    {
        $this->assertSchoolHeadBelongsToSupervisorSchool($schoolHead);

        $observations = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning'])
            ->latest()
            ->paginate(10);

        return view('supervisor.teachers.school-head-observations', compact('schoolHead', 'observations'));
    }

    /**
     * Display the ratee profile for a school head.
     *
     * Read-only decision support: it summarises existing observation data and
     * never modifies the school head's status, position or career stage.
     */
    public function schoolHeadProfile(SchoolHeadProfile $schoolHead)
    {
        $this->assertSchoolHeadBelongsToSupervisorSchool($schoolHead);

        $schoolHead->load(['user', 'school']);

        $observations = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning', 'cotIndicatorVersion'])
            ->latest()
            ->paginate(10);

        $rateeProfile = app(RateeProfileService::class)->for($schoolHead);

        return view('supervisor.teachers.school-head-profile', compact('schoolHead', 'observations', 'rateeProfile'));
    }

    /**
     * A supervisor may only view school heads of the school they belong to.
     * Supervisors without a school are unrestricted.
     */
    private function assertSchoolHeadBelongsToSupervisorSchool(SchoolHeadProfile $schoolHead): void
    {
        $user = Auth::user();

        if (! $user->school_id) {
            return;
        }

        if ($schoolHead->school_id !== $user->school_id && $schoolHead->user?->school_id !== $user->school_id) {
            abort(403, 'This school head does not belong to your school.');
        }
    }

    /**
     * Show the form for creating a new observation.
     */
    public function createObservation()
    {
        $user = Auth::user();

        $schoolYear = $this->getCurrentSchoolYear();

        // Get teachers from the same school (using teacher's school_id directly)
        $teachers = Teacher::query()
            ->with(['user', 'school', 'subjects'])
            ->where('school_id', $user->school_id)
            ->get();

        // Get school heads (can filter by division/district later)
        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user', 'school'])
            ->get();

        // Prepare teacher data for JavaScript (richer info for browse & preview)
        $teacherIds = $teachers->pluck('id');

        $recentObs = Observation::whereIn('observee_id', $teacherIds)
            ->where('observee_type', Teacher::class)
            ->latest()
            ->get()
            ->groupBy('observee_id');

        $teacherData = $teachers->filter(function ($teacher) {
            return $teacher->user !== null;
        })->map(function ($teacher) use ($recentObs) {
            $observations = $recentObs->get($teacher->id, collect())->take(5)->map(function ($obs) {
                return [
                    'id' => $obs->id,
                    'date' => $obs->observation_date ? $obs->observation_date->format('M d, Y') : 'No date',
                    'stage' => $obs->stage,
                    'status' => $obs->status,
                    'subject' => $obs->subject,
                    'score' => $obs->overall_score ? number_format($obs->overall_score, 2) : null,
                    'url' => route('supervisor.observations.show', $obs),
                ];
            });

            $totalObs = $recentObs->get($teacher->id, collect())->count();
            $completedObs = $recentObs->get($teacher->id, collect())->where('stage', 'post_conference')->count();
            $inProgressObs = $recentObs->get($teacher->id, collect())->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count();

            return [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'email' => $teacher->user->email,
                'subject' => $teacher->subjectsLabel ?? 'Not set',
                'subjects' => $teacher->subjects->pluck('name')->values()->all(),
                'grade_level' => $teacher->grade_level ?? 'Not set',
                'department' => $teacher->department ?? 'Not set',
                'position' => $teacher->position ?? 'Teacher',
                'employee_number' => $teacher->employee_number ?? '—',
                'school_name' => $teacher->school?->name ?? 'No school assigned',
                'profile_url' => route('supervisor.teachers.show', $teacher),
                'recent_observations' => $observations,
                'obs_stats' => [
                    'total' => $totalObs,
                    'completed' => $completedObs,
                    'in_progress' => $inProgressObs,
                ],
            ];
        })->values();

        // Prepare school head data for JavaScript
        $schoolHeadData = $schoolHeads->filter(function ($schoolHead) {
            return $schoolHead->user !== null;
        })->map(function ($schoolHead) {
            return [
                'id' => $schoolHead->id,
                // The observations.school_head_id column references users.id,
                // so assignment dropdowns must submit the user id, not the
                // profile id (which is used for observee selection).
                'user_id' => $schoolHead->user->id,
                'name' => $schoolHead->user->name,
                'email' => $schoolHead->user->email,
                'subject' => $schoolHead->subject ?? 'Not set',
                'grade_level' => $schoolHead->grade_level ?? 'Not set',
                'position' => $schoolHead->position ?? $schoolHead->current_designation ?? 'School Head',
                'position_level' => $schoolHead->position_level ?? '—',
                'school_name' => $schoolHead->school?->name ?? 'No school assigned',
            ];
        })->values();

        // Published COT instruments for the current school year (the
        // "template" selection step of the scheduling wizard). The ratee role
        // determines whether a template appears for teacher or school head
        // observations; a career stage only narrows the auto-resolve, so all
        // published templates for the school year are listed.
        $cotTemplates = CotIndicatorVersion::query()
            ->with(['indicators' => fn ($query) => $query->active()])
            ->withCount('indicators')
            ->where('school_year', $schoolYear)
            ->published()
            ->orderByDesc('is_default')
            ->orderBy('ratee_role')
            ->orderBy('label')
            ->get()
            ->map(fn (CotIndicatorVersion $version) => [
                'id' => $version->id,
                'label' => $version->label,
                'school_year' => $version->school_year,
                'is_default' => $version->is_default,
                'ratee_role' => $version->rateeRole(),
                'ratee_role_label' => $version->rateeRoleLabel(),
                'career_stage_label' => $version->careerStageLabel(),
                'framework_label' => $version->frameworkLabel(),
                'instrument_label' => $version->instrumentLabel(),
                // Reflects the active indicators loaded above, keeping the
                // badge consistent with the preview modal contents.
                'indicators_count' => $version->indicators->count(),
                'requires_post_conference' => $version->requiresPostConference(),
                // Indicators organized by domain for the preview modal.
                'indicator_groups' => $version->indicators
                    ->groupBy('domain')
                    ->map(fn ($group) => $group->map(fn (CotIndicator $indicator) => [
                        'code' => $indicator->code,
                        'description' => $indicator->description,
                    ])->values())
                    ->toArray(),
            ])
            ->values();

        // EPOC Rating Instrument domains & indicators (used for the template
        // preview in the create wizard for School Head observations).
        $epocDomains = [
            'Domain 1: Establishing a Warm and Clear Opening of the Post Observation Conference' => [
                "School Head acknowledges teacher's time (Thanks the teacher for allowing him/her to observe a class)",
                'School Head states the purpose of the conversation',
                'Talks in a voice that is warm, friendly and sincere',
            ],
            'Domain 2: Focus on what\'s going well' => [
                'Congratulates teachers for doing a job well (cite specific instances or teacher behavior/activities that are worth mentioning. Refer to the strengths noted)',
                'Asks the teacher to clearly state the objectives of the lesson',
                "Paraphrases and affirms the teacher's lesson objective (Asks what the pupils are able to demonstrate at the end of the lesson)",
                'Asks the teacher what she did to teach the lesson',
                'Asks teacher what made him/her happy about the delivery of the lesson. The SH listens intently to what the teacher is saying',
                'The SH affirms what the teacher considered as things that went well in the delivery of the lesson',
                'The SH extends the positive focus in addition to what the teacher identified as what went well, citing additional specific things referring to the strengths noted',
            ],
            'Domain 3: Identify Challenges Facing the Teacher' => [
                'The SH asks the teacher to tell which part of the lesson she thinks did not go well',
                "The SH paraphrases teacher's message to check whether they have the same understanding",
                'The SH enables the teacher to tell additional parts that did not go well by citing specific instances recorded in the strengths noted',
                'The SH avoids diversion and stays focused on the issues/data/documentation at hand when teacher makes caustic statements',
                "The SH is able to verify the teacher's perception about the identified areas for improvement",
            ],
            'Domain 4: Generating Ideas for Addressing Teacher\'s Challenges' => [
                'The SH guides the teacher in identifying possible strategies in addressing the challenges',
                'The SH helps solve the problem by offering ideas for improvement if and when the teacher is not able to do so',
                'The SH connects the teacher to available and appropriate resources to help address the challenges',
                'The SH avoids compromising statements that provide an excuse for poor performance',
            ],
            'Domain 5: Prioritizing the Next Steps' => [
                'The Teacher and the principal reviews ideas for improvement and assign priority to possible options',
            ],
            'Domain 6: Ending the Post Observation Conference' => [
                'The SH makes the teacher agree on the next steps by asking the teacher to choose whose help he/she would want to ask to assist in improving the identified challenges',
                'The SH enables the teacher to make a commitment regarding the next steps identified',
                'The SH thanks the teacher for the conversation',
            ],
        ];

        return view('supervisor.observations.create', compact('teacherData', 'schoolHeadData', 'schoolYear', 'cotTemplates', 'epocDomains'));
    }

    /**
     * Store a newly created observation.
     */
    public function storeObservation(Request $request)
    {
        // Normalize empty strings to null for nullable integer/enum fields so
        // Laravel's nullable rule treats them as absent rather than failing
        // the subsequent integer/in rules.
        foreach (['school_head_id', 'quarter', 'observation_number', 'form_template_id', 'cot_indicator_version_id'] as $field) {
            if ($request->input($field) === '' || $request->input($field) === null) {
                $request->merge([$field => null]);
            }
        }

        $validator = validator($request->all(), [
            'observation_type' => ['required', 'in:teacher_observation,school_head_observation'],
            'observee_id' => ['required'],
            'observation_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'school_year' => ['nullable', 'string'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:4'],
            'observation_number' => ['nullable', 'integer', 'min:1', 'max:2'],
            'subject' => ['nullable', 'string'],
            'grade_level' => ['nullable', 'string'],
            'observation_mode' => ['nullable', 'in:in_person,virtual,hybrid'],
            'schedule_type' => ['required', 'in:scheduled,immediate'],
            'form_template_id' => ['nullable', 'integer'],
            'cot_indicator_version_id' => ['nullable', 'integer'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule_conference' => ['nullable', 'boolean'],
            'conference_date' => ['nullable', 'date'],
            'conference_start_time' => ['nullable', 'date_format:H:i'],
            'conference_end_time' => ['nullable', 'date_format:H:i'],
            'conference_location' => ['nullable', 'string', 'max:255'],
            'conference_mode' => ['nullable', 'in:in_person,virtual,hybrid'],
            'school_head_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $schoolYear = $request->input('school_year') ?? $this->getCurrentSchoolYear();

            // Only validate end_time > start_time when both values are present.
            if ($request->filled('start_time') && $request->filled('end_time')) {
                if ($request->input('end_time') <= $request->input('start_time')) {
                    $validator->errors()->add('end_time', 'The end time must be after the start time.');
                }
            }

            // Only validate conference_end_time > conference_start_time when both are present.
            if ($request->filled('conference_start_time') && $request->filled('conference_end_time')) {
                if ($request->input('conference_end_time') <= $request->input('conference_start_time')) {
                    $validator->errors()->add('conference_end_time', 'The conference end time must be after the conference start time.');
                }
            }

            // The chosen observation template must belong to the school year
            // and apply to the selected observation type.
            if ($request->filled('form_template_id')) {
                $template = FormTemplate::find($request->input('form_template_id'));

                if (! $template) {
                    $validator->errors()->add('form_template_id', 'The selected observation template does not exist.');
                } elseif ($template->school_year !== $schoolYear) {
                    $validator->errors()->add('form_template_id', "The selected template is not available for the {$schoolYear} school year.");
                } elseif ($template->observation_type && $template->observation_type !== $request->input('observation_type')) {
                    $validator->errors()->add('form_template_id', 'The selected template does not apply to the chosen observation type.');
                }
            }

            // The chosen COT template must be published, belong to the school
            // year, and apply to the selected observation type.
            // School Head observations use the EPOC instrument instead, so no
            // COT template validation is performed for that observation type.
            if ($request->filled('cot_indicator_version_id') && $request->input('observation_type') !== 'school_head_observation') {
                $cotTemplate = CotIndicatorVersion::find($request->input('cot_indicator_version_id'));

                if (! $cotTemplate) {
                    $validator->errors()->add('cot_indicator_version_id', 'The selected COT template does not exist.');
                } elseif (! $cotTemplate->isPublished()) {
                    $validator->errors()->add('cot_indicator_version_id', 'The selected COT template is not published.');
                } elseif ($cotTemplate->school_year !== $schoolYear) {
                    $validator->errors()->add('cot_indicator_version_id', "The selected COT template is not available for the {$schoolYear} school year.");
                } else {
                    $expectedRole = $request->input('observation_type') === 'teacher_observation' ? 'teacher' : 'school_head';
                    if ($cotTemplate->rateeRole() !== $expectedRole) {
                        $validator->errors()->add('cot_indicator_version_id', 'The selected COT template does not apply to the chosen observation type.');
                    }
                }
            }

            // Simple duplicate check: the same supervisor must not schedule the
            // same ratee twice on the same date at the same start time.
            if ($request->input('schedule_type') === 'scheduled'
                && $request->filled('start_time')
                && $request->filled('observation_date')
                && $request->filled('observee_id')) {
                $observeeType = $request->input('observation_type') === 'teacher_observation'
                    ? Teacher::class
                    : SchoolHeadProfile::class;

                $conflict = Observation::where('observer_id', Auth::id())
                    ->where('observee_id', $request->input('observee_id'))
                    ->where('observee_type', $observeeType)
                    ->whereDate('observation_date', $request->input('observation_date'))
                    ->where('start_time', $request->input('start_time'))
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('start_time', 'You already have an observation scheduled for this ratee at this time.');
                }
            }
        });

        $validated = $validator->validated();

        // Determine observee type and ID based on observation type
        $observeeType = $validated['observation_type'] === 'teacher_observation'
            ? Teacher::class
            : SchoolHeadProfile::class;

        // Determine status based on schedule type
        $status = $validated['schedule_type'] === 'scheduled' ? 'scheduled' : 'in_progress';
        $stage = $validated['schedule_type'] === 'scheduled' ? 'pre_observation_planning' : 'observation';

        $schoolYear = $validated['school_year'] ?? $this->getCurrentSchoolYear();

        // Use the supervisor-selected template when provided; otherwise fall
        // back to the active template for the school year / observation type.
        $activeTemplate = ! empty($validated['form_template_id'])
            ? FormTemplate::find($validated['form_template_id'])
            : $this->formTemplateService->getActiveTemplate($schoolYear, $validated['observation_type']);

        // Use the supervisor-selected COT template when provided; otherwise
        // resolve the published version for the school year / observee.
        // School Head observations use the EPOC instrument instead, so no COT
        // template is resolved or stored for that observation type.
        $cotIndicatorVersion = $validated['observation_type'] === 'school_head_observation'
            ? null
            : (! empty($validated['cot_indicator_version_id'])
                ? CotIndicatorVersion::find($validated['cot_indicator_version_id'])
                : $this->cotIndicatorService->getVersionModel($schoolYear));

        if ($observeeType === Teacher::class) {
            $observee = Teacher::find($validated['observee_id']);
            if ($observee) {
                $cotIndicatorVersion = $this->cotIndicatorService->resolveVersionForObservee(
                    $schoolYear,
                    'teacher',
                    $observee->career_stage,
                ) ?? $cotIndicatorVersion;
            }
        } elseif ($validated['observation_type'] !== 'school_head_observation') {
            $cotIndicatorVersion = $this->cotIndicatorService->resolveVersionForObservee(
                $schoolYear,
                'school_head',
                null,
            ) ?? $cotIndicatorVersion;
        }

        $observation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $validated['observee_id'],
            'observee_type' => $observeeType,
            'observation_type' => $validated['observation_type'],
            'observation_date' => $validated['observation_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'stage' => $stage,
            'notes' => $validated['notes'] ?? null,
            'status' => $status,
            'school_year' => $schoolYear,
            'quarter' => $validated['quarter'] ?? $this->getCurrentTerm(),
            'observation_number' => $validated['observation_number'] ?? 1,
            'subject' => $validated['subject'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'observation_mode' => $validated['observation_mode'] ?? 'in_person',
            'form_template_id' => $activeTemplate?->id,
            'cot_indicator_version_id' => $cotIndicatorVersion?->id,
            'school_head_id' => $validated['school_head_id'] ?? null,
        ]);

        // Post-Observation Conference handling:
        // - Teacher observations: manual decision via schedule_conference toggle (existing workflow).
        // - School Head observations: driven by the selected PPSSH template's
        //   requires_post_conference flag. No duplicate manual toggle.
        $isSchoolHeadObs = $validated['observation_type'] === 'school_head_observation';
        if ($isSchoolHeadObs) {
            $requiresPostConference = $cotIndicatorVersion?->requiresPostConference() ?? true;
            if ($requiresPostConference) {
                $observation->postConference()->create([
                    'conference_date' => $validated['conference_date'] ?? null,
                    'start_time' => $validated['conference_start_time'] ?? null,
                    'end_time' => $validated['conference_end_time'] ?? null,
                    'location' => $validated['conference_location'] ?? null,
                    'mode' => $validated['conference_mode'] ?? 'in_person',
                ]);
            }
        } else {
            $scheduleConference = $request->boolean('schedule_conference')
                || $request->filled('conference_date')
                || $request->filled('conference_start_time')
                || $request->filled('conference_location');

            if ($scheduleConference) {
                $observation->postConference()->create([
                    'conference_date' => $validated['conference_date'] ?? null,
                    'start_time' => $validated['conference_start_time'] ?? null,
                    'end_time' => $validated['conference_end_time'] ?? null,
                    'location' => $validated['conference_location'] ?? null,
                    'mode' => $validated['conference_mode'] ?? 'in_person',
                ]);
            }
        }

        app(AuditLogService::class)->log(
            'created', 'observations', (string) $observation->getKey(),
            "Created observation for {$observation->observee_type} #{$observation->observee_id}",
            'success', [], $observation->toArray()
        );

        // Send notification if scheduled
        if ($status === 'scheduled') {
            $observee = $observation->observee;
            if ($observee && $observee->user) {
                $observeeUser = $observee->user;
                $formattedDate = $observation->observation_date?->format('M d, Y') ?? 'No date';
                $observationLink = match (true) {
                    $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                    $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                    default => route('supervisor.observations.show', $observation->id),
                };

                $timeLabel = $observation->start_time_label;
                if ($timeLabel && $observation->end_time_label) {
                    $timeLabel .= ' - '.$observation->end_time_label;
                }
                $locationLabel = $observation->location;

                // In-app notification
                $this->notificationService->notifyObservationScheduled(
                    $observeeUser,
                    $formattedDate,
                    $observationLink,
                    $timeLabel,
                    $locationLabel
                );

                // Email notification (non-blocking: failures must not prevent redirect)
                try {
                    $observerName = Auth::user()->name;
                    $subject = 'ASPIRE - Classroom Observation Scheduled';
                    $emailBody = $this->buildObservationScheduledEmail($observeeUser->name, $observerName, $formattedDate, $observation->observation_type, $observationLink, $timeLabel, $locationLabel);
                    $this->mailerService->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue observation scheduled email: '.$e->getMessage());
                }
            }

            // Notify school head if assigned
            if ($observation->school_head_id) {
                $schoolHeadUser = \App\Models\User::find($observation->school_head_id);
                if ($schoolHeadUser) {
                    $shLink = route('school-head.observations.show', $observation->id);
                    $this->notificationService->notify(
                        $schoolHeadUser,
                        \App\Enums\NotificationType::OBSERVATION,
                        'Observation Assignment',
                        'You have been assigned to be present during a '.($observation->observation_type === 'teacher_observation' ? 'teacher' : 'school head').' observation on '.($formattedDate ?? 'No date').'.',
                        null,
                        $shLink
                    );
                }
            }
        }

        // Redirect based on schedule type
        if ($validated['schedule_type'] === 'scheduled') {
            return redirect()->route('supervisor.observations.preObservationPlanning', $observation->id)
                ->with('success', 'Observation has been scheduled successfully.');
        } else {
            return redirect()->route('supervisor.observations.observation', $observation->id)
                ->with('success', 'Observation has been created. Start the COT evaluation now.');
        }
    }

    /**
     * Create a linked School Head PPSSH observation for the given teacher observation.
     * The new observation is of type school_head_observation and is linked via
     * related_observation_id to the primary teacher COT observation.
     * The school head is pre-filled from the teacher observation's school_head_id.
     */
    public function createLinkedPpsshObservation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if ($observation->observation_type !== 'teacher_observation') {
            return back()->with('error', 'This action is only available for teacher observations.');
        }

        if ($observation->isLinkedObservation()) {
            return back()->with('error', 'A linked PPSSH observation already exists for this observation.');
        }

        // Use the same school head that was assigned to the teacher observation
        $schoolHeadId = $observation->school_head_id;
        $schoolHead = $schoolHeadId ? User::find($schoolHeadId) : null;

        // Determine the active PPSSH template for the current school year
        $schoolYear = $observation->school_year ?? $this->getCurrentSchoolYear();
        $activeTemplate = $this->formTemplateService->getActiveTemplate($schoolYear, 'school_head_observation');

        // Determine the COT indicator version to use for the SH observation
        // Resolve a published PPSSH version for the school year
        $cotIndicatorVersion = CotIndicatorVersion::where('school_year', $schoolYear)
            ->published()
            ->where('rateeRole', 'school_head')
            ->inRandomOrder() // Pick first available; could be made configurable
            ->first();

        $observeeType = SchoolHeadProfile::class;
        $observeeId = $schoolHead?->id ?? null;

        // Create the linked PPSSH observation
        $linkedObservation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $observeeId,
            'observee_type' => $observeeType,
            'observation_type' => 'school_head_observation',
            'observation_date' => $observation->observation_date,
            'start_time' => $observation->start_time ?? null,
            'end_time' => $observation->end_time ?? null,
            'location' => $observation->location ?? null,
            'stage' => 'pre_observation_planning',
            'notes' => 'Linked PPSSH observation for ' . $observation->subject,
            'status' => 'in_progress',
            'school_year' => $schoolYear,
            'quarter' => $observation->quarter ?? $this->getCurrentTerm(),
            'observation_number' => 1,
            'subject' => $observation->subject ?? null,
            'grade_level' => $observation->grade_level ?? null,
            'observation_mode' => 'in_person',
            'form_template_id' => $activeTemplate?->id,
            'cot_indicator_version_id' => $cotIndicatorVersion?->id,
            'school_head_id' => $schoolHeadId,
            'related_observation_id' => $observation->id,
        ]);

        // Post-observation Conference handling for SH observation
        $isSchoolHeadObs = true;
        $requiresPostConference = true; // PPSSH templates typically require it
        if ($requiresPostConference) {
            $linkedObservation->postConference()->create([
                'conference_date' => null,
                'start_time' => null,
                'end_time' => null,
                'location' => null,
                'mode' => 'in_person',
            ]);
        }

        app(AuditLogService::class)->log(
            'created', 'observations', (string) $linkedObservation->getKey(),
            "Created linked PPSSH observation #{$linkedObservation->getKey()} for teacher observation #{$observation->getKey()}",
            'success', [], $linkedObservation->toArray()
        );

        // Notify school head if assigned
        if ($linkedObservation->school_head_id) {
            $shUser = User::find($linkedObservation->school_head_id);
            if ($shUser) {
                $shLink = route('school-head.observations.show', $linkedObservation->id);
                $this->notificationService->notify(
                    $shUser,
                    \App\Enums\NotificationType::OBSERVATION,
                    'Linked PPSSH Observation Assignment',
                    'You have been assigned to conduct a School Head post-observation conference linked to teacher observation #' . $observation->getKey() . ' on ' . ($observation->observation_date?->format('M d, Y') ?? 'No date') . '.',
                    null,
                    $shLink
                );
            }
        }

        // Notify the teacher (observee) that a linked PPSSH observation was created
        $teacher = $observation->observee;
        if ($teacher && $teacher->user) {
            $teacherLink = route('teacher.observations.show', $observation->id);
            $this->notificationService->notify(
                $teacher->user,
                \App\Enums\NotificationType::OBSERVATION,
                'Linked PPSSH Observation Created',
                'A School Head post-observation conference has been created and linked to your observation on ' . ($observation->observation_date?->format('M d, Y') ?? 'No date') . '.',
                null,
                $teacherLink
            );
        }

        return redirect()->route('supervisor.observations.preObservationPlanning', $linkedObservation->id)
            ->with('success', 'Linked PPSSH observation has been created successfully. Proceed to Pre-Observation Planning.');
    }

    /**
     * Get current school year.
     */
    private function getCurrentSchoolYear(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($currentMonth >= 6) {
            return $currentYear.'-'.($currentYear + 1);
        } else {
            return ($currentYear - 1).'-'.$currentYear;
        }
    }

    /**
     * Get current DepEd term (trimester).
     *
     * TERM 1: Jun 8 – Sep 15
     * TERM 2: Sep 16 – Dec 18
     * TERM 3: Jan 4 – Apr 8
     */
    private function getCurrentTerm(): int
    {
        $now = now();
        $boundaries = [
            1 => [Carbon::create($now->year, 6, 8), Carbon::create($now->year, 9, 15)],
            2 => [Carbon::create($now->year, 9, 16), Carbon::create($now->year, 12, 18)],
            3 => [Carbon::create($now->year, 1, 4), Carbon::create($now->year, 4, 8)],
        ];

        foreach ($boundaries as $term => [$start, $end]) {
            if ($now->between($start, $end)) {
                return $term;
            }
        }

        return 1;
    }

    /**
     * Show Pre-Observation Planning form
     */
    public function preObservationPlanning(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);

        $planning = $observation->preObservationPlanning;

        // Get previous observations for this observee to show strengths/weaknesses
        $previousObservations = Observation::with(['cotRatings', 'observee'])
            ->where('observee_id', $observation->observee_id)
            ->where('observee_type', $observation->observee_type)
            ->where('id', '!=', $observation->id)
            ->whereNotNull('overall_score')
            ->latest()
            ->take(5)
            ->get();

        // Calculate strengths & weaknesses from previous COT ratings
        $prevStrengths = collect();
        $prevWeaknesses = collect();
        if ($previousObservations->isNotEmpty()) {
            $prevRatings = CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn ($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn ($r) => $r->avg_rating < 3)->values();
        }

        $preConference = $observation->preConference;

        $observerRole = Auth::user()->role;

        return view('supervisor.observations.pre-observation-planning', compact(
            'observation', 'planning', 'previousObservations', 'prevStrengths', 'prevWeaknesses', 'preConference', 'observerRole'
        ));
    }

    /**
     * Store Pre-Observation Planning data
     */
    public function storePreObservationPlanning(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'pre_observation_planning', $obsType);

        $validated = $request->validate(array_merge([
            'lesson_plan_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,pptx,xlsx'],
            'ai_insights' => ['nullable', 'string'],
            'suggested_focus' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
            'observation_tool' => ['nullable', 'string', 'in:ppst,classroom_observation_tool,tisuyon'],
        ], $templateRules));

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $file = $request->file('lesson_plan_file');
            $originalName = $file->getClientOriginalName();
            $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filePath = $file->storeAs('lesson_plans', $filename, 'public');
        }

        $data = [
            'lesson_plan_file' => $filePath ?? $observation->preObservationPlanning?->lesson_plan_file,
            'ai_insights' => $validated['ai_insights'] ?? null,
            'suggested_focus' => $validated['suggested_focus'] ?? null,
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
            'observation_tool' => $validated['observation_tool'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'pre_observation_planning', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        if ($request->input('continue') === 'pre_conference') {
            $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
            $currentIdx = array_search($observation->stage, $stageOrder);
            $targetIdx = array_search('pre_conference', $stageOrder);

            if ($targetIdx === $currentIdx + 1) {
                $observation->logChange([
                    'to_stage' => 'pre_conference',
                    'notes' => 'Pre-Observation Planning completed',
                ]);
                $observation->update(['stage' => 'pre_conference']);
            }

            return redirect()->route('supervisor.observations.preConference', $observation->id)
                ->with('success', 'Pre-Observation Planning has been saved. Proceed to Pre-Conference.');
        }

        return redirect()->route('supervisor.observations.preObservationPlanning', $observation->id)
            ->with('success', 'Pre-Observation Planning notes have been saved.');
    }

    /**
     * Request lesson plan from the teacher
     */
    public function requestLessonPlan(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school']);

        $teacherUser = $observation->observee?->user;
        if (! $teacherUser) {
            return redirect()->back()->with('error', 'Teacher not found for this observation.');
        }

        $requester = Auth::user();
        $requesterName = $requester->name;
        $observationLink = route('teacher.observations.show', $observation);

        // Notify the teacher in-app
        $this->notificationService->notifyLessonPlanRequested($teacherUser, $requesterName, $observationLink);

        // Send email to the teacher
        $subject = "Lesson Plan Requested – {$observation->subject}";
        try {
            $this->mailerService->sendGenericEmailLater(
                $teacherUser->email,
                $teacherUser->name,
                $subject,
                $this->buildLessonPlanRequestedEmail($requesterName, $observation, $observationLink)
            );
        } catch (\Throwable $e) {
            Log::error('Failed to queue lesson plan email to teacher: '.$e->getMessage());
        }

        // Notify the school head(s) of the teacher's school
        $school = $observation->observee?->school;
        if ($school) {
            $schoolHeads = $school->users()->where('role', 'school_head')->get();
            foreach ($schoolHeads as $schoolHead) {
                $schoolHeadLink = route('supervisor.observations.show', $observation);
                $this->notificationService->notifyLessonPlanUploaded($schoolHead, $teacherUser->name, $schoolHeadLink);
                try {
                    $this->mailerService->sendGenericEmailLater(
                        $schoolHead->email, $schoolHead->name, $subject,
                        $this->buildLessonPlanRequestedEmail($requesterName, $observation, $schoolHeadLink)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to queue lesson plan email to school head: '.$e->getMessage());
                }
            }
        }

        // Notify the supervisor (requester) as confirmation
        $supervisorLink = route('supervisor.observations.preObservationPlanning', $observation);
        $teacherName = $teacherUser->name;
        $this->notificationService->notifyLessonPlanRequestedToSupervisor($requester, $teacherName, $supervisorLink);

        return redirect()->back()->with('success', 'Lesson plan request has been sent to the teacher.');
    }

    protected function buildLessonPlanRequestedEmail(string $requesterName, Observation $observation, string $observationLink): string
    {
        $subject = "Lesson Plan Requested – {$observation->subject}";

        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #d97706; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Lesson Plan Requested</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$requesterName}</strong> has requested you to submit a lesson plan for the following observation:
                                </p>
                                <table style='background-color: #fffbeb; border-left: 4px solid #d97706; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Grade Level:</strong> {$observation->grade_level}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>School Year:</strong> {$observation->school_year}</td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated notification from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }

    /**
     * Show Pre-Conference form
     */
    public function preConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);

        $preConference = $observation->preConference;
        $planning = $observation->preObservationPlanning;

        // Load previous COT data for sidebar performance summary
        $previousObservations = Observation::with(['cotRatings'])
            ->where('observee_id', $observation->observee_id)
            ->where('observee_type', $observation->observee_type)
            ->where('id', '!=', $observation->id)
            ->whereNotNull('overall_score')
            ->latest()
            ->take(5)
            ->get();

        $prevStrengths = collect();
        $prevWeaknesses = collect();
        if ($previousObservations->isNotEmpty()) {
            $prevRatings = CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn ($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn ($r) => $r->avg_rating < 3)->values();
        }

        return view('supervisor.observations.pre-conference', compact(
            'observation', 'preConference', 'planning', 'prevStrengths', 'prevWeaknesses'
        ));
    }

    /**
     * Store Pre-Conference data
     */
    public function storePreConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'pre_conference', $obsType);

        $validated = $request->validate(array_merge([
            'discussion_notes' => ['nullable', 'string'],
            'finalized_focus' => ['nullable', 'string'],
            'conference_date' => ['nullable', 'date'],
            'teacher_reflection' => ['nullable', 'string'],
            'lesson_plan_review' => ['nullable', 'string'],
            'instructional_materials' => ['nullable', 'string'],
            'topic' => ['nullable', 'string'],
            'learning_objectives' => ['nullable', 'string'],
            'teaching_strategies' => ['nullable', 'string'],
            'assessment_activity' => ['nullable', 'string'],
            'expected_challenges' => ['nullable', 'string'],
            'feedback_areas' => ['nullable', 'string'],
            'ai_insights_reviewed' => ['nullable', 'boolean'],
        ], $templateRules));

        $data = [
            'discussion_notes' => $validated['discussion_notes'] ?? null,
            'finalized_focus' => $validated['finalized_focus'] ?? null,
            'conference_date' => $validated['conference_date'] ?? now(),
            'teacher_reflection' => $validated['teacher_reflection'] ?? null,
            'lesson_plan_review' => $validated['lesson_plan_review'] ?? null,
            'instructional_materials' => $validated['instructional_materials'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'learning_objectives' => $validated['learning_objectives'] ?? null,
            'teaching_strategies' => $validated['teaching_strategies'] ?? null,
            'assessment_activity' => $validated['assessment_activity'] ?? null,
            'expected_challenges' => $validated['expected_challenges'] ?? null,
            'feedback_areas' => $validated['feedback_areas'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'pre_conference', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        // Mark AI insights as reviewed
        if ($request->has('ai_insights_reviewed')) {
            $observation->preObservationPlanning()->updateOrCreate(
                ['observation_id' => $observation->id],
                ['ai_insights_reviewed' => true]
            );
        }

        // If save draft, stay on pre-conference page without advancing stage
        if ($request->has('save_draft')) {
            return redirect()->route('supervisor.observations.preConference', $observation->id)
                ->with('success', 'Pre-Conference draft saved.');
        }

        // Only advance stage forward (prevent regression)
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('observation', $stageOrder);

        if ($targetIdx === $currentIdx + 1) {
            $observation->logChange([
                'to_stage' => 'observation',
                'notes' => 'Pre-Conference completed',
            ]);
            $observation->update(['stage' => 'observation']);
        }

        return redirect()->route('supervisor.observations.observation', $observation->id)
            ->with('success', 'Pre-Conference has been saved.');
    }

    /**
     * Show Observation form (Digital COT)
     */
    public function observation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $cotRatings = $observation->cotRatings;
        $preConference = $observation->preConference;
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        $schoolYear = $observation->school_year ?? config('cot.default_version', '2025-2026');
        $cotVersion = $this->cotIndicatorService->getVersionForObservation($observation);
        $cotIndicators = $cotVersion['indicators'] ?? [];
        $ratingScale = $cotVersion['rating_scale'] ?? config('cot.rating_scale', []);
        $ratingScaleCss = $cotVersion['rating_scale_css'] ?? config('cot.rating_scale_css', []);
        $existingSuggestions = $observation->preObservationPlanning?->ai_insights;

        // School Head observations use the EPOC instrument instead of the COT
        // rating sheet, so load the EPOC relationships for the integrated form.
        $epocEvaluation = null;
        $schoolHead = null;
        if ($observation->isSchoolHeadObservation()) {
            $observation->loadMissing(['epocEvaluation.ratings', 'schoolHead']);
            $epocEvaluation = $observation->epocEvaluation;
            $schoolHead = $observation->schoolHead;
        }

        return view('supervisor.observations.observation', compact(
            'observation', 'cotRatings', 'preConference',
            'cotIndicators', 'ratingScale', 'ratingScaleCss',
            'existingSuggestions', 'schoolYear',
            'epocEvaluation', 'schoolHead'
        ));
    }

    /**
     * Store Observation data (COT Ratings for teacher observations,
     * EPOC ratings for school head observations)
     */
    public function storeObservationData(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        // School Head observations use the EPOC instrument instead of the COT
        // rating sheet, so save EPOC ratings + narrative/agreement.
        if ($observation->isSchoolHeadObservation()) {
            return $this->storeEpocObservationData($request, $observation);
        }

        $cotVersion = $this->cotIndicatorService->getVersionForObservation($observation);
        $scaleValues = array_keys($cotVersion['rating_scale'] ?? config('cot.rating_scale', []));

        $validated = $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*.indicator_code' => ['required', 'string'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['nullable', 'integer', Rule::in($scaleValues)],
            'ratings.*.not_observed' => ['nullable', 'boolean'],
            'ratings.*.not_applicable' => ['nullable', 'boolean'],
            'ratings.*.has_rating' => ['nullable', 'string'],
            'ratings.*.comments' => ['nullable', 'string'],
            'other_comments' => ['nullable', 'string'],
            'star_notes' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ]);

        // Delete only this user's existing ratings (preserve school head EPOC ratings)
        $observation->cotRatings()->delete();

        // Create new ratings
        $createdRatings = [];
        foreach ($validated['ratings'] as $item) {
            $createdRatings[] = CotRating::create([
                'observation_id' => $observation->id,
                'indicator_code' => $item['indicator_code'],
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => (! empty($item['not_observed']) || ! empty($item['not_applicable'])) ? null : ($item['rating'] ?? null),
                'not_observed' => ! empty($item['not_observed']),
                'not_applicable' => ! empty($item['not_applicable']),
                'comments' => $item['comments'] ?? null,
            ]);
        }

        // Generate AI feedback for each rating (dispatched to queue to avoid rate limits)
        foreach ($createdRatings as $cotRating) {
            GeneratePostObservationFeedback::dispatch($cotRating);
        }

        // Handle evidence file uploads
        $evidenceFiles = $observation->evidence_files ?? [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('observation_evidences', 'public');
                $evidenceFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Save additional notes
        $otherComments = $request->input('other_comments');
        $observation->update([
            'evidence_files' => $evidenceFiles,
            'notes' => $otherComments ? ($observation->notes ? $observation->notes."\n\n".$otherComments : $otherComments) : $observation->notes,
        ]);

        // Auto-save STAR notes to post-conference if provided
        $starNotes = $request->input('star_notes');
        $supervisorNotes = $request->input('supervisor_notes');
        if ($starNotes || $supervisorNotes) {
            $observation->postConference()->updateOrCreate(
                ['observation_id' => $observation->id],
                [
                    'star_notes' => $starNotes,
                    'supervisor_notes' => $supervisorNotes,
                ]
            );
        }

        // Calculate overall score (average of numeric ratings excluding NO/N/A)
        $rated = $observation->cotRatings()->where('not_observed', false)->where('not_applicable', false)->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;

        // Only advance stage forward (prevent regression).
        // For school-head PPSSH templates that do NOT require post-conference,
        // stay on observation stage — finalize can proceed directly.
        $requiresPostConference = true;
        if ($observation->isSchoolHeadObservation()) {
            $pinned = $observation->cotIndicatorVersion;
            $requiresPostConference = $pinned ? $pinned->requiresPostConference() : ($cotVersion['requires_post_conference'] ?? true);
        }
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('post_conference', $stageOrder);

        $updates = ['overall_score' => $avgRating, 'status' => 'cot_completed'];
        if ($requiresPostConference && $targetIdx === $currentIdx + 1) {
            $updates['stage'] = 'post_conference';
            $observation->logChange([
                'to_stage' => 'post_conference',
                'to_status' => 'cot_completed',
                'notes' => 'COT Ratings completed',
            ]);
        } else {
            $note = $requiresPostConference ? 'COT Ratings updated' : 'COT Ratings completed (no post-conference per PPSSH template)';
            $observation->logChange([
                'to_status' => 'cot_completed',
                'notes' => $note,
            ]);
        }
        $observation->update($updates);

        // Auto-trigger AI post-observation analysis (non-blocking)
        $observation->loadMissing(['postConference', 'preObservationPlanning', 'observee']);
        try {
            $this->aiFeedback->generatePostConferenceComparison($observation);
        } catch (AIRateLimitException $e) {
            Log::warning("AI rate limit hit for post-conference comparison on observation {$observation->id}: {$e->getMessage()}");
        }

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved. AI analysis has been generated.');
    }

    /**
     * Store Observation data for School Head observations (EPOC instrument).
     *
     * Saves the EPOC ratings, narrative observation and agreement into the
     * epoc_evaluations / epoc_ratings tables, then advances the workflow the
     * same way as the COT path (status -> cot_completed, stage -> post_conference).
     */
    protected function storeEpocObservationData(Request $request, Observation $observation)
    {
        $validated = $request->validate([
            'epoc_ratings' => ['required', 'array'],
            'epoc_ratings.*.domain' => ['required', 'string'],
            'epoc_ratings.*.indicator' => ['required', 'string'],
            'epoc_ratings.*.rating' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'epoc_ratings.*.comments' => ['nullable', 'string'],
            'epoc_narrative_observation' => ['nullable', 'string'],
            'epoc_agreement' => ['nullable', 'string'],
            'other_comments' => ['nullable', 'string'],
            'star_notes' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ]);

        // Delete + recreate the EPOC evaluation (ratings cascade).
        $observation->epocEvaluation()->delete();

        $epocEvaluation = $observation->epocEvaluation()->create([
            'school_head_name' => $observation->schoolHead?->name,
            'observation_date' => $observation->observation_date,
            'narrative_observation' => $validated['epoc_narrative_observation'] ?? null,
            'agreement' => $validated['epoc_agreement'] ?? null,
        ]);

        foreach ($validated['epoc_ratings'] as $item) {
            $epocEvaluation->ratings()->create([
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => $item['rating'] ?? null,
                'comments' => $item['comments'] ?? null,
            ]);
        }

        $rated = $epocEvaluation->ratings()->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;
        $epocEvaluation->update(['overall_score' => $avgRating]);

        // Save evidence file uploads
        $evidenceFiles = $observation->evidence_files ?? [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('observation_evidences', 'public');
                $evidenceFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $otherComments = $request->input('other_comments');
        $observation->update([
            'evidence_files' => $evidenceFiles,
            'notes' => $otherComments ? ($observation->notes ? $observation->notes."\n\n".$otherComments : $otherComments) : $observation->notes,
        ]);

        // Save supervisor/private notes to post-conference
        $starNotes = $request->input('star_notes');
        $supervisorNotes = $request->input('supervisor_notes');
        if ($starNotes || $supervisorNotes) {
            $observation->postConference()->updateOrCreate(
                ['observation_id' => $observation->id],
                [
                    'star_notes' => $starNotes,
                    'supervisor_notes' => $supervisorNotes,
                ]
            );
        }

        // Advance the workflow (same as COT path). School head observations
        // always require post-conference.
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('post_conference', $stageOrder);

        $updates = ['overall_score' => $avgRating, 'status' => 'cot_completed'];
        if ($targetIdx === $currentIdx + 1) {
            $updates['stage'] = 'post_conference';
            $observation->logChange([
                'to_stage' => 'post_conference',
                'to_status' => 'cot_completed',
                'notes' => 'EPOC ratings completed',
            ]);
        } else {
            $observation->logChange([
                'to_status' => 'cot_completed',
                'notes' => 'EPOC ratings updated',
            ]);
        }
        $observation->update($updates);

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = $observee instanceof SchoolHeadProfile
                ? route('school-head.observations.show', $observation->id)
                : route('supervisor.observations.show', $observation->id);
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved.');
    }

    /**
     * Finalize the observation - marks it as completed
     */
    public function finalize(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->canFinalize()) {
            return back()->with('error', 'This observation cannot be finalized yet.');
        }

        $observation->finalize();

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.show', $observation->id)
            ->with('success', 'Observation has been finalized. The teacher can now view the results.');
    }

    /**
     * Show EPOC Evaluation form for School Head
     */
    public function epocEvaluation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $epocEvaluation = $observation->epocEvaluation;
        $schoolHead = $observation->schoolHead;

        return view('supervisor.observations.epoc', compact('observation', 'epocEvaluation', 'schoolHead'));
    }

    /**
     * Store EPOC Evaluation data
     */
    public function storeEPOC(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'school_head_name' => ['nullable', 'string'],
            'observation_date' => ['nullable', 'date'],
            'ratings' => ['required', 'array'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'ratings.*.comments' => ['nullable', 'string'],
            'narrative_observation' => ['nullable', 'string'],
            'agreement' => ['nullable', 'string'],
        ]);

        // Delete existing EPOC evaluation if any
        $observation->epocEvaluation()->delete();

        // Create EPOC evaluation
        $epocEvaluation = $observation->epocEvaluation()->create([
            'school_head_name' => $validated['school_head_name'] ?? $observation->schoolHead?->name,
            'observation_date' => $validated['observation_date'] ?? $observation->observation_date,
            'narrative_observation' => $validated['narrative_observation'] ?? null,
            'agreement' => $validated['agreement'] ?? null,
        ]);

        // Create EPOC ratings
        foreach ($validated['ratings'] as $item) {
            $epocEvaluation->ratings()->create([
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => $item['rating'] ?? null,
                'comments' => $item['comments'] ?? null,
            ]);
        }

        // Calculate overall score (average of non-null ratings, scale 1-5)
        $rated = $epocEvaluation->ratings()->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;
        $epocEvaluation->update(['overall_score' => $avgRating]);

        return redirect()->route('supervisor.observations.show', $observation->id)
            ->with('success', 'EPOC evaluation has been saved successfully.');
    }

    /**
     * Download EPOC evaluation as DOCX document
     */
    public function downloadEpoc(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->epocEvaluation) {
            return back()->with('error', 'No EPOC evaluation has been completed for this observation.');
        }

        $service = new \App\Services\CotDocumentService();
        $path = $service->generateEpocDocument($observation);
        $filename = basename($path);

        return Storage::disk(CotDocumentService::DISK)
            ->download($path, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Show Post-Conference form
     */
    public function postConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $postConference = $observation->postConference;
        $cotRatings = $observation->cotRatings;
        $planning = $observation->preObservationPlanning;
        $preConference = $observation->preConference;

        return view('supervisor.observations.post-conference', compact('observation', 'postConference', 'cotRatings', 'planning', 'preConference'));
    }

    /**
     * Store Post-Conference data
     */
    public function storePostConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'post_conference', $obsType);

        $validated = $request->validate(array_merge([
            'ai_comparison' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'conference_date' => ['nullable', 'date'],
            'star_notes' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'challenges_facing_teacher' => ['nullable', 'string'],
            'ideas_for_addressing_challenges' => ['nullable', 'string'],
            'prioritized_next_steps' => ['nullable', 'string'],
            'teacher_reflection' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ], $templateRules));

        $data = [
            'ai_comparison' => $validated['ai_comparison'] ?? null,
            'feedback' => $validated['feedback'] ?? null,
            'conference_date' => $validated['conference_date'] ?? now(),
            'star_notes' => $validated['star_notes'] ?? null,
            'areas_for_improvement' => $validated['areas_for_improvement'] ?? null,
            'challenges_facing_teacher' => $validated['challenges_facing_teacher'] ?? null,
            'ideas_for_addressing_challenges' => $validated['ideas_for_addressing_challenges'] ?? null,
            'prioritized_next_steps' => $validated['prioritized_next_steps'] ?? null,
            'teacher_reflection' => $validated['teacher_reflection'] ?? null,
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'post_conference', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        $observation->logChange([
            'to_status' => 'completed',
            'notes' => 'Post-Conference completed',
        ]);
        $observation->update(['status' => 'completed']);

        app(AuditLogService::class)->log(
            'completed', 'observations', (string) $observation->getKey(),
            "Observation #{$observation->getKey()} completed",
            'success', [], $observation->toArray()
        );

        // Notify the observee about feedback
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyFeedbackReceived($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.index')
            ->with('success', 'Post-Conference has been saved. Observation is now complete.');
    }

    /**
     * Download Post-Observation Report
     */
    public function downloadReport(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $reportService = app(ObservationReportService::class);
        $markdown = $reportService->generate($observation);

        $filename = 'post-observation-report-'
            .preg_replace('/[^a-z0-9]/i', '-', $observation->observee?->user?->name ?? 'teacher')
            .'-'
            .($observation->observation_date?->format('Y-m-d') ?? date('Y-m-d'))
            .'.md';

        return response()->streamDownload(function () use ($markdown) {
            echo $markdown;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=utf-8',
        ]);
    }

    /**
     * Download Post-Observation Report as PDF
     */
    public function downloadReportPDF(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $pdfService = app(PDFReportService::class);

        return $pdfService->downloadPDF($observation);
    }

    /**
     * Generate (or regenerate) the completed COT document for an observation.
     */
    public function generateCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot generate the COT document: '.implode(' ', $errors));
        }

        try {
            $service->generateDocument($observation);
        } catch (\Throwable $e) {
            Log::error('COT document generation failed', ['observation_id' => $observation->id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to generate the COT document. Please try again.');
        }

        return redirect()->back()->with('success', 'COT document generated successfully.');
    }

    /**
     * Preview the completed COT document in the browser.
     */
    public function previewCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot preview the COT document: '.implode(' ', $errors));
        }

        return response(view('reports.cot-document', $service->viewData($observation)))
            ->header('Content-Type', 'text/html');
    }

    /**
     * Download the generated COT document (DOCX).
     */
    public function downloadCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $path = $service->documentPath($observation);

        if (!$path) {
            return redirect()->back()->with('error', 'No COT document has been generated for this observation yet. Generate it first.');
        }

        return Storage::disk(CotDocumentService::DISK)->download(
            $path,
            basename($path)
        );
    }

    /**
     * Download the COT document as PDF.
     */
    public function downloadCotPdf(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot generate the COT document PDF: '.implode(' ', $errors));
        }

        $filename = $service->filenameFor($observation).'.pdf';

        return $service->generatePdf($observation)->download($filename);
    }

    /**
     * Save the pre-conference agenda checklist state.
     */
    public function saveAgendaChecklist(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'checked' => 'required|array',
            'checked.*' => 'integer|min:0',
        ]);

        $existing = $observation->preConference;
        $merged = array_merge(
            $existing?->form_responses ?? [],
            ['agenda_checklist' => $validated['checked']]
        );

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['form_responses' => $merged]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * View indicator trends for an observee
     */
    public function indicatorTrends(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(IndicatorTrendService::class);
        $trends = $trendService->getIndicatorTrends(
            $observation->observee_id,
            $observation->observee_type
        );

        return view('supervisor.observations.indicator-trends', [
            'observation' => $observation,
            'trends' => $trends,
        ]);
    }

    /**
     * View progress comparison between current and previous observation
     */
    public function progressComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $comparisonService = app(ObservationComparisonService::class);
        $comparison = $comparisonService->compareWithPrevious($observation);

        $trendService = app(IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(ProfessionalDevelopmentService::class);
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher');

        return view('supervisor.observations.progress-comparison', [
            'observation' => $observation,
            'comparison' => $comparison,
            'lowIndicators' => $lowIndicators,
            'pdPlan' => $pdPlan,
        ]);
    }

    /**
     * View PD recommendations for an observee
     */
    public function pdRecommendations(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(ProfessionalDevelopmentService::class);
        $recommendations = $pdService->getRecommendations($lowIndicators->toArray());
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher');

        return view('supervisor.observations.pd-recommendations', [
            'observation' => $observation,
            'recommendations' => $recommendations,
            'pdPlan' => $pdPlan,
            'lowIndicators' => $lowIndicators,
        ]);
    }

    /**
     * Show observation details
     */
    public function showObservation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load([
            'observee.user',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
            'epocEvaluation.ratings',
            'cancelledBy',
            'schoolHead',
        ]);

        return view('supervisor.observations.show', compact('observation'));
    }

    /**
     * Show the cancellation form for an observation.
     */
    public function showCancelForm(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->canCancel()) {
            return redirect()->route('supervisor.observations.show', $observation)
                ->with('error', 'This observation cannot be cancelled in its current state.');
        }

        return view('supervisor.observations.cancel', compact('observation'));
    }

    /**
     * Cancel an observation.
     */
    public function cancel(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->canCancel()) {
            return redirect()->route('supervisor.observations.show', $observation)
                ->with('error', 'This observation cannot be cancelled in its current state.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'in:teacher_request,supervisor_initiative,conflict_in_schedule,health_reason,insufficient_documentation,technical_issues,weather_emergency,other'],
            'cancellation_other_reason' => ['nullable', 'string', 'max:500'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['cancellation_reason'] === 'other'
            ? ($validated['cancellation_other_reason'] ?? 'Other')
            : $validated['cancellation_reason'];

        $observation->cancel($reason, $validated['internal_note'] ?? null);

        app(AuditLogService::class)->log(
            'cancelled', 'observations', (string) $observation->getKey(),
            "Observation #{$observation->getKey()} cancelled: {$reason}",
            'success', [], $observation->toArray(),
            ['cancellation_reason' => $reason]
        );

        // Notify the observee
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $observeeUser = $observee->user;
            $observationLink = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };

            $this->notificationService->notifyObservationCancelled(
                $observeeUser,
                $observationLink
            );

            $observerName = Auth::user()->name;
            $subject = 'ASPIRE - Observation Cancelled';
            $emailBody = $this->buildObservationCancelledEmail(
                $observeeUser->name,
                $observerName,
                $observation->observation_date?->format('M d, Y') ?? 'No date',
                $observation->observation_type,
                str_replace('_', ' ', ucwords($reason)),
                $observationLink
            );
            try {
                $this->mailerService->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
            } catch (\Throwable $e) {
                Log::error('Failed to queue observation cancelled email: '.$e->getMessage());
            }
        }

        return redirect()->route('supervisor.observations.index')
            ->with('success', 'Observation has been cancelled successfully.');
    }

    /**
     * Authorize that the user can access the observation
     */
    private function authorizeObservation(Observation $observation)
    {
        if ($observation->observer_id !== Auth::id()) {
            abort(403, 'You are not authorized to access this observation.');
        }
    }

    /**
     * Display list of observations.
     */
    public function observations(Request $request)
    {
        $user = Auth::user();

        $query = Observation::query()
            ->with(['observee.user', 'postConference', 'schoolHead', 'epocEvaluation'])
            ->where('observer_id', $user->id);

        // Search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('grade_level', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });

            // Search by observee name (morphTo workaround)
            $teacherIds = Teacher::whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->pluck('id');

            $schoolHeadIds = SchoolHeadProfile::whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->pluck('id');

            if ($teacherIds->isNotEmpty()) {
                $query->orWhere(function ($q) use ($teacherIds) {
                    $q->where('observee_type', Teacher::class)
                        ->whereIn('observee_id', $teacherIds);
                });
            }

            if ($schoolHeadIds->isNotEmpty()) {
                $query->orWhere(function ($q) use ($schoolHeadIds) {
                    $q->where('observee_type', SchoolHeadProfile::class)
                        ->whereIn('observee_id', $schoolHeadIds);
                });
            }
        }

        $observations = $query
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->stage, function ($q, $stage) {
                $q->where('stage', $stage);
            })
            ->when($request->observation_type, function ($q, $type) {
                $q->where('observation_type', $type);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('observation_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('observation_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        // Stats for the header
        $baseQuery = Observation::where('observer_id', $user->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_progress' => (clone $baseQuery)->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->where('status', '!=', 'cancelled')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        return view('supervisor.observations.index', compact('observations', 'stats'));
    }

    /**
     * Display reports hub. Three tabs share this route:
     * overview (default), analytics, performance.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        $view = $request->view ?? 'overview';
        if (! in_array($view, ['overview', 'analytics', 'performance'], true)) {
            $view = 'overview';
        }

        $data = match ($view) {
            'analytics' => ['view' => $view] + $this->reportsAnalyticsData($user),
            'performance' => ['view' => $view] + $this->reportsPerformanceData($user),
            default => ['view' => 'overview'] + $this->reportsOverviewData($user),
        };

        return view('supervisor.reports.index', $data);
    }

    /**
     * Data for the Overview tab: headline stats, recent activity, simple trends.
     */
    private function reportsOverviewData(User $user): array
    {
        $baseQuery = Observation::where('observer_id', $user->id);

        $stats = [
            'total_teachers' => Teacher::whereHas('user', fn ($query) => $query->where('school_id', $user->school_id))->count(),
            'total_observations' => (clone $baseQuery)->count(),
            'completed_observations' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress_observations' => (clone $baseQuery)
                ->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        $recentObservations = Observation::with('observee.user')
            ->where('observer_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        $chartData = (clone $baseQuery)
            ->get(['created_at'])
            ->groupBy(fn (Observation $observation) => $observation->created_at?->format('Y-m'))
            ->sortKeys()
            ->map->count();

        $scoresData = (clone $baseQuery)
            ->whereNotNull('overall_score')
            ->latest('observation_date')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return compact('stats', 'recentObservations', 'chartData', 'scoresData');
    }

    /**
     * Data for the Analytics tab: monthly activity/score trends, COT rating
     * distribution, domain averages, strongest/weakest indicators.
     */
    private function reportsAnalyticsData(User $user): array
    {
        $windowStart = now()->subMonths(11)->startOfMonth()->toDateString();
        $windowEnd = now()->endOfMonth()->toDateString();

        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i));

        $monthlyLabels = $months->map(fn ($date) => $date->format('M Y'))->values();

        $countRows = Observation::where('observer_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'created_at']);
        $counts = $countRows
            ->groupBy(fn (Observation $observation) => ($observation->observation_date ?? $observation->created_at)?->format('Y-m'))
            ->map->count();
        $monthlyCounts = $months->map(fn ($date) => (int) ($counts->get($date->format('Y-m'), 0)))->values();

        $averageRows = Observation::where('observer_id', $user->id)
            ->whereNotNull('overall_score')
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'overall_score']);
        $averages = $averageRows
            ->groupBy(fn (Observation $observation) => $observation->observation_date?->format('Y-m'))
            ->map(fn ($group) => round((float) $group->avg('overall_score'), 2));
        $monthlyAverages = $months->map(fn ($date) => $averages->get($date->format('Y-m')))->values();

        $distributionRows = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
            ->selectRaw('rating, not_observed, COUNT(*) as total')
            ->groupBy('rating', 'not_observed')
            ->get();
        $distribution = [
            'labels' => ['Poor (2)', 'Unsatisfactory (3)', 'Satisfactory (4)', 'Very Sat. (5)', 'Outstanding (6)', 'Not Observed'],
            'counts' => [
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 2 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 3 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 4 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 5 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 6 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (bool) $row->not_observed)->total ?? 0),
            ],
        ];

        $domainAverages = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
            ->where('not_observed', false)
            ->where('not_applicable', false)
            ->whereNotNull('domain')
            ->selectRaw('domain, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('domain')
            ->orderByDesc('average')
            ->get()
            ->map(fn ($row) => [
                'domain' => $row->domain,
                'average' => round((float) $row->average, 2),
                'total' => (int) $row->total,
            ]);

        $indicatorStats = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
            ->where('not_observed', false)
            ->where('not_applicable', false)
            ->selectRaw('indicator_code, MAX(indicator) as indicator, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('indicator_code')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->map(fn ($row) => [
                'code' => $row->indicator_code,
                'indicator' => $row->indicator,
                'average' => round((float) $row->average, 2),
                'total' => (int) $row->total,
            ]);

        $strengths = $indicatorStats->filter(fn ($row) => $row['average'] >= 4.5)->sortByDesc('average')->take(5)->values();
        $weaknesses = $indicatorStats->filter(fn ($row) => $row['average'] <= 3.5)->sortBy('average')->take(5)->values();

        $statusCounts = Observation::where('observer_id', $user->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return compact('monthlyLabels', 'monthlyCounts', 'monthlyAverages', 'distribution', 'domainAverages', 'strengths', 'weaknesses', 'statusCounts');
    }

    /**
     * Data for the Performance tab: per-teacher score summaries and trends
     * across the supervisor's completed observations.
     */
    private function reportsPerformanceData(User $user): array
    {
        $teachers = Teacher::with('user')
            ->whereHas('user', fn ($query) => $query->where('school_id', $user->school_id))
            ->get();

        $scoredObservations = Observation::where('observer_id', $user->id)
            ->where('observee_type', Teacher::class)
            ->whereIn('observee_id', $teachers->pluck('id'))
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->get(['observee_id', 'overall_score', 'observation_date'])
            ->groupBy('observee_id');

        $rows = $teachers->map(function (Teacher $teacher) use ($scoredObservations) {
            $scores = $scoredObservations->get($teacher->id, collect());
            $values = $scores->pluck('overall_score')->map(fn ($score) => (float) $score);
            $latest = $scores->last();
            $previous = $scores->count() >= 2 ? $scores[$scores->count() - 2]->overall_score : null;
            $latestScore = $latest?->overall_score;
            $average = $values->isNotEmpty() ? round($values->avg(), 2) : null;

            return [
                'teacher' => $teacher,
                'position' => $teacher->position ?: 'Teacher',
                'observations_count' => $scores->count(),
                'average' => $average,
                'band' => $this->performanceBand($average),
                'trend' => $previous !== null && $latestScore !== null
                    ? round((float) $latestScore - (float) $previous, 2)
                    : null,
                'last_observed' => $latest?->observation_date,
            ];
        });

        $observed = $rows->filter(fn ($row) => $row['observations_count'] > 0);
        $sorted = $observed
            ->sort(function ($a, $b) {
                return [$b['average'], strtolower($a['teacher']->user->name)] <=> [$a['average'], strtolower($b['teacher']->user->name)];
            })
            ->values()
            ->concat(
                $rows->filter(fn ($row) => $row['observations_count'] === 0)
                    ->sortBy(fn ($row) => strtolower($row['teacher']->user->name))
                    ->values()
            );

        return [
            'performanceRows' => $sorted,
            'performanceSummary' => [
                'teachers_total' => $rows->count(),
                'teachers_observed' => $observed->count(),
                'school_average' => $observed->isNotEmpty() ? round($observed->avg('average'), 2) : null,
                'improving' => $observed->where('trend', '>', 0)->count(),
                'declining' => $observed->where('trend', '<', 0)->count(),
                'needs_attention' => $observed->where('average', '<', 4)->count(),
            ],
        ];
    }

    /**
     * DepEd descriptive band for an average COT score (2-6 scale).
     */
    private function performanceBand(?float $average): ?array
    {
        if ($average === null) {
            return null;
        }

        return match (true) {
            $average >= 5.5 => ['label' => 'Outstanding', 'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'],
            $average >= 4.5 => ['label' => 'Very Satisfactory', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
            $average >= 3.5 => ['label' => 'Satisfactory', 'class' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'],
            $average >= 2.5 => ['label' => 'Unsatisfactory', 'class' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
            default => ['label' => 'Poor', 'class' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'],
        };
    }

    /**
     * Export observations as CSV
     */
    public function exportReports()
    {
        $user = Auth::user();

        $observations = Observation::with(['observee.user', 'observer'])
            ->where('observer_id', $user->id)
            ->latest()
            ->get();

        $filename = 'observations-report-'.now()->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($observations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Type', 'Observee', 'Observer', 'Date', 'Stage', 'Status', 'Score', 'Subject', 'Grade Level', 'School Year', 'Quarter', 'Created At']);

            foreach ($observations as $obs) {
                fputcsv($handle, [
                    $obs->id,
                    $obs->observation_type,
                    $obs->observee?->user?->name ?? 'N/A',
                    $obs->observer?->name ?? 'N/A',
                    $obs->observation_date?->format('Y-m-d'),
                    $obs->stage,
                    $obs->status,
                    $obs->overall_score,
                    $obs->subject ?? 'N/A',
                    $obs->grade_level ?? 'N/A',
                    $obs->school_year ?? 'N/A',
                    $obs->quarter ?? 'N/A',
                    $obs->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display observation history for a specific observee (teacher/school head).
     */
    public function teacherObservationHistory(Request $request, $observeeId)
    {
        $user = Auth::user();
        $observeeType = $request->type;

        if (! $observeeType) {
            return redirect()->route('supervisor.observations.index')
                ->with('error', 'Observee type is required.');
        }

        $baseQuery = Observation::with(['observee.user', 'preObservationPlanning', 'preConference', 'postConference', 'cotRatings'])
            ->where('observer_id', $user->id)
            ->where('observee_id', $observeeId)
            ->where('observee_type', $observeeType);

        $observations = $baseQuery->latest()->paginate(10);

        // Get the observee name from the first result
        $observeeName = $observations->first()?->observee?->user?->name ?? 'Unknown';

        // Stats (use separate query for accuracy)
        $allForStats = $baseQuery->get();
        $stats = [
            'total' => $allForStats->count(),
            'completed' => $allForStats->where('stage', 'post_conference')->count(),
            'avg_score' => $allForStats->whereNotNull('overall_score')->avg('overall_score'),
        ];

        return view('supervisor.observations.teacher-history', compact('observations', 'observeeName', 'observeeId', 'observeeType', 'stats'));
    }

    private function buildObservationScheduledEmail(string $observeeName, string $observerName, string $date, string $observationType, string $link, ?string $time = null, ?string $location = null): string
    {
        $typeLabel = $observationType === 'teacher_observation' ? 'Teacher Observation' : 'School Head Observation';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Observation Scheduled</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e40af; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .detail-label { font-weight: 600; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
                    <p>Automated Supervision Platform for Instructional Reform & Excellence</p>
                </div>
                <div class='content'>
                    <h2>Hello {$observeeName},</h2>
                    <p>A classroom observation has been scheduled for you. Please review the details below:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 16px 0;'>
                        <tr><td class='detail-label'>Type:</td><td>{$typeLabel}</td></tr>
                        <tr><td class='detail-label'>Scheduled By:</td><td>{$observerName}</td></tr>
                        <tr><td class='detail-label'>Observation Date:</td><td>{$date}</td></tr>
                        " . ($time ? "<tr><td class='detail-label'>Time:</td><td>{$time}</td></tr>" : '') . "
                        " . ($location ? "<tr><td class='detail-label'>Location:</td><td>{$location}</td></tr>" : '') . "
                    </table>
                    <p style='margin-top: 20px;'><strong>What to expect:</strong></p>
                    <ul>
                        <li>Pre-Observation Planning: You may be required to submit a lesson plan and answer pre-observation questions.</li>
                        <li>Classroom Observation: The actual observation will take place on the scheduled date.</li>
                        <li>Post-Conference: A feedback session will follow after the observation.</li>
                    </ul>
                    <p>Please ensure you are prepared for the observation on the scheduled date. If you have any questions, contact your supervisor.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>&copy; 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildObservationCancelledEmail(string $observeeName, string $observerName, string $date, string $observationType, string $reason, string $link): string
    {
        $typeLabel = $observationType === 'teacher_observation' ? 'Teacher Observation' : 'School Head Observation';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Observation Cancelled</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .detail-label { font-weight: 600; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
                    <p>Automated Supervision Platform for Instructional Reform & Excellence</p>
                </div>
                <div class='content'>
                    <h2>Hello {$observeeName},</h2>
                    <p>We regret to inform you that your {$typeLabel} scheduled for <strong>{$date}</strong> has been <strong>cancelled</strong>.</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 16px 0;'>
                        <tr><td class='detail-label'>Type:</td><td>{$typeLabel}</td></tr>
                        <tr><td class='detail-label'>Cancelled By:</td><td>{$observerName}</td></tr>
                        <tr><td class='detail-label'>Original Date:</td><td>{$date}</td></tr>
                        <tr><td class='detail-label'>Reason:</td><td>{$reason}</td></tr>
                    </table>
                    <p>If you have any questions, please contact your supervisor directly.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>&copy; 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function generateAiInsights(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load(['preObservationPlanning', 'observee']);

        // Generate inline (with a generous time budget) so the result arrives
        // in full within the same request instead of relying on a background
        // queue worker. No template fallback here: if AI is disabled/unavailable
        // we return a friendly notice instead of canned text.
        set_time_limit(300);

        try {
            $insights = app(\App\AI\Services\PreObservationService::class)->generateInsights($observation, false);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        if (!$insights) {
            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => $insights]
        );

        app(AuditLogService::class)->logAi(
            'insights_generated',
            "Pre-observation AI insights generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_observation', 'observation_id' => $observation->id],
        );

        return response()->json([
            'status' => 'completed',
            'ai_insights' => $insights,
            'source' => 'ai',
        ]);
    }

    /**
     * Check whether pre-observation AI insights are ready.
     * Called by the frontend via polling after dispatching the job.
     */
    public function aiInsightsStatus(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load('preObservationPlanning');

        $insights = $observation->preObservationPlanning?->ai_insights;

        if ($insights) {
            return response()->json([
                'status' => 'completed',
                'ai_insights' => $insights,
                'source' => 'ai',
            ]);
        }

        return response()->json([
            'status' => 'processing',
            'message' => 'AI insights are still being generated.',
        ]);
    }

    public function clearAiInsights(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => null]
        );

        app(AuditLogService::class)->logAi(
            'insights_cleared',
            "Pre-observation AI insights cleared for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_observation', 'observation_id' => $observation->id],
        );

        return response()->json(['success' => true]);
    }

    public function generateAiSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        try {
            $data = $this->aiFeedback->generatePreConferenceSuggestions($observation, templateFallback: false);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        if ($data === null || (blank($data['discussion_notes'] ?? null) && blank($data['finalized_focus'] ?? null))) {
            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notify(
                $observee->user,
                NotificationType::AI_SUGGESTION,
                'AI discussion notes are ready',
                'AI has drafted discussion notes and focus areas for your observation. Please review them with your supervisor.',
                null,
                route('teacher.observations.show', $observation),
            );
        }

        app(AuditLogService::class)->logAi(
            'suggestions_generated',
            "Pre-conference AI suggestions generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_conference', 'observation_id' => $observation->id],
        );

        return response()->json([
            'discussion_notes' => $data['discussion_notes'] ?? '',
            'finalized_focus' => $data['finalized_focus'] ?? '',
        ]);
    }

    public function generateAiComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load(['postConference', 'preObservationPlanning', 'observee']);

        try {
            $comparison = $this->aiFeedback->generatePostConferenceComparison($observation, templateFallback: false);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('post_conference', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('post_conference'), 503);
        }

        if (! $comparison) {
            return response()->json(AIStatus::unavailable('post_conference'), 503);
        }

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_comparison' => $comparison]
        );

        app(AuditLogService::class)->logAi(
            'comparison_generated',
            "Post-conference AI comparison generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'post_conference', 'observation_id' => $observation->id],
        );

        return response()->json(['ai_comparison' => $comparison]);
    }

    public function generateObservationSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $suggestions = $this->aiSuggestions->generateObservationSuggestions($observation);

        if ($suggestions === null) {
            return response()->json(['error' => 'Failed to generate observation suggestions. Try again later.'], 500);
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notify(
                $observee->user,
                NotificationType::AI_SUGGESTION,
                'AI observation suggestions are ready',
                'AI has generated suggestions for your observation. Please review them with your supervisor.',
                null,
                route('teacher.observations.show', $observation),
            );
        }

        app(AuditLogService::class)->logAi(
            'guidance_generated',
            "During-observation AI guidance generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'during_observation', 'observation_id' => $observation->id],
        );

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Lightweight JSON autosave endpoint for workflow stages.
     * Mirrors the store methods but never advances the stage or triggers AI.
     */
    public function autosave(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $stage = $request->input('stage', 'observation');
        $savedFields = [];

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;

        if ($stage === 'observation') {
            // School Head observations use the EPOC instrument.
            if ($observation->isSchoolHeadObservation()) {
                if ($request->has('epoc_ratings') && is_array($request->input('epoc_ratings'))) {
                    $epoc = $observation->epocEvaluation ?? new \App\Models\EpocEvaluation();
                    if (! $epoc->exists) {
                        $epoc = $observation->epocEvaluation()->create([
                            'school_head_name' => $observation->schoolHead?->name,
                            'observation_date' => $observation->observation_date,
                        ]);
                    }
                    foreach ($request->input('epoc_ratings') as $item) {
                        $hasRating = array_key_exists('rating', $item) && $item['rating'] !== null && $item['rating'] !== '';
                        // Untouched rows carry no rating value; preserve previously
                        // saved ratings rather than overwriting them with null.
                        if (! $hasRating) {
                            continue;
                        }
                        \App\Models\EpocRating::updateOrCreate(
                            ['epoc_evaluation_id' => $epoc->id, 'indicator' => $item['indicator']],
                            [
                                'domain' => $item['domain'] ?? null,
                                'rating' => $item['rating'],
                                'comments' => $item['comments'] ?? null,
                            ]
                        );
                    }
                    if ($request->has('epoc_narrative_observation')) {
                        $epoc->narrative_observation = $request->input('epoc_narrative_observation');
                    }
                    if ($request->has('epoc_agreement')) {
                        $epoc->agreement = $request->input('epoc_agreement');
                    }
                    $epoc->save();
                    $savedFields[] = 'epoc_ratings';
                }
            } elseif ($request->has('ratings') && is_array($request->input('ratings'))) {
                foreach ($request->input('ratings') as $item) {
                    if (empty($item['indicator_code'])) {
                        continue;
                    }

                    // Untouched rows carry no rating, not_observed or not_applicable value; do
                    // not overwrite previously saved data with a null rating.
                    $hasRating = array_key_exists('rating', $item) && $item['rating'] !== null && $item['rating'] !== '';
                    $hasNo = ! empty($item['not_observed']);
                    $hasNa = ! empty($item['not_applicable']);
                    if (! $hasRating && ! $hasNo && ! $hasNa) {
                        continue;
                    }

                    CotRating::updateOrCreate(
                        ['observation_id' => $observation->id, 'indicator_code' => $item['indicator_code']],
                        [
                            'domain' => $item['domain'] ?? null,
                            'indicator' => $item['indicator'] ?? null,
                            'rating' => ($hasNo || $hasNa) ? null : $item['rating'],
                            'not_observed' => $hasNo,
                            'not_applicable' => $hasNa,
                            'comments' => $item['comments'] ?? null,
                        ]
                    );
                }
                $savedFields[] = 'ratings';
            }

            if ($request->has('other_comments')) {
                $observation->notes = $request->input('other_comments');
                $savedFields[] = 'other_comments';
            }

            $starNotes = $request->input('star_notes');
            $supervisorNotes = $request->input('supervisor_notes');
            if ($request->has('star_notes') || $request->has('supervisor_notes')) {
                $observation->postConference()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    [
                        'star_notes' => $starNotes ?: null,
                        'supervisor_notes' => $supervisorNotes ?: null,
                    ]
                );
                $savedFields = array_merge($savedFields, ['star_notes', 'supervisor_notes']);
            }

            $observation->save();
        } else {
            $configs = [
                'pre_observation_planning' => [
                    'relation' => 'preObservationPlanning',
                    'fillable' => ['ai_insights', 'suggested_focus', 'supervisor_notes', 'observation_tool'],
                ],
                'pre_conference' => [
                    'relation' => 'preConference',
                    'fillable' => ['discussion_notes', 'finalized_focus', 'teacher_reflection', 'lesson_plan_review', 'instructional_materials', 'conference_date', 'topic', 'learning_objectives', 'teaching_strategies', 'assessment_activity', 'expected_challenges', 'feedback_areas'],
                ],
                'post_conference' => [
                    'relation' => 'postConference',
                    'fillable' => ['ai_comparison', 'feedback', 'star_notes', 'areas_for_improvement', 'challenges_facing_teacher', 'ideas_for_addressing_challenges', 'prioritized_next_steps', 'teacher_reflection', 'supervisor_notes', 'conference_date'],
                ],
            ];

            if (! isset($configs[$stage])) {
                return response()->json(['ok' => false, 'message' => 'Unknown autosave stage.'], 422);
            }

            $save = [];
            foreach ($configs[$stage]['fillable'] as $field) {
                if ($request->has($field)) {
                    $save[$field] = $request->input($field);
                    $savedFields[] = $field;
                }
            }

            // Persist any form-template driven fields via the standard parser.
            $save = array_merge(
                $save,
                $this->formTemplateService->parseFormData($schoolYear, $stage, $request->all(), $obsType)
            );

            if (! empty($save)) {
                $observation->{$configs[$stage]['relation']}()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    $save
                );
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Draft saved.',
            'saved_fields' => array_values(array_unique($savedFields)),
            'saved_at' => now()->format('g:i:s A'),
        ]);
    }
}
