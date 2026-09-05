<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\CotRating;
use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * COT (Classroom Observation Tool) Service
 * 
 * Handles all business logic related to observations and COT ratings.
 * FLOW: Observation → COT Rating → AI Feedback → Prediction → Decision
 */
class COTService
{
    /**
     * Create a new observation
     */
    public function createObservation(array $data): Observation
    {
        $observation = Observation::create([
            'teacher_id' => $data['teacher_id'],
            'supervisor_id' => $data['supervisor_id'],
            'observation_date' => $data['observation_date'],
            'subject' => $data['subject'],
            'grade_section' => $data['grade_section'],
            'duration_minutes' => $data['duration_minutes'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        return $observation;
    }

    /**
     * Complete an observation and create COT ratings
     */
    public function completeObservation(int $observationId, array $ratings): Observation
    {
        $observation = Observation::findOrFail($observationId);
        
        // Create COT ratings
        foreach ($ratings as $rating) {
            CotRating::create([
                'observation_id' => $observationId,
                'rating_category' => $rating['category'],
                'score' => $rating['score'],
                'max_score' => $rating['max_score'],
                'comments' => $rating['comments'] ?? null,
            ]);
        }

        // Mark observation as completed
        $observation->update(['status' => 'completed']);

        return $observation->fresh();
    }

    /**
     * Get teacher's average COT score
     */
    public function getTeacherAverageScore(int $teacherId): float
    {
        $average = CotRating::whereHas('observation', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->avg('score');

        return round($average ?? 0, 2);
    }

    /**
     * Get COT ratings breakdown by category
     */
    public function getRatingsByCategory(int $teacherId): Collection
    {
        return CotRating::whereHas('observation', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->selectRaw('rating_category, AVG(score) as average_score, COUNT(*) as total')
        ->groupBy('rating_category')
        ->get();
    }

    /**
     * Get recent observations for a teacher
     */
    public function getRecentObservations(int $teacherId, int $limit = 5): Collection
    {
        return Observation::where('teacher_id', $teacherId)
            ->with('cotRatings')
            ->orderBy('observation_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get observations pending for supervisor
     */
    public function getPendingObservations(int $supervisorId): Collection
    {
        return Observation::where('supervisor_id', $supervisorId)
            ->where('status', 'pending')
            ->with('teacher')
            ->orderBy('observation_date', 'asc')
            ->get();
    }

    /**
     * Calculate overall rating from multiple COT ratings
     */
    public function calculateOverallRating(int $observationId): float
    {
        $ratings = CotRating::where('observation_id', $observationId)
            ->where('not_applicable', false)
            ->get();
        
        if ($ratings->isEmpty()) {
            return 0;
        }

        $totalPercentage = $ratings->sum(function ($rating) {
            return $rating->percentage();
        });

        return round($totalPercentage / $ratings->count(), 2);
    }

    /**
     * Check if teacher needs intervention
     */
    public function needsIntervention(int $teacherId): bool
    {
        $averageScore = $this->getTeacherAverageScore($teacherId);
        $recentObservations = Observation::where('teacher_id', $teacherId)
            ->where('created_at', '>=', now()->subMonths(3))
            ->count();

        // Needs intervention if score is low and has recent observations
        return $averageScore < 70 && $recentObservations > 0;
    }

    /**
     * Get performance trend for teacher
     */
    public function getPerformanceTrend(int $teacherId, int $months = 6): Collection
    {
        return Observation::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->where('observation_date', '>=', now()->subMonths($months))
            ->with(['cotRatings'])
            ->orderBy('observation_date', 'asc')
            ->get()
            ->map(function ($observation) {
                return [
                    'date' => $observation->observation_date->format('Y-m'),
                    'average_score' => $this->calculateOverallRating($observation->id),
                ];
            });
    }
}
