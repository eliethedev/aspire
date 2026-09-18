<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\School;
use App\Services\CalendarEventService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request, CalendarEventService $calendar)
    {
        $today = now()->toDateString();
        $weekEnd = now()->addDays(7)->toDateString();

        $total = Observation::count();
        $completed = Observation::whereIn('status', ['completed', 'cot_completed'])
            ->orWhere('stage', 'post_conference')
            ->count();

        $stats = [
            'today' => Observation::whereDate('observation_date', $today)
                ->whereNotIn('status', ['cancelled'])
                ->count(),
            'this_week' => Observation::whereBetween('observation_date', [$today, $weekEnd])
                ->whereNotIn('status', ['cancelled'])
                ->count(),
            'pending' => Observation::where('stage', '!=', 'post_conference')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];

        // Cycles needing admin attention: overdue or awaiting teacher confirmation.
        $attentionBase = Observation::with(['observee', 'teacher.user', 'school'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where(function ($q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->where('stage', '!=', 'post_conference')
                        ->whereDate('observation_date', '<', $today);
                })->orWhere(function ($sub) use ($today) {
                    $sub->where('confirmation_status', 'pending')
                        ->whereDate('observation_date', '>=', $today);
                });
            });

        $attentionCount = (clone $attentionBase)->count();

        $attention = $attentionBase
            ->orderBy('observation_date')
            ->take(8)
            ->get()
            ->map(fn ($obs) => [
                'id' => $obs->id,
                'observee_name' => $obs->observee?->user?->name ?? $obs->teacher?->user?->name ?? 'Unknown',
                'school_name' => $obs->school?->name ?? 'No school',
                'observation_date' => $obs->observation_date?->toDateString(),
                'observation_date_label' => $obs->observation_date?->format('M d, Y') ?? 'No date',
                'stage_label' => ucwords(str_replace('_', ' ', $obs->stage ?? 'pending')),
                'reason' => $obs->observation_date && $obs->observation_date->toDateString() < $today
                    ? 'Overdue'
                    : 'Awaiting confirmation',
                'link' => route('admin.observations.show', $obs),
            ])
            ->all();

        // Upcoming load per school (next 30 days, actionable cycles only).
        $schoolLoad = Observation::selectRaw('school_id, COUNT(*) as total')
            ->whereBetween('observation_date', [$today, now()->addDays(30)->toDateString()])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('school_id')
            ->groupBy('school_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $schoolNames = School::whereIn('id', $schoolLoad->pluck('school_id'))->pluck('name', 'id');

        $schoolBreakdown = $schoolLoad->map(fn ($row) => [
            'name' => $schoolNames[$row->school_id] ?? 'Unknown school',
            'total' => (int) $row->total,
        ])->all();

        $schools = School::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.calendar', [
            'events' => $calendar->forAdmin(),
            'stats' => $stats,
            'attention' => $attention,
            'attentionCount' => $attentionCount,
            'schoolBreakdown' => $schoolBreakdown,
            'schools' => $schools,
        ]);
    }
}
