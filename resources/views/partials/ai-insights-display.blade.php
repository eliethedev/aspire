{{-- Organized AI insights display --}}
{{-- Expects a parsed array like: ['lesson_focus' => string, 'key_things_to_watch' => [...], ...] or ['raw' => string] --}}
@php
    $sectionStyles = [
        'lesson_focus' => [
            'label' => 'Lesson Focus',
            'accent' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400',
            'bar' => 'border-indigo-200 dark:border-indigo-800',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
        ],
        'key_things_to_watch' => [
            'label' => 'Key Things to Watch',
            'accent' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
            'bar' => 'border-blue-200 dark:border-blue-800',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        ],
        'conference_talking_points' => [
            'label' => 'Pre-Conference Talking Points',
            'accent' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
            'bar' => 'border-amber-200 dark:border-amber-800',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
        ],
        'potential_challenges' => [
            'label' => 'Potential Challenges',
            'accent' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
            'bar' => 'border-red-200 dark:border-red-800',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
        ],
    ];
@endphp

@if(isset($sections['raw']))
    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $sections['raw'] }}</p>
@else
    <div class="space-y-3">
        @foreach($sections as $key => $content)
            @php
                $style = $sectionStyles[$key] ?? [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'accent' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                    'bar' => 'border-gray-200 dark:border-gray-700',
                    'icon' => '',
                ];
            @endphp
            <div class="rounded-xl border {{ $style['bar'] }} bg-white/60 dark:bg-gray-800/50 overflow-hidden">
                <div class="flex items-center gap-2 px-3 py-2 border-b {{ $style['bar'] }}">
                    @if($style['icon'])
                        <span class="shrink-0 {{ $style['accent'] }} rounded-lg p-1.5">{!! $style['icon'] !!}</span>
                    @endif
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">{{ $style['label'] }}</span>
                </div>
                <div class="px-4 py-3">
                    @if(is_array($content))
                        <ul class="space-y-1.5">
                            @foreach($content as $item)
                                <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                    <svg class="w-3.5 h-3.5 mt-1 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $content }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif