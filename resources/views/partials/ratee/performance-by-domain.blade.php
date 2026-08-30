@php $domains = $rateeProfile['domain_summary']; @endphp
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-6 section-card">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Performance by Domain</h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">Avg across scored</span>
    </div>
    @forelse($domains as $domain)
        <div class="py-3.5 {{ !$loop->last ? 'border-b border-slate-100 dark:border-gray-800' : '' }}">
            <div class="flex items-center justify-between gap-4 mb-2">
                <p class="text-sm font-medium text-slate-800 dark:text-gray-100">{{ $domain['domain'] }}</p>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $domain['average_rating'] >= 4 ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400' : 'bg-amber-50 dark:bg-yellow-500/10 border-amber-200 dark:border-yellow-500/30 text-amber-700 dark:text-yellow-400' }}">
                    {{ number_format($domain['average_rating'], 2) }} <span class="font-medium opacity-70">/ 6</span>
                </span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 dark:bg-gray-800 overflow-hidden">
                <div class="h-full rounded-full {{ $domain['average_rating'] >= 4 ? 'bg-indigo-500' : 'bg-amber-500' }}" style="width: {{ min(100, ($domain['average_rating'] / 6) * 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1.5">{{ $domain['observation_count'] }} observation{{ $domain['observation_count'] === 1 ? '' : 's' }}</p>
        </div>
    @empty
        <div class="text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl bg-slate-50/50 dark:bg-gray-800">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-chart-column text-slate-400 dark:text-gray-500"></i>
            </div>
            <p class="text-sm font-medium text-slate-600 dark:text-gray-300">No scored observations yet.</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Domain averages appear after at least one rated COT.</p>
        </div>
    @endforelse
</div>
