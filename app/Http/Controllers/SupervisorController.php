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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupervisorController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display the supervisor dashboard.
     */
    public function dashboard()
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
            'average_score' => Observation::where('observer_id', $user->id)
                ->whereNotNull('overall_score')->avg('overall_score') ?? 0,
        ];

        // Get recent observations
        $recentObservations = Observation::with(['observee'])
            ->where('observer_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('supervisor.dashboard', compact('stats', 'recentObservations'));
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
        $teacherData = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'email' => $teacher->user->email,
                'subject' => $teacher->subject ?? 'Not set',
                'grade_level' => $teacher->grade_level ?? 'Not set',
                'department' => $teacher->department ?? 'Not set',
                'position' => $teacher->position ?? 'Teacher',
                'employee_number' => $teacher->employee_number ?? '—',
            ];
        });

        // Prepare school head data for JavaScript
        $schoolHeadData = $schoolHeads->map(function ($schoolHead) {
            return [
                'id' => $schoolHead->id,
                'name' => $schoolHead->user->name,
                'email' => $schoolHead->user->email,
                'subject' => $schoolHead->subject ?? 'Not set',
                'grade_level' => $schoolHead->grade_level ?? 'Not set',
                'position' => $schoolHead->position ?? $schoolHead->current_designation ?? 'School Head',
                'position_level' => $schoolHead->position_level ?? '—',
            ];
        });

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
                $this->notificationService->notifyObservationScheduled(
                    $observee->user,
                    $observation->observation_date->format('M d, Y'),
                    route('supervisor.observations.show', $observation->id)
                );
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
            'lesson_plan_file' => ['nullable', 'file', 'mimes:pdf,doc,docx'],
            'ai_insights' => ['nullable', 'string'],
            'suggested_focus' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
            'observation_tool' => ['nullable', 'string', 'in:ppst,classroom_observation_tool,tisuyon'],
        ]);

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $filePath = $request->file('lesson_plan_file')->store('lesson_plans', 'public');
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

        $observation->logChange([
            'to_stage' => 'pre_conference',
            'notes' => 'Pre-Observation Planning completed',
        ]);
        $observation->update(['stage' => 'pre_conference']);

        if ($request->input('continue') === 'pre_conference') {
            return redirect()->route('supervisor.observations.preConference', $observation->id)
                ->with('success', 'Pre-Observation Planning has been saved. Proceed to Pre-Conference.');
        }

        return redirect()->route('supervisor.observations.preObservationPlanning', $observation->id)
            ->with('success', 'Pre-Observation Planning notes have been saved.');
    }

    /**
     * Show Pre-Conference form
     */
    public function preConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $preConference = $observation->preConference;
        $planning = $observation->preObservationPlanning;

        return view('supervisor.observations.pre-conference', compact('observation', 'preConference', 'planning'));
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

        $observation->logChange([
            'to_stage' => 'observation',
            'notes' => 'Pre-Conference completed',
        ]);
        $observation->update(['stage' => 'observation']);

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

        return view('supervisor.observations.observation', compact('observation', 'cotRatings', 'preConference'));
    }

    /**
     * Store Observation data (COT Ratings)
     */
    public function storeObservationData(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'ratings.*.comments' => ['nullable', 'string'],
        ]);

        // Delete existing ratings
        $observation->cotRatings()->delete();

        // Create new ratings
        foreach ($validated['ratings'] as $rating) {
            CotRating::create([
                'observation_id' => $observation->id,
                'domain' => $rating['domain'],
                'indicator' => $rating['indicator'],
                'rating' => $rating['rating'],
                'comments' => $rating['comments'] ?? null,
            ]);
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

        // Calculate overall score
        $avgRating = $observation->cotRatings()->avg('rating');
        $observation->logChange([
            'to_stage' => 'post_conference',
            'to_status' => 'cot_completed',
            'notes' => 'COT Ratings completed',
        ]);
        $observation->update([
            'overall_score' => $avgRating,
            'status' => 'cot_completed',
            'stage' => 'post_conference',
            'evidence_files' => $evidenceFiles,
        ]);

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notifyObservationCompleted(
                $observee->user,
                route('supervisor.observations.show', $observation->id)
            );
        }

        return redirect()->route('supervisor.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved.');
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

        return view('supervisor.observations.post-conference', compact('observation', 'postConference', 'cotRatings', 'planning'));
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
            $this->notificationService->notifyFeedbackReceived(
                $observee->user,
                route('supervisor.observations.show', $observation->id)
            );
        }

        return redirect()->route('supervisor.observations.index')
            ->with('success', 'Post-Conference has been saved. Observation is now complete.');
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
            'cotRatings'
        ]);

        return view('supervisor.observations.show', compact('observation'));
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
        $stats = [
            'total' => Observation::where('observer_id', $user->id)->count(),
            'in_progress' => Observation::where('observer_id', $user->id)->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count(),
            'completed' => Observation::where('observer_id', $user->id)->where('stage', 'post_conference')->count(),
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
}
