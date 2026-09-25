<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePostObservationFeedback;
use App\Models\CotIndicatorVersion;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Offline-first sync (Architecture B).
 *
 * Tablet encodes with zero connectivity into IndexedDB, then pushes here
 * when signal returns. All writes are idempotent by client_id.
 */
class SyncController extends Controller
{
    /**
     * Pre-trip bundle: everything the tablet needs to encode offline.
     * Call this while online before visiting the school.
     */
    public function bootstrap(Request $request)
    {
        return response()->json($this->buildBundle(Auth::user()));
    }

    /**
     * Offline capture page with the bundle rendered server-side, so the
     * teacher/head lists work on first paint even if the browser later
     * fails to fetch or run the IndexedDB layer.
     */
    public function offlinePage()
    {
        $user = Auth::user();
        $bundle = $this->buildBundle($user);

        return view('supervisor.observations.offline', [
            'bundle' => $bundle,
            'schoolName' => $user->school?->name,
        ]);
    }

    /**
     * Shared bundle builder for the JSON endpoint and the capture page.
     */
    public function buildBundle(User $user): array
    {
        $schoolYear = $this->currentSchoolYear();

        $teachers = Teacher::query()
            ->with(['user:id,name,email,school_id', 'subjects:id,name'])
            ->where('school_id', $user->school_id)
            ->get()
            ->filter(fn ($t) => $t->user !== null)
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->user->name,
                'email' => $t->user->email,
                'subjects' => $t->subjects->pluck('name')->values()->all(),
                'grade_level' => $t->grade_level,
                'position' => $t->position,
            ])
            ->values();

        // School heads observable offline (profile id is the observee_id,
        // mirroring the online wizard). Includes school name so the tablet
        // can tell same-school heads apart when the list is division-wide.
        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user:id,name,email,school_id', 'user.school:id,name', 'school:id,name'])
            ->get()
            ->filter(fn ($h) => $h->user !== null)
            ->map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->user->name,
                'email' => $h->user->email,
                'school_name' => $h->school_name ?? 'No school assigned',
                'position' => $h->position_level_label ?? $h->position ?? $h->current_designation ?? 'School Head',
            ])
            ->values();

        // Already-scheduled observations on the server so the tablet can warn
        // before re-capturing the same teacher/date offline (which the push
        // endpoint would reject as a possible duplicate).
        $scheduledRows = Observation::query()
            ->where('observer_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('observation_date')
            ->limit(50)
            ->get(['id', 'observation_type', 'observee_id', 'observee_type', 'observation_date', 'subject', 'stage', 'status']);

        $schedTeacherIds = $scheduledRows->where('observee_type', Teacher::class)->pluck('observee_id')->unique()->values();
        $schedHeadIds = $scheduledRows->where('observee_type', SchoolHeadProfile::class)->pluck('observee_id')->unique()->values();

        $schedTeacherNames = Teacher::with('user:id,name')->whereIn('id', $schedTeacherIds)->get()
            ->mapWithKeys(fn ($t) => [$t->id => $t->user?->name ?? ('Teacher #'.$t->id)]);
        $schedHeadNames = SchoolHeadProfile::with('user:id,name')->whereIn('id', $schedHeadIds)->get()
            ->mapWithKeys(fn ($h) => [$h->id => $h->user?->name ?? ('School head #'.$h->id)]);

        $scheduled = $scheduledRows->map(function ($o) use ($schedTeacherNames, $schedHeadNames) {
            $isHead = $o->observee_type === SchoolHeadProfile::class;

            return [
                'server_id' => $o->id,
                'observation_type' => $o->observation_type,
                'observee_id' => $o->observee_id,
                'observee_name' => $isHead
                    ? ($schedHeadNames[$o->observee_id] ?? ('School head #'.$o->observee_id))
                    : ($schedTeacherNames[$o->observee_id] ?? ('Teacher #'.$o->observee_id)),
                'observation_date' => $o->observation_date?->format('Y-m-d'),
                'subject' => $o->subject,
                'stage' => $o->stage,
                'status' => $o->status,
            ];
        })->values();

        // Read-only history for field review: finished work (completed or
        // cancelled) with its ratings. Upcoming/in-progress items already
        // appear under `scheduled`, so this split keeps both lists disjoint.
        // Notes are truncated to bound the bundle size for weak devices.
        $historyRows = Observation::query()
            ->where('observer_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderByDesc('observation_date')
            ->limit(30)
            ->get(['id', 'observation_type', 'observee_id', 'observee_type', 'observation_date', 'subject', 'grade_level', 'notes', 'overall_score', 'stage', 'status']);

        $histTeacherIds = $historyRows->where('observee_type', Teacher::class)->pluck('observee_id')->unique()->values();
        $histHeadIds = $historyRows->where('observee_type', SchoolHeadProfile::class)->pluck('observee_id')->unique()->values();

        $histTeacherNames = Teacher::with('user:id,name')->whereIn('id', $histTeacherIds)->get()
            ->mapWithKeys(fn ($t) => [$t->id => $t->user?->name ?? ('Teacher #'.$t->id)]);
        $histHeadNames = SchoolHeadProfile::with('user:id,name')->whereIn('id', $histHeadIds)->get()
            ->mapWithKeys(fn ($h) => [$h->id => $h->user?->name ?? ('School head #'.$h->id)]);

        $historyObsIds = $historyRows->pluck('id');
        $historyRatings = CotRating::whereIn('observation_id', $historyObsIds)
            ->get(['observation_id', 'indicator_code', 'domain', 'rating', 'not_observed', 'not_applicable', 'comments'])
            ->groupBy('observation_id');

        $history = $historyRows->map(function ($o) use ($histTeacherNames, $histHeadNames, $historyRatings) {
            $isHead = $o->observee_type === SchoolHeadProfile::class;

            return [
                'server_id' => $o->id,
                'observation_type' => $o->observation_type,
                'observee_name' => $isHead
                    ? ($histHeadNames[$o->observee_id] ?? ('School head #'.$o->observee_id))
                    : ($histTeacherNames[$o->observee_id] ?? ('Teacher #'.$o->observee_id)),
                'observation_date' => $o->observation_date?->format('Y-m-d'),
                'subject' => $o->subject,
                'grade_level' => $o->grade_level,
                'notes' => $o->notes ? mb_substr($o->notes, 0, 500) : null,
                'overall_score' => $o->overall_score !== null ? (float) $o->overall_score : null,
                'stage' => $o->stage,
                'status' => $o->status,
                'ratings' => ($historyRatings[$o->id] ?? collect())->map(fn ($r) => [
                    'indicator_code' => $r->indicator_code,
                    'domain' => $r->domain,
                    'rating' => $r->rating,
                    'not_observed' => (bool) $r->not_observed,
                    'not_applicable' => (bool) $r->not_applicable,
                    'comments' => $r->comments,
                ])->values(),
            ];
        })->values();

        $cotTemplates = CotIndicatorVersion::query()
            ->with(['indicators' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->where('school_year', $schoolYear)
            ->published()
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'label' => $v->label,
                'is_default' => (bool) $v->is_default,
                'rating_scale' => $v->ratingScale(),
                'indicators' => $v->indicators->map(fn ($i) => [
                    'code' => $i->code,
                    'domain' => $i->domain,
                    'description' => $i->description,
                ])->values(),
            ])
            ->values();

        return [
            'school_year' => $schoolYear,
            'server_time' => now()->toIso8601String(),
            'teachers' => $teachers,
            'school_heads' => $schoolHeads,
            'cot_templates' => $cotTemplates,
            'scheduled' => $scheduled,
            'history' => $history,
        ];
    }

    /**
     * Upload one evidence file queued offline for an already-synced observation.
     *
     * Two-phase by design: the JSON push runs first (creating the observation),
     * then each blob follows as multipart. Idempotent by file_id — retried
     * uploads of the same tablet file are acknowledged without duplicating.
     * Entry format matches the online flow ({path, original_name, size,
     * mime_type}) plus client_file_id, so existing views render both sources.
     */
    public function pushFiles(Request $request)
    {
        $validated = $request->validate([
            'observation_client_id' => ['required', 'uuid'],
            'file_id' => ['required', 'uuid'],
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,pdf,doc,docx'],
        ]);

        $observation = Observation::where('client_id', $validated['observation_client_id'])->first();

        if (! $observation) {
            return response()->json([
                'message' => 'Observation not found on the server. Sync the observation first, then its files.',
            ], 404);
        }

        if ((int) $observation->observer_id !== (int) Auth::id()) {
            return response()->json(['message' => 'You are not authorized for this observation.'], 403);
        }

        $files = $observation->evidence_files ?? [];
        foreach ($files as $existing) {
            if (($existing['client_file_id'] ?? null) === $validated['file_id']) {
                return response()->json([
                    'status' => 'already_uploaded',
                    'server_id' => $observation->id,
                ], 200);
            }
        }

        $uploaded = $request->file('file');
        $files[] = [
            'path' => $uploaded->store('observation_evidences', 'public'),
            'original_name' => $uploaded->getClientOriginalName(),
            'size' => $uploaded->getSize(),
            'mime_type' => $uploaded->getMimeType(),
            'client_file_id' => $validated['file_id'],
            'synced_from' => 'offline',
        ];
        $observation->update(['evidence_files' => $files]);

        return response()->json([
            'status' => 'uploaded',
            'server_id' => $observation->id,
        ], 201);
    }

    /**
     * Push offline outbox items. Always returns 207 multi-status so the
     * client can mark each item synced / conflict / error independently.
     */
    public function push(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'max:50'],
            'items.*.client_id' => ['required', 'uuid'],
            'items.*.device_updated_at' => ['nullable', 'date'],
            // Offline-workflow items reference the PRE-SCHEDULED observation
            // (confirmed + downloaded) instead of creating a new one.
            'items.*.server_id' => ['nullable', 'integer'],
            'items.*.payload' => ['required', 'array'],
        ]);

        $synced = [];
        $conflicts = [];
        $errors = [];

        foreach ($validated['items'] as $item) {
            try {
                $result = $this->storeItem($item, $request->input('device_id'));
                if ($result['status'] === 'synced' || $result['status'] === 'already_synced') {
                    $synced[] = $result;
                } else {
                    $conflicts[] = $result;
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errors[] = [
                    'client_id' => $item['client_id'],
                    'reason' => 'validation_failed',
                    'messages' => $e->errors(),
                ];
            } catch (\Throwable $e) {
                Log::error('Sync push item failed', [
                    'client_id' => $item['client_id'],
                    'error' => $e->getMessage(),
                ]);
                $errors[] = [
                    'client_id' => $item['client_id'],
                    'reason' => 'server_error',
                    'message' => config('app.debug') ? $e->getMessage() : 'Failed to save. Try again.',
                ];
            }
        }

        return response()->json([
            'synced' => $synced,
            'conflicts' => $conflicts,
            'errors' => $errors,
        ], 207);
    }

    protected function storeItem(array $item, ?string $deviceId): array
    {
        $user = Auth::user();
        $clientId = $item['client_id'];
        $payload = $item['payload'];
        $deviceUpdatedAt = $item['device_updated_at'] ?? null;

        // Idempotency: already pushed from this tablet.
        $existing = Observation::where('client_id', $clientId)->first();
        if ($existing) {
            return [
                'client_id' => $clientId,
                'server_id' => $existing->id,
                'status' => 'already_synced',
                'ai_status' => $existing->ai_status,
            ];
        }

        // Offline-workflow path: the tablet encoded ratings for a
        // PRE-SCHEDULED, teacher-confirmed observation. Update it in place —
        // never create a duplicate — keyed by server_id + observer ownership.
        if (! empty($item['server_id'])) {
            return $this->storeOfflineWorkflowItem($item, $deviceId);
        }

        $data = validator($payload, [
            'observation_type' => ['required', 'in:teacher_observation,school_head_observation'],
            // observee existence is resolved per type below (teachers vs
            // school-head profiles live in different tables).
            'observee_id' => ['required', 'integer'],
            'observation_date' => ['required', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'school_year' => ['nullable', 'string', 'max:20'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:3'],
            'cot_indicator_version_id' => ['nullable', 'integer', 'exists:cot_indicator_versions,id'],
            'ratings' => ['nullable', 'array', 'max:50'],
            'ratings.*.indicator_code' => ['required_with:ratings', 'string', 'max:50'],
            'ratings.*.rating' => ['nullable', 'integer', 'min:2', 'max:8'],
            'ratings.*.not_observed' => ['nullable', 'boolean'],
            'ratings.*.comments' => ['nullable', 'string', 'max:2000'],
            'ratings.*.client_id' => ['nullable', 'uuid'],
            'ratings.*.domain' => ['nullable', 'string', 'max:100'],
            'ratings.*.indicator' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $isSchoolHead = $data['observation_type'] === 'school_head_observation';

        if ($isSchoolHead) {
            $observee = SchoolHeadProfile::with('user')->find($data['observee_id']);
            $observeeClass = SchoolHeadProfile::class;
            $observeeLabel = 'school head';
        } else {
            $observee = Teacher::with('user')->find($data['observee_id']);
            $observeeClass = Teacher::class;
            $observeeLabel = 'teacher';
        }

        if (! $observee) {
            return [
                'client_id' => $clientId,
                'status' => 'conflict',
                'reason' => 'observee_not_found',
                'message' => "This {$observeeLabel} no longer exists on the server.",
            ];
        }

        // School scoping mirrors the online flow: teachers must belong to the
        // supervisor's school; school heads follow the same rule supervisors
        // get on the school-head profile page (unrestricted when the
        // supervisor has no school assigned).
        $observeeSchool = $observee->school_id ?? $observee->user?->school_id;
        if ($isSchoolHead) {
            $blocked = $user->school_id
                && $observeeSchool
                && (int) $observeeSchool !== (int) $user->school_id;
        } else {
            $blocked = $user->school_id && (int) $observeeSchool !== (int) $user->school_id;
        }
        if ($blocked) {
            return [
                'client_id' => $clientId,
                'status' => 'conflict',
                'reason' => $isSchoolHead ? 'school_head_not_in_school' : 'teacher_not_in_school',
                'message' => "This {$observeeLabel} does not belong to your school.",
            ];
        }

        // Simple duplicate guard: same observer + ratee + date + subject already synced.
        $duplicate = Observation::where('observer_id', $user->id)
            ->where('observee_id', $observee->id)
            ->where('observee_type', $observeeClass)
            ->whereDate('observation_date', $data['observation_date'])
            ->where('status', '!=', 'cancelled')
            ->when(! empty($data['subject']), fn ($q) => $q->where('subject', $data['subject']))
            ->exists();
        if ($duplicate) {
            return [
                'client_id' => $clientId,
                'status' => 'conflict',
                'reason' => 'possible_duplicate',
                'message' => 'A matching observation already exists on the server. Review before re-pushing.',
            ];
        }

        // School-head observations use the EPOC instrument on the server, so
        // any COT ratings accidentally queued with them are dropped.
        $ratings = $isSchoolHead ? [] : ($data['ratings'] ?? []);

        return DB::transaction(function () use ($user, $observee, $observeeClass, $data, $isSchoolHead, $ratings, $clientId, $deviceUpdatedAt, $deviceId) {
            $schoolYear = $data['school_year'] ?? $this->currentSchoolYear();

            $observation = Observation::create([
                'client_id' => $clientId,
                'sync_source' => 'offline',
                'sync_status' => 'synced',
                'device_updated_at' => $deviceUpdatedAt,
                'ai_status' => empty($ratings) ? 'none' : 'pending',
                'server_version' => 1,
                'observer_id' => $user->id,
                'observer_type' => User::class,
                'observee_id' => $observee->id,
                'observee_type' => $observeeClass,
                'observation_type' => $data['observation_type'],
                'observation_date' => $data['observation_date'],
                'subject' => $data['subject'] ?? null,
                'grade_level' => $data['grade_level'] ?? null,
                'notes' => $data['notes'] ?? null,
                'school_year' => $schoolYear,
                'quarter' => $data['quarter'] ?? null,
                'stage' => 'observation',
                'status' => 'in_progress',
                'cot_indicator_version_id' => $isSchoolHead ? null : ($data['cot_indicator_version_id'] ?? null),
            ]);

            $observation->logChange([
                'from_status' => null,
                'to_status' => 'in_progress',
                'notes' => 'Created via offline sync'.($deviceId ? " (device {$deviceId})" : ''),
            ]);

            $ratingIds = [];
            foreach ($ratings as $r) {
                // Per-rating idempotency (tablet retry of a half-pushed batch).
                if (! empty($r['client_id']) && ($dup = CotRating::where('client_id', $r['client_id'])->first())) {
                    $ratingIds[] = $dup->id;
                    continue;
                }

                $rating = CotRating::create([
                    'client_id' => $r['client_id'] ?? null,
                    'device_updated_at' => $deviceUpdatedAt,
                    'observation_id' => $observation->id,
                    'indicator_code' => $r['indicator_code'],
                    // domain/indicator are NOT NULL in schema; fall back to
                    // the indicator code when the tablet sent a bare rating.
                    'domain' => $r['domain'] ?? 'General',
                    'indicator' => $r['indicator'] ?? $r['indicator_code'],
                    'rating' => $r['rating'] ?? null,
                    'not_observed' => (bool) ($r['not_observed'] ?? false),
                    'not_applicable' => false,
                    'comments' => $r['comments'] ?? null,
                ]);
                $ratingIds[] = $rating->id;

                // Deferred cloud AI: queue, never run inline during sync.
                try {
                    GeneratePostObservationFeedback::dispatch($rating);
                } catch (\Throwable $e) {
                    Log::warning('Failed to dispatch AI job for synced rating', [
                        'rating_id' => $rating->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'rating_ids' => $ratingIds,
                'status' => 'synced',
                'ai_status' => $observation->ai_status,
            ];
        });
    }

    protected function currentSchoolYear(): string
    {
        $now = now();
        $year = $now->month >= 8 ? $now->year : $now->year - 1;

        return $year.'-'.($year + 1);
    }

    /**
     * Offline-workflow sync (IT adviser mandate): write tablet-encoded COT
     * ratings + STAR notes back onto the PRE-SCHEDULED observation.
     *
     * Guards:
     * - server_id must exist and belong to the pushing observer;
     * - the observation must have passed teacher confirmation (package states);
     * - per-rating idempotency by rating client_id (tablet retries safe);
     * - if the server already holds ratings for this observation, the push
     *   is a `server_already_rated` conflict instead of a silent overwrite —
     *   the supervisor resolves it online.
     *
     * On success the row moves to `synced`, overall_score is recomputed, and
     * post-observation analytics jobs are queued (never inline).
     */
    protected function storeOfflineWorkflowItem(array $item, ?string $deviceId): array
    {
        $user = Auth::user();
        $clientId = $item['client_id'];
        $payload = $item['payload'];
        $deviceUpdatedAt = $item['device_updated_at'] ?? null;

        $observation = Observation::with('cotRatings')->find($item['server_id']);

        if (! $observation) {
            return [
                'client_id' => $clientId,
                'status' => 'conflict',
                'reason' => 'observation_not_found',
                'message' => 'This observation no longer exists on the server.',
            ];
        }

        $isOwner = (int) $observation->observer_id === (int) $user->id;
        $isAssignedHead = $observation->school_head_id !== null
            && (int) $observation->school_head_id === (int) $user->id;

        if (! $isOwner && ! $isAssignedHead) {
            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'status' => 'conflict',
                'reason' => 'not_observer',
                'message' => 'You are not the observer for this observation.',
            ];
        }

        if (! in_array($observation->status, ['confirmed_ready_for_download', 'downloaded_offline', 'synced'], true)) {
            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'status' => 'conflict',
                'reason' => 'not_confirmed',
                'message' => 'The teacher has not confirmed this observation yet.',
            ];
        }

        // Duplicate-submission prevention: a retry of an already-applied
        // tablet save carries the same observation client_id.
        if ($observation->client_id && $observation->client_id === $clientId) {
            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'status' => 'already_synced',
                'ai_status' => $observation->ai_status,
            ];
        }

        // The tablet is the source of truth for a strictly-offline encoding,
        // so server-side ratings mean someone encoded online meanwhile.
        if ($observation->cotRatings->isNotEmpty()) {
            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'status' => 'conflict',
                'reason' => 'server_already_rated',
                'message' => 'Ratings already exist on the server. Review online before re-pushing.',
            ];
        }

        $data = validator($payload, [
            'notes' => ['nullable', 'string', 'max:10000'],
            'star_notes' => ['nullable', 'string', 'max:10000'],
            'ratings' => ['required', 'array', 'min:1', 'max:50'],
            'ratings.*.indicator_code' => ['required', 'string', 'max:50'],
            'ratings.*.rating' => ['nullable', 'integer', 'min:2', 'max:8'],
            'ratings.*.not_observed' => ['nullable', 'boolean'],
            'ratings.*.not_applicable' => ['nullable', 'boolean'],
            'ratings.*.comments' => ['nullable', 'string', 'max:2000'],
            'ratings.*.client_id' => ['nullable', 'uuid'],
            'ratings.*.domain' => ['nullable', 'string', 'max:100'],
            'ratings.*.indicator' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        return DB::transaction(function () use ($user, $observation, $data, $clientId, $deviceUpdatedAt, $deviceId) {
            // Capture before update(): save() re-syncs originals afterwards.
            $fromStatus = $observation->status;
            $ratingIds = [];
            foreach ($data['ratings'] as $r) {
                if (! empty($r['client_id']) && ($dup = CotRating::where('client_id', $r['client_id'])->first())) {
                    $ratingIds[] = $dup->id;
                    continue;
                }

                $rating = CotRating::create([
                    'client_id' => $r['client_id'] ?? null,
                    'device_updated_at' => $deviceUpdatedAt,
                    'observation_id' => $observation->id,
                    'indicator_code' => $r['indicator_code'],
                    'domain' => $r['domain'] ?? 'General',
                    'indicator' => $r['indicator'] ?? $r['indicator_code'],
                    'rating' => $r['rating'] ?? null,
                    'not_observed' => (bool) ($r['not_observed'] ?? false),
                    'not_applicable' => (bool) ($r['not_applicable'] ?? false),
                    'comments' => $r['comments'] ?? null,
                ]);
                $ratingIds[] = $rating->id;

                try {
                    GeneratePostObservationFeedback::dispatch($rating);
                } catch (\Throwable $e) {
                    Log::warning('Failed to dispatch AI job for synced rating', [
                        'rating_id' => $rating->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Deterministic rule-based summary (same math as the tablet shows
            // offline): average of numeric ratings + adjectival band.
            $scores = collect($data['ratings'])
                ->where('not_observed', '!==', true)
                ->whereNotNull('rating')
                ->pluck('rating');
            $average = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
            $scaleMax = $observation->ratingScaleMax();

            $observation->update([
                'client_id' => $clientId,
                'sync_source' => 'offline',
                'sync_status' => 'synced',
                'device_updated_at' => $deviceUpdatedAt,
                'ai_status' => 'pending',
                'server_version' => ($observation->server_version ?? 0) + 1,
                'notes' => $data['notes'] ?? $observation->notes,
                'stage' => 'observation',
                'status' => 'synced',
                'overall_score' => $average,
            ]);

            $observation->logChange([
                'from_status' => $fromStatus,
                'to_status' => 'synced',
                'notes' => 'Offline tablet sync'.($deviceId ? " (device {$deviceId})" : ''),
            ]);

            return [
                'client_id' => $clientId,
                'server_id' => $observation->id,
                'rating_ids' => $ratingIds,
                'status' => 'synced',
                'overall_score' => $average,
                'descriptive' => CotRating::descriptiveTotal($average, (float) $scaleMax),
                'ai_status' => 'pending',
            ];
        });
    }
}
