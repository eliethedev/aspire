@php
    // Renders a friendly model picker: curated dropdown + "Custom model…" text input.
    // Usage:
    //   @include('admin.ai.partials.model-select', ['name' => 'ai_gemini_model', 'id' => 'gemini-model', 'current' => $value, 'provider' => 'gemini'])
    //   ... or pass 'groups' => ['gemini','openai'] to render grouped options from several providers.
    $catalog = $catalog ?? config('ai.model_catalog', []);

    if (isset($groups)) {
        $optionGroups = collect($groups)
            ->filter(fn ($key) => isset($catalog[$key]))
            ->map(fn ($key) => [
                'label' => $catalog[$key]['label'],
                'options' => $catalog[$key]['models'],
            ])
            ->values()
            ->all();
    } else {
        $optionGroups = [
            ['label' => null, 'options' => $catalog[$provider]['models'] ?? []],
        ];
    }

    $knownIds = collect($optionGroups)->flatMap(fn ($g) => collect($g['options'])->pluck('id'))->all();
    $currentValue = $current ?? '';
    $isCustom = filled($currentValue) && ! in_array($currentValue, $knownIds, true);
@endphp
<div data-model-field>
    <select name="{{ $name }}" id="{{ $id }}" data-model-select
            onchange="aiModelSelectChanged(this)"
            class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        @if (empty($currentValue))
            <option value="">— Choose a model —</option>
        @endif
        @foreach ($optionGroups as $group)
            @if ($group['label'])
                <optgroup label="{{ $group['label'] }}">
            @endif
            @foreach ($group['options'] as $option)
                <option value="{{ $option['id'] }}" {{ (! $isCustom && $currentValue === $option['id']) ? 'selected' : '' }}>
                    {{ $option['name'] }}
                </option>
            @endforeach
            @if ($group['label'])
                </optgroup>
            @endif
        @endforeach
        <option value="__custom__" {{ $isCustom ? 'selected' : '' }}>Custom model&hellip;</option>
    </select>
    <input type="text" name="{{ $name }}_custom" value="{{ $isCustom ? $currentValue : '' }}"
           data-custom-input
           placeholder="Type the exact model ID (e.g., gemini-exp-1206)"
           class="mt-2 {{ $isCustom ? '' : 'hidden' }} w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>
