{{-- Per-section AI insight editors (one field per organized section).
     Expects $sections (a parsed insightsSections() array). When the insights
     could not be split into sections (['raw' => ...]), a single textarea is
     shown instead. Each field carries data-insight-field="{key}" so the
     shared AiInsightsSections JS helper can collect/rebuild them. --}}
@php
    $editorMeta = [
        'lesson_focus' => ['label' => 'Lesson Focus', 'hint' => 'Short paragraph — what the lesson aims to achieve.', 'rows' => 3],
        'key_things_to_watch' => ['label' => 'Key Things to Watch', 'hint' => 'One concrete item per line.', 'rows' => 4],
        'conference_talking_points' => ['label' => 'Pre-Conference Talking Points', 'hint' => 'One question per line.', 'rows' => 4],
        'potential_challenges' => ['label' => 'Potential Challenges', 'hint' => 'One risk per line.', 'rows' => 3],
    ];
    $editorSections = $sections ?? [];
    $editorFieldClass = 'w-full px-3 py-2 rounded-lg border border-amber-300 dark:border-amber-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm';
@endphp
@if(isset($editorSections['raw']))
    <div>
        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">AI Insights</label>
        <textarea data-insight-field="raw" rows="8" class="{{ $editorFieldClass }}">{{ $editorSections['raw'] }}</textarea>
    </div>
@else
    @foreach($editorMeta as $key => $meta)
        @php $val = $editorSections[$key] ?? ''; @endphp
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $meta['label'] }}</label>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-1">{{ $meta['hint'] }}</p>
            <textarea data-insight-field="{{ $key }}" rows="{{ $meta['rows'] }}" class="{{ $editorFieldClass }}">{{ is_array($val) ? implode("\n", $val) : $val }}</textarea>
        </div>
    @endforeach
@endif
