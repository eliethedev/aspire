{{-- Lightweight offline encoding workflow (vanilla JS, no dependencies).
     Expects optional $observation (for per-observation pre-caching).
     - "Prepare for Offline" fetches GET /sync/bootstrap into IndexedDB store `offline_observations`
       and registers the Service Worker caching /supervisor/observations/offline + CSS/JS.
     - Badge #offline-mode-badge shows "Offline Mode (Saved Locally)" when offline or drafts exist.
     - [data-sync-now] posts IndexedDB `outbox` to POST /sync/push on reconnect / tap. --}}
<span id="offline-mode-badge" class="hidden items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800" role="status">
    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
    <span data-badge-label>Offline Mode (Saved Locally)</span>
</span>
<button type="button" data-prepare-offline @if(isset($observation)) data-observation-id="{{ $observation->id }}" @endif
    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300 text-sm font-semibold hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
    title="Fetch observation payload and cache it on this device for offline encoding">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
    Prepare for Offline
</button>
<button type="button" data-sync-now
    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors"
    title="Read the local outbox and POST it to /sync/push">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    Sync Now
</button>
@once
@push('scripts')
<script>window.ASPIRE_BASE_URL = @json(request()->getBaseUrl());</script>
<script src="{{ request()->getBaseUrl() }}/js/offline-encode.js"></script>
@endpush
@endonce
