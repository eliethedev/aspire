<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CalendarController extends Controller
{
    /**
     * Display the calendar view for supervisor
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all observations for the current supervisor's school
        $observations = Observation::query()
            ->with(['teacher.user', 'supervisor'])
            ->where('supervisor_id', $user->id)
            ->get()
            ->map(function ($observation) {
                return [
                    'id' => $observation->id,
                    'title' => $observation->teacher->user->name,
                    'start' => $observation->observation_date->toIso8601String(),
                    'end' => $observation->observation_date->toIso8601String(),
                    'extendedProps' => [
                        'teacher_name' => $observation->teacher->user->name,
                        'teacher_id' => $observation->teacher->id,
                        'stage' => $observation->stage,
                        'status' => $observation->status,
                        'observation_date' => $observation->observation_date->format('Y-m-d'),
                        'overall_score' => $observation->overall_score,
                        'notes' => $observation->notes,
                        'supervisor_id' => $observation->supervisor_id,
                    ],
                    'backgroundColor' => $this->getStageColor($observation->stage),
                    'borderColor' => $this->getStageColor($observation->stage),
                    'textColor' => '#ffffff',
                ];
            });

        return Inertia::render('Supervisor/Calendar/Index', [
            'events' => $observations,
            'initialDate' => now()->toDateString(),
        ]);
    }

    /**
     * Get observations for a specific date (for sidebar/modal)
     */
    public function getObservationsByDate(Request $request)
    {
        $user = Auth::user();
        $date = $request->query('date');

        $observations = Observation::query()
            ->with(['teacher.user', 'supervisor'])
            ->where('supervisor_id', $user->id)
            ->whereDate('observation_date', $date)
            ->get()
            ->map(function ($observation) {
                return [
                    'id' => $observation->id,
                    'teacher_name' => $observation->teacher->user->name,
                    'stage' => $observation->stage,
                    'status' => $observation->status,
                    'observation_date' => $observation->observation_date->format('Y-m-d'),
                    'overall_score' => $observation->overall_score,
                    'notes' => $observation->notes,
                    'stage_label' => $this->getStageLabel($observation->stage),
                    'status_label' => ucfirst($observation->status),
                    'color' => $this->getStageColor($observation->stage),
                ];
            });

        return response()->json($observations);
    }

    /**
     * Get color based on observation stage
     */
    private function getStageColor(string $stage): string
    {
        return match ($stage) {
            'pre_observation_planning' => '#3b82f6', // Blue
            'pre_conference' => '#8b5cf6', // Purple
            'observation' => '#a855f7', // Purple (darker)
            'post_conference' => '#10b981', // Green
            default => '#6b7280', // Gray
        };
    }

    /**
     * Get human-readable stage label
     */
    private function getStageLabel(string $stage): string
    {
        return match ($stage) {
            'pre_observation_planning' => 'Pre-Observation',
            'pre_conference' => 'Pre-Conference',
            'observation' => 'Observation',
            'post_conference' => 'Post-Conference',
            default => 'Unknown',
        };
    }
}
