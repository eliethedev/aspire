{{--
    Observation workflow stepper — compact, space-efficient version.
    Expects: $observation, $currentStage ($observation->stage).
    Optional: $routeBase ('supervisor' | 'school-head').
--}}
@php
    $routeBase = $routeBase ?? (str_starts_with(request()->route()->getName() ?? '', 'school-head') ? 'school-head' : 'supervisor');
    $routePrefix = $routeBase === 'school-head' ? 'school-head' : 'supervisor';

    $isSchoolHeadObs = $observation->isSchoolHeadObservation();
    $friendlyStages = [
        'pre_observation_planning' => 'Prepare',
        'pre_conference' => 'Pre-Observation Conversation',
        'observation' => $isSchoolHeadObs ? 'School Head Observation' : 'Classroom Observation',
        'post_conference' => 'Post-Observation Conference',
        'epoc' => 'Enhanced Post Observation Conference',
        'completed' => 'Completed',
    ];
    $shortStages = [
        'pre_observation_planning' => 'Prepare',
        'pre_conference' => 'Pre-Obs. Talk',
        'observation' => 'Observe',
        'post_conference' => 'Post-Obs. Talk',
        'epoc' => 'EPOC',
        'completed' => 'Done',
    ];
    $stageKeys = $isSchoolHeadObs
        ? ['pre_observation_planning', 'observation', 'epoc', 'post_conference']
        : ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $stageRoutes = [
        'pre_observation_planning' => $routePrefix . '.observations.preObservationPlanning',
        'pre_conference' => $routePrefix . '.observations.preConference',
        'observation' => $routePrefix . '.observations.observation',
        'post_conference' => $routePrefix . '.observations.postConference',
        'epoc' => $routePrefix . '.observations.observation',
    ];
    $currentStage = $currentStage ?? ($observation->stage ?? null);
    $currentIdx = array_search($currentStage, $stageKeys);
    if ($currentIdx === false) {
        $currentIdx = in_array($currentStage, ['completed', 'epoc'], true) ? count($stageKeys) : 0;
    }
    $totalSteps = count($stageKeys);
    $progressPct = $totalSteps > 1 ? round(($currentIdx / ($totalSteps - 1)) * 100) : 100;
    $progressPct = max(0, min(100, $progressPct));
    $currentKey = $stageKeys[min($currentIdx, $totalSteps - 1)] ?? null;
@endphp
<nav aria-label="Observation progress" class="mb-3">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-3 py-2">
        {{-- Compact status line: step counter + current stage + pct --}}
        <div class="flex items-center justify-between gap-2 mb-1.5">
            <p class="min-w-0 flex items-center gap-1.5 text-xs">
                <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white leading-none">Step {{ min($currentIdx + 1, $totalSteps) }}/{{ $totalSteps }}</span>
                <span class="truncate font-semibold text-gray-800 dark:text-gray-100">{{ $friendlyStages[$currentKey] ?? '' }}</span>
            </p>
            <p class="shrink-0 text-[11px] font-medium text-gray-400 dark:text-gray-500 tabular-nums">{{ $progressPct }}%</p>
        </div>
        <div class="mb-2 h-1 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden" role="progressbar" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Observation progress">
            <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $progressPct }}%"></div>
        </div>

        <ol class="flex items-center">
            @foreach($stageKeys as $i => $key)
                @php
                    $isCurrent = $i === $currentIdx;
                    $isCompleted = $i < $currentIdx;
                    $isLast = $i === $totalSteps - 1;
                    $canAccess = $isCurrent || $isCompleted;
                    $circleClasses = $isCompleted
                        ? 'bg-emerald-600 text-white'
                        : ($isCurrent
                            ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 dark:ring-indigo-900'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500');
                    $labelClasses = $isCompleted
                        ? 'text-gray-500 dark:text-gray-400'
                        : ($isCurrent
                            ? 'text-indigo-700 dark:text-indigo-300 font-semibold'
                            : 'text-gray-400 dark:text-gray-500');
                @endphp
                <li class="flex-1 min-w-0 {{ $isLast ? 'flex-none' : '' }}">
                    <div class="flex items-center">
                        @if($canAccess)
                            <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                               @if($isCurrent) aria-current="step" @endif
                               title="{{ $friendlyStages[$key] }}"
                               class="group flex min-w-0 items-center gap-1 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $isCurrent ? 'cursor-default' : '' }}">
                                <span class="flex shrink-0 items-center justify-center w-5 h-5 text-[10px] rounded-full font-bold transition-all group-hover:shadow {{ $circleClasses }}">
                                    @if($isCompleted)
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span class="sr-only">Completed: </span>
                                    @else
                                        <span aria-hidden="true">{{ $i + 1 }}</span>
                                    @endif
                                </span>
                                <span class="min-w-0 leading-none">
                                    <span class="block sm:hidden text-[10px] {{ $labelClasses }} truncate">{{ $shortStages[$key] ?? $friendlyStages[$key] }}</span>
                                    <span class="hidden sm:block text-[11px] {{ $labelClasses }} truncate max-w-[130px]">{{ $friendlyStages[$key] }}</span>
                                    <span class="sr-only">{{ $friendlyStages[$key] }}{{ $isCurrent ? ' (current step)' : ($isCompleted ? ' (completed)' : '') }}</span>
                                </span>
                            </a>
                        @else
                            <div class="flex min-w-0 items-center gap-1 opacity-70" title="{{ $friendlyStages[$key] }}" aria-disabled="true">
                                <span class="flex shrink-0 items-center justify-center w-5 h-5 text-[10px] rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 font-bold">{{ $i + 1 }}</span>
                                <span class="min-w-0 leading-none">
                                    <span class="block sm:hidden text-[10px] text-gray-400 dark:text-gray-500 truncate">{{ $shortStages[$key] ?? $friendlyStages[$key] }}</span>
                                    <span class="hidden sm:block text-[11px] text-gray-400 dark:text-gray-500 truncate max-w-[130px]">{{ $friendlyStages[$key] }}</span>
                                </span>
                            </div>
                        @endif
                        @if(!$isLast)
                            <div class="flex-1 min-w-[6px] mx-1 h-0.5 rounded-full {{ $i < $currentIdx ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700' }}" aria-hidden="true"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</nav>
