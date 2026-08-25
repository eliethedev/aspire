<!-- Performance tab -->
<div class="space-y-6">
    <!-- Summary cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Teachers</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $performanceSummary['teachers_total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Observed</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $performanceSummary['teachers_observed'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">School Average</p>
            <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ $performanceSummary['school_average'] !== null ? number_format($performanceSummary['school_average'], 2) . '/6' : 'N/A' }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Improving</p>
            <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $performanceSummary['improving'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Needs Attention</p>
            <p class="mt-1 text-2xl font-bold {{ $performanceSummary['needs_attention'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $performanceSummary['needs_attention'] }}</p>
        </div>
    </div>

    <!-- Performance table -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="hidden md:grid grid-cols-12 gap-3 px-5 py-3 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <div class="col-span-4">Teacher</div>
            <div class="col-span-1 text-center">Obs.</div>
            <div class="col-span-3">Average Score</div>
            <div class="col-span-1 text-center">Trend</div>
            <div class="col-span-2">Rating Band</div>
            <div class="col-span-1 text-right">Last Obs.</div>
        </div>

        @forelse($performanceRows as $row)
            @php $teacher = $row['teacher']; @endphp
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 last:border-b-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors items-center">
                <!-- Teacher -->
                <div class="md:col-span-4 flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(mb_substr($teacher->user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block">{{ $teacher->user->name }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $row['position'] }}</p>
                    </div>
                </div>
                <!-- Count -->
                <div class="md:col-span-1 md:text-center text-sm text-gray-700 dark:text-gray-300">
                    {{ $row['observations_count'] }}
                </div>
                <!-- Average + bar -->
                <div class="md:col-span-3">
                    @if($row['average'] !== null)
                        <div class="flex items-center gap-2">
                            <div class="flex-1 min-w-[60px] max-w-[140px] h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                @php $width = max(4, min(100, round((($row['average'] - 1) / 5) * 100))); @endphp
                                <div class="h-full rounded-full {{ $row['average'] >= 4.5 ? 'bg-green-500' : ($row['average'] >= 3.5 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $width }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['average'], 2) }}<span class="text-xs font-normal text-gray-400 dark:text-gray-500">/6</span></span>
                        </div>
                    @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">No data yet</span>
                    @endif
                </div>
                <!-- Trend -->
                <div class="md:col-span-1 md:text-center">
                    @if($row['trend'] !== null)
                        <span class="inline-flex items-center gap-0.5 text-sm font-semibold {{ $row['trend'] > 0 ? 'text-green-600 dark:text-green-400' : ($row['trend'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400') }}">
                            @if($row['trend'] > 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                +{{ number_format($row['trend'], 2) }}
                            @elseif($row['trend'] < 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                {{ number_format($row['trend'], 2) }}
                            @else
                                &mdash;
                            @endif
                        </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                    @endif
                </div>
                <!-- Band -->
                <div class="md:col-span-2">
                    @if($row['band'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $row['band']['class'] }}">{{ $row['band']['label'] }}</span>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                    @endif
                </div>
                <!-- Last observed -->
                <div class="md:col-span-1 md:text-right text-xs text-gray-500 dark:text-gray-400">
                    {{ $row['last_observed']?->format('M d, Y') ?? '—' }}
                </div>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">No teachers found</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Teachers assigned to your school will appear here once you observe them.</p>
            </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-400 dark:text-gray-500">
        Averages use the COT overall score (2&ndash;6 scale). Trend compares the two most recent completed observations.
    </p>
</div>
