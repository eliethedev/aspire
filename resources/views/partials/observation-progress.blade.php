{{--
    Observation workflow progress indicator.
    Expects: $stageKeys (ordered), $currentStage (observation->stage),
             and optionally $stageLabels for a readable label.
    Renders "Step X of N" with a percentage progress bar.
--}}
@php
    $stageKeys = $stageKeys ?? [];
    $stageLabels = $stageLabels ?? [];
    $currentStage = $currentStage ?? ($observation->stage ?? null);
@endphp
@if(!empty($stageKeys))
@php
    $totalStages = count($stageKeys);
    $currentIdx = array_search($currentStage ?? '', $stageKeys ?? []);
    $completed = $currentIdx === false ? 0 : $currentIdx;
    $stepNum = min($totalStages, max(1, $completed + 1));
    $percent = $totalStages > 1 ? round(($completed / ($totalStages - 1)) * 100) : 100;
    $currentLabel = ($stageLabels[$currentStage] ?? null);
@endphp
<div class="mb-6" role="status" aria-label="Observation progress">
    <div class="flex items-center justify-between mb-2">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            Step {{ $stepNum }} of {{ $totalStages }}
            @if($currentLabel)
                <span class="text-gray-500 dark:text-gray-400 font-normal">&middot; {{ $currentLabel }}</span>
            @endif
        </p>
        <p class="text-sm font-semibold {{ $percent >= 100 ? 'text-green-600 dark:text-green-400' : 'text-indigo-600 dark:text-indigo-400' }}">
            {{ $percent }}% Complete
        </p>
    </div>
    <div class="progress-track">
        <div class="progress-fill" style="width: {{ $percent }}%"></div>
    </div>
</div>
@endif
