<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Services\CareerProgressionService;
use App\Services\RateeProfileService;
use App\Services\TeacherAttentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * Display list of teachers supervised by the current supervisor.
     */
    public function teachers(Request $request)
    {
        $user = Auth::user();

        // Schools available for browsing. Supervisors default to their own
        // school but may browse teachers from any school via the filter.
        $schools = School::orderBy('name')->get(['id', 'name']);
        $schoolIds = $schools->pluck('id')->all();

        $defaultSchoolId = $user->school_id ?? 'all';
        $schoolParam = $request->input('school_id', $defaultSchoolId);

        $selectedSchoolId = null; // null = all schools
        if (is_numeric($schoolParam) && in_array((int) $schoolParam, $schoolIds, true)) {
            $selectedSchoolId = (int) $schoolParam;
        } elseif ($schoolParam === 'all' || $schoolParam === '' || $schoolParam === null) {
            $selectedSchoolId = null;
        } elseif ($defaultSchoolId !== 'all' && in_array((int) $defaultSchoolId, $schoolIds, true)) {
            $selectedSchoolId = (int) $defaultSchoolId;
        }

        // Attention diagnostics across every browsable school, then narrowed
        // to the selected scope so flags, counts and the "needs" filter agree.
        $allAttention = app(TeacherAttentionService::class)->forSchools($schoolIds);
        $attention = collect($allAttention)
            ->filter(fn ($row) => $selectedSchoolId === null
                || ($row['teacher']->user?->school_id) === $selectedSchoolId
                || ($row['teacher']->school_id) === $selectedSchoolId)
            ->all();

        $order = ['high' => 0, 'medium' => 1, 'low' => 2];
        $needsAttentionCount = collect($attention)->filter(fn ($row) => $row['level'] !== 'ok')->count();

        // Per-school totals for the school filter dropdown.
        $schoolStats = [];
        foreach ($schoolIds as $id) {
            $schoolStats[$id] = ['total' => 0, 'needs' => 0];
        }
        foreach ($allAttention as $row) {
            $sid = $row['teacher']->user?->school_id ?? $row['teacher']->school_id;
            if ($sid === null || ! isset($schoolStats[$sid])) {
                continue;
            }
            $schoolStats[$sid]['total']++;
            if ($row['level'] !== 'ok') {
                $schoolStats[$sid]['needs']++;
            }
        }

        $attentionFilter = $request->get('attention');

        // Get teachers in the selected school scope (own school by default).
        $baseQuery = Teacher::query()
            ->with(['user', 'school', 'subjects'])
            ->withCount('observations')
            ->when($selectedSchoolId !== null, function ($query) use ($selectedSchoolId) {
                $query->where(function ($q) use ($selectedSchoolId) {
                    $q->where('school_id', $selectedSchoolId)
                        ->orWhereHas('user', fn ($uq) => $uq->where('school_id', $selectedSchoolId));
                });
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        // Scope total ignoring the attention filter (used by the "All" tab).
        $totalCount = (clone $baseQuery)->count();

        $filteredBase = (clone $baseQuery)
            ->when($attentionFilter === 'needs', function ($query) use ($attention) {
                $ids = collect($attention)->filter(fn ($row) => $row['level'] !== 'ok')->keys();
                $query->whereIn('teachers.id', $ids->isNotEmpty() ? $ids->all() : [0]);
            });

        // Attention severity lives in PHP (TeacherAttentionService), so order
        // IDs in memory BEFORE paginating. Paginating first would only sort
        // the current page and break global ordering.
        $perPage = (int) ($request->per_page ?? 15) ?: 15;
        $page = max((int) $request->input('page', 1), 1);

        $orderedIds = $filteredBase->with(['user'])
            ->get(['teachers.*'])
            ->map(function (Teacher $teacher) use ($attention, $order) {
                $row = $attention[$teacher->id] ?? ['level' => 'ok'];

                return [
                    'id' => $teacher->id,
                    'order' => $order[$row['level']] ?? 3,
                    'name' => strtolower($teacher->user?->name ?? ''),
                ];
            })
            ->sort(fn ($a, $b) => [$a['order'], $a['name']] <=> [$b['order'], $b['name']])
            ->pluck('id')
            ->values();

        $total = $orderedIds->count();
        $pageIds = $orderedIds->forPage($page, $perPage)->values();

        $pageModels = $pageIds->isNotEmpty()
            ? Teacher::query()
                ->with(['user', 'school', 'subjects'])
                ->withCount('observations')
                ->whereIn('id', $pageIds->all())
                ->get()
                ->sortBy(fn (Teacher $t) => array_search($t->id, $pageIds->all()))
                ->values()
            : collect();

        // Enrich each teacher with its attention flags and summary.
        $pageModels->transform(function (Teacher $teacher) use ($attention, $order) {
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

        $teachers = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageModels,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('supervisor.teachers.index', compact(
            'teachers', 'needsAttentionCount', 'totalCount', 'order',
            'schools', 'schoolStats', 'selectedSchoolId'
        ));
    }

    /**
     * Display a teacher's profile with their details and recent observations.
     */
    public function teacherProfile(Teacher $teacher)
    {
        $user = Auth::user();

        $teacher->load(['user', 'school', 'subjects']);

        // Supervisors may browse (read-only) teachers from other schools via
        // the school filter. Write actions stay limited to their own school.
        $teacherSchoolId = $teacher->user?->school_id ?? $teacher->school_id;
        $isOwnSchool = ! $user->school_id || $teacherSchoolId === $user->school_id;

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
                ->whereIn('stage', ['pre_observation_planning', 'observation'])
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

        $attention = $teacherSchoolId
            ? app(\App\Services\TeacherAttentionService::class)->forSchool($teacherSchoolId)
            : [];
        $teacherAttention = $attention[$teacher->id] ?? ['flags' => [], 'level' => 'ok', 'summary' => 'On track'];

        return view('supervisor.teachers.show', compact('teacher', 'observations', 'stats', 'rateeProfile', 'teacherAttention', 'isOwnSchool'))
            ->with('careerContext', $careerContext)
            ->with('careerNextStages', $careerService->nextStageOptions($careerContext['career_stage']))
            ->with('careerEvidence', $careerService->evidenceFor($teacher))
            ->with('careerReadiness', $careerService->readinessFor($teacher))
            ->with('careerRoute', route('supervisor.teachers.career-assessment', $teacher))
            ->with('canAssess', $isOwnSchool)
            ->with('canEditAssessment', $isOwnSchool);
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
}
