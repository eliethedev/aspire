<div x-data="calendar()" class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Calendar</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $intro }}</p>
        </div>
        @if(!empty($scheduleRoute))
            <a href="{{ $scheduleRoute }}"
               class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fa-solid fa-plus mr-2"></i>{{ $scheduleLabel }}
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Calendar grid -->
        <div class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-4 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <button type="button"
                        @click="prev()"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :aria-label="'Previous month'"
                        title="Previous month">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <h2 x-text="monthLabel" class="flex-1 text-center text-lg font-semibold text-slate-900 dark:text-white"></h2>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="today()"
                            class="inline-flex items-center px-3 h-9 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Today
                    </button>
                    <button type="button"
                            @click="next()"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :aria-label="'Next month'"
                            title="Next month">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1 mb-1">
                <template x-for="d in weekdays" :key="d">
                    <div class="text-center text-xs font-semibold text-gray-400 dark:text-gray-500 py-1.5"><span x-text="d"></span></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <template x-for="cell in cells" :key="cell.iso">
                    <button type="button"
                            @click="selectedDate = cell.iso"
                            :class="{
                                'bg-indigo-600 text-white border-indigo-600 shadow-md': cell.iso === selectedDate,
                                'bg-gray-50 dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 border-gray-100 dark:border-gray-700 text-slate-700 dark:text-gray-200': cell.iso !== selectedDate && cell.inMonth,
                                'bg-transparent border-transparent text-gray-300 dark:text-gray-600': !cell.inMonth && cell.iso !== selectedDate,
                                'ring-2 ring-indigo-500 ring-offset-1 dark:ring-offset-gray-900': cell.isToday
                            }"
                            class="min-h-[56px] flex flex-col items-center justify-center gap-1 rounded-xl border text-sm transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                        <span x-text="cell.day" class="font-medium leading-4"></span>
                        <template x-if="cell.events.length">
                            <div class="flex items-center gap-0.5">
                                <template x-for="e in cell.events.slice(0, 3)" :key="e.id">
                                    <span class="w-1.5 h-1.5 rounded-full" :style="'background-color:' + e.color"></span>
                                </template>
                                <span x-show="cell.events.length > 3" class="text-[9px] font-bold text-gray-400 dark:text-gray-500" x-text="'+' + (cell.events.length - 3)"></span>
                            </div>
                        </template>
                    </button>
                </template>
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <template x-for="l in legend" :key="l.key">
                    <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <span class="w-2.5 h-2.5 rounded-full" :style="'background-color:' + l.color"></span>
                        <span x-text="l.label"></span>
                    </span>
                </template>
            </div>
            
            <!-- Selected day details -->
            <div x-show="selectedDate" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white" x-text="selectedLabel"></h3>
                    <button type="button"
                            @click="selectedDate = null"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            :aria-label="'Close day details'"
                            title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <template x-if="selectedEvents.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No activities scheduled for this day.</p>
                </template>
                <div class="space-y-3">
                    <template x-for="e in selectedEvents" :key="e.id">
                        <div class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3">
                            <span class="mt-1.5 w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color:' + e.color"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] leading-4 font-semibold text-white" :style="'background-color:' + e.color" x-text="e.type_label"></span>
                                    <span class="text-sm font-semibold text-slate-800 dark:text-gray-100 truncate" x-text="e.title"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] leading-4 font-semibold text-white" :style="'background-color:' + statusStyle(e.status)" x-text="e.status_label"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="e.subtitle"></p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                    <template x-if="e.stage_label">
                                        <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-flag text-gray-300 dark:text-gray-600"></i><span x-text="e.stage_label"></span></span>
                                    </template>
                                    <template x-if="e.start_time">
                                        <span class="inline-flex items-center gap-1.5"><i class="fa-regular fa-clock text-gray-300 dark:text-gray-600"></i><span x-text="timeWindow(e)"></span></span>
                                    </template>
                                    <template x-if="e.location">
                                        <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-gray-300 dark:text-gray-600"></i><span x-text="e.location"></span></span>
                                    </template>
                                    <template x-if="e.score !== null && e.score !== ''">
                                        <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-gauge-high text-gray-300 dark:text-gray-600"></i><span x-text="'Score: ' + e.score"></span></span>
                                    </template>
                                </div>
                            </div>
                            <a :href="e.link"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                View <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="space-y-6">
            <!-- Upcoming -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Upcoming</h3>
                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-[11px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-500/10 rounded-full" x-text="upcoming.length"></span>
                </div>
                <template x-if="upcoming.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming activities.</p>
                </template>
                <div class="space-y-3">
                    <template x-for="e in upcoming" :key="e.id">
                        <a :href="e.link" class="flex items-start gap-3 group focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 -m-1">
                            <div class="w-11 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 text-center py-1 bg-gray-50 dark:bg-gray-800/60">
                                <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500" x-text="monthShort(e.date)"></div>
                                <div class="text-base font-bold leading-5 text-slate-800 dark:text-white" x-text="dayNum(e.date)"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="'background-color:' + e.color"></span>
                                    <span class="text-sm font-medium text-slate-800 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400" x-text="e.title"></span>
                                    <span x-show="e.date === today" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase shrink-0">Today</span>
                                    <span class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] leading-4 font-semibold text-white shrink-0" :style="'background-color:' + statusStyle(e.status)" x-text="e.status_label"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="e.type_label + ' · ' + e.subtitle"></p>
                            </div>
                        </a>
                    </template>
                </div>
            </div>

            <!-- Past schedules (completed / cancelled / done) -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Past schedules</h3>
                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-[11px] font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-full" x-text="past.length"></span>
                </div>
                <template x-if="past.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No past schedules.</p>
                </template>
                <div class="space-y-3">
                    <template x-for="e in past" :key="e.id">
                        <a :href="e.link" class="flex items-start gap-3 group focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 -m-1">
                            <div class="w-11 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 text-center py-1 bg-gray-50 dark:bg-gray-800/60">
                                <div class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500" x-text="monthShort(e.date)"></div>
                                <div class="text-base font-bold leading-5 text-slate-800 dark:text-white" x-text="dayNum(e.date)"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="'background-color:' + e.color"></span>
                                    <span class="text-sm font-medium text-slate-800 dark:text-gray-100 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400" x-text="e.title"></span>
                                    <span class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] leading-4 font-semibold text-white shrink-0" :style="'background-color:' + statusStyle(e.status)" x-text="e.status_label"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="e.type_label + ' · ' + e.subtitle"></p>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script id="calendar-data" type="application/json">
        @json([
            'events' => $events,
            'today' => now()->toDateString(),
        ])
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('calendar', () => ({
                events: [],
                today: '',
                viewYear: new Date().getFullYear(),
                viewMonth: new Date().getMonth(),
                selectedDate: null,
                weekdays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                legend: [
                    { key: 'observation', label: 'Observation', color: '#6366f1' },
                    { key: 'pre_conference', label: 'Pre-Conference', color: '#8b5cf6' },
                    { key: 'post_conference', label: 'Post-Conference', color: '#10b981' },
                ],
                init() {
                    const el = document.getElementById('calendar-data');
                    const data = el ? JSON.parse(el.textContent) : {};
                    this.events = data.events || [];
                    this.today = data.today || new Date().toISOString().slice(0, 10);
                    const t = this.parseDate(this.today);
                    this.viewYear = t.getFullYear();
                    this.viewMonth = t.getMonth();
                    this.selectedDate = this.today;
                },
                parseDate(iso) {
                    return new Date(iso + 'T00:00:00');
                },
                get monthLabel() {
                    return this.parseDate(this.viewYear + '-' + String(this.viewMonth + 1).padStart(2, '0') + '-01').toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
                },
                prev() {
                    this.viewMonth -= 1;
                    if (this.viewMonth < 0) { this.viewMonth = 11; this.viewYear -= 1; }
                    this.selectedDate = null;
                },
                next() {
                    this.viewMonth += 1;
                    if (this.viewMonth > 11) { this.viewMonth = 0; this.viewYear += 1; }
                    this.selectedDate = null;
                },
                today() {
                    const t = this.parseDate(this.today);
                    this.viewYear = t.getFullYear();
                    this.viewMonth = t.getMonth();
                    this.selectedDate = this.today;
                },
                isoOf(date) {
                    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                },
                eventsFor(iso) {
                    return this.events.filter(e => e.date === iso);
                },
                get cells() {
                    const first = this.parseDate(this.viewYear + '-' + String(this.viewMonth + 1).padStart(2, '0') + '-01');
                    const start = new Date(first.getFullYear(), first.getMonth(), 1 - first.getDay());
                    const cells = [];
                    for (let i = 0; i < 42; i++) {
                        const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                        const iso = this.isoOf(d);
                        cells.push({
                            iso,
                            day: d.getDate(),
                            inMonth: d.getMonth() === this.viewMonth,
                            isToday: iso === this.today,
                            events: this.eventsFor(iso),
                        });
                    }
                    return cells;
                },
                get selectedEvents() {
                    return this.selectedDate ? this.eventsFor(this.selectedDate) : [];
                },
                get selectedLabel() {
                    if (!this.selectedDate) { return null; }
                    const d = this.parseDate(this.selectedDate);
                    return d.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },
                get upcoming() {
                    return this.events
                        .filter(e => e.date >= this.today)
                        .sort((a, b) => a.date === b.date ? 0 : a.date < b.date ? -1 : 1)
                        .slice(0, 8);
                },
                get past() {
                    return this.events
                        .filter(e => e.date < this.today)
                        .sort((a, b) => a.date === b.date ? 0 : a.date > b.date ? -1 : 1)
                        .slice(0, 8);
                },
                monthShort(iso) {
                    return this.parseDate(iso).toLocaleDateString(undefined, { month: 'short' });
                },
                dayNum(iso) {
                    return parseInt(iso.slice(8, 10), 10);
                },
                timeWindow(e) {
                    if (e.end_time) { return e.start_time + ' – ' + e.end_time; }
                    return e.start_time || '';
                },
                statusStyle(status) {
                    const map = {
                        scheduled: '#2563eb',
                        in_progress: '#d97706',
                        cot_completed: '#7c3aed',
                        completed: '#059669',
                        cancelled: '#dc2626',
                    };
                    return map[status] || '#6b7280';
                },
            }));
        });
    </script>
</div>