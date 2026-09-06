@props(['label', 'value', 'accent' => 'indigo'])

@php
$accents = [
    'indigo' => ['text' => 'text-gray-900 dark:text-gray-100', 'chip' => 'bg-indigo-100 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
    'amber' => ['text' => 'text-amber-600 dark:text-amber-400', 'chip' => 'bg-amber-100 dark:bg-amber-900/30', 'icon' => 'text-amber-600 dark:text-amber-400'],
    'green' => ['text' => 'text-green-600 dark:text-green-400', 'chip' => 'bg-green-100 dark:bg-green-900/30', 'icon' => 'text-green-600 dark:text-green-400'],
    'red' => ['text' => 'text-red-600 dark:text-red-400', 'chip' => 'bg-red-100 dark:bg-red-900/30', 'icon' => 'text-red-600 dark:text-red-400'],
    'blue' => ['text' => 'text-blue-600 dark:text-blue-400', 'chip' => 'bg-blue-100 dark:bg-blue-900/30', 'icon' => 'text-blue-600 dark:text-blue-400'],
    'purple' => ['text' => 'text-purple-600 dark:text-purple-400', 'chip' => 'bg-purple-100 dark:bg-purple-900/30', 'icon' => 'text-purple-600 dark:text-purple-400'],
];
$style = $accents[$accent] ?? $accents['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'stat-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3']) }}>
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $label }}</p>
            <p class="text-lg font-bold {{ $style['text'] }} mt-0.5 truncate">{{ $value }}</p>
        </div>
        @if(isset($icon))
        <div class="w-8 h-8 rounded-lg {{ $style['chip'] }} flex items-center justify-center shrink-0">
            <span class="{{ $style['icon'] }} [&>svg]:w-4 [&>svg]:h-4">{{ $icon }}</span>
        </div>
        @endif
    </div>
</div>
