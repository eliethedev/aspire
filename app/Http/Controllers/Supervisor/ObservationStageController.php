<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisor\Concerns\AuthorizesObservations;
use App\Http\Controllers\Supervisor\Concerns\ResolvesSchoolYearTerm;
use App\Jobs\GeneratePostConferenceComparison;
use App\Jobs\GeneratePostObservationFeedback;
use App\Models\CotRating;
use App\Models\EpocEvaluation;
use App\Models\EpocRating;
use App\Models\EpocTemplate;
use App\Models\FormTemplate;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AIFeedbackService;
use App\Services\AISuggestionService;
use App\Services\AuditLogService;
use App\Services\CotDocumentService;
use App\Services\CotIndicatorService;
use App\Services\FormTemplateService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ObservationStageController extends Controller
{
    use AuthorizesObservations, ResolvesSchoolYearTerm;

    protected NotificationService $notificationService;

    protected PHPMailerService $mailerService;

    protected AIFeedbackService $aiFeedback;

    protected AISuggestionService $aiSuggestions;

    protected FormTemplateService $formTemplateService;

    protected CotIndicatorService $cotIndicatorService;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailerService, AIFeedbackService $aiFeedback, AISuggestionService $aiSuggestions, FormTemplateService $formTemplateService, CotIndicatorService $cotIndicatorService)
    {
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->aiFeedback = $aiFeedback;
        $this->aiSuggestions = $aiSuggestions;
        $this->formTemplateService = $formTemplateService;
        $this->cotIndicatorService = $cotIndicatorService;
    }

    /**
     * Show Pre-Observation Planning form
     */
    public function preObservationPlanning(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);

        $planning = $observation->preObservationPlanning;

        // Get previous observations for this observee to show strengths/weaknesses
        $previousObservations = Observation::with(['cotRatings', 'observee'])
            ->where('observee_id', $observation->observee_id)
            ->where('observee_type', $observation->observee_type)
            ->where('id', '!=', $observation->id)
            ->whereNotNull('overall_score')
            ->latest()
            ->take(5)
            ->get();

        // Calculate strengths & weaknesses from previous COT ratings
        $prevStrengths = collect();
        $prevWeaknesses = collect();
        if ($previousObservations->isNotEmpty()) {
            $prevRatings = CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn ($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn ($r) => $r->avg_rating < 3)->values();
        }

        $preConference = $observation->preConference;

        $observerRole = Auth::user()->role;

        return view('supervisor.observations.pre-observation-planning', compact(
            'observation', 'planning', 'previousObservations', 'prevStrengths', 'prevWeaknesses', 'preConference', 'observerRole'
        ));
    }

    /**
     * Store Pre-Observation Planning data
     */
    public function storePreObservationPlanning(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'pre_observation_planning', $obsType);

        $validated = $request->validate(array_merge([
            'lesson_plan_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,pptx,xlsx'],
            'ai_insights' => ['nullable', 'string'],
            'suggested_focus' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
            'observation_tool' => ['nullable', 'string', 'in:ppst,classroom_observation_tool,tisuyon'],
        ], $templateRules));

        $filePath = null;
        if ($request->hasFile('lesson_plan_file')) {
            $file = $request->file('lesson_plan_file');
            $originalName = $file->getClientOriginalName();
            $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filePath = $file->storeAs('lesson_plans', $filename, 'public');
        }

        $data = [
            'lesson_plan_file' => $filePath ?? $observation->preObservationPlanning?->lesson_plan_file,
            'ai_insights' => $validated['ai_insights'] ?? null,
            'suggested_focus' => $validated['suggested_focus'] ?? null,
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
            'observation_tool' => $validated['observation_tool'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'pre_observation_planning', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        if ($request->input('continue') === 'pre_conference') {
            // School head observations skip the pre-conference step and
            // proceed directly to the observation (which uses the EPOC sheet).
            if ($observation->isSchoolHeadObservation()) {
                $stageOrder = ['pre_observation_planning', 'observation', 'post_conference'];
                $currentIdx = array_search($observation->stage, $stageOrder);
                $targetIdx = array_search('observation', $stageOrder);

                if ($targetIdx === $currentIdx + 1) {
                    $observation->logChange([
                        'to_stage' => 'observation',
                        'notes' => 'Pre-Observation Planning completed',
                    ]);
                    $observation->update(['stage' => 'observation']);
                }

                return redirect()->route('supervisor.observations.observation', $observation->id)
                    ->with('success', 'Pre-Observation Planning has been saved. Proceed to the School Head Observation.');
            }

            $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
            $currentIdx = array_search($observation->stage, $stageOrder);
            $targetIdx = array_search('pre_conference', $stageOrder);

            if ($targetIdx === $currentIdx + 1) {
                $observation->logChange([
                    'to_stage' => 'pre_conference',
                    'notes' => 'Pre-Observation Planning completed',
                ]);
                $observation->update(['stage' => 'pre_conference']);
            }

            return redirect()->route('supervisor.observations.preConference', $observation->id)
                ->with('success', 'Pre-Observation Planning has been saved. Proceed to Pre-Conference.');
        }

        return redirect()->route('supervisor.observations.preObservationPlanning', $observation->id)
            ->with('success', 'Pre-Observation Planning notes have been saved.');
    }

    /**
     * Request lesson plan from the teacher
     */
    public function requestLessonPlan(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        // Rate limit: at most one request email per observation every 15 seconds.
        $rateKey = 'lesson-plan-request:'.$observation->getKey();
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $retryAfter = RateLimiter::availableIn($rateKey);
            $message = "A request was just sent. Please wait {$retryAfter} seconds before requesting again.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message, 'retry_after' => $retryAfter], 429);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            $observation->load(['observee.user', 'observee.school']);

            $teacherUser = $observation->observee?->user;
            if (! $teacherUser) {
                $message = 'Teacher not found for this observation.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return redirect()->back()->with('error', $message);
            }

        $requester = Auth::user();
        $requesterName = $requester->name;
        $observationLink = route('teacher.observations.show', $observation);

        // Notify the teacher in-app
        $this->notificationService->notifyLessonPlanRequested($teacherUser, $requesterName, $observationLink);

        // Send email to the teacher
        $subject = "Lesson Plan Requested â€“ {$observation->subject}";
        try {
            $this->mailerService->sendGenericEmailLater(
                $teacherUser->email,
                $teacherUser->name,
                $subject,
                $this->buildLessonPlanRequestedEmail($requesterName, $observation, $observationLink)
            );
        } catch (\Throwable $e) {
            Log::error('Failed to queue lesson plan email to teacher: '.$e->getMessage());
        }

        // Notify the school head(s) of the teacher's school
        $school = $observation->observee?->school;
        if ($school) {
            $schoolHeads = $school->users()->where('role', 'school_head')->get();
            foreach ($schoolHeads as $schoolHead) {
                $schoolHeadLink = route('supervisor.observations.show', $observation);
                $this->notificationService->notifyLessonPlanUploaded($schoolHead, $teacherUser->name, $schoolHeadLink);
                try {
                    $this->mailerService->sendGenericEmailLater(
                        $schoolHead->email, $schoolHead->name, $subject,
                        $this->buildLessonPlanRequestedEmail($requesterName, $observation, $schoolHeadLink)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to queue lesson plan email to school head: '.$e->getMessage());
                }
            }
        }

        // Notify the supervisor (requester) as confirmation
        $supervisorLink = route('supervisor.observations.preObservationPlanning', $observation);
        $teacherName = $teacherUser->name;
        $this->notificationService->notifyLessonPlanRequestedToSupervisor($requester, $teacherName, $supervisorLink);

        $message = 'Lesson plan request has been sent to the teacher. They were notified in-app and by email.';

        // Start the 15-second cooldown for this observation.
        RateLimiter::hit($rateKey, 15);

        // AJAX (fetch) callers can't see session flash data after following
        // the redirect, so respond with JSON they can surface inline.
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Failed to send lesson plan request: '.$e->getMessage(), ['observation_id' => $observation->getKey()]);
            $message = 'Failed to request lesson plan. Please try again.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    protected function buildLessonPlanRequestedEmail(string $requesterName, Observation $observation, string $observationLink): string
    {
        $subject = "Lesson Plan Requested â€“ {$observation->subject}";

        return "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f7f6; padding: 40px 0;'>
                <tr><td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #d97706; padding: 30px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 22px;'>Lesson Plan Requested</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 30px 40px;'>
                                <p style='color: #374151; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;'>
                                    <strong>{$requesterName}</strong> has requested you to submit a lesson plan for the following observation:
                                </p>
                                <table style='background-color: #fffbeb; border-left: 4px solid #d97706; padding: 16px; margin: 0 0 20px 0; border-radius: 4px; width: 100%;'>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Subject:</strong> {$observation->subject}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>Grade Level:</strong> {$observation->grade_level_label}</td></tr>
                                    <tr><td style='padding: 4px 0; color: #374151; font-size: 14px;'><strong>School Year:</strong> {$observation->school_year}</td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #f9fafb; padding: 20px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='color: #9ca3af; font-size: 12px; margin: 0;'>This is an automated notification from the ASPIRE Classroom Observation System.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
    }

    /**
     * Show Pre-Conference form
     */
    public function preConference(Observation $observation)
    {
        $this->authorizeObservation($observation);

        // School head observations skip the pre-conference step entirely.
        if ($observation->isSchoolHeadObservation()) {
            return redirect()->route('supervisor.observations.observation', $observation->id)
                ->with('info', 'Pre-Observation Conference is not part of the School Head observation flow.');
        }

        $observation->load(['observee.user', 'observee.school', 'preObservationPlanning', 'preConference']);

        $preConference = $observation->preConference;
        $planning = $observation->preObservationPlanning;

        // Load previous COT data for sidebar performance summary
        $previousObservations = Observation::with(['cotRatings'])
            ->where('observee_id', $observation->observee_id)
            ->where('observee_type', $observation->observee_type)
            ->where('id', '!=', $observation->id)
            ->whereNotNull('overall_score')
            ->latest()
            ->take(5)
            ->get();

        $prevStrengths = collect();
        $prevWeaknesses = collect();
        if ($previousObservations->isNotEmpty()) {
            $prevRatings = CotRating::whereIn('observation_id', $previousObservations->pluck('id'))
                ->selectRaw('domain, AVG(rating) as avg_rating, COUNT(*) as total')
                ->groupBy('domain')
                ->get();

            $prevStrengths = $prevRatings->filter(fn ($r) => $r->avg_rating >= 4)->values();
            $prevWeaknesses = $prevRatings->filter(fn ($r) => $r->avg_rating < 3)->values();
        }

        return view('supervisor.observations.pre-conference', compact(
            'observation', 'preConference', 'planning', 'prevStrengths', 'prevWeaknesses'
        ));
    }

    /**
     * Store Pre-Conference data
     */
    public function storePreConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        // School head observations skip the pre-conference step entirely.
        if ($observation->isSchoolHeadObservation()) {
            return redirect()->route('supervisor.observations.observation', $observation->id)
                ->with('info', 'Pre-Observation Conference is not part of the School Head observation flow.');
        }

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'pre_conference', $obsType);

        $validated = $request->validate(array_merge([
            'discussion_notes' => ['nullable', 'string'],
            'finalized_focus' => ['nullable', 'string'],
            'conference_date' => ['nullable', 'date'],
            'teacher_reflection' => ['nullable', 'string'],
            'lesson_plan_review' => ['nullable', 'string'],
            'instructional_materials' => ['nullable', 'string'],
            'topic' => ['nullable', 'string'],
            'learning_objectives' => ['nullable', 'string'],
            'teaching_strategies' => ['nullable', 'string'],
            'assessment_activity' => ['nullable', 'string'],
            'expected_challenges' => ['nullable', 'string'],
            'feedback_areas' => ['nullable', 'string'],
            'ai_insights_reviewed' => ['nullable', 'boolean'],
        ], $templateRules));

        $data = [
            'discussion_notes' => $validated['discussion_notes'] ?? null,
            'finalized_focus' => $validated['finalized_focus'] ?? null,
            'conference_date' => $validated['conference_date'] ?? now(),
            'teacher_reflection' => $validated['teacher_reflection'] ?? null,
            'lesson_plan_review' => $validated['lesson_plan_review'] ?? null,
            'instructional_materials' => $validated['instructional_materials'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'learning_objectives' => $validated['learning_objectives'] ?? null,
            'teaching_strategies' => $validated['teaching_strategies'] ?? null,
            'assessment_activity' => $validated['assessment_activity'] ?? null,
            'expected_challenges' => $validated['expected_challenges'] ?? null,
            'feedback_areas' => $validated['feedback_areas'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'pre_conference', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        // Mark AI insights as reviewed
        if ($request->has('ai_insights_reviewed')) {
            $observation->preObservationPlanning()->updateOrCreate(
                ['observation_id' => $observation->id],
                ['ai_insights_reviewed' => true]
            );
        }

        // If save draft, stay on pre-conference page without advancing stage
        if ($request->has('save_draft')) {
            return redirect()->route('supervisor.observations.preConference', $observation->id)
                ->with('success', 'Pre-Conference draft saved.');
        }

        // Only advance stage forward (prevent regression)
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('observation', $stageOrder);

        if ($targetIdx === $currentIdx + 1) {
            $observation->logChange([
                'to_stage' => 'observation',
                'notes' => 'Pre-Conference completed',
            ]);
            $observation->update(['stage' => 'observation']);
        }

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
        $observation->loadMissing(['preObservationPlanning', 'observee']);

        $schoolYear = $observation->school_year ?? config('cot.default_version', '2025-2026');
        $cotVersion = $this->cotIndicatorService->getVersionForObservation($observation);
        $cotIndicators = $cotVersion['indicators'] ?? [];
        $ratingScale = $cotVersion['rating_scale'] ?? config('cot.rating_scale', []);
        $ratingScaleCss = $cotVersion['rating_scale_css'] ?? config('cot.rating_scale_css', []);
        $existingSuggestions = $observation->preObservationPlanning?->ai_insights;

        // School Head observations use the EPOC instrument instead of the COT
        // rating sheet, so load the EPOC relationships for the integrated form.
        $epocEvaluation = null;
        $schoolHead = null;
        if ($observation->isSchoolHeadObservation()) {
            $observation->loadMissing(['epocEvaluation.ratings', 'schoolHead']);
            $epocEvaluation = $observation->epocEvaluation;
            $schoolHead = $observation->schoolHead;
        }

        return view('supervisor.observations.observation', compact(
            'observation', 'cotRatings', 'preConference',
            'cotIndicators', 'ratingScale', 'ratingScaleCss',
            'existingSuggestions', 'schoolYear',
            'epocEvaluation', 'schoolHead'
        ));
    }

    /**
     * Store Observation data (COT Ratings for teacher observations,
     * EPOC ratings for school head observations)
     */
    public function storeObservationData(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        // School Head observations use the EPOC instrument instead of the COT
        // rating sheet, so save EPOC ratings + narrative/agreement.
        if ($observation->isSchoolHeadObservation()) {
            return $this->storeEpocObservationData($request, $observation);
        }

        $cotVersion = $this->cotIndicatorService->getVersionForObservation($observation);
        $scaleValues = array_keys($cotVersion['rating_scale'] ?? config('cot.rating_scale', []));

        $validated = $request->validate([
            'ratings' => ['required', 'array'],
            'ratings.*.indicator_code' => ['required', 'string'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['nullable', 'integer', Rule::in($scaleValues)],
            'ratings.*.not_observed' => ['nullable', 'boolean'],
            'ratings.*.not_applicable' => ['nullable', 'boolean'],
            'ratings.*.has_rating' => ['nullable', 'string'],
            'ratings.*.comments' => ['nullable', 'string'],
            'other_comments' => ['nullable', 'string'],
            'star_notes' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ]);

        // Reject a completely blank rating sheet: at least one indicator must
        // have a numeric rating or be explicitly marked NO / N/A. Otherwise
        // the observation completes with no score and empty dashboard results.
        $hasSelection = collect($validated['ratings'])->contains(
            fn ($item) => ! empty($item['rating']) || ! empty($item['not_observed']) || ! empty($item['not_applicable'])
        );
        if (! $hasSelection) {
            throw ValidationException::withMessages([
                'ratings' => 'Please rate at least one indicator (or mark it NO / N/A) before continuing to the post-conference.',
            ]);
        }

        // Replace ratings atomically: upsert by indicator_code so unchanged
        // rows keep their IDs (and AI feedback), delete only stale codes.
        $createdRatings = DB::transaction(function () use ($observation, $validated) {
            $incomingCodes = collect($validated['ratings'])->pluck('indicator_code')->all();
            $observation->cotRatings()->whereNotIn('indicator_code', $incomingCodes)->delete();

            $rows = [];
            foreach ($validated['ratings'] as $item) {
                $rows[] = CotRating::updateOrCreate(
                    ['observation_id' => $observation->id, 'indicator_code' => $item['indicator_code']],
                    [
                        'domain' => $item['domain'],
                        'indicator' => $item['indicator'],
                        'rating' => (! empty($item['not_observed']) || ! empty($item['not_applicable'])) ? null : ($item['rating'] ?? null),
                        'not_observed' => ! empty($item['not_observed']),
                        'not_applicable' => ! empty($item['not_applicable']),
                        'comments' => $item['comments'] ?? null,
                    ]
                );
            }

            return $rows;
        });

        // Generate AI feedback for each rating (dispatched to queue to avoid rate limits).
        // Not Applicable indicators are intentionally excluded â€” they have no score to analyze.
        foreach ($createdRatings as $cotRating) {
            if (! $cotRating->isNotApplicable()) {
                GeneratePostObservationFeedback::dispatch($cotRating);
            }
        }

        // Handle evidence file uploads
        $evidenceFiles = $observation->evidence_files ?? [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('observation_evidences', 'public');
                $evidenceFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Save additional notes
        $otherComments = $request->input('other_comments');
        $observation->update([
            'evidence_files' => $evidenceFiles,
            'notes' => $otherComments ? ($observation->notes ? $observation->notes."\n\n".$otherComments : $otherComments) : $observation->notes,
        ]);

        // Auto-save STAR notes to post-conference if provided
        $starNotes = $request->input('star_notes');
        $supervisorNotes = $request->input('supervisor_notes');
        if ($starNotes || $supervisorNotes) {
            $observation->postConference()->updateOrCreate(
                ['observation_id' => $observation->id],
                [
                    'star_notes' => $starNotes,
                    'supervisor_notes' => $supervisorNotes,
                ]
            );
        }

        // Calculate overall score (average of numeric ratings excluding NO/N/A)
        $rated = $observation->cotRatings()->where('not_observed', false)->where('not_applicable', false)->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;

        // Only advance stage forward (prevent regression).
        // For school-head PPSSH templates that do NOT require post-conference,
        // stay on observation stage â€” finalize can proceed directly.
        $requiresPostConference = true;
        if ($observation->isSchoolHeadObservation()) {
            $pinned = $observation->cotIndicatorVersion;
            $requiresPostConference = $pinned ? $pinned->requiresPostConference() : ($cotVersion['requires_post_conference'] ?? true);
        }
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('post_conference', $stageOrder);

        $updates = ['overall_score' => $avgRating, 'status' => 'cot_completed'];
        if ($requiresPostConference && $targetIdx === $currentIdx + 1) {
            $updates['stage'] = 'post_conference';
            $observation->logChange([
                'to_stage' => 'post_conference',
                'to_status' => 'cot_completed',
                'notes' => 'COT Ratings completed',
            ]);
        } else {
            $note = $requiresPostConference ? 'COT Ratings updated' : 'COT Ratings completed (no post-conference per PPSSH template)';
            $observation->logChange([
                'to_status' => 'cot_completed',
                'notes' => $note,
            ]);
        }
        $observation->update($updates);

        // Auto-trigger AI post-observation analysis via the queue (non-blocking).
        // The HTTP call runs in a dedicated job so the request never blocks on
        // the external AI provider.
        GeneratePostConferenceComparison::dispatch($observation);

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.postConference', $observation->id)
            ->with('success', 'Observation ratings have been saved. AI analysis has been generated.');
    }

    /**
     * Store Observation data for School Head observations (EPOC instrument).
     *
     * Saves the EPOC ratings, narrative observation and agreement into the
     * epoc_evaluations / epoc_ratings tables, then advances the workflow the
     * same way as the COT path (status -> cot_completed, stage -> post_conference).
     */
    protected function storeEpocObservationData(Request $request, Observation $observation)
    {
        $validated = $request->validate([
            'epoc_ratings' => ['required', 'array'],
            'epoc_ratings.*.domain' => ['required', 'string'],
            'epoc_ratings.*.indicator' => ['required', 'string'],
            'epoc_ratings.*.rating' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'epoc_ratings.*.comments' => ['nullable', 'string'],
            'epoc_narrative_observation' => ['nullable', 'string'],
            'epoc_agreement' => ['nullable', 'string'],
            'other_comments' => ['nullable', 'string'],
            'star_notes' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ]);

        // Delete + recreate the EPOC evaluation (ratings cascade).
        $observation->epocEvaluation()->delete();

        $epocEvaluation = $observation->epocEvaluation()->create([
            'school_head_name' => $observation->schoolHead?->name,
            'observation_date' => $observation->observation_date,
            'narrative_observation' => $validated['epoc_narrative_observation'] ?? null,
            'agreement' => $validated['epoc_agreement'] ?? null,
        ]);

        foreach ($validated['epoc_ratings'] as $item) {
            $epocEvaluation->ratings()->create([
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => $item['rating'] ?? null,
                'comments' => $item['comments'] ?? null,
            ]);
        }

        $rated = $epocEvaluation->ratings()->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;
        $epocEvaluation->update(['overall_score' => $avgRating]);

        // Save evidence file uploads
        $evidenceFiles = $observation->evidence_files ?? [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('observation_evidences', 'public');
                $evidenceFiles[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $otherComments = $request->input('other_comments');
        $observation->update([
            'evidence_files' => $evidenceFiles,
            'notes' => $otherComments ? ($observation->notes ? $observation->notes."\n\n".$otherComments : $otherComments) : $observation->notes,
        ]);

        // Save supervisor/private notes to post-conference
        $starNotes = $request->input('star_notes');
        $supervisorNotes = $request->input('supervisor_notes');
        if ($starNotes || $supervisorNotes) {
            $observation->postConference()->updateOrCreate(
                ['observation_id' => $observation->id],
                [
                    'star_notes' => $starNotes,
                    'supervisor_notes' => $supervisorNotes,
                ]
            );
        }

        // Advance the workflow (same as COT path). School head observations
        // always require post-conference.
        $stageOrder = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageOrder);
        $targetIdx = array_search('post_conference', $stageOrder);

        $updates = ['overall_score' => $avgRating, 'status' => 'cot_completed'];
        if ($targetIdx === $currentIdx + 1) {
            $updates['stage'] = 'post_conference';
            $observation->logChange([
                'to_stage' => 'post_conference',
                'to_status' => 'cot_completed',
                'notes' => 'EPOC ratings completed',
            ]);
        } else {
            $observation->logChange([
                'to_status' => 'cot_completed',
                'notes' => 'EPOC ratings updated',
            ]);
        }
        $observation->update($updates);

        // Notify the observee that their observation is complete
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = $observee instanceof SchoolHeadProfile
                ? route('school-head.observations.show', $observation->id)
                : route('supervisor.observations.show', $observation->id);
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

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
        $preConference = $observation->preConference;
        $epocEvaluation = $observation->epocEvaluation;

        return view('supervisor.observations.post-conference', compact('observation', 'postConference', 'cotRatings', 'planning', 'preConference', 'epocEvaluation'));
    }

    /**
     * Store Post-Conference data
     */
    public function storePostConference(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;
        $templateRules = $this->formTemplateService->getValidationRules($schoolYear, 'post_conference', $obsType);

        $validated = $request->validate(array_merge([
            'ai_comparison' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'conference_date' => ['nullable', 'date'],
            'star_notes' => ['nullable', 'string'],
            'areas_for_improvement' => ['nullable', 'string'],
            'challenges_facing_teacher' => ['nullable', 'string'],
            'ideas_for_addressing_challenges' => ['nullable', 'string'],
            'prioritized_next_steps' => ['nullable', 'string'],
            'teacher_reflection' => ['nullable', 'string'],
            'supervisor_notes' => ['nullable', 'string'],
        ], $templateRules));

        $existingPostConference = $observation->postConference;

        $data = [
            'ai_comparison' => $request->has('ai_comparison') ? ($validated['ai_comparison'] ?? null) : ($existingPostConference?->ai_comparison ?? null),
            'feedback' => $request->has('feedback') ? ($validated['feedback'] ?? null) : ($existingPostConference?->feedback ?? null),
            'conference_date' => $validated['conference_date'] ?? now(),
            // CID guide fields are no longer collected on the teacher
            // post-conference form (CID Form 2 is school-head only). Keep any
            // previously saved values instead of wiping them with null.
            'star_notes' => $validated['star_notes'] ?? $existingPostConference?->star_notes ?? null,
            'areas_for_improvement' => $validated['areas_for_improvement'] ?? $existingPostConference?->areas_for_improvement ?? null,
            'challenges_facing_teacher' => $validated['challenges_facing_teacher'] ?? $existingPostConference?->challenges_facing_teacher ?? null,
            'ideas_for_addressing_challenges' => $validated['ideas_for_addressing_challenges'] ?? $existingPostConference?->ideas_for_addressing_challenges ?? null,
            'prioritized_next_steps' => $validated['prioritized_next_steps'] ?? $existingPostConference?->prioritized_next_steps ?? null,
            'teacher_reflection' => $validated['teacher_reflection'] ?? null,
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
        ];

        $formData = $this->formTemplateService->parseFormData($schoolYear, 'post_conference', $request->all(), $obsType);
        $data = array_merge($data, $formData);

        $observation->postConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            $data
        );

        $observation->logChange([
            'to_status' => 'completed',
            'notes' => 'Post-Conference completed',
        ]);
        $observation->update(['status' => 'completed']);

        app(AuditLogService::class)->log(
            'completed', 'observations', (string) $observation->getKey(),
            "Observation #{$observation->getKey()} completed",
            'success', [], $observation->toArray()
        );

        // Notify the observee about feedback
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyFeedbackReceived($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.index')
            ->with('success', 'Post-Conference has been saved. Observation is now complete.');
    }

    /**
     * Finalize the observation - marks it as completed
     */
    public function finalize(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (!$observation->canFinalize()) {
            return back()->with('error', 'This observation cannot be finalized yet.');
        }

        $observation->finalize();

        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $link = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };
            $this->notificationService->notifyObservationCompleted($observee->user, $link);
        }

        return redirect()->route('supervisor.observations.show', $observation->id)
            ->with('success', 'Observation has been finalized. The teacher can now view the results.');
    }

    /**
     * Show EPOC Evaluation form for School Head
     */
    public function epocEvaluation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->isSchoolHeadObservation()) {
            return back()->with('error', 'The EPOC evaluation is only available for school head observations.');
        }

        $epocEvaluation = $observation->epocEvaluation;
        $schoolHead = $observation->schoolHead;

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $epocTemplate = \App\Models\EpocTemplate::activeFor($schoolYear);
        $epocDomains = $epocTemplate?->groupedDomains() ?? \App\Models\EpocTemplate::defaultDomains();

        return view('supervisor.observations.epoc', compact('observation', 'epocEvaluation', 'schoolHead', 'epocTemplate', 'epocDomains'));
    }

    /**
     * Store EPOC Evaluation data
     */
    public function storeEPOC(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->isSchoolHeadObservation()) {
            return back()->with('error', 'The EPOC evaluation is only available for school head observations.');
        }

        $validated = $request->validate([
            'school_head_name' => ['nullable', 'string'],
            'observation_date' => ['nullable', 'date'],
            'epoc_template_id' => ['nullable', 'integer', 'exists:epoc_templates,id'],
            'ratings' => ['required', 'array'],
            'ratings.*.domain' => ['required', 'string'],
            'ratings.*.indicator' => ['required', 'string'],
            'ratings.*.rating' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'ratings.*.comments' => ['nullable', 'string'],
            'narrative_observation' => ['nullable', 'string'],
            'agreement' => ['nullable', 'string'],
        ]);

        // Delete existing EPOC evaluation if any
        $observation->epocEvaluation()->delete();

        // Create EPOC evaluation
        $epocEvaluation = $observation->epocEvaluation()->create([
            'epoc_template_id' => $validated['epoc_template_id'] ?? null,
            'school_head_name' => $validated['school_head_name'] ?? $observation->schoolHead?->name,
            'observation_date' => $validated['observation_date'] ?? $observation->observation_date,
            'narrative_observation' => $validated['narrative_observation'] ?? null,
            'agreement' => $validated['agreement'] ?? null,
        ]);

        // Create EPOC ratings
        foreach ($validated['ratings'] as $item) {
            $epocEvaluation->ratings()->create([
                'domain' => $item['domain'],
                'indicator' => $item['indicator'],
                'rating' => $item['rating'] ?? null,
                'comments' => $item['comments'] ?? null,
            ]);
        }

        // Calculate overall score (average of non-null ratings, scale 1-5)
        $rated = $epocEvaluation->ratings()->whereNotNull('rating');
        $avgRating = $rated->exists() ? $rated->avg('rating') : null;
        $epocEvaluation->update(['overall_score' => $avgRating]);

        return redirect()->route('supervisor.observations.show', $observation->id)
            ->with('success', 'EPOC evaluation has been saved successfully.');
    }

    /**
     * Download EPOC evaluation as DOCX document
     */
    public function downloadEpoc(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->isSchoolHeadObservation()) {
            return back()->with('error', 'The EPOC evaluation is only available for school head observations.');
        }

        if (!$observation->epocEvaluation) {
            return back()->with('error', 'No EPOC evaluation has been completed for this observation.');
        }

        $service = new \App\Services\CotDocumentService();
        $path = $service->generateEpocDocument($observation);
        $filename = basename($path);

        return Storage::disk(CotDocumentService::DISK)
            ->download($path, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Save the pre-conference agenda checklist state.
     */
    public function saveAgendaChecklist(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $validated = $request->validate([
            'checked' => 'required|array',
            'checked.*' => 'integer|min:0',
        ]);

        $existing = $observation->preConference;
        $merged = array_merge(
            $existing?->form_responses ?? [],
            ['agenda_checklist' => $validated['checked']]
        );

        $observation->preConference()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['form_responses' => $merged]
        );

        return response()->json(['ok' => true]);
    }

    public function autosave(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        $stage = $request->input('stage', 'observation');
        $savedFields = [];

        $schoolYear = $observation->school_year ?? config('cot.default_version', date('Y').'-'.(date('Y') + 1));
        $obsType = $observation->observation_type;

        if ($stage === 'observation') {
            // School Head observations use the EPOC instrument.
            if ($observation->isSchoolHeadObservation()) {
                if ($request->has('epoc_ratings') && is_array($request->input('epoc_ratings'))) {
                    $epoc = $observation->epocEvaluation ?? new \App\Models\EpocEvaluation();
                    if (! $epoc->exists) {
                        $epoc = $observation->epocEvaluation()->create([
                            'school_head_name' => $observation->schoolHead?->name,
                            'observation_date' => $observation->observation_date,
                        ]);
                    }
                    foreach ($request->input('epoc_ratings') as $item) {
                        $hasRating = array_key_exists('rating', $item) && $item['rating'] !== null && $item['rating'] !== '';
                        // Untouched rows carry no rating value; preserve previously
                        // saved ratings rather than overwriting them with null.
                        if (! $hasRating) {
                            continue;
                        }
                        \App\Models\EpocRating::updateOrCreate(
                            ['epoc_evaluation_id' => $epoc->id, 'indicator' => $item['indicator']],
                            [
                                'domain' => $item['domain'] ?? null,
                                'rating' => $item['rating'],
                                'comments' => $item['comments'] ?? null,
                            ]
                        );
                    }
                    if ($request->has('epoc_narrative_observation')) {
                        $epoc->narrative_observation = $request->input('epoc_narrative_observation');
                    }
                    if ($request->has('epoc_agreement')) {
                        $epoc->agreement = $request->input('epoc_agreement');
                    }
                    $epoc->save();
                    $savedFields[] = 'epoc_ratings';
                }
            } elseif ($request->has('ratings') && is_array($request->input('ratings'))) {
                foreach ($request->input('ratings') as $item) {
                    if (empty($item['indicator_code'])) {
                        continue;
                    }

                    // Untouched rows carry no rating, not_observed or not_applicable value; do
                    // not overwrite previously saved data with a null rating.
                    $hasRating = array_key_exists('rating', $item) && $item['rating'] !== null && $item['rating'] !== '';
                    $hasNo = ! empty($item['not_observed']);
                    $hasNa = ! empty($item['not_applicable']);
                    if (! $hasRating && ! $hasNo && ! $hasNa) {
                        continue;
                    }

                    CotRating::updateOrCreate(
                        ['observation_id' => $observation->id, 'indicator_code' => $item['indicator_code']],
                        [
                            'domain' => $item['domain'] ?? null,
                            'indicator' => $item['indicator'] ?? null,
                            'rating' => ($hasNo || $hasNa) ? null : $item['rating'],
                            'not_observed' => $hasNo,
                            'not_applicable' => $hasNa,
                            'comments' => $item['comments'] ?? null,
                        ]
                    );
                }
                $savedFields[] = 'ratings';
            }

            if ($request->has('other_comments')) {
                $observation->notes = $request->input('other_comments');
                $savedFields[] = 'other_comments';
            }

            $starNotes = $request->input('star_notes');
            $supervisorNotes = $request->input('supervisor_notes');
            if ($request->has('star_notes') || $request->has('supervisor_notes')) {
                $observation->postConference()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    [
                        'star_notes' => $starNotes ?: null,
                        'supervisor_notes' => $supervisorNotes ?: null,
                    ]
                );
                $savedFields = array_merge($savedFields, ['star_notes', 'supervisor_notes']);
            }

            $observation->save();
        } else {
            $configs = [
                'pre_observation_planning' => [
                    'relation' => 'preObservationPlanning',
                    'fillable' => ['ai_insights', 'suggested_focus', 'supervisor_notes', 'observation_tool'],
                ],
                'pre_conference' => [
                    'relation' => 'preConference',
                    'fillable' => ['discussion_notes', 'finalized_focus', 'teacher_reflection', 'lesson_plan_review', 'instructional_materials', 'conference_date', 'topic', 'learning_objectives', 'teaching_strategies', 'assessment_activity', 'expected_challenges', 'feedback_areas'],
                ],
                'post_conference' => [
                    'relation' => 'postConference',
                    'fillable' => ['ai_comparison', 'feedback', 'star_notes', 'areas_for_improvement', 'challenges_facing_teacher', 'ideas_for_addressing_challenges', 'prioritized_next_steps', 'teacher_reflection', 'supervisor_notes', 'conference_date'],
                ],
            ];

            if (! isset($configs[$stage])) {
                return response()->json(['ok' => false, 'message' => 'Unknown autosave stage.'], 422);
            }

            $save = [];
            foreach ($configs[$stage]['fillable'] as $field) {
                if ($request->has($field)) {
                    $save[$field] = $request->input($field);
                    $savedFields[] = $field;
                }
            }

            // Persist any form-template driven fields via the standard parser.
            $save = array_merge(
                $save,
                $this->formTemplateService->parseFormData($schoolYear, $stage, $request->all(), $obsType)
            );

            if (! empty($save)) {
                $observation->{$configs[$stage]['relation']}()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    $save
                );
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Draft saved.',
            'saved_fields' => array_values(array_unique($savedFields)),
            'saved_at' => now()->format('g:i:s A'),
        ]);
    }
}
