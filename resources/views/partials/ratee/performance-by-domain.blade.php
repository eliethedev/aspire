@php $domains = $rateeProfile['domain_summary']; @endphp
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 section-card">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Performance by Domain</h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">Avg across scored</span>
    </div>
    @forelse($domains as $domain)
        <div class="py-3.5 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
            <div class="flex items-center justify-between gap-4 mb-2">
                <p class="text-sm font-medium text-slate-800">{{ $domain['domain'] }}</p>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $domain['average_rating'] >= 4 ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700' }}">
                    {{ number_format($domain['average_rating'], 2) }} <span class="font-medium opacity-70">/ 6</span>
                </span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full {{ $domain['average_rating'] >= 4 ? 'bg-indigo-500' : 'bg-amber-500' }}" style="width: {{ min(100, ($domain['average_rating'] / 6) * 100) }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-1.5">{{ $domain['observation_count'] }} observation{{ $domain['observation_count'] === 1 ? '' : 's' }}</p>
        </div>
    @empty
        <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-chart-column text-slate-400"></i>
            </div>
            <p class="text-sm font-medium text-slate-600">No scored observations yet.</p>
            <p class="text-xs text-slate-500 mt-1">Domain averages appear after at least one rated COT.</p>
        </div>
    @endforelse
</div>
