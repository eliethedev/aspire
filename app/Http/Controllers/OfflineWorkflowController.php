<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePreObservationAiPromptsJob;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\OfflinePackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Offline clinical-supervision workflow (IT adviser mandate).
 *
 *   schedule (pending_teacher_confirmation)
 *     → teacher accepts + uploads DLL (confirmByTeacher)
 *     → status confirmed_ready_for_download, AI prompts queued
 *     → supervisor prepares + downloads bundle (preparePackage / offlinePackage)
 *     → status downloaded_offline, tablet encodes with zero connectivity
 *     → push sync writes back to THIS observation (Api\SyncController)
 *
 * Auth is the app's session guard (same-origin PWA + CSRF), mirroring the
 * existing /sync/* endpoints. If Sanctum is ever installed, move these
 * routes to routes/api.php behind `auth:sanctum` — no logic changes needed:
 * every method only uses Auth::user() + route-model binding.
 */
class OfflineWorkflowController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected OfflinePackageService $packages,
        protected AuditLogService $audit,
    ) {}

    /**
     * Teacher accepts the schedule and uploads the DLL in one step.
     * POST /teacher/observations/{observation}/confirm-package
     */
    public function confirmByTeacher(Request $request, Observation $observation)
    {
        $teacher = Auth::user()->teacher;

        if (! $teacher || (int) $observation->observee_id !== (int) $teacher->id
            || $observation->observee_type !== Teacher::class) {
            abort(403, 'This observation is not assigned to you.');
        }

        // Idempotent re-confirm: the tablet may retry on flaky signal.
        if ($observation->status === 'confirmed_ready_for_download'
            && $observation->confirmation_status === 'confirmed') {
            return $this->respond($request, ['status' => 'already_confirmed'], 200);
        }

        if (! $observation->isPendingTeacherConfirmation()) {
            return $this->respond($request, [
                'message' => 'This observation can no longer be confirmed.',
            ], 422);
        }

        $existingFile = $observation->lesson_plan_path
            ?? $observation->preObservationPlanning?->lesson_plan_file;

        $validated = $request->validate([
            'accept' => ['required', 'accepted'],
            'lesson_plan_file' => [
                $existingFile ? 'nullable' : 'required',
                'file', 'mimes:pdf,doc,docx', 'max:20480',
            ],
        ]);

        return DB::transaction(function () use ($request, $observation, $existingFile) {
            $lessonPath = $existingFile;

            if ($request->hasFile('lesson_plan_file')) {
                $file = $request->file('lesson_plan_file');
                $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $lessonPath = $file->storeAs('lesson_plans', $filename, 'public');

                if ($existingFile && $existingFile !== $lessonPath) {
                    Storage::disk('public')->delete($existingFile);
                }

                $observation->preObservationPlanning()->updateOrCreate(
                    ['observation_id' => $observation->id],
                    ['lesson_plan_file' => $lessonPath]
                );
            }

            // Extract text now so the bundle/summary never depend on the
            // queued job having run; extraction failure is non-fatal.
            $lessonText = $this->packages->lessonPlanText(
                $observation->setAttribute('lesson_plan_path', $lessonPath)
            );

            $observation->confirmWithLessonPlan(
                $lessonPath,
                $lessonText !== '' ? mb_substr($lessonText, 0, 2000) : null
            );

            $this->audit->log(
                'confirmed_with_lesson_plan', 'observations', (string) $observation->id,
                "Teacher confirmed observation #{$observation->id} and submitted DLL.",
                'success', [], $observation->toArray()
            );

            $observer = $observation->observer;
            if ($observer instanceof User) {
                $link = route('supervisor.observations.show', $observation);
                $this->notifications->notifyObservationConfirmed(
                    $observer, Auth::user()->name, $link
                );
            }

            GeneratePreObservationAiPromptsJob::dispatch($observation->id);

            return $this->respond($request, [
                'status' => 'confirmed_ready_for_download',
                'observation_id' => $observation->id,
                'ai_prompts' => 'queued',
            ], 200);
        });
    }

    /**
     * Supervisor runs AI prompt generation on demand before the trip.
     * Requires the observer's lesson-plan review confirmation first:
     * checking the box stores who reviewed and when; refreshes afterwards
     * skip the checkbox. POST .../prepare-package
     * (also mounted for school-head observers)
     */
    public function preparePackage(Request $request, Observation $observation)
    {
        $this->authorizeObserver($observation);

        if (! in_array($observation->status, ['confirmed_ready_for_download', 'downloaded_offline'], true)) {
            return $this->respond($request, [
                'message' => 'The teacher must confirm and upload the lesson plan first.',
                'required' => 'teacher_confirmation',
            ], 409);
        }

        $lessonPath = $observation->lesson_plan_path
            ?? $observation->preObservationPlanning?->lesson_plan_file;

        if (! $lessonPath || ! Storage::disk('public')->exists($lessonPath)) {
            return $this->respond($request, [
                'message' => 'No lesson plan is on file yet. Ask the teacher to upload the DLL first.',
                'required' => 'lesson_plan',
            ], 409);
        }

        if (! $observation->hasLessonPlanReview()) {
            $request->validate([
                'lesson_plan_reviewed' => ['required', 'accepted'],
            ], [
                'lesson_plan_reviewed.required' => 'Please confirm you reviewed the lesson plan first.',
                'lesson_plan_reviewed.accepted' => 'Please confirm you reviewed the lesson plan first.',
            ]);

            $observation->markLessonPlanReviewed(Auth::id());
        }

        // Synchronous on purpose: the supervisor is online and waiting for
        // the "ready" badge. Internal AI failures degrade to the
        // deterministic rubric fallback inside the service.
        $payload = $this->packages->generatePrompts($observation->fresh());

        return $this->respond($request, [
            'ai_ready' => true,
            'fallback' => (bool) ($payload['fallback'] ?? true),
            'provider' => $payload['provider'] ?? null,
            'generated_at' => $payload['generated_at'] ?? null,
        ], 200);
    }

    /**
     * The offline bundle JSON the tablet caches in IndexedDB.
     * GET /supervisor/observations/{observation}/offline-package
     */
    public function offlinePackage(Request $request, Observation $observation)
    {
        $this->authorizeObserver($observation);

        if (! $observation->isReadyForDownload()) {
            return $this->respond($request, [
                'message' => 'The offline package is locked until the teacher confirms and uploads the lesson plan.',
                'required' => 'teacher_confirmation',
                'status' => $observation->status,
            ], 409);
        }

        if (! $observation->hasLessonPlanReview()) {
            return $this->respond($request, [
                'message' => 'Please review the lesson plan and confirm your review before downloading.',
                'required' => 'lesson_plan_review',
                'status' => $observation->status,
            ], 409);
        }

        if ($observation->status === 'confirmed_ready_for_download') {
            $observation->markPackageDownloaded();
        }

        return response()->json($this->packages->buildPackage($observation->fresh()));
    }

    /**
     * Server-rendered offline workspace shell (service-worker cached, works
     * with zero connectivity; the JS layer hydrates the cached bundle).
     * GET /supervisor/observations/{observation}/offline-workspace
     */
    public function offlineWorkspace(Observation $observation)
    {
        $this->authorizeObserver($observation);

        if (! $observation->isReadyForDownload()) {
            // back(), not a role route: school-head observers cannot hit
            // supervisor URLs (role middleware would bounce them).
            return back()->with('error', 'The teacher must confirm and upload the lesson plan before offline use.');
        }

        if (! $observation->hasLessonPlanReview()) {
            return back()->with('error', 'Please review the lesson plan and confirm your review before offline use.');
        }

        if ($observation->status === 'confirmed_ready_for_download') {
            $observation->markPackageDownloaded();
        }

        return view('supervisor.observations.offline-package', [
            'observation' => $observation->fresh(),
            'bundle' => $this->packages->buildPackage($observation->fresh()),
        ]);
    }

    /**
     * Only the owning observer (or the assigned school head) may touch the
     * package. Teachers can never download another role's bundle: the route
     * lives outside their middleware group AND this check re-verifies.
     */
    protected function authorizeObserver(Observation $observation): void
    {
        $user = Auth::user();

        $isOwner = (int) $observation->observer_id === (int) $user->id;
        $isAssignedHead = $observation->school_head_id !== null
            && (int) $observation->school_head_id === (int) $user->id;

        if (! $isOwner && ! $isAssignedHead) {
            abort(403, 'You are not the observer for this observation.');
        }
    }

    /** JSON for API-style callers, redirect for classic form posts. */
    protected function respond(Request $request, array $data, int $status)
    {
        if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
            return response()->json($data, $status);
        }

        if ($status >= 400) {
            return back()->with('error', $data['message'] ?? 'Request failed.');
        }

        return back()->with('success', $data['message'] ?? 'Done.');
    }
}
