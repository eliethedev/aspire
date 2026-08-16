@php
    $items = $rateeProfile['areas_attention'];
    $insufficient = $rateeProfile['attention_insufficient'];
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-1">Areas Requiring Attention</h2>
    <p class="text-xs text-gray-400 dark:text-gray-500 mb-5">
        Domains consistently rated below 4.0 across at least 2 observations &middot; support only &middot; no negative judgment
    </p>

    @if($insufficient)
        <div class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Not enough observation data yet.</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">A clearer picture becomes available after more scored observations.</p>
            </div>
        </div>
    @elseif($items->isEmpty())
        <div class="flex items-start gap-3 rounded-lg border border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-900/20 p-4">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-medium text-green-800 dark:text-green-300">No areas require attention right now.</p>
                <p class="text-xs text-green-700 dark:text-green-400 mt-0.5">All rated domains average at or above 4.0.</p>
            </div>
        </div>
    @else
        <ul class="space-y-3">
            @foreach($items as $item)
                <li class="flex items-center justify-between gap-4 rounded-lg border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-900/20 p-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['domain'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Average across {{ $item['observation_count'] }} observations</p>
                    </div>
                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400 shrink-0">{{ number_format($item['average_rating'], 2) }} / 6</span>
                </li>
            @endforeach
        </ul>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">Consider scheduling additional observations or a coaching conversation to support growth in these areas.</p>
    @endif
</div>
