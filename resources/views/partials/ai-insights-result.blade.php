{{-- AI insights result block (organized sections, no page reload needed).
     Expects $insights (string|array). Used for the initial server render AND
     injected verbatim via innerHTML after AJAX generation — both paths show
     the identical organized layout (Lesson Focus, Key Things to Watch,
     Pre-Conference Talking Points, Potential Challenges).
     All buttons call page-level globals (accept/modify/reject/clear/copy),
     so freshly injected markup works without rebinding. --}}
@php
    $tmpPlanning = new \App\Models\PreObservationPlanning(['ai_insights' => $insights ?? null]);
    $insightSections = $tmpPlanning->insightsSections();
    $insightsTextValue = is_array($insights) ? (json_encode($insights) ?: '') : (string) ($insights ?? '');
@endphp
@if(! empty($insightSections))
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-200 dark:border-gray-700 insight-card" x-data="{ aiResultOpen: true }">
    <div class="flex items-center justify-between gap-2 mb-2">
        <button type="button" @click="aiResultOpen = !aiResultOpen" :aria-expanded="aiResultOpen.toString()"
                class="inline-flex min-h-[44px] items-center gap-1.5 rounded-md px-1 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <svg class="w-4 h-4 transition-transform" :class="aiResultOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            <span x-text="aiResultOpen ? 'Hide result' : 'Show result'">Hide result</span>
        </button>
        <button type="button" onclick="copyToClipboard(this, 'ai-insights-text')"
                class="p-1.5 min-w-[44px] min-h-[44px] inline-flex items-center justify-center rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0" title="Copy AI Insights">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </button>
    </div>
    <div x-show="aiResultOpen">
        @if(isset($insightSections['raw']))
            <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed" id="ai-insights-text">{{ $insightSections['raw'] }}</div>
        @else
            <div id="ai-insights-text" class="ai-insights-body">{!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center" id="ai-action-buttons">
    <button type="button" onclick="acceptAiInsights()"
            class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>
        Accept &amp; Apply
    </button>
    <button type="button" onclick="modifyAiInsights()"
            class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-amber-700 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:bg-amber-900/30 border border-amber-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
        Modify
    </button>
    <button type="button" onclick="rejectAiInsights()"
            class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-red-600 dark:text-red-400 bg-gray-50 dark:bg-gray-800 hover:bg-red-50 dark:bg-red-900/20 border border-gray-200 dark:border-gray-700 hover:border-red-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Dismiss
    </button>
    <button type="button" onclick="clearAiInsights()"
            class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-red-600 dark:text-red-400 hover:text-white bg-red-50 dark:bg-red-900/20 hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-colors inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Clear
    </button>
</div>

<div id="modify-ai-container" class="hidden space-y-3">
    <textarea id="modify-ai-textarea" rows="6"
              class="w-full px-3 py-2 rounded-lg border border-amber-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">{{ $insightsTextValue }}</textarea>
    <div class="flex flex-col sm:flex-row gap-2">
        <button type="button" onclick="applyModifiedInsights()"
                class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">Apply Modified Insights</button>
        <button type="button" onclick="cancelModify()"
                class="px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
    </div>
</div>
@endif
