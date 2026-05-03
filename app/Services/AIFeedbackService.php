<?php

namespace App\Services;

use App\Models\CotRating;
use App\Models\AiFeedback;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;

/**
 * AI Feedback Service
 * 
 * Generates and manages AI-powered feedback for COT ratings.
 * STRICT SEPARATION: This service handles AI feedback only.
 * Human feedback is managed separately.
 */
class AIFeedbackService
{
    /**
     * Generate AI feedback for a COT rating
     * 
     * @param int $cotRatingId The COT rating ID
     * @return AiFeedback|null
     */
    public function generateFeedback(int $cotRatingId): ?AiFeedback
    {
        $cotRating = CotRating::with(['observation.teacher'])->findOrFail($cotRatingId);
        
        // Analyze the rating data
        $analysis = $this->analyzeRating($cotRating);
        
        // Generate recommendations based on score
        $recommendations = $this->generateRecommendations($cotRating);
        
        // Identify strengths
        $strengths = $this->identifyStrengths($cotRating);
        
        // Identify areas for improvement
        $areasForImprovement = $this->identifyAreasForImprovement($cotRating);
        
        // Calculate confidence score based on available data
        $confidenceScore = $this->calculateConfidenceScore($cotRating);

        // Create AI feedback record
        $aiFeedback = AiFeedback::create([
            'cot_rating_id' => $cotRatingId,
            'analysis' => $analysis,
            'recommendations' => $recommendations,
            'strengths' => $strengths,
            'areas_for_improvement' => $areasForImprovement,
            'confidence_score' => $confidenceScore,
            'model_version' => config('services.ai.model_version', 'ASPIRE-v1.0'),
        ]);

        Log::info("AI Feedback generated for COT Rating {$cotRatingId}");

        return $aiFeedback;
    }

    /**
     * Analyze a COT rating and return textual analysis
     */
    private function analyzeRating(CotRating $cotRating): string
    {
        $percentage = $cotRating->percentage();
        $category = $cotRating->rating_category;
        
        if ($percentage >= 90) {
            return "Excellent performance in {$category}. The teacher demonstrates mastery in this area with consistent application of best practices.";
        } elseif ($percentage >= 80) {
            return "Proficient performance in {$category}. The teacher shows good understanding with minor areas for refinement.";
        } elseif ($percentage >= 70) {
            return "Developing performance in {$category}. The teacher is approaching proficiency with targeted support needed.";
        } elseif ($percentage >= 60) {
            return "Beginning performance in {$category}. Structured coaching and professional development recommended.";
        }
        
        return "Performance in {$category} requires immediate intervention. Intensive support and monitoring necessary.";
    }

    /**
     * Generate recommendations based on rating
     */
    private function generateRecommendations(CotRating $cotRating): array
    {
        $recommendations = [];
        $percentage = $cotRating->percentage();
        $category = $cotRating->rating_category;
        
        if ($percentage < 70) {
            $recommendations[] = "Schedule follow-up observation for {$category} within 2 weeks";
            $recommendations[] = "Provide targeted professional development resources for {$category}";
            $recommendations[] = "Arrange peer mentoring with high-performing teacher in {$category}";
        } elseif ($percentage < 80) {
            $recommendations[] = "Share best practices and exemplar materials for {$category}";
            $recommendations[] = "Encourage self-reflection and goal setting for {$category}";
        } else {
            $recommendations[] = "Document exemplary practices in {$category} for knowledge sharing";
            $recommendations[] = "Consider peer coaching opportunities in {$category}";
        }
        
        return $recommendations;
    }

    /**
     * Identify strengths from rating
     */
    private function identifyStrengths(CotRating $cotRating): array
    {
        $strengths = [];
        $percentage = $cotRating->percentage();
        
        if ($percentage >= 80) {
            $strengths[] = "Strong command of {$cotRating->rating_category}";
        }
        if ($percentage >= 90) {
            $strengths[] = "Consistently exceeds expectations";
            $strengths[] = "Serves as model for peers";
        }
        
        return $strengths;
    }

    /**
     * Identify areas for improvement
     */
    private function identifyAreasForImprovement(CotRating $cotRating): array
    {
        $areas = [];
        $percentage = $cotRating->percentage();
        
        if ($percentage < 70) {
            $areas[] = "Fundamental skills in {$cotRating->rating_category}";
        }
        if ($percentage < 80) {
            $areas[] = "Consistency in applying {$cotRating->rating_category} techniques";
        }
        if ($percentage < 60) {
            $areas[] = "Core competencies requiring intensive support";
        }
        
        return $areas;
    }

    /**
     * Calculate confidence score based on available data
     */
    private function calculateConfidenceScore(CotRating $cotRating): float
    {
        $baseScore = 0.75; // Base confidence
        
        // Adjust based on observation completeness
        $observation = $cotRating->observation;
        if ($observation && $observation->notes) {
            $baseScore += 0.10;
        }
        
        // Adjust based on rating category clarity
        $knownCategories = ['instruction', 'assessment', 'classroom_management', 'content_knowledge'];
        if (in_array($cotRating->rating_category, $knownCategories)) {
            $baseScore += 0.05;
        }
        
        // Cap at 0.95 (leave room for uncertainty)
        return min($baseScore, 0.95);
    }

    /**
     * Get AI feedback requiring human review
     */
    public function getFeedbackNeedingReview(): \Illuminate\Support\Collection
    {
        return AiFeedback::where('confidence_score', '<', 0.60)
            ->with(['cotRating.observation.teacher'])
            ->get();
    }

    /**
     * Regenerate AI feedback with updated model
     */
    public function regenerateFeedback(int $cotRatingId): ?AiFeedback
    {
        // Delete existing AI feedback
        AiFeedback::where('cot_rating_id', $cotRatingId)->delete();
        
        // Generate new feedback
        return $this->generateFeedback($cotRatingId);
    }
}
