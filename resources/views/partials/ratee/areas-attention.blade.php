@php $items = $rateeProfile['areas_attention']; $insufficient = $rateeProfile['attention_insufficient']; @endphp
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 section-card">
    <div class="flex items-start justify-between gap-3 mb-1">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-amber-500"></span> Areas Requiring Attention</h2>
        <span class="hidden sm:inline-flex text-xs px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">Avg &lt; 4.0 · ≥2 obs</span>
    </div>
    <p class="text-xs text-slate-500 mb-5">Domains consistently rated below 4.0 across at least 2 observations · support only · no negative judgment</p>
    @if($insufficient)
        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0"><i class="fas fa-circle-info text-slate-400 text-sm"></i></div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Not enough observation data yet.</p>
                <p class="text-xs text-slate-500 mt-1">A clearer picture appears after more scored observations.</p>
            </div>
        </div>
    @elseif($items->isEmpty())
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="w-8 h-8 rounded-xl bg-white border border-emerald-200 flex items-center justify-center shrink-0"><i class="fas fa-check text-emerald-600 text-sm"></i></div>
            <div>
                <p class="text-sm font-semibold text-emerald-800">No areas require attention right now.</p>
                <p class="text-xs text-emerald-700/80 mt-1">All rated domains average at or above 4.0.</p>
            </div>
        </div>
    @else
        <ul class="space-y-2.5">
            @foreach($items as $item)
                <li class="flex items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900">{{ $item['domain'] }}</p>
                        <p class="text-xs text-slate-600 mt-0.5">Average across {{ $item['observation_count'] }} observations</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white border border-amber-200 text-amber-700 text-sm font-bold">{{ number_format($item['average_rating'], 2) }} <span class="text-xs font-medium opacity-70">/ 6</span></span>
                </li>
            @endforeach
        </ul>
        <p class="text-xs text-slate-500 mt-4 flex items-center gap-1.5"><i class="fas fa-lightbulb text-amber-500"></i> Consider a coaching conversation to support growth in these areas.</p>
    @endif
</div>
