{{-- Sync source + deferred-AI badges for an observation (Architecture B).
     Expects $observation. Real feedback always wins over the ai_status flag:
     counts come from withCount on the index, or from loaded relations on detail. --}}
@php
    $syncSource = $observation->sync_source ?? 'online';
    $aiFlag = $observation->ai_status ?? 'none';

    $readyCount = null;
    if (isset($observation->ai_ready_count)) {
        $readyCount = (int) $observation->ai_ready_count;
    } elseif ($observation->relationLoaded('cotRatings')
        && ($firstRating = $observation->cotRatings->first())
        && $firstRating->relationLoaded('aiFeedback')) {
        $readyCount = $observation->cotRatings->filter(fn ($r) => $r->aiFeedback !== null)->count();
    }

    $hasAi = $readyCount !== null ? $readyCount > 0 : $aiFlag === 'done';
@endphp
@if($syncSource === 'offline')
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold border bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/30 dark:text-sky-300 dark:border-sky-800" title="Encoded with zero connectivity, synced later">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
        Captured offline
    </span>
@endif
@if($hasAi)
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800" title="AI suggestions have generated">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        AI ready
    </span>
@elseif($aiFlag === 'pending')
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold border bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800" title="AI suggestions will generate on the server">
        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        AI pending
    </span>
@elseif($aiFlag === 'failed')
    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold border bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800" title="AI generation failed for this observation">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        AI failed
    </span>
@endif
