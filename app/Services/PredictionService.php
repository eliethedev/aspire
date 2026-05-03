<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Prediction;
use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Support\Collection;

/**
 * Prediction Service
 * 
 * Generates AI-powered predictions for teacher performance, promotion readiness,
 * and training needs based on COT ratings and observations.
 * FLOW: Observation → COT Rating → AI Feedback → Prediction → Decision
 */
class PredictionService
{
    protected COTService $cotService;

    public function __construct(COTService $cotService)
    {
        $this->cotService = $cotService;
    }

    /**
     * Generate performance prediction for a teacher
     */
    public function predictPerformance(int $teacherId): Prediction
    {
        $teacher = Teacher::findOrFail($teacherId);
        $averageScore = $this->cotService->getTeacherAverageScore($teacherId);
        $trend = $this->cotService->getPerformanceTrend($teacherId);
        
        // Analyze trend
        $trendDirection = $this->analyzeTrendDirection($trend);
        
        // Generate prediction based on score and trend
        if ($averageScore >= 85 && $trendDirection === 'improving') {
            $outcome = 'Excellent performance trajectory. Teacher is on track for advanced certification.';
            $confidence = 0.88;
            $action = 'Prepare for advanced roles or mentoring assignments';
        } elseif ($averageScore >= 75 && $trendDirection === 'stable') {
            $outcome = 'Satisfactory performance. Teacher meets all standard expectations.';
            $confidence = 0.82;
            $action = 'Continue current support level and professional development';
        } elseif ($averageScore >= 60) {
            $outcome = 'Performance at risk. Intervention recommended to prevent decline.';
            $confidence = 0.75;
            $action = 'Implement targeted coaching plan within 30 days';
        } else {
            $outcome = 'Performance requires immediate intensive support.';
            $confidence = 0.85;
            $action = 'Urgent: Initiate performance improvement plan with daily check-ins';
        }

        return Prediction::create([
            'teacher_id' => $teacherId,
            'prediction_type' => 'performance',
            'predicted_outcome' => $outcome,
            'confidence_level' => $confidence,
            'factors_considered' => [
                'average_cot_score' => $averageScore,
                'trend_direction' => $trendDirection,
                'total_observations' => Observation::where('teacher_id', $teacherId)->count(),
                'recent_improvement_rate' => $this->calculateImprovementRate($trend),
            ],
            'action_recommended' => $action,
            'status' => 'active',
            'valid_until' => now()->addMonths(6),
        ]);
    }

    /**
     * Predict promotion readiness
     */
    public function predictPromotion(int $teacherId): ?Prediction
    {
        $teacher = Teacher::findOrFail($teacherId);
        $averageScore = $this->cotService->getTeacherAverageScore($teacherId);
        $observationsCount = Observation::where('teacher_id', $teacherId)
            ->where('status', 'completed')
            ->count();

        // Minimum requirements for promotion consideration
        if ($observationsCount < 3 || $averageScore < 80) {
            return null; // Not eligible for promotion prediction yet
        }

        $ratingsByCategory = $this->cotService->getRatingsByCategory($teacherId);
        $consistencyScore = $this->calculateConsistencyScore($ratingsByCategory);

        if ($averageScore >= 90 && $consistencyScore >= 0.85) {
            $outcome = 'Highly ready for promotion. Consistently exceeds expectations across all categories.';
            $confidence = 0.90;
            $action = 'Initiate promotion review process and leadership development';
        } elseif ($averageScore >= 85 && $consistencyScore >= 0.75) {
            $outcome = 'Ready for promotion with minor development areas.';
            $confidence = 0.82;
            $action = 'Begin promotion preparation with targeted coaching';
        } elseif ($averageScore >= 80) {
            $outcome = 'Developing readiness. Needs 6-12 months of continued growth.';
            $confidence = 0.70;
            $action = 'Create development plan focused on leadership competencies';
        } else {
            return null;
        }

        return Prediction::create([
            'teacher_id' => $teacherId,
            'prediction_type' => 'promotion',
            'predicted_outcome' => $outcome,
            'confidence_level' => $confidence,
            'factors_considered' => [
                'average_score' => $averageScore,
                'consistency_score' => $consistencyScore,
                'observations_count' => $observationsCount,
                'categories_mastery' => $ratingsByCategory->where('average_score', '>=', 80)->pluck('rating_category'),
            ],
            'action_recommended' => $action,
            'status' => 'active',
            'valid_until' => now()->addMonths(12),
        ]);
    }

    /**
     * Predict training needs
     */
    public function predictTrainingNeeds(int $teacherId): Prediction
    {
        $ratingsByCategory = $this->cotService->getRatingsByCategory($teacherId);
        
        $weakAreas = $ratingsByCategory->where('average_score', '<', 70);
        $developingAreas = $ratingsByCategory->whereBetween('average_score', [70, 80]);

        if ($weakAreas->isNotEmpty()) {
            $outcome = 'Immediate training required in: ' . $weakAreas->pluck('rating_category')->implode(', ');
            $confidence = 0.88;
            $action = 'Enroll in intensive training programs for identified weak areas within 2 weeks';
        } elseif ($developingAreas->isNotEmpty()) {
            $outcome = 'Developmental training beneficial for: ' . $developingAreas->pluck('rating_category')->implode(', ');
            $confidence = 0.78;
            $action = 'Schedule professional development workshops for developing areas';
        } else {
            $outcome = 'No immediate training needs. Consider advanced or enrichment training.';
            $confidence = 0.85;
            $action = 'Offer advanced certification or mentorship opportunities';
        }

        return Prediction::create([
            'teacher_id' => $teacherId,
            'prediction_type' => 'training',
            'predicted_outcome' => $outcome,
            'confidence_level' => $confidence,
            'factors_considered' => [
                'weak_areas' => $weakAreas->pluck('rating_category'),
                'developing_areas' => $developingAreas->pluck('rating_category'),
                'strength_areas' => $ratingsByCategory->where('average_score', '>=', 80)->pluck('rating_category'),
            ],
            'action_recommended' => $action,
            'status' => 'active',
            'valid_until' => now()->addMonths(3),
        ]);
    }

    /**
     * Get all active predictions for a teacher
     */
    public function getActivePredictions(int $teacherId): Collection
    {
        return Prediction::where('teacher_id', $teacherId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Analyze trend direction from performance data
     */
    private function analyzeTrendDirection(Collection $trend): string
    {
        if ($trend->count() < 2) {
            return 'insufficient_data';
        }

        $first = $trend->first()['average_score'] ?? 0;
        $last = $trend->last()['average_score'] ?? 0;
        $difference = $last - $first;

        if ($difference > 5) {
            return 'improving';
        } elseif ($difference < -5) {
            return 'declining';
        }

        return 'stable';
    }

    /**
     * Calculate improvement rate from trend
     */
    private function calculateImprovementRate(Collection $trend): float
    {
        if ($trend->count() < 2) {
            return 0.0;
        }

        $scores = $trend->pluck('average_score');
        $first = $scores->first();
        $last = $scores->last();

        if ($first == 0) {
            return 0.0;
        }

        return round((($last - $first) / $first) * 100, 2);
    }

    /**
     * Calculate consistency score across categories
     */
    private function calculateConsistencyScore(Collection $ratingsByCategory): float
    {
        if ($ratingsByCategory->isEmpty()) {
            return 0.0;
        }

        $scores = $ratingsByCategory->pluck('average_score');
        $mean = $scores->avg();
        
        // Calculate standard deviation
        $variance = $scores->map(function ($score) use ($mean) {
            return pow($score - $mean, 2);
        })->avg();
        
        $stdDev = sqrt($variance);
        
        // Lower standard deviation = higher consistency (inverse relationship)
        $consistency = max(0, 1 - ($stdDev / 100));
        
        return round($consistency, 2);
    }

    /**
     * Invalidate expired predictions
     */
    public function invalidateExpiredPredictions(): int
    {
        return Prediction::where('valid_until', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }
}
