<div x-show="$store.ratingTip.open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak
     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="rating-tip-title">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="$store.ratingTip.closeAndSave()" aria-hidden="true"></div>

    <div class="relative w-full max-w-2xl max-h-[85vh] flex flex-col bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-800">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800 bg-gradient-to-r from-indigo-50 to-white dark:from-gray-900 dark:to-gray-900">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="rating-tip-title" class="text-lg font-bold text-gray-900 dark:text-gray-100">How the Observation Rating Sheet Works</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">A quick guide to rating indicators so results stay fair, accurate, and consistent.</p>
                </div>
            </div>
            <button type="button" @click="$store.ratingTip.closeAndSave()"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    title="Close" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
            <!-- What it is -->
            <section>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1.5">What it is</h3>
                <p>
                    Each observation uses an official COT rating sheet. Indicators are listed under
                    <strong>domains</strong> — Content Knowledge &amp; Pedagogy, Learning Environment, Diversity of
                    Learners, Curriculum &amp; Planning, and Assessment &amp; Reporting. Rate every indicator based
                    strictly on what you observed during the lesson.
                </p>
            </section>

            <!-- The scale -->
            <section>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1.5">The rating scale</h3>
                <div class="overflow-x-auto mt-1.5">
                    <table class="w-full text-xs border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                <th class="px-2 py-2 font-semibold text-left border-b border-gray-200 dark:border-gray-700">Scale</th>
                                <th class="px-2 py-2 font-semibold text-left border-b border-gray-200 dark:border-gray-700">Adjectival Rating</th>
                                <th class="px-2 py-2 font-semibold text-left border-b border-gray-200 dark:border-gray-700">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-bold text-emerald-700 dark:text-emerald-400">5</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-medium text-emerald-700 dark:text-emerald-400">Outstanding (O)</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">Performance represents an extraordinary level of achievement and commitment across all areas.</td>
                            </tr>
                            <tr class="bg-green-50 dark:bg-green-900/20">
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-bold text-green-700 dark:text-green-400">4</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-medium text-green-700 dark:text-green-400">Very Satisfactory (VS)</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">Performance consistently meets and often exceeds expectations.</td>
                            </tr>
                            <tr class="bg-sky-50 dark:bg-sky-900/20">
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-bold text-sky-700 dark:text-sky-400">3</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-medium text-sky-700 dark:text-sky-400">Satisfactory (S)</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">Performance meets standard expectations and requirements.</td>
                            </tr>
                            <tr class="bg-amber-50 dark:bg-amber-900/20">
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-bold text-amber-700 dark:text-amber-400">2</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 font-medium text-amber-700 dark:text-amber-400">Unsatisfactory (US)</td>
                                <td class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">Performance does not meet standard requirements; significant improvement is needed.</td>
                            </tr>
                            <tr class="bg-rose-50 dark:bg-rose-900/20">
                                <td class="px-2 py-1.5 font-bold text-rose-700 dark:text-rose-400">1</td>
                                <td class="px-2 py-1.5 font-medium text-rose-700 dark:text-rose-400">Poor (P)</td>
                                <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300">Performance consistently fails to meet minimum standards despite interventions.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-1.5">Higher numbers mean stronger practice. <strong>Only rate what you actually saw.</strong></p>
            </section>

            <!-- What to consider -->
            <section>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1.5">What to consider</h3>
                <ul class="space-y-1.5 list-disc pl-5">
                    <li>Base each rating on <strong>evidence you observed</strong> in the lesson: strategies used, classroom management, learner engagement, and assessments.</li>
                    <li>Read the <strong>full indicator description</strong> (the probe) and judge the practice within the lesson itself — not outside it, and not from assumptions about the teacher.</li>
                    <li>Add a <strong>comment per indicator</strong> to record the specific evidence behind your rating.</li>
                    <li>Your overall score is the <strong>average of all rated indicators</strong>.</li>
                </ul>
            </section>

            <!-- NO -->
            <section>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1.5">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-gray-500 text-white text-[10px] font-bold">NO</span>
                        Not Observed
                    </span>
                </h3>
                <p>
                    Use <strong>NO</strong> when the indicator <em>applies</em> to the lesson but you did
                    <strong>not see the behavior</strong> during the observation. It is flagged as not observed and is
                    <strong>excluded from the overall score</strong>. Reserve it for indicators that genuinely were
                    absent in this lesson — don't use a <strong>Mark All as NO</strong> shortcut unless it truly reflects
                    the class you watched.
                </p>
            </section>

            <!-- N/A -->
            <section>
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1.5">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded bg-amber-400 text-white text-[10px] font-bold">N/A</span>
                        Not Applicable
                    </span>
                </h3>
                <p>
                    Use <strong>N/A</strong> when the indicator genuinely <strong>does not apply</strong> to this lesson
                    or context (for example, an ICT-focused indicator in a lesson with no technology). An N/A indicator is
                    <strong>not recorded at all</strong> and is excluded from the sheet and score.
                </p>
                <p class="mt-1.5 text-amber-700 dark:text-amber-400 font-medium">
                    N/A is <u>not</u> for skipping indicators you simply missed or did not get to. If it applies but
                    wasn't seen, mark <strong>NO</strong>; only mark <strong>N/A</strong> when it truly does not apply.
                </p>
            </section>

            <!-- EPOC note -->
            <section class="rounded-xl border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50/50 dark:bg-indigo-900/10 px-4 py-3">
                <p class="text-xs text-indigo-700 dark:text-indigo-300">
                    <strong>School Head observations</strong> use the EPOC rating sheet with its own independent
                    <strong>1&ndash;5</strong> scale — the same NO/N/A and evidence-based principles above still apply.
                </p>
            </section>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox"
                       x-model="$store.ratingTip.dontShowAgain"
                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Don't show this again</span>
            </label>
            <button type="button"
                    @click="$store.ratingTip.closeAndSave()"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm transition-colors">
                Got it
            </button>
        </div>
    </div>
</div>