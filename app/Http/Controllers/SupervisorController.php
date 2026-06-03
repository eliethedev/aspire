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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupervisorController extends Controller
{
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

        // Get school heads (get all school heads for now, can filter by division/district later)
        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user', 'school'])
            ->get();

        // Prepare teacher data for JavaScript
        $teacherData = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'subject' => $teacher->subject ?? null,
                'grade_level' => $teacher->grade_level ?? null
            ];
        });

        // Prepare school head data for JavaScript
        $schoolHeadData = $schoolHeads->map(function ($schoolHead) {
            return [
                'id' => $schoolHead->id,
                'name' => $schoolHead->user->name,
                'subject' => $schoolHead->subject ?? null,
                'grade_level' => $schoolHead->grade_level ?? null
            ];
        });

        return view('supervisor.observations.create', compact('teachers', 'schoolHeads', 'teacherData', 'schoolHeadData'));
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
            // TODO: Send notification to observee
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

        $planning = $observation->preObservationPlanning;

        return view('supervisor.observations.pre-observation-planning', compact('observation', 'planning'));
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
            ]
        );

        $observation->update(['stage' => 'pre_conference']);

        return redirect()->route('supervisor.observations.preConference', $observation->id)
            ->with('success', 'Pre-Observation Planning has been saved.');
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
        ]);

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'discussion_notes' => $validated['discussion_notes'] ?? null,
                'finalized_focus' => $validated['finalized_focus'] ?? null,
                'conference_date' => $validated['conference_date'] ?? now(),
            ]
        );

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

        // Calculate overall score
        $avgRating = $observation->cotRatings()->avg('rating');
        $observation->update([
            'overall_score' => $avgRating,
            'stage' => 'post_conference'
        ]);

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
        ]);

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            [
                'ai_comparison' => $validated['ai_comparison'] ?? null,
                'feedback' => $validated['feedback'] ?? null,
                'conference_date' => $validated['conference_date'] ?? now(),
            ]
        );

        $observation->update(['status' => 'completed']);

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
        
        $observations = Observation::query()
            ->with(['observee'])
            ->where('observer_id', $user->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->stage, function ($query, $stage) {
                $query->where('stage', $stage);
            })
            ->when($request->observation_type, function ($query, $type) {
                $query->where('observation_type', $type);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return view('supervisor.observations.index', compact('observations'));
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

        return view('supervisor.reports.index', compact('stats', 'recentObservations'));
    }
}
