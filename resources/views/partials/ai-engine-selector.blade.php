{{-- AI generation-engine transparency + manual mode switch.
     Used by the lesson-plan workflow panels (pre-observation assistant,
     pre-conference AI panel). Shows which specialized engine powered the
     output and lets the user override the router ("Auto" = goal-driven).

     Requires: $planning (with optional ai_insights_meta array).
--}}
@php
    $engineRouter = app(\App\AI\Routing\LessonPlanModelRouter::class);
    $engineModes = $engineRouter->modes();
    $engineMeta = $planning?->ai_insights_meta ?? null;
    $engineMeta = is_array($engineMeta) ? $engineMeta : null;
    $engineCurrentMode = $engineMeta['mode'] ?? 'auto';

    // Which AI is currently in use. Before anything has been generated we
    // show the configured default engine; afterwards the engine that
    // actually powered the stored insights.
    $defaultProvider = (string) config('ai.provider', 'gemini');
    $defaultModel = (string) config('ai.models.default', 'gemini-3.6-flash');
    $providerName = (string) (config("ai.providers.{$defaultProvider}.name")
        ?? config("ai.model_catalog.{$defaultProvider}.label")
        ?? ucfirst($defaultProvider));
    $defaultIndicator = 'Currently using '.$providerName.' · '.$defaultModel;
    $currentIndicator = $defaultIndicator;
    if ($engineMeta && !empty($engineMeta['label'])) {
        $currentIndicator = $engineMeta['label']
            .(!empty($engineMeta['model']) ? ' · '.$engineMeta['model'] : '')
            .(!empty($engineMeta['fallback_used']) ? ' · baseline failover' : '')
            .(!empty($engineMeta['manual']) ? ' · manual' : '');
    }
@endphp
<div class="mb-3 rounded-lg border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50/60 dark:bg-indigo-900/10 px-3 py-2.5">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <span id="ai-engine-badge" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-700 dark:text-indigo-300" title="AI engine currently powering this assistant">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <span id="ai-engine-badge-text">{{ $currentIndicator }}</span>
        </span>
        <label class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 dark:text-gray-400">
            Engine
            <select id="ai-engine-mode"
                    class="text-[11px] font-semibold rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 px-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="auto" {{ $engineCurrentMode === 'auto' ? 'selected' : '' }}>Auto (recommended)</option>
                @foreach($engineModes as $modeKey => $modeInfo)
                    <option value="{{ $modeKey }}" {{ $engineCurrentMode === $modeKey ? 'selected' : '' }} title="{{ $modeInfo['description'] }}">{{ $modeInfo['label'] }}</option>
                @endforeach
            </select>
        </label>
        <span class="text-[11px] text-gray-400 dark:text-gray-500">Auto matches the engine to the lesson subject &amp; goals.</span>
    </div>
</div>
<script>
(function () {
    function selectedMode() {
        var el = document.getElementById('ai-engine-mode');
        return el ? (el.value || 'auto') : 'auto';
    }

    window.aiEngineDefaultIndicator = @json($defaultIndicator);

    function renderBadge(meta) {
        var badge = document.getElementById('ai-engine-badge');
        var text = document.getElementById('ai-engine-badge-text');
        if (!badge || !text) return;
        if (!meta || !meta.label) {
            text.textContent = window.aiEngineDefaultIndicator;
            return;
        }
        var parts = [meta.label];
        if (meta.model) parts.push(meta.model);
        if (meta.fallback_used) parts.push('baseline failover');
        else if (meta.manual) parts.push('manual');
        text.textContent = parts.join(' · ');
    }

    window.aiEngineSelectedMode = selectedMode;
    window.aiEngineRenderBadge = renderBadge;
})();
</script>
