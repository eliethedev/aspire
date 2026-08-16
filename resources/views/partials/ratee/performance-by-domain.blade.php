@php
    $domains = $rateeProfile['domain_summary'];
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Performance by Domain</h2>
        <span class="text-xs text-gray-400 dark:text-gray-500">Average rating across scored observations</span>
    </div>

    @forelse($domains as $domain)
        <div class="py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
            <div class="flex items-center justify-between gap-4 mb-1.5">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $domain['domain'] }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">
                    {{ number_format($domain['average_rating'], 2) }}
                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500">/ 6</span>
                </p>
            </div>
            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                <div class="h-full rounded-full {{ $domain['average_rating'] >= 4 ? 'bg-indigo-500' : 'bg-amber-500' }}"
                     style="width: {{ min(100, ($domain['average_rating'] / 6) * 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $domain['observation_count'] }} observation{{ $domain['observation_count'] === 1 ? '' : 's' }}</p>
        </div>
    @empty
        <div class="text-center py-8">
            <div class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">No scored observations yet.</p>
        </div>
    @endforelse
</div>
