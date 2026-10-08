<?php

namespace App\Http\Controllers\Supervisor;

use App\AI\Exceptions\AIRateLimitException;
use App\AI\Support\AIStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Services\AIFeedbackService;
use App\Services\AISuggestionService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservationAiController extends Controller
{
    use Concerns\AuthorizesObservations;

    protected NotificationService $notificationService;

    protected AIFeedbackService $aiFeedback;

    protected AISuggestionService $aiSuggestions;

    public function __construct(NotificationService $notificationService, AIFeedbackService $aiFeedback, AISuggestionService $aiSuggestions)
    {
        $this->notificationService = $notificationService;
        $this->aiFeedback = $aiFeedback;
        $this->aiSuggestions = $aiSuggestions;
    }

    /**
     * Validated manual lesson-plan generation-mode override
     * ("auto" or missing → the router decides from lesson goals).
     */
    protected function lessonPlanManualMode(Request $request): ?string
    {
        $mode = $request->input('mode', $request->query('mode'));

        if (! is_string($mode) || $mode === '' || $mode === 'auto') {
            return null;
        }

        return app(\App\AI\Routing\LessonPlanModelRouter::class)->isValidMode($mode) ? $mode : null;
    }

    public function generateAiInsights(Observation $observation, Request $request)
    {
        $this->authorizeObservation($observation);
        $observation->load(['preObservationPlanning', 'observee']);

        // Generate inline (with a generous time budget) so the result arrives
        // in full within the same request instead of relying on a background
        // queue worker. No template fallback here: if AI is disabled/unavailable
        // we return a friendly notice instead of canned text.
        set_time_limit(300);

        $manualMode = $this->lessonPlanManualMode($request);
        $service = app(\App\AI\Services\PreObservationService::class);

        try {
            $insights = $service->generateInsights($observation, false, $manualMode);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        if (!$insights) {
            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        $routing = $service->getLastRouting();

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => $insights, 'ai_insights_meta' => $routing]
        );

        app(AuditLogService::class)->logAi(
            'insights_generated',
            "Pre-observation AI insights generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_observation', 'observation_id' => $observation->id],
        );

        return response()->json([
            'status' => 'completed',
            'ai_insights' => $insights,
            'source' => 'ai',
            'meta' => $routing,
            // Server-organized sections (Lesson Focus, Key Things to Watch,
            // Pre-Conference Talking Points, Potential Challenges) rendered
            // with the same partial as first paint — the frontend swaps this
            // in with zero page reload.
            'panel_html' => view('partials.ai-insights-result', ['insights' => $insights])->render(),
        ]);
    }

    /**
     * Check whether pre-observation AI insights are ready.
     * Called by the frontend via polling after dispatching the job.
     */
    public function aiInsightsStatus(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load('preObservationPlanning');

        $insights = $observation->preObservationPlanning?->ai_insights;

        if ($insights) {
            return response()->json([
                'status' => 'completed',
                'ai_insights' => $insights,
                'source' => 'ai',
                'meta' => $observation->preObservationPlanning?->ai_insights_meta,
                'panel_html' => view('partials.ai-insights-result', ['insights' => $insights])->render(),
            ]);
        }

        return response()->json([
            'status' => 'processing',
            'message' => 'AI insights are still being generated.',
        ]);
    }

    /**
     * Requeue deferred AI generation for an observation stuck at ai_status=failed
     * (Architecture B). Pending observations may still have jobs in flight, so
     * only failed ones can be retried — otherwise feedback rows could duplicate.
     */
    public function retryAi(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if ($observation->ai_status !== 'failed') {
            return back()->with('info', 'AI does not need retrying for this observation.');
        }

        $count = app(\App\Services\AiRetryService::class)->retry($observation);

        return back()->with(
            'success',
            $count > 0
                ? "AI regeneration queued for {$count} rating(s). Check back shortly."
                : 'AI feedback is already present — marked as ready.'
        );
    }

    public function clearAiInsights(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => null, 'ai_insights_meta' => null]
        );

        app(AuditLogService::class)->logAi(
            'insights_cleared',
            "Pre-observation AI insights cleared for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_observation', 'observation_id' => $observation->id],
        );

        return response()->json(['success' => true]);
    }

    public function generateAiSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        try {
            $data = $this->aiFeedback->generatePreConferenceSuggestions($observation, templateFallback: false);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        if ($data === null || (blank($data['discussion_notes'] ?? null) && blank($data['finalized_focus'] ?? null))) {
            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notify(
                $observee->user,
                NotificationType::AI_SUGGESTION,
                'AI discussion notes are ready',
                'AI has drafted discussion notes and focus areas for your observation. Please review them with your supervisor.',
                null,
                route('teacher.observations.show', $observation),
            );
        }

        app(AuditLogService::class)->logAi(
            'suggestions_generated',
            "Pre-conference AI suggestions generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_conference', 'observation_id' => $observation->id],
        );

        return response()->json([
            'discussion_notes' => $data['discussion_notes'] ?? '',
            'finalized_focus' => $data['finalized_focus'] ?? '',
        ]);
    }

    /**
     * Per-field AI suggestions for the "Things to Think About" boxes.
     * Advisory only: the supervisor reviews and applies each suggestion.
     */
    public function generateThingsSuggestions(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'field' => ['required', 'in:teaching_strategies,instructional_materials,assessment_activity'],
        ]);

        try {
            $data = $this->aiFeedback->generateThingsToThinkAbout($observation, $validated['field'], templateFallback: true);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('pre_observation', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        if ($data === null || empty($data['suggestions'])) {
            return response()->json(AIStatus::unavailable('pre_observation'), 503);
        }

        app(AuditLogService::class)->logAi(
            'things_suggestions_generated',
            "Things-to-think-about AI suggestions ({$validated['field']}) generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'pre_conference', 'field' => $validated['field'], 'observation_id' => $observation->id],
        );

        return response()->json([
            'field' => $validated['field'],
            'suggestions' => array_values($data['suggestions']),
            'analysis' => $data['analysis'] ?? '',
            'fallback' => (bool) ($data['fallback'] ?? false),
        ]);
    }

    public function generateAiComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);
        $observation->load(['postConference', 'preObservationPlanning', 'observee']);

        try {
            $comparison = $this->aiFeedback->generatePostConferenceComparison($observation, templateFallback: false);
        } catch (AIRateLimitException $e) {
            return response()->json(AIStatus::unavailable('post_conference', $e), 429);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(AIStatus::unavailable('post_conference'), 503);
        }

        if (! $comparison) {
            return response()->json(AIStatus::unavailable('post_conference'), 503);
        }

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_comparison' => $comparison]
        );

        app(AuditLogService::class)->logAi(
            'comparison_generated',
            "Post-conference AI comparison generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'post_conference', 'observation_id' => $observation->id],
        );

        return response()->json(['ai_comparison' => $comparison]);
    }

    public function generateObservationSuggestions(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $suggestions = $this->aiSuggestions->generateObservationSuggestions($observation);

        if ($suggestions === null) {
            return response()->json(['error' => 'Failed to generate observation suggestions. Try again later.'], 500);
        }

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $this->notificationService->notify(
                $observee->user,
                NotificationType::AI_SUGGESTION,
                'AI observation suggestions are ready',
                'AI has generated suggestions for your observation. Please review them with your supervisor.',
                null,
                route('teacher.observations.show', $observation),
            );
        }

        app(AuditLogService::class)->logAi(
            'guidance_generated',
            "During-observation AI guidance generated for observation #{$observation->id}",
            (string) $observation->id,
            'success',
            ['type' => 'during_observation', 'observation_id' => $observation->id],
        );

        return response()->json(['suggestions' => $suggestions]);
    }
}
