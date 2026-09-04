{{--
    Observation workflow stepper with friendly, user-facing labels.
    Expects: $observation, $currentStage ($observation->stage).
    Optional: $routeBase ('supervisor' | 'school-head') to select route names.
    Renders a horizontal stepper where users feel they are conducting ONE observation.
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
        'epoc' => 'EPOC Evaluation',
        'completed' => 'Completed',
    ];
    $stageKeys = $isSchoolHeadObs
        ? ['pre_observation_planning', 'pre_conference', 'observation', 'epoc', 'post_conference']
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
@endphp
<div class="mb-8">
    <div class="flex items-center justify-between">
        @foreach($stageKeys as $i => $key)
            @php
                $isCurrent = $key === $currentStage;
                $isCompleted = $i < $currentIdx;
                $canAccess = $isCurrent || $isCompleted;
            @endphp
            @if($i > 0)
                <div class="flex-1 mx-4 h-1 rounded-full {{ $isCompleted ? 'bg-green-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            @endif
            @if($canAccess)
                <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                   class="flex items-center group {{ $isCurrent ? 'cursor-default' : 'cursor-pointer' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $isCompleted ? 'bg-green-600 text-white' : 'bg-indigo-600 text-white ring-2 ring-indigo-200' }} font-semibold transition-colors group-hover:shadow-md text-sm">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="ml-2 {{ $isCompleted ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100 font-medium' }} text-sm group-hover:text-indigo-600 dark:text-indigo-400 transition-colors">{{ $friendlyStages[$key] }}</span>
                </a>
            @else
                <div class="flex items-center opacity-50">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 dark:text-gray-500 font-semibold text-sm">{{ $i + 1 }}</div>
                    <span class="ml-2 text-gray-400 dark:text-gray-500 text-sm">{{ $friendlyStages[$key] }}</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
