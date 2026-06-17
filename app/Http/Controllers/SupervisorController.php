<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Observation;
use App\Models\User;
use App\Models\PreObservationPlanning;
use App\Models\PreConference;
use App\Models\PostConference;
use App\Models\CotRating;
use App\Models\SchoolHeadProfile;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use App\Services\AIFeedbackService;
use App\Services\AISuggestionService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupervisorController extends Controller
{
    protected NotificationService $notificationService;
    protected PHPMailerService $mailerService;
    protected AIFeedbackService $aiFeedback;
    protected AISuggestionService $aiSuggestions;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailerService, AIFeedbackService $aiFeedback, AISuggestionService $aiSuggestions)
    {
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->aiFeedback = $aiFeedback;
        $this->aiSuggestions = $aiSuggestions;
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
            'total_teachers' => Teacher::whereHas('user', fn($q) => $q->where('school_id', $user->school_id))->count(),
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

        $cotScores = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->pluck('overall_score')
            ->toArray();

        $cotLabels = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->get()
            ->map(fn($o, $i) => 'Obs ' . ($i + 1))
            ->toArray();

        $prevAvg = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->take(max(count($cotScores) - 1, 1))
            ->avg('overall_score');

        $trend = $prevAvg ? ($stats['average_score'] - round($prevAvg, 2)) : 0;

        return view('supervisor.dashboard', compact(
            'stats', 'recentObservations', 'cotScores', 'cotLabels', 'trend'
        ));
    }

    /**
     * Display list of teachers supervised by the current supervisor.
     */
    public function teachers(Request $request)
    {
        $user = Auth::user();
        
        // Get teachers from the same school as the supervisor
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
            ->paginate($request->per_page ?? 15);

        return view('supervisor.teachers.index', compact('teachers'));
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

        $teacher->load(['user', 'school']);

        $observations = Observation::with(['preObservationPlanning', 'preConference', 'postConference', 'cotRatings'])
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

        return view('supervisor.teachers.show', compact('teacher', 'observations', 'stats'));
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
        $observations = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning'])
            ->latest()
            ->paginate(10);

        return view('supervisor.teachers.school-head-observations', compact('schoolHead', 'observations'));
    }

    /**
     * Show the form for creating a new observation.
     */
    public function createObservation()
    {
        $user = Auth::user();
        
        // Get teachers from the same school (using teacher's school_id directly)
        $teachers = Teacher::query()
            ->with(['user'])
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
                'subject' => $teacher->subject ?? 'Not set',
                'grade_level' => $teacher->grade_level ?? 'Not set',
                'department' => $teacher->department ?? 'Not set',
                'position' => $teacher->position ?? 'Teacher',
                'employee_number' => $teacher->employee_number ?? '—',
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
                'name' => $schoolHead->user->name,
                'email' => $schoolHead->user->email,
                'subject' => $schoolHead->subject ?? 'Not set',
                'grade_level' => $schoolHead->grade_level ?? 'Not set',
                'position' => $schoolHead->position ?? $schoolHead->current_designation ?? 'School Head',
                'position_level' => $schoolHead->position_level ?? '—',
            ];
        })->values();

        return view('supervisor.observations.create', compact('teacherData', 'schoolHeadData'));
    }

    /**
     * Store a newly created observation.
     */
    public function storeObservation(Request $request)
    {
        $validated = $request->validate([
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
        ]);

        // Determine observee type and ID based on observation type
        $observeeType = $validated['observation_type'] === 'teacher_observation' 
            ? Teacher::class 
            : SchoolHeadProfile::class;

        // Determine status based on schedule type
        $status = $validated['schedule_type'] === 'scheduled' ? 'scheduled' : 'in_progress';
        $stage = $validated['schedule_type'] === 'scheduled' ? 'pre_observation_planning' : 'observation';

        $observation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $validated['observee_id'],
            'observee_type' => $observeeType,
            'observation_type' => $validated['observation_type'],
            'observation_date' => $validated['observation_date'],
            'stage' => $stage,
            'notes' => $validated['notes'] ?? null,
            'status' => $status,
            'school_year' => $validated['school_year'] ?? $this->getCurrentSchoolYear(),
            'quarter' => $validated['quarter'] ?? $this->getCurrentQuarter(),
            'observation_number' => $validated['observation_number'] ?? 1,
            'subject' => $validated['subject'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'observation_mode' => $validated['observation_mode'] ?? 'in_person',
        ]);

        // Send notification if scheduled
        if ($status === 'scheduled') {
            $observee = $observation->observee;
            if ($observee && $observee->user) {
                $observeeUser = $observee->user;
                $formattedDate = $observation->observation_date?->format('M d, Y') ?? 'No date';
                $observationLink = match(true) {
                    $observee instanceof \App\Models\Teacher => route('teacher.observations.show', $observation->id),
                    $observee instanceof \App\Models\SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                    default => route('supervisor.observations.show', $observation->id),
                };

                // In-app notification
                $this->notificationService->notifyObservationScheduled(
                    $observeeUser,
                    $formattedDate,
                    $observationLink
                );

                // Email notification
                $observerName = Auth::user()->name;
                $subject = 'ASPIRE - Classroom Observation Scheduled';
                $emailBody = $this->buildObservationScheduledEmail($observeeUser->name, $observerName, $formattedDate, $observation->observation_type, $observationLink);
                $this->mailerService->sendGenericEmail($observeeUser->email, $observeeUser->name, $subject, $emailBody);
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
     * Get current school year.
     */
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

    /**
     * Get current quarter.
     */
    private function getCurrentQuarter(): int
    {
        $currentMonth = now()->month;
        
        if ($currentMonth >= 6 && $currentMonth <= 8) {
            return 1;
        } elseif ($currentMonth >= 9 && $currentMonth <= 11) {
            return 2;
        } elseif ($currentMonth >= 12 || $currentMonth <= 2) {
            return 3;
        } else {
            return 4;
        }
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
            $prevRatings = \App\Models\CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn($r) => $r->avg_rating < 3)->values();
        }

        $preConference = $observation->preConference;

        return view('supervisor.observations.pre-observation-planning', compact(
            'observation', 'planning', 'previousObservations', 'prevStrengths', 'prevWeaknesses', 'preConference'
        ));
    }

    /**
     * Store Pre-Observation Planning data
     */
    public function storePreObservationPlanning(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'lesson_plan_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,pptx,xlsx'],
            'ai_insights' => ['nullable', 'string'],
            'suggested_focus' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
            'observation_tool' => ['nullable', 'string', 'in:ppst,classroom_observation_tool,tisuyon'],
        ]);

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $file = $request->file('lesson_plan_file');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filePath = $file->storeAs('lesson_plans', $filename, 'public');
        }

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'lesson_plan_file' => $filePath ?? $observation->preObservationPlanning?->lesson_plan_file,
                'ai_insights' => $validated['ai_insights'] ?? null,
                'suggested_focus' => $validated['suggested_focus'] ?? null,
                'supervisor_notes' => $validated['supervisor_notes'] ?? null,
                'observation_tool' => $validated['observation_tool'] ?? null,
            ]
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
        if (!$teacherUser) {
            return redirect()->back()->with('error', 'Teacher not found for this observation.');
        }

        $requester = Auth::user();
        $requesterName = $requester->name;
        $observationLink = route('teacher.observations.show', $observation);

        // Notify the teacher in-app
        $this->notificationService->notifyLessonPlanRequested($teacherUser, $requesterName, $observationLink);

        // Send email to the teacher
        $subject = "Lesson Plan Requested – {$observation->subject}";
        $this->mailerService->sendGenericEmail(
            $teacherUser->email,
            $teacherUser->name,
            $subject,
            $this->buildLessonPlanRequestedEmail($requesterName, $observation, $observationLink)
        );

        // Notify the school head(s) of the teacher's school
        $school = $observation->observee?->school;
        if ($school) {
            $schoolHeads = $school->users()->where('role', 'school_head')->get();
            foreach ($schoolHeads as $schoolHead) {
                $schoolHeadLink = route('supervisor.observations.show', $observation);
                $this->notificationService->notifyLessonPlanUploaded($schoolHead, $teacherUser->name, $schoolHeadLink);
                $this->mailerService->sendGenericEmail(
                    $schoolHead->email, $schoolHead->name, $subject,
                    $this->buildLessonPlanRequestedEmail($requesterName, $observation, $schoolHeadLink)
                );
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
            $prevRatings = \App\Models\CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn($r) => $r->avg_rating < 3)->values();
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

        $validated = $request->validate([
            'discussion_notes' => ['nullable', 'string'],
            'finalized_focus' => ['nullable', 'string'],
            'conference_date' => ['nullable', 'date'],
            'teacher_reflection' => ['nullable', 'string'],
            'lesson_plan_review' => ['nullable', 'string'],
            'instructional_materials' => ['nullable', 'string'],
            'ai_insights_reviewed' => ['nullable', 'boolean'],
        ]);

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'discussion_notes' => $validated['discussion_notes'] ?? null,
                'finalized_focus' => $validated['finalized_focus'] ?? null,
                'conference_date' => $validated['conference_date'] ?? now(),
                'teacher_reflection' => $validated['teacher_reflection'] ?? null,
                'lesson_plan_review' => $validated['lesson_plan_review'] ?? null,
                'instructional_materials' => $validated['instructional_materials'] ?? null,
            ]
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
        $cotVersion = config("cot.versions.{$schoolYear}", config('cot.versions.' . config('cot.default_version')));
        $cotIndicators = $cotVersion['indicators'] ?? [];
        $ratingScale = config('cot.rating_scale', []);
        $ratingScaleCss = config('cot.rating_scale_css', []);
        $existingSuggestions = $observation->preObservationPlanning?->ai_insights;

        return view('supervisor.observations.observation', compact(
            'observation', 'cotRatings', 'preConference',
            'cotIndicators', 'ratingScale', 'ratingScaleCss',
            'existingSuggestions', 'schoolYear'
        ));
    }

    /**
     * Store Observation data (COT Ratings)
     */
    public function storeObservationData(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*.indicator_code' => ['required', 'string'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['nullable', 'integer', 'in:2,3,4,5,6'],
            'ratings.*.not_observed' => ['nullable', 'boolean'],
            'ratings.*.has_rating' => ['nullable', 'string'],
            'ratings.*.comments' => ['nullable', 'string'],
            'other_comments' => ['nullable', 'string'],
            'star_notes' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ]);

        // Delete existing ratings
        $observation->cotRatings()->delete();

        // Create new ratings
        $createdRatings = [];
        foreach ($validated['ratings'] as $item) {
            $createdRatings[] = CotRating::create([
                'observation_id' => $observation->id,
                'indicator_code' => $item['indicator_code'],
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => !empty($item['not_observed']) ? null : ($item['rating'] ?? null),
                'not_observed' => !empty($item['not_observed']),
                'comments' => $item['comments'] ?? null,
            ]);
        }

        // Generate AI feedback for each rating (runs synchronously)
        foreach ($createdRatings as $cotRating) {
            $this->aiFeedback->generateFeedback($cotRating->id);
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
            'notes' => $otherComments ? ($observation->notes ? $observation->notes . "\n\n" . $otherComments : $otherComments) : $observation->notes,
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

        // Calculate overall score (average of numeric ratings excluding NO)
        $rated = $observation->cotRatings()->where('not_observed', false)->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;

        // Only advance stage forward (prevent regression)
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('post_conference', $stageOrder);

        $updates = ['overall_score' => $avgRating, 'status' => 'cot_completed'];
        if ($targetIdx === $currentIdx + 1) {
            $updates['stage'] = 'post_conference';
            $observation->logChange([
                'to_stage' => 'post_conference',
                'to_status' => 'cot_completed',
                'notes' => 'COT Ratings completed',
            ]);
        } else {
            $observation->logChange([
                'to_status' => 'cot_completed',
                'notes' => 'COT Ratings updated',
            ]);
        }
        $observation->update($updates);

        // Auto-trigger AI post-observation analysis
        $observation->loadMissing(['postConference', 'preObservationPlanning', 'observee']);
        $this->aiFeedback->generatePostConferenceComparison($observation);

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match(true) {
                $observee instanceof \App\Models\Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof \App\Models\SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved. AI analysis has been generated.');
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

        $validated = $request->validate([
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
        ]);

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
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
            ]
        );

        $observation->logChange([
            'to_status' => 'completed',
            'notes' => 'Post-Conference completed',
        ]);
        $observation->update(['status' => 'completed']);

        // Notify the observee about feedback
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match(true) {
                $observee instanceof \App\Models\Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof \App\Models\SchoolHeadProfile => route('school-head.observations.show', $observation->id),
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
            'cancelledBy',
        ]);

        return view('supervisor.observations.show', compact('observation'));
    }

    /**
     * Show the cancellation form for an observation.
     */
    public function showCancelForm(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->canCancel()) {
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

        if (!$observation->canCancel()) {
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

        $observation->cancel($reason, $validated['internal_note']);

        // Notify the observee
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $observeeUser = $observee->user;
            $observationLink = match(true) {
                $observee instanceof \App\Models\Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof \App\Models\SchoolHeadProfile => route('school-head.observations.show', $observation->id),
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
            $this->mailerService->sendGenericEmail($observeeUser->email, $observeeUser->name, $subject, $emailBody);
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
            ->with(['observee.user'])
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
     * Display reports dashboard.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        // Get statistics
        $stats = [
            'total_teachers' => Teacher::whereHas('user', function ($query) use ($user) {
                $query->where('school_id', $user->school_id);
            })->count(),
            'total_observations' => Observation::where('observer_id', $user->id)->count(),
            'completed_observations' => Observation::where('observer_id', $user->id)
                ->where('status', 'completed')->count(),
            'pending_observations' => Observation::where('observer_id', $user->id)
                ->where('status', 'pending')->count(),
        ];

        // Get recent observations
        $recentObservations = Observation::with(['observee'])
            ->where('observer_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Chart data - observations by month
        $chartData = Observation::where('observer_id', $user->id)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Scores trend
        $scoresData = Observation::where('observer_id', $user->id)
            ->whereNotNull('overall_score')
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return view('supervisor.reports.index', compact('stats', 'recentObservations', 'chartData', 'scoresData'));
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

        $filename = 'observations-report-' . now()->format('Y-m-d') . '.csv';
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

        if (!$observeeType) {
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

    private function buildObservationScheduledEmail(string $observeeName, string $observerName, string $date, string $observationType, string $link): string
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

        $insights = $this->aiFeedback->generatePreObservationInsights($observation);

        if ($insights === null) {
            return response()->json(['error' => 'Failed to generate insights. Try again later.'], 500);
        }

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => $insights]
        );

        $source = $this->aiFeedback->isGeminiConfigured() ? 'gemini' : 'rule-based';
        return response()->json(['ai_insights' => $insights, 'source' => $source]);
    }

    public function clearAiInsights(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => null]
        );

        return response()->json(['success' => true]);
    }

    public function generateAiSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        if (!$this->aiFeedback->isGeminiConfigured()) {
            return response()->json(['error' => 'Gemini API is not configured.'], 400);
        }

        $planning = $observation->preObservationPlanning;
        $teacherName = $observation->observee?->name ?? 'Unknown';
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $objective = $planning?->objective ?? 'Not specified';
        $strategies = $planning?->teaching_strategies ?? 'Not specified';
        $materials = $planning?->materials ?? 'Not specified';
        $assessment = $planning?->assessment_methods ?? 'Not specified';

        $lessonPlanContent = '';
        $lessonPlanFile = $planning?->lesson_plan_file;
        if ($lessonPlanFile && \Illuminate\Support\Facades\Storage::disk('public')->exists($lessonPlanFile)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($lessonPlanFile);
            $lessonPlanContent = app(\App\AI\Services\DocumentExtractorService::class)->extractText($fullPath);
        }

        $lessonPlanSection = $lessonPlanContent
            ? "--- Lesson Plan Content ---\n{$lessonPlanContent}\n\n"
            : '';

        $prompt = <<<PROMPT
You are an expert instructional coach. Based on the pre-observation data below, generate two concise text blocks for a pre-conference form.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Objective: {$objective}
Teaching Strategies: {$strategies}
Materials: {$materials}
Assessment Methods: {$assessment}

{$lessonPlanSection}Return JSON with exactly two keys:

1. "discussion_notes" — 3-4 bullet points covering key discussion topics: teaching strategies, learner diversity, assessment methods, and any support the teacher may need.

2. "finalized_focus" — 2-3 concise focus areas agreed upon for the classroom observation, based on the objective and strategies.

Keep both concise and actionable. No preamble.
PROMPT;

        $data = app(GeminiService::class)->generateJson($prompt, [
            'temperature' => 0.3,
            'max_output_tokens' => 1024,
        ]);

        if (!$data) {
            return response()->json(['error' => 'Failed to generate suggestions.'], 500);
        }

        return response()->json([
            'discussion_notes' => $data['discussion_notes'] ?? '',
            'finalized_focus' => $data['finalized_focus'] ?? '',
        ]);
    }

    public function generateAiComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load(['postConference', 'preObservationPlanning', 'observee']);

        if (!$this->aiFeedback->isGeminiConfigured()) {
            return response()->json(['error' => 'Gemini API is not configured. Set GEMINI_API_KEY in .env'], 400);
        }

        $comparison = $this->aiFeedback->generatePostConferenceComparison($observation);

        if (!$comparison) {
            $message = 'Failed to generate AI comparison. ';
            if (!$this->aiFeedback->isGeminiConfigured()) {
                $message .= 'Gemini API key is not set.';
            } else {
                $message .= 'The Gemini API quota may be exceeded or the service is unreachable. Check storage/logs/laravel.log for details.';
            }
            return response()->json(['error' => $message], 500);
        }

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_comparison' => $comparison]
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

        return response()->json(['suggestions' => $suggestions]);
    }
}
