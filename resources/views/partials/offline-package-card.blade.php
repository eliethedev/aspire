{{-- Offline-package card for the observation show page (supervisor + school-head).
     Expects $observation. State-driven: waiting → lesson-plan review
     (required) → prepare → download. Download controls only render once the
     teacher has confirmed AND the observer has confirmed the review. --}}
@php
    $pkgState = $observation->status;
    $pkgReady = in_array($pkgState, ['confirmed_ready_for_download', 'downloaded_offline'], true)
        && $observation->confirmation_status === 'confirmed';
    $pkgReviewed = $observation->hasLessonPlanReview();
    $isSh = request()->routeIs('school-head.*');
    $prepRoute = $isSh ? 'school-head.observations.prepare-package' : 'supervisor.observations.prepare-package';
    $pkgRoute = $isSh ? 'school-head.observations.offline-package' : 'supervisor.observations.offline-package';
    $wsRoute = $isSh ? 'school-head.observations.offline-workspace' : 'supervisor.observations.offline-workspace';
    $dllPath = $observation->lesson_plan_path ?? $observation->preObservationPlanning?->lesson_plan_file;
    $dllName = $dllPath ? basename($dllPath) : null;
    $dllSummary = $observation->lesson_plan_summary;
@endphp
<div class="mock-panel" aria-label="Offline observation package">
    <div class="mock-panel-head">
        <h2>Offline package</h2>
        <span class="hint">
            @if($pkgState === 'confirmed_ready_for_download' && ! $pkgReviewed) Review the lesson plan
            @elseif($pkgState === 'confirmed_ready_for_download') Ready to prepare
            @elseif($pkgState === 'downloaded_offline') Ready for offline visit
            @elseif($pkgState === 'synced') Synced back
            @elseif($pkgState === 'finalized' || $pkgState === 'completed') Finalized
            @else Waiting on teacher
            @endif
        </span>
        <span id="offline-package-badge" data-state="checking" class="mock-status todo">Checking…</span>
    </div>
    <div class="mock-mod-body">
        @if($observation->isPendingTeacherConfirmation())
            <p style="font-size:12.5px;color:var(--m-muted)">
                The offline package unlocks after the teacher accepts the schedule and uploads the lesson plan.
                Current state: <strong>{{ ucwords(str_replace('_', ' ', $pkgState)) }}</strong>.
            </p>
        @elseif($pkgReady)
            {{-- Step 1 · Read the lesson plan (friendly review card) --}}
            <div style="border:1px solid var(--m-line);border-radius:8px;padding:12px;margin-bottom:12px;background:var(--m-panel-2)">
                <p style="font-size:12.5px;font-weight:650;color:var(--m-text);margin-bottom:6px">
                    <span class="mock-step-num" style="display:inline-flex;width:22px;height:22px;border-radius:50%;border:1px solid var(--m-line);align-items:center;justify-content:center;font-size:11px;font-weight:700;margin-right:6px;{{ $pkgReviewed ? 'background:rgba(63,185,80,.14);border-color:rgba(63,185,80,.5);color:var(--m-green)' : '' }}">{{ $pkgReviewed ? '✓' : '1' }}</span>
                    Read the teacher's lesson plan
                </p>
                @if($dllName)
                    <p style="font-size:12.5px;margin-bottom:4px">
                        <a href="{{ Storage::url($dllPath) }}" target="_blank" rel="noopener" class="link" style="font-weight:600">📄 {{ $dllName }}</a>
                        <span class="mock-mono"> · uploaded {{ $observation->preObservationPlanning?->updated_at?->diffForHumans() ?? 'recently' }}</span>
                    </p>
                    @if($dllSummary)
                        <p style="font-size:12px;color:var(--m-muted);margin-bottom:2px">{{ Str::limit($dllSummary, 220) }}</p>
                    @endif
                @else
                    <p style="font-size:12.5px;color:var(--m-amber)">No lesson-plan file on record yet — ask the teacher to upload the DLL first.</p>
                @endif
            </div>

            @if($pkgReviewed)
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <span class="mock-status done">✓ Reviewed{{ $observation->lesson_plan_reviewed_at ? ' · ' . $observation->lesson_plan_reviewed_at->diffForHumans() : '' }}</span>
                    <form method="POST" action="{{ route($prepRoute, $observation) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="mock-btn">⚙ Prepare Offline Package @if($observation->hasPreObservationPrompts())(refresh AI)@endif</button>
                    </form>
                    <button type="button" class="mock-btn primary" data-pkg-download="{{ $observation->id }}" data-pkg-url="{{ route($pkgRoute, $observation) }}">
                        ⬇ Download for Offline Use
                    </button>
                    <a class="mock-btn" href="{{ route($wsRoute, $observation) }}">Open offline workspace</a>
                </div>
            @else
                {{-- Step 2 · Confirm the review (required, one click) --}}
                <form method="POST" action="{{ route($prepRoute, $observation) }}" x-data="{ checked: false }" aria-label="Confirm lesson plan review">
                    @csrf
                    <label style="display:flex;gap:10px;align-items:flex-start;border:1px dashed var(--m-accent-line);border-radius:8px;padding:12px;margin-bottom:10px;cursor:pointer;background:var(--m-accent-soft)">
                        <input type="checkbox" name="lesson_plan_reviewed" value="1" x-model="checked" style="width:18px;height:18px;margin-top:1px;accent-color:#1f6feb" required>
                        <span style="font-size:12.5px;color:var(--m-text)">
                            <strong>I have read this lesson plan</strong> and it aligns with the observation focus.
                            <span style="display:block;font-weight:400;color:var(--m-muted);font-size:12px">Required before preparing the package. Your name and time are recorded as the reviewer.</span>
                        </span>
                    </label>
                    @if(isset($errors) && $errors->has('lesson_plan_reviewed'))
                        <p style="font-size:12px;color:var(--m-rose);margin-bottom:8px">{{ $errors->first('lesson_plan_reviewed') }}</p>
                    @endif
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                        <button type="submit" class="mock-btn primary" :disabled="!checked" :title="checked ? 'Generate AI prompts and unlock download' : 'Tick the review box first'">⚙ Confirm review &amp; prepare package</button>
                        <span style="font-size:12px;color:var(--m-muted)" x-text="checked ? 'Ready — this also unlocks Download.' : 'Tick the box to continue.'"></span>
                    </div>
                </form>
            @endif

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:10px">
                @if($pkgState === 'downloaded_offline')
                    <span style="font-size:12px;color:var(--m-muted)">Downloaded {{ $observation->offline_downloaded_at?->diffForHumans() }} · opens with zero connectivity.</span>
                @endif
                @if($observation->hasPreObservationPrompts())
                    <span style="font-size:12px;color:var(--m-muted)">AI prompts ready{{ ($observation->pre_observation_ai_prompts['fallback'] ?? false) ? ' (rule-based fallback)' : '' }}.</span>
                @endif
            </div>
        @endif
    </div>
</div>
<script src="{{ asset('js/aspire-offline-package.js') }}"></script>
<script>
(function () {
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('[data-pkg-download]');
        if (!btn || !window.AspireOfflinePackage) return;
        btn.disabled = true;
        window.AspireOfflinePackage.downloadObservationPackage(
            parseInt(btn.getAttribute('data-pkg-download'), 10),
            { url: btn.getAttribute('data-pkg-url') }
        ).then(function () { window.location.reload(); })
        .catch(function (e) { btn.disabled = false; alert(e.message || 'Download failed.'); });
    });
    if (window.AspireOfflinePackage) window.AspireOfflinePackage.updateSyncBadge();
})();
</script>
