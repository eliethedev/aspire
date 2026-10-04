<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CoachingAgreement;
use App\Models\CotRating;
use App\Models\Observation;
use App\Enums\NotificationType;
use App\Services\CoachingFocusSuggestionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachingAgreementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();

        $agreements = CoachingAgreement::with(['observation', 'teacher.user'])
            ->where('supervisor_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('supervisor.coaching.index', compact('agreements'));
    }

    public function create(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if ($observation->observee_type !== 'App\Models\Teacher') {
            abort(400, 'Coaching agreements are only available for teacher observations.');
        }

        $observation->load([
            'observee.user',
            'postConference',
            'cotRatings',
            'aiFeedbacks' => function ($q) {
                $q->where('status', 'published')->latest();
            },
        ]);

        $teacherId = $observation->observee_id;

        // Past observations of this teacher with a recorded COT score.
        $pastObservations = Observation::with('observer')
            ->where('observee_type', 'App\Models\Teacher')
            ->where('observee_id', $teacherId)
            ->where('id', '!=', $observation->id)
            ->whereNotNull('overall_score')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('observation_date')
            ->limit(6)
            ->get();

        // Indicators that were rated weak (<=3 or Not Observed) across those past observations.
        $weakIndicators = CotRating::query()
            ->whereIn('observation_id', $pastObservations->pluck('id'))
            ->where(function ($q) {
                $q->where('rating', '<=', 3)->orWhere('not_observed', true);
            })
            ->get()
            ->groupBy('indicator_code')
            ->map(fn ($group) => [
                'indicator' => $group->first()->indicator,
                'domain' => $group->first()->domain,
                'count' => $group->count(),
                'avg_rating' => round($group->avg(fn ($r) => $r->not_observed ? 0 : ($r->rating ?? 0)), 1),
                'not_observed_count' => $group->where('not_observed', true)->count(),
            ])
            ->sortByDesc('count')
            ->take(4)
            ->values();

        // Previous coaching agreements for this teacher.
        $pastAgreements = CoachingAgreement::where('teacher_id', $teacherId)
            ->where('observation_id', '!=', $observation->id)
            ->latest()
            ->take(3)
            ->get();

        $suggestedFocusAreas = app(CoachingFocusSuggestionService::class)->suggest($observation);

        return view('supervisor.coaching.create', compact(
            'observation',
            'suggestedFocusAreas',
            'pastObservations',
            'weakIndicators',
            'pastAgreements'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'observation_id' => 'required|exists:observations,id',
            'focus_areas' => 'nullable|array',
            'focus_areas.*' => 'nullable|string|max:500',
            'action_steps' => 'nullable|array',
            'action_steps.*' => 'nullable|string|max:500',
            'resources_needed' => 'nullable|string|max:2000',
            'success_indicators' => 'nullable|string|max:2000',
            'timeline' => 'nullable|string|max:500',
            'supervisor_notes' => 'nullable|string|max:2000',
        ]);

        $observation = Observation::findOrFail($data['observation_id']);

        $data['teacher_id'] = $observation->observee_id;
        $data['supervisor_id'] = Auth::id();
        $data['status'] = 'draft';
        // Drop blank dynamic rows (the form always submits at least one
        // empty row, which the empty-string middleware turns into null).
        $data['focus_areas'] = $this->cleanListInput($data['focus_areas'] ?? null);
        $data['action_steps'] = $this->cleanListInput($data['action_steps'] ?? null);

        $agreement = CoachingAgreement::create($data);

        $teacherUser = $observation->observee?->user;
        if ($teacherUser) {
            $this->notificationService->notify(
                $teacherUser,
                NotificationType::PROFESSIONAL_DEVELOPMENT,
                'New improvement plan needs your review',
                'Your supervisor created an improvement plan for your ' . ($observation->subject ?? 'recent') . ' observation. Please review and sign it.',
                null,
                route('teacher.coaching.show', $agreement),
            );
        }

        return redirect()->route('supervisor.coaching.show', $agreement)
            ->with('success', 'Coaching agreement created successfully.');
    }

    public function show(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        $agreement->load(['observation.observee.user', 'observation.observer', 'teacher.user', 'supervisor']);

        return view('supervisor.coaching.show', compact('agreement'));
    }

    public function edit(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        if ($agreement->isCompleted()) {
            return back()->with('error', 'Cannot edit a completed agreement.');
        }

        $agreement->load(['observation.observee.user']);

        return view('supervisor.coaching.edit', compact('agreement'));
    }

    public function update(Request $request, CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        if ($agreement->isCompleted()) {
            return back()->with('error', 'Cannot edit a completed agreement.');
        }

        $data = $request->validate([
            'focus_areas' => 'nullable|array',
            'focus_areas.*' => 'nullable|string|max:500',
            'action_steps' => 'nullable|array',
            'action_steps.*' => 'nullable|string|max:500',
            'resources_needed' => 'nullable|string|max:2000',
            'success_indicators' => 'nullable|string|max:2000',
            'timeline' => 'nullable|string|max:500',
            'supervisor_notes' => 'nullable|string|max:2000',
        ]);

        $data['focus_areas'] = $this->cleanListInput($data['focus_areas'] ?? $agreement->focus_areas);
        $data['action_steps'] = $this->cleanListInput($data['action_steps'] ?? $agreement->action_steps);

        $agreement->update($data);

        return redirect()->route('supervisor.coaching.show', $agreement)
            ->with('success', 'Coaching agreement updated.');
    }

    public function sign(CoachingAgreement $agreement, Request $request)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        if ($agreement->isCompleted()) {
            return back()->with('error', 'This agreement is already completed.');
        }

        $data = $request->validate([
            'signature' => 'required|string|max:255',
            'supervisor_notes' => 'nullable|string|max:2000',
        ]);

        $agreement->update([
            'supervisor_signature' => $data['signature'],
            'supervisor_signed_at' => now(),
            'supervisor_notes' => $data['supervisor_notes'] ?? $agreement->supervisor_notes,
        ]);

        if ($agreement->isFullySigned()) {
            $agreement->update(['status' => 'active']);
        }

        $teacherUser = $agreement->teacher?->user;
        if ($teacherUser) {
            $this->notificationService->notify(
                $teacherUser,
                NotificationType::PROFESSIONAL_DEVELOPMENT,
                'Supervisor signed your improvement plan',
                'Your supervisor signed the improvement plan for your ' . ($agreement->observation->subject ?? 'recent') . ' observation.' . ($agreement->isFullySigned() ? ' The plan is now active.' : ''),
                null,
                route('teacher.coaching.show', $agreement),
            );
        }

        return redirect()->route('supervisor.coaching.show', $agreement)
            ->with('success', 'Agreement signed successfully.');
    }

    public function complete(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        if (! $agreement->canComplete()) {
            return back()->with('error', 'Only an active, fully signed plan can be marked completed.');
        }

        $agreement->complete();

        $teacherUser = $agreement->teacher?->user;
        if ($teacherUser) {
            $this->notificationService->notify(
                $teacherUser,
                NotificationType::PROFESSIONAL_DEVELOPMENT,
                'Your improvement plan is completed',
                'Your improvement plan for the ' . ($agreement->observation->subject ?? 'recent') . ' observation has been marked completed.',
                null,
                route('teacher.coaching.show', $agreement),
            );
        }

        return redirect()->route('supervisor.coaching.show', $agreement)
            ->with('success', 'Improvement plan marked as completed.');
    }

    public function reopen(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        if (! $agreement->isCompleted()) {
            return back()->with('error', 'Only a completed plan can be reopened.');
        }

        $agreement->reopen();

        $teacherUser = $agreement->teacher?->user;
        if ($teacherUser) {
            $this->notificationService->notify(
                $teacherUser,
                NotificationType::PROFESSIONAL_DEVELOPMENT,
                'Your improvement plan was reopened',
                'Your improvement plan for the ' . ($agreement->observation->subject ?? 'recent') . ' observation was reopened for further follow-up.',
                null,
                route('teacher.coaching.show', $agreement),
            );
        }

        return redirect()->route('supervisor.coaching.show', $agreement)
            ->with('success', 'Improvement plan reopened.');
    }

    public function export(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        $agreement->load(['observation.observee.user', 'observation.observer', 'teacher.user', 'supervisor']);

        $teacherName = $agreement->teacher->user->name ?? 'Teacher';
        $supervisorName = $agreement->supervisor->name ?? 'Supervisor';
        $obsDate = $agreement->observation->observation_date?->format('F d, Y') ?? 'N/A';

        $md = "# Coaching Agreement\n\n";
        $md .= "---\n\n";
        $md .= "| | |\n|---|---|\n";
        $md .= "| **Teacher** | {$teacherName} |\n";
        $md .= "| **Supervisor** | {$supervisorName} |\n";
        $md .= "| **Observation Date** | {$obsDate} |\n";
        $md .= "| **Status** | " . ucfirst($agreement->status) . " |\n";
        $md .= "| **Created** | " . $agreement->created_at->format('F d, Y') . " |\n\n";
        $md .= "---\n\n";

        if ($agreement->focus_areas) {
            $md .= "## Focus Areas\n\n";
            foreach ($agreement->focus_areas as $i => $area) {
                $md .= ($i + 1) . ". {$area}\n";
            }
            $md .= "\n";
        }

        if ($agreement->action_steps) {
            $md .= "## Action Steps\n\n";
            foreach ($agreement->action_steps as $i => $step) {
                $md .= ($i + 1) . ". {$step}\n";
            }
            $md .= "\n";
        }

        if ($agreement->resources_needed) {
            $md .= "## Resources Needed\n\n{$agreement->resources_needed}\n\n";
        }

        if ($agreement->success_indicators) {
            $md .= "## Success Indicators\n\n{$agreement->success_indicators}\n\n";
        }

        if ($agreement->timeline) {
            $md .= "## Timeline\n\n{$agreement->timeline}\n\n";
        }

        $md .= "## Signatures\n\n";
        $md .= "- **Teacher:** " . ($agreement->teacher_signed_at ? "{$agreement->teacher_signature} (" . $agreement->teacher_signed_at->format('M d, Y') . ")" : "Not yet signed") . "\n";
        $md .= "- **Supervisor:** " . ($agreement->supervisor_signed_at ? "{$agreement->supervisor_signature} (" . $agreement->supervisor_signed_at->format('M d, Y') . ")" : "Not yet signed") . "\n\n";

        $md .= "---\n\n*Generated by ASPIRE on " . now()->format('F d, Y h:i A') . "*\n";

        $filename = 'coaching-agreement-'
            . preg_replace('/[^a-z0-9]/i', '-', $teacherName)
            . '-' . ($agreement->observation->observation_date?->format('Y-m-d') ?? date('Y-m-d'))
            . '.md';

        return response()->streamDownload(function () use ($md) {
            echo $md;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=utf-8',
        ]);
    }

    public function destroy(CoachingAgreement $agreement)
    {
        $user = Auth::user();

        if ($agreement->supervisor_id !== $user->id) {
            abort(403);
        }

        $agreement->delete();

        return redirect()->route('supervisor.coaching.index')
            ->with('success', 'Coaching agreement deleted.');
    }

    /**
     * Strip blank dynamic rows (null/empty/whitespace) and reindex.
     */
    protected function cleanListInput($values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
    }

    private function authorizeObservation(Observation $observation): void
    {
        $user = Auth::user();

        if ($observation->observer_id !== $user->id) {
            abort(403);
        }
    }
}
