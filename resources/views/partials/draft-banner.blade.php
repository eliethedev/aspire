{{--
    Draft / workflow status banner shown on observation stage pages.
    Expects: $observation.
--}}
@if(!in_array($observation->status, ['completed', 'cancelled']))
<div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 mb-6 flex flex-col sm:flex-row sm:items-center gap-3" role="status">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        <div>
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Draft in progress</p>
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">Your progress is saved automatically. No need to click save between steps.</p>
        </div>
    </div>
    @if($observation->status)
        <span class="sm:ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            {{ ucfirst($observation->status) }}
        </span>
    @endif
</div>
@endif
