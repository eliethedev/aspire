<?php

namespace App\Http\Controllers\Supervisor;

use App\AI\Exceptions\AIRateLimitException;
use App\AI\Routing\LessonPlanModelRouter;
use App\AI\Services\CotIndicatorAnalysisService;
use App\AI\Services\LessonPlanSuggestionService;
use App\AI\Services\LessonPlanSummaryService;
use App\AI\Services\OverallRecommendationService;
use App\Http\Controllers\Controller;
use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Goal-specific AI task endpoints.
 *
 * Each endpoint serves ONE supervisor goal (suggestion, summary,
 * indicator analysis, overall recommendation). Rate limiting happens in
 * two layers: global per-user middleware + per-task service limits.
 */
class AITaskController extends Controller
{
    public function lessonPlanSuggestions(Observation $observation, Request $request, LessonPlanSuggestionService $service): JsonResponse
    {
        if ($response = $this->guard($observation)) {
            return $response;
        }

        $manualMode = $this->manualMode($request);

        try {
            $data = $service->suggest($observation, $manualMode);
        } catch (AIRateLimitException $e) {
            return $this->rateLimited($e);
        } catch (Throwable $e) {
            Log::error('AITaskController: lesson_plan_suggestion failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to generate suggestions. Try again later.'], 500);
        }

        if ($data === null) {
            return $this->unavailable();
        }

        return response()->json([
            'task' => 'lesson_plan_suggestion',
            'data' => $data,
            'meta' => $service->getLastRouting(),
            'modes' => app(LessonPlanModelRouter::class)->modes(),
        ]);
    }

    public function lessonPlanSummary(Observation $observation, Request $request, LessonPlanSummaryService $service): JsonResponse
    {
        if ($response = $this->guard($observation)) {
            return $response;
        }

        $manualMode = $this->manualMode($request);

        try {
            $data = $service->summarize($observation, $manualMode);
        } catch (AIRateLimitException $e) {
            return $this->rateLimited($e);
        } catch (Throwable $e) {
            Log::error('AITaskController: lesson_plan_summary failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to summarize lesson plan. Try again later.'], 500);
        }

        if ($data === null) {
            return response()->json([
                'error' => 'Unable to summarize. Make sure a lesson plan is uploaded for this observation.',
            ], 422);
        }

        return response()->json([
            'task' => 'lesson_plan_summary',
            'data' => $data,
            'meta' => $service->getLastRouting(),
            'modes' => app(LessonPlanModelRouter::class)->modes(),
        ]);
    }

    public function cotIndicatorAnalysis(Observation $observation, CotRating $cotRating, CotIndicatorAnalysisService $service): JsonResponse
    {
        if ($response = $this->guard($observation)) {
            return $response;
        }

        if ($cotRating->observation_id !== $observation->id) {
            abort(404);
        }

        try {
            $data = $service->analyze($cotRating);
        } catch (AIRateLimitException $e) {
            return $this->rateLimited($e);
        } catch (Throwable $e) {
            Log::error('AITaskController: cot_indicator_analysis failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to analyze indicator. Try again later.'], 500);
        }

        if ($data === null) {
            return $this->unavailable();
        }

        return response()->json([
            'task' => 'cot_indicator_analysis',
            'data' => $data,
            'disclaimer' => 'Advisory analysis only. The supervisor remains responsible for the final rating.',
        ]);
    }

    public function overallRecommendation(Observation $observation, OverallRecommendationService $service): JsonResponse
    {
        if ($response = $this->guard($observation)) {
            return $response;
        }

        try {
            $data = $service->recommend($observation);
        } catch (AIRateLimitException $e) {
            return $this->rateLimited($e);
        } catch (Throwable $e) {
            Log::error('AITaskController: overall_recommendation failed: '.$e->getMessage());

            return response()->json(['error' => 'Failed to generate recommendations. Try again later.'], 500);
        }

        if ($data === null) {
            return response()->json([
                'error' => 'Unable to generate recommendations. COT ratings must be recorded first.',
            ], 422);
        }

        return response()->json([
            'task' => 'overall_recommendation',
            'data' => $data,
            'disclaimer' => 'Advisory only. The supervisor makes all final decisions.',
        ]);
    }

    /**
     * Validated manual generation-mode override ("auto" or null → router decides).
     */
    protected function manualMode(Request $request): ?string
    {
        $mode = $request->input('mode', $request->query('mode'));

        if (! is_string($mode) || $mode === '' || $mode === 'auto') {
            return null;
        }

        return app(LessonPlanModelRouter::class)->isValidMode($mode) ? $mode : null;
    }

    /**
     * Shared authorization: authenticated observer of the same school.
     */
    protected function guard(Observation $observation): ?JsonResponse
    {
        $user = Auth::user();
        $observee = $observation->observee;

        if (! config('ai.enabled', true)) {
            return response()->json(['error' => 'AI features are disabled.'], 403);
        }

        if (! $observee || ! $observee->user || $observee->user->school_id !== $user?->school_id) {
            abort(403, 'This observation does not belong to your school.');
        }

        return null;
    }

    protected function rateLimited(AIRateLimitException $e): JsonResponse
    {
        return response()->json([
            'error' => "Rate limit reached for this AI task. Please retry in {$e->retryAfter} seconds.",
            'retry_after' => $e->retryAfter,
        ], 429)->withHeaders(['Retry-After' => (string) $e->retryAfter]);
    }

    protected function unavailable(): JsonResponse
    {
        return response()->json([
            'error' => 'AI service is unavailable right now. Please try again later.',
        ], 503);
    }
}
