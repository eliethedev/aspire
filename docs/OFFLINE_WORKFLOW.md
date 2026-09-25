# ASPIRE Offline Clinical-Supervision Workflow

IT-adviser-mandated sequence: schedule → teacher confirm + DLL → AI prompts →
offline package download → zero-connectivity encoding → push sync onto the
same observation.

## State machine (`observations.status`)

```
scheduled ─┐  legacy rows: scheduled + confirmation_status=pending
pending_teacher_confirmation ─┘  (identical via isPendingTeacherConfirmation())
        │  teacher accepts + uploads DLL  →  POST confirm-package
        ▼
confirmed_ready_for_download  (AI prompts queued/generated)
        │  supervisor reads the DLL + ticks the required review box,
        │  then prepares + downloads the bundle
        ▼
downloaded_offline  (tablet encodes with zero connectivity)
        │  POST /sync/push with server_id
        ▼
synced  (+ post-observation analytics queued) ──► finalized (existing flow)
```

`completed_offline` exists in the enum as the tablet outbox state; the server
row moves straight to `synced` on a successful push (mandate-literal).

`scheduled` is intentionally kept as the creation status: ~30 existing
counters filter on it literally. `scheduled` + `confirmation_status=pending`
IS the pending state (see `Observation::isPendingTeacherConfirmation()`).

## Mandate → implementation map

| Mandate | Implementation |
|---|---|
| Migration columns | `2026_09_26_000001_add_offline_workflow_to_observations_table` (`teacher_confirmed_at`, `lesson_plan_path`, `lesson_plan_summary`, `pre_observation_ai_prompts` JSON, `offline_downloaded_at`; `client_id`/`sync_status` already existed) |
| `POST /api/observations/{id}/confirm-by-teacher` | `POST /teacher/observations/{observation}/confirm-package` (`OfflineWorkflowController@confirmByTeacher`) — same shape, session guard (see below) |
| `GET /api/observations/{id}/offline-package` | `GET …/offline-package` (`offlinePackage`) + `POST …/prepare-package` (`preparePackage`, **requires the observer's lesson-plan review confirmation**) + `GET …/offline-workspace` (cached shell) for supervisor AND school-head observers |
| `POST /api/sync/push` | Existing `POST /sync/push` (`Api\SyncController@push`, HTTP 207) extended with the `server_id` update path (`storeOfflineWorkflowItem`) |
| `GeneratePreObservationAiPromptsJob` | `app/Jobs/GeneratePreObservationAiPromptsJob.php` (queued on confirm; sync-safe) |
| Dexie `offline_packages` / `outbox_*` | `public/js/aspire-offline-package.js` (dependency-free IDB layer, same API shape; `window.AspireOfflinePackage`) |

## Deliberate deviations (environment-forced)

1. **Session auth instead of Sanctum Bearer.** Packagist is unreachable from
   this environment so `laravel/sanctum` cannot be installed. The tablet PWA
   is same-origin, so the session cookie + `X-CSRF-TOKEN` header (the pattern
   `offline-encode.js` already proves) is sufficient and avoids long-lived
   tokens on shared school tablets. Migration path: install Sanctum, move the
   three `OfflineWorkflowController` routes + `sync/*` to `routes/api.php`
   behind `auth:sanctum` — controllers only use `Auth::user()`, no logic change.
2. **No Dexie.js** (npm unreachable). The bundled IDB helper mirrors the
   needed Dexie surface (`put/get/delete/all` per table); swapping the file
   for real Dexie requires zero caller edits.
3. **No `routes/api.php`.** `bootstrap/app.php` only loads `web.php`; the JSON
   endpoints live beside the existing `/sync/*` group under `auth` middleware.

## Edge cases handled

- **AI outage:** `OfflinePackageService` degrades to deterministic
  rubric-derived prompts (`provider: rule-based-fallback`, `fallback: true`);
  the tablet labels them honestly. A failed AI call never blocks a visit.
- **Duplicate submission:** observation `client_id` + per-rating `client_id`
  make pushes idempotent (`already_synced`); re-confirm is a 200 no-op.
- **Server-side ratings exist:** push returns `server_already_rated` conflict
  instead of overwriting; tablet marks the record `conflict` with the reason.
- **Unowned / unconfirmed rows:** `not_observer` / `not_confirmed` conflicts.
- **419 mid-offline:** outbox untouched; user re-logs in online, nothing lost.
- **Old teacher flow:** classic Confirm auto-promotes to
  `confirmed_ready_for_download` when a DLL is already on file, so both
  buttons converge on one gate.
- **Shared tablets:** workspace shell is SW-cached per URL; the JSON bundle
  lives in IndexedDB. Logging out does not wipe the outbox (by design — the
  queue survives until `synced`).

## Ops

- Run `php artisan migrate` (adds enum values + columns; SQLite-safe).
- Queue must run (`QUEUE_CONNECTION=database` + worker) for AI prompt jobs;
  `prepare-package` also works synchronously while online.
- Cached shell: open each `offline-workspace` URL once online; SW version
  `aspire-offline-v4` caches it runtime-first-visit.
- Tests: `php artisan test --filter=OfflineWorkflowTest` (8 tests, AI-disabled
  fallback path exercised deterministically).
