<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Enums\NotificationType;
use App\Models\Teacher;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\User;
use App\Models\CotRating;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use App\Services\AIFeedbackService;
use App\Services\FormTemplateService;
use App\Services\CotIndicatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ObservationController extends Controller
{
    protected NotificationService $notificationService;
    protected PHPMailerService $mailer;
    protected AIFeedbackService $aiFeedback;
    protected FormTemplateService $formTemplateService;
    protected CotIndicatorService $cotIndicatorService;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailer, AIFeedbackService $aiFeedback, FormTemplateService $formTemplateService, CotIndicatorService $cotIndicatorService)
    {
        $this->notificationService = $notificationService;
        $this->mailer = $mailer;
        $this->aiFeedback = $aiFeedback;
        $this->formTemplateService = $formTemplateService;
        $this->cotIndicatorService = $cotIndicatorService;
    }

    /**
     * Display observations where the school head is the observee OR the observer.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolHead = $user->schoolHeadProfile;

        if (!$schoolHead) {
            return redirect()->route('school-head.dashboard')->with('error', 'School head profile not found.');
        }

        $query = Observation::with(['observee.user', 'observer', 'schoolHead', 'epocEvaluation'])
            ->where(function ($q) use ($schoolHead, $user) {
                $q->where(function ($q2) use ($schoolHead) {
                    $q2->where('observee_id', $schoolHead->id)
                        ->where('observee_type', SchoolHeadProfile::class);
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('observer_id', $user->id)
                        ->where('observer_type', User::class);
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('school_head_id', $user->id);
                });
            });

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $observations = $query
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->stage, fn($q, $s) => $q->where('stage', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'upcoming' => (clone $query)->whereIn('status', ['scheduled'])->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        return view('school-head.observations.index', compact('observations', 'stats'));
    }

    /**
     * Display observations where the school head is assigned as co-observer.
     */
    public function coObservations(Request $request)
    {
        $user = Auth::user();

        $query = Observation::with(['observee.user', 'observer', 'schoolHead', 'epocEvaluation'])
            ->where('school_head_id', $user->id)
            ->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $observations = $query
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->stage, fn($q, $s) => $q->where('stage', $s))
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'upcoming' => (clone $query)->whereIn('status', ['scheduled', 'in_progress'])->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        return view('school-head.co-observations.index', compact('observations', 'stats'));
    }

    /**
     * Show observation details.
     */
    public function show(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load([
            'observee.user',
            'observer',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings',
            'epocEvaluation.ratings',
            'cancelledBy',
            'schoolHead',
        ]);

        return view('school-head.observations.show', compact('observation'));
    }

    /**
     * Show the form for creating a new teacher observation.
     */
    public function createObservation()
    {
        $user = Auth::user();

        $teachers = Teacher::query()
            ->with(['user', 'subjects'])
            ->where('school_id', $user->school_id)
            ->get();

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
                    'url' => route('school-head.observations.show', $obs),
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
                'recent_observations' => $observations,
                'obs_stats' => [
                    'total' => $totalObs,
                    'completed' => $completedObs,
                    'in_progress' => $inProgressObs,
                ],
            ];
        })->values();

        return view('school-head.observations.create', compact('teacherData'));
    }

    /**
     * Store a newly created teacher observation (school head as observer, without supervisor).
     */
    public function storeObservation(Request $request)
    {
        $validated = $request->validate([
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
        ]);

        $status = $validated['schedule_type'] === 'scheduled' ? 'scheduled' : 'in_progress';
        $stage = $validated['schedule_type'] === 'scheduled' ? 'pre_observation_planning' : 'observation';

        $schoolYear = $validated['school_year'] ?? $this->getCurrentSchoolYear();
        $activeTemplate = $this->formTemplateService->getActiveTemplate($schoolYear, 'teacher_observation');

        $observee = Teacher::find($validated['observee_id']);
        $cotIndicatorVersion = $this->cotIndicatorService->getVersionModel($schoolYear);
        if ($observee) {
            $cotIndicatorVersion = $this->cotIndicatorService->resolveVersionForObservee(
                $schoolYear,
                'teacher',
                $observee->career_stage,
            ) ?? $cotIndicatorVersion;
        }

        $observation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $validated['observee_id'],
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => $validated['observation_date'],
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
        ]);

        app(AuditLogService::class)->log(
            'created', 'observations', (string) $observation->getKey(),
            "School head created observation for teacher #{$observation->observee_id}",
            'success', [], $observation->toArray()
        );

        // Send notification if scheduled
        if ($status === 'scheduled') {
            $observee = $observation->observee;
            if ($observee && $observee->user) {
                $observeeUser = $observee->user;
                $formattedDate = $observation->observation_date?->format('M d, Y') ?? 'No date';
                $observationLink = route('teacher.observations.show', $observation->id);

                $this->notificationService->notifyObservationScheduled(
                    $observeeUser,
                    $formattedDate,
                    $observationLink
                );

                $observerName = Auth::user()->name;
                $subject = 'ASPIRE - Classroom Observation Scheduled';
                $emailBody = $this->buildObservationScheduledEmail($observeeUser->name, $observerName, $formattedDate, $observationLink);
                $this->mailer->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
            }
        }

        if ($validated['schedule_type'] === 'scheduled') {
            return redirect()->route('school-head.observations.preObservationPlanning', $observation->id)
                ->with('success', 'Observation has been scheduled successfully. Proceed to Pre-Observation Planning.');
        } else {
            return redirect()->route('school-head.observations.observation', $observation->id)
                ->with('success', 'Observation has been created. Start the COT evaluation now.');
        }
    }

    /**
     * Show Pre-Observation Planning form.
     */
    public function preObservationPlanning(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);
        $planning = $observation->preObservationPlanning;

        $previousObservations = Observation::with(['cotRatings', 'observee'])
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

            $prevStrengths = $prevRatings->filter(fn($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn($r) => $r->avg_rating < 3)->values();
        }

        $preConference = $observation->preConference;

        $observerRole = 'school_head';

        return view('school-head.observations.pre-observation-planning', compact(
            'observation', 'planning', 'previousObservations', 'prevStrengths', 'prevWeaknesses', 'preConference', 'observerRole'
        ));
    }

    /**
     * Store Pre-Observation Planning data.
     */
    public function storePreObservationPlanning(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'pre_observation_planning', $obsType);

        $validated = $request->validate(array_merge([
            'lesson_plan_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,pptx,xlsx'],
            'ai_insights' => ['nullable', 'string'],
            'suggested_focus' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
            'observation_tool' => ['nullable', 'string', 'in:ppst,classroom_observation_tool,tisuyon'],
        ], $templateRules));

        // School head can only use COT or Tisuyon, not PPST
        if (Auth::user()->isSchoolHead() && ($validated['observation_tool'] ?? '') === 'ppst') {
            $validated['observation_tool'] = 'classroom_observation_tool';
        }

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $file = $request->file('lesson_plan_file');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
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

            return redirect()->route('school-head.observations.preConference', $observation->id)
                ->with('success', 'Pre-Observation Planning has been saved. Proceed to Pre-Conference.');
        }

        return redirect()->route('school-head.observations.preObservationPlanning', $observation->id)
            ->with('success', 'Pre-Observation Planning notes have been saved.');
    }

    /**
     * Request lesson plan from the teacher.
     */
    public function requestLessonPlan(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school']);

        $teacherUser = $observation->observee?->user;
        if (!$teacherUser) {
            return redirect()->back()->with('error', 'Teacher not found for this observation.');
        }

        $requester = Auth::user();
        $requesterName = $requester->name;
        $observationLink = route('teacher.observations.show', $observation);

        $this->notificationService->notifyLessonPlanRequested($teacherUser, $requesterName, $observationLink);

        $subject = "Lesson Plan Requested – {$observation->subject}";
        $this->mailer->sendGenericEmailLater(
            $teacherUser->email,
            $teacherUser->name,
            $subject,
            $this->buildLessonPlanRequestedEmail($requesterName, $observation, $observationLink)
        );

        return redirect()->back()->with('success', 'Lesson plan request has been sent to the teacher.');
    }

    /**
     * Show Pre-Conference form.
     */
    public function preConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);
        $preConference = $observation->preConference;
        $planning = $observation->preObservationPlanning;

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

            $prevStrengths = $prevRatings->filter(fn($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn($r) => $r->avg_rating < 3)->values();
        }

        return view('school-head.observations.pre-conference', compact(
            'observation', 'preConference', 'planning', 'prevStrengths', 'prevWeaknesses'
        ));
    }

    /**
     * Store Pre-Conference data.
     */
    public function storePreConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
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

        if ($request->has('ai_insights_reviewed')) {
            $observation->preObservationPlanning()->updateOrCreate(
                ['observation_id' => $observation->id],
                ['ai_insights_reviewed' => true]
            );
        }

        if ($request->has('save_draft')) {
            return redirect()->route('school-head.observations.preConference', $observation->id)
                ->with('success', 'Pre-Conference draft saved.');
        }

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

        return redirect()->route('school-head.observations.observation', $observation->id)
            ->with('success', 'Pre-Conference has been saved.');
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
     * Show Observation form (Digital COT).
     */
    public function observation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $cotRatings = $observation->cotRatings()->get();
        $preConference = $observation->preConference;
        $observation->loadMissing(['preObservationPlanning', 'observee', 'schoolHead']);

        $schoolYear = $observation->school_year ?? config('cot.default_version', '2025-2026');
        $cotVersion = $this->cotIndicatorService->getVersionForObservation($observation);
        $cotIndicators = $cotVersion['indicators'] ?? [];
        $ratingScale = $cotVersion['rating_scale'] ?? config('cot.rating_scale', []);
        $ratingScaleCss = $cotVersion['rating_scale_css'] ?? config('cot.rating_scale_css', []);
        $existingSuggestions = $observation->preObservationPlanning?->ai_insights;

        // School head observations use the EPOC instrument instead of the COT
        // rating sheet, so load the EPOC relationships for the integrated form.
        $epocEvaluation = null;
        $schoolHead = null;
        if ($observation->isSchoolHeadObservation()) {
            $observation->loadMissing(['epocEvaluation.ratings', 'schoolHead']);
            $epocEvaluation = $observation->epocEvaluation;
            $schoolHead = $observation->schoolHead;
        }

        return view('school-head.observations.observation', compact(
            'observation', 'cotRatings', 'preConference',
            'cotIndicators', 'ratingScale', 'ratingScaleCss',
            'existingSuggestions', 'schoolYear',
            'epocEvaluation', 'schoolHead'
        ));
    }

    /**
     * Store Observation data (COT Ratings).
     */
    public function storeObservationData(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        // School head observations use the EPOC instrument for their rating
        // sheet, so route to the dedicated EPOC store path.
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

        // Delete only this user's existing ratings
        $observation->cotRatings()->delete();

        $createdRatings = [];
        foreach ($validated['ratings'] as $item) {
            $createdRatings[] = CotRating::create([
                'observation_id' => $observation->id,
                'indicator_code' => $item['indicator_code'],
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => (!empty($item['not_observed']) || !empty($item['not_applicable'])) ? null : ($item['rating'] ?? null),
                'not_observed' => !empty($item['not_observed']),
                'not_applicable' => !empty($item['not_applicable']),
                'comments' => $item['comments'] ?? null,
            ]);
        }

        // Not Applicable indicators are intentionally excluded — they have no score to analyze.
        foreach ($createdRatings as $cotRating) {
            if (! $cotRating->isNotApplicable()) {
                \App\Jobs\GeneratePostObservationFeedback::dispatch($cotRating);
            }
        }

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
            'notes' => $otherComments ? ($observation->notes ? $observation->notes . "\n\n" . $otherComments : $otherComments) : $observation->notes,
        ]);

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

        $rated = $observation->cotRatings()->where('not_observed', false)->where('not_applicable', false)->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;

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

        $observation->loadMissing(['postConference', 'preObservationPlanning', 'observee']);
        try {
            $this->aiFeedback->generatePostConferenceComparison($observation);
        } catch (\App\AI\Exceptions\AIRateLimitException $e) {
            \Illuminate\Support\Facades\Log::warning("AI rate limit hit for post-conference comparison on observation {$observation->id}: {$e->getMessage()}");
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = route('teacher.observations.show', $observation->id);
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('school-head.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved. AI analysis has been generated.');
    }

    /**
     * Store the EPOC ratings for a school head observation (the school head
     * is the observee). School head observations use the EPOC instrument
     * instead of the COT rating sheet.
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

        // Save evidence file uploads.
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

        // Save supervisor/private notes to post-conference.
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

        // Advance the workflow.
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

        $observation->loadMissing(['postConference', 'preObservationPlanning', 'observee']);
        try {
            $this->aiFeedback->generatePostConferenceComparison($observation);
        } catch (\App\AI\Exceptions\AIRateLimitException $e) {
            \Illuminate\Support\Facades\Log::warning("AI rate limit hit for post-conference comparison on observation {$observation->id}: {$e->getMessage()}");
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = route('school-head.observations.show', $observation->id);
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('school-head.observations.postConference', $observation->id)
            ->with('success', 'EPOC ratings have been saved. AI analysis has been generated.');
    }

    /**
     * Show Post-Conference form.
     */
    public function postConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $postConference = $observation->postConference;
        $cotRatings = $observation->cotRatings()->get();
        $planning = $observation->preObservationPlanning;
        $preConference = $observation->preConference;
        $epocEvaluation = $observation->epocEvaluation()->with('ratings')->get()->first();

        return view('school-head.observations.post-conference', compact('observation', 'postConference', 'cotRatings', 'planning', 'preConference', 'epocEvaluation'));
    }

    /**
     * Store Post-Conference data.
     */
    public function storePostConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
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
            "School head completed observation #{$observation->getKey()}",
            'success', [], $observation->toArray()
        );

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = route('teacher.observations.show', $observation->id);
            $this->notificationService->notifyFeedbackReceived($observee->user, $link);
        }

        return redirect()->route('school-head.observations.index')
            ->with('success', 'Post-Conference has been saved. Observation is now complete.');
    }

    /**
     * Show the cancellation form.
     */
    public function showCancelForm(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->canCancel()) {
            return redirect()->route('school-head.observations.show', $observation)
                ->with('error', 'This observation cannot be cancelled in its current state.');
        }

        return view('school-head.observations.cancel', compact('observation'));
    }

    /**
     * Cancel an observation.
     */
    public function cancel(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->canCancel()) {
            return redirect()->route('school-head.observations.show', $observation)
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

        $observation->cancel($reason, $validated['internal_note']);

        app(AuditLogService::class)->log(
            'cancelled', 'observations', (string) $observation->getKey(),
            "School head cancelled observation #{$observation->getKey()}: {$reason}",
            'success', [], $observation->toArray(),
            ['cancellation_reason' => $reason]
        );

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $observeeUser = $observee->user;
            $observationLink = route('teacher.observations.show', $observation->id);

            $this->notificationService->notifyObservationCancelled($observeeUser, $observationLink);

            $observerName = Auth::user()->name;
            $subject = 'ASPIRE - Observation Cancelled';
            $emailBody = $this->buildObservationCancelledEmail(
                $observeeUser->name, $observerName,
                $observation->observation_date?->format('M d, Y') ?? 'No date',
                str_replace('_', ' ', ucwords($reason)),
                $observationLink
            );
            $this->mailer->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
        }

        return redirect()->route('school-head.observations.index')
            ->with('success', 'Observation has been cancelled successfully.');
    }

    /**
     * Generate AI insights.
     */
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
        } catch (\App\AI\Exceptions\AIRateLimitException $e) {
            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation'), 503);
        }

        if (!$insights) {
            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation'), 503);
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
     */
    public function aiInsightsStatus(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load('preObservationPlanning');

        $insights = $observation->preObservationPlanning?->ai_insights;

        if ($insights) {
            $source = config('services.gemini.api_key') ? 'ai' : 'rule-based';

            return response()->json([
                'status' => 'completed',
                'ai_insights' => $insights,
                'source' => $source,
            ]);
        }

        return response()->json([
            'status' => 'processing',
            'message' => 'AI insights are still being generated.',
        ]);
    }

    /**
     * Clear AI insights.
     */
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

    /**
     * Generate AI comparison.
     */
    public function generateAiComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load(['postConference', 'preObservationPlanning', 'observee']);

        if (!$this->aiFeedback->isGeminiConfigured()) {
            return response()->json(['error' => 'Gemini API is not configured.'], 400);
        }

        $comparison = $this->aiFeedback->generatePostConferenceComparison($observation);

        if (!$comparison) {
            return response()->json(['error' => 'Failed to generate AI comparison.'], 500);
        }

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_comparison' => $comparison]
        );

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notify(
                $observee->user,
                NotificationType::AI_SUGGESTION,
                'AI comparison is ready',
                'AI has compared your pre- and post-conference responses. Please review them with your supervisor.',
                null,
                route('teacher.observations.show', $observation),
            );
        }

        app(AuditLogService::class)->logAi(
            'comparison_generated',
            "Post-conference AI comparison generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'post_conference', 'observation_id' => $observation->id],
        );

        return response()->json(['ai_comparison' => $comparison]);
    }

    /**
     * Generate AI pre-conference suggestions (strategy AI).
     */
    public function generateAiSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        set_time_limit(300);

        try {
            $data = $this->aiFeedback->generatePreConferenceSuggestions($observation, false);
        } catch (\App\AI\Exceptions\AIRateLimitException $e) {
            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation'), 503);
        }

        if ($data === null || (blank($data['discussion_notes'] ?? null) && blank($data['finalized_focus'] ?? null))) {
            return response()->json(\App\AI\Support\AIStatus::unavailable('pre_observation'), 503);
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

    /**
     * Download Post-Observation Report.
     */
    public function downloadReport(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $reportService = app(\App\Services\ObservationReportService::class);
        $markdown = $reportService->generate($observation);

        $filename = 'post-observation-report-'
            . preg_replace('/[^a-z0-9]/i', '-', $observation->observee?->user?->name ?? 'teacher')
            . '-'
            . ($observation->observation_date?->format('Y-m-d') ?? date('Y-m-d'))
            . '.md';

        return response()->streamDownload(function () use ($markdown) {
            echo $markdown;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=utf-8',
        ]);
    }

    /**
     * Download Post-Observation Report as PDF.
     */
    public function downloadReportPDF(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $pdfService = app(\App\Services\PDFReportService::class);
        return $pdfService->downloadPDF($observation);
    }

    /**
     * View indicator trends for an observee.
     */
    public function indicatorTrends(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(\App\Services\IndicatorTrendService::class);
        $trends = $trendService->getIndicatorTrends(
            $observation->observee_id,
            $observation->observee_type
        );

        return view('school-head.observations.indicator-trends', [
            'observation' => $observation,
            'trends' => $trends,
        ]);
    }

    /**
     * View progress comparison between current and previous observation.
     */
    public function progressComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $comparisonService = app(\App\Services\ObservationComparisonService::class);
        $comparison = $comparisonService->compareWithPrevious($observation);

        $trendService = app(\App\Services\IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(\App\Services\ProfessionalDevelopmentService::class);
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher');

        return view('school-head.observations.progress-comparison', [
            'observation' => $observation,
            'comparison' => $comparison,
            'lowIndicators' => $lowIndicators,
            'pdPlan' => $pdPlan,
        ]);
    }

    /**
     * View PD recommendations for an observee.
     */
    public function pdRecommendations(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(\App\Services\IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(\App\Services\ProfessionalDevelopmentService::class);
        $recommendations = $pdService->getRecommendations($lowIndicators->toArray());
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher');

        return view('school-head.observations.pd-recommendations', [
            'observation' => $observation,
            'recommendations' => $recommendations,
            'pdPlan' => $pdPlan,
            'lowIndicators' => $lowIndicators,
        ]);
    }

    /**
     * Confirm an observation (as observee).
     */
    public function confirm(Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if (!$observation->canConfirm()) {
            return back()->with('error', 'This observation cannot be confirmed at this time.');
        }

        $observation->confirm();

        app(AuditLogService::class)->log(
            'confirmed', 'observations', (string) $observation->getKey(),
            "School head confirmed observation #{$observation->getKey()}",
            'success', [], $observation->toArray()
        );

        $observer = $observation->observer;
        if ($observer) {
            $link = route('school-head.observations.show', $observation);
            $this->notificationService->notifyObservationConfirmed($observer, Auth::user()->name, $link);
        }

        return back()->with('success', 'You have confirmed the observation schedule.');
    }

    /**
     * Reject an observation (as observee).
     */
    public function reject(Request $request, Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if (!$observation->canConfirm()) {
            return back()->with('error', 'This observation cannot be rejected at this time.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'in:scheduling_conflict,health_concern,insufficient_preparation,other'],
            'rejection_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $observation->reject($validated['rejection_reason'], $validated['rejection_notes'] ?? null);

        app(AuditLogService::class)->log(
            'rejected', 'observations', (string) $observation->getKey(),
            "School head rejected observation #{$observation->getKey()}: {$validated['rejection_reason']}",
            'success', [], $observation->toArray()
        );

        return back()->with('success', 'You have rejected the observation schedule.');
    }

    /**
     * Upload plan (as observee).
     */
    public function uploadPlan(Request $request, Observation $observation)
    {
        $schoolHead = Auth::user()->schoolHeadProfile;

        if ($observation->observee_id !== $schoolHead?->id || $observation->observee_type !== SchoolHeadProfile::class) {
            abort(403);
        }

        if ($observation->stage !== 'pre_observation_planning') {
            return back()->with('error', 'Plan can only be uploaded during the Pre-Observation Planning stage.');
        }

        $validated = $request->validate([
            'plan_file' => ['required', 'file', 'mimes:pdf,doc,docx,pptx,xlsx', 'max:20480'],
            'plan_type' => ['required', 'string', 'in:leadership_plan,lesson_plan,other'],
        ]);

        $file = $request->file('plan_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filePath = $file->storeAs('leadership_plans', $filename, 'public');

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'lesson_plan_file' => $filePath,
                'lesson_plan_notes' => $validated['plan_type'],
            ]
        );

        app(AuditLogService::class)->log(
            'plan_uploaded', 'observations', (string) $observation->getKey(),
            "School head uploaded plan for observation #{$observation->getKey()}",
            'success', [], $observation->toArray()
        );

        return back()->with('success', 'Plan uploaded successfully.');
    }

    /**
     * Authorize that the user can access the observation.
     */
    private function authorizeObservation(Observation $observation)
    {
        if ($observation->observer_id !== Auth::id()
            && $observation->observee_id !== Auth::user()->schoolHeadProfile?->id
            && $observation->school_head_id !== Auth::id()) {
            abort(403, 'You are not authorized to access this observation.');
        }
    }

    private function getCurrentSchoolYear(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($currentMonth >= 6) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

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

    private function buildObservationScheduledEmail(string $observeeName, string $observerName, string $date, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #1e40af; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Classroom Observation Scheduled</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    Hello <strong>{$observeeName}</strong>,
                                </p>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$observerName}</strong> has scheduled a classroom observation for you:
                                </p>
                                <table style='background-color: #eff6ff; border-left: 4px solid #1e40af; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Observation Date:</strong> {$date}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Tool:</strong> Classroom Observation Tool (COT)</td></tr>
                                </table>
                                <p style='color: #374151; font-size: 14px; line-height: 1.6;'>Please prepare accordingly. You may upload your lesson plan through the system.</p>
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

    private function buildObservationCancelledEmail(string $observeeName, string $observerName, string $date, string $reason, string $link): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #dc2626; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Observation Cancelled</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    Hello <strong>{$observeeName}</strong>,
                                </p>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    Your observation scheduled for <strong>{$date}</strong> has been <strong>cancelled</strong> by {$observerName}.
                                </p>
                                <table style='background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Reason:</strong> {$reason}</td></tr>
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

    private function buildLessonPlanRequestedEmail(string $requesterName, Observation $observation, string $observationLink): string
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
}
