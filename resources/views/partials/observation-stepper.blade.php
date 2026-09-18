{{--
    Observation workflow stepper with friendly, user-facing labels.
    Expects: $observation, $currentStage ($observation->stage).
    Optional: $routeBase ('supervisor' | 'school-head') to select route names.
    Renders a responsive stepper where users feel they are conducting ONE observation.

    Responsive behavior:
    - Mobile: compact 4-column grid, circles on top + short labels below, plus a
      "Step X of 4" summary with progress bar. No horizontal overflow.
    - sm and up: classic horizontal stepper with full labels, truncated safely.
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
    // Short labels used on small screens to avoid overflow.
    $shortStages = [
        'pre_observation_planning' => 'Prepare',
        'pre_conference' => 'Pre-Conf',
        'observation' => $isSchoolHeadObs ? 'Observe' : 'Observe',
        'post_conference' => 'Post-Conf',
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
        // School head observations use the EPOC instrument as the observation
        // rating sheet, so the EPOC step links to the observation page where
        // the EPOC form is rendered.
        'epoc' => $routePrefix . '.observations.observation',
    ];
    $currentStage = $currentStage ?? ($observation->stage ?? null);
    $currentIdx = array_search($currentStage, $stageKeys);
    if ($currentIdx === false) {
        // e.g. stage is "completed" or an unexpected value: treat every
        // listed step as completed so the bar never breaks.
        $currentIdx = in_array($currentStage, ['completed', 'epoc'], true) ? count($stageKeys) : 0;
    }
    $totalSteps = count($stageKeys);
    $progressPct = $totalSteps > 1 ? round(($currentIdx / ($totalSteps - 1)) * 100) : 100;
    $progressPct = max(0, min(100, $progressPct));
@endphp
<nav aria-label="Observation progress" class="mb-5 sm:mb-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 px-1 py-3.5 sm:px-1 sm:py-4">
        {{-- Mobile summary: screen-reader + sighted users know where they are --}}
        <div class="sm:hidden mb-3" aria-hidden="false">
            <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                    Step {{ min($currentIdx + 1, $totalSteps) }} of {{ $totalSteps }}
                    <span class="font-normal text-gray-500 dark:text-gray-400">&middot; {{ $friendlyStages[$stageKeys[min($currentIdx, $totalSteps - 1)]] ?? '' }}</span>
                </p>
                <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 tabular-nums shrink-0">{{ $progressPct }}%</p>
            </div>
            <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden" role="progressbar" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Observation progress">
                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

        <ol class="flex items-start sm:items-center">
            @foreach($stageKeys as $i => $key)
                @php
                    $isCurrent = $i === $currentIdx;
                    $isCompleted = $i < $currentIdx;
                    $isLast = $i === $totalSteps - 1;
                    $canAccess = $isCurrent || $isCompleted;
                    $circleClasses = $isCompleted
                        ? 'bg-green-600 dark:bg-green-500 text-white'
                        : ($isCurrent
                            ? 'bg-indigo-600 dark:bg-indigo-500 text-white ring-4 ring-indigo-100 dark:ring-indigo-900/40'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500');
                    $labelClasses = $isCompleted
                        ? 'text-gray-500 dark:text-gray-400'
                        : ($isCurrent
                            ? 'text-gray-900 dark:text-gray-100 font-semibold'
                            : 'text-gray-400 dark:text-gray-500');
                @endphp
                <li class="flex-1 min-w-0 {{ $isLast ? 'flex-none' : '' }}">
                    <div class="flex items-center gap-0">
                        @if($canAccess)
                            <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                               @if($isCurrent) aria-current="step" @endif
                               title="{{ $friendlyStages[$key] }}"
                               class="group flex min-w-0 items-center gap-1.5 sm:gap-2 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 {{ $isCurrent ? 'cursor-default' : '' }}">
                                <span class="flex shrink-0 items-center justify-center w-7 h-7 text-[11px] sm:w-10 sm:h-10 sm:text-sm rounded-full font-semibold transition-all group-hover:shadow-md {{ $circleClasses }}">
                                    @if($isCompleted)
                                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span class="sr-only">Completed: </span>
                                    @else
                                        <span aria-hidden="true">{{ $i + 1 }}</span>
                                    @endif
                                </span>
                                <span class="min-w-0 leading-tight">
                                    {{-- Short label on mobile (stack-safe), full label on sm+ --}}
                                    <span class="block sm:hidden text-[11px] {{ $labelClasses }} truncate">{{ $shortStages[$key] ?? $friendlyStages[$key] }}</span>
                                    <span class="hidden sm:block text-xs lg:text-sm {{ $labelClasses }} truncate max-w-[110px] lg:max-w-none">{{ $friendlyStages[$key] }}</span>
                                    <span class="sr-only">{{ $friendlyStages[$key] }}{{ $isCurrent ? ' (current step)' : ($isCompleted ? ' (completed)' : '') }}</span>
                                </span>
                            </a>
                        @else
                            <div class="flex min-w-0 items-center gap-1.5 sm:gap-2 opacity-70" title="{{ $friendlyStages[$key] }}" aria-disabled="true">
                                <span class="flex shrink-0 items-center justify-center w-7 h-7 text-[11px] sm:w-10 sm:h-10 sm:text-sm rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 font-semibold">{{ $i + 1 }}</span>
                                <span class="min-w-0 leading-tight">
                                    <span class="block sm:hidden text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ $shortStages[$key] ?? $friendlyStages[$key] }}</span>
                                    <span class="hidden sm:block text-xs lg:text-sm text-gray-400 dark:text-gray-500 truncate max-w-[110px] lg:max-w-none">{{ $friendlyStages[$key] }}</span>
                                </span>
                            </div>
                        @endif
                        @if(!$isLast)
                            <div class="flex-1 min-w-[8px] mx-1.5 sm:mx-3 h-0.5 sm:h-1 rounded-full {{ $i < $currentIdx ? 'bg-green-500 dark:bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}" aria-hidden="true"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</nav>
