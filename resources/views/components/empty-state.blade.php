@props(['title' => 'Nothing here yet', 'hint' => null, 'actionUrl' => null, 'actionLabel' => null])

<div {{ $attributes->merge(['class' => 'text-center py-12 sm:py-16 px-4']) }}>
    @if(isset($icon))
    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4 text-gray-400 dark:text-gray-500 [&>svg]:w-7 [&>svg]:h-7">
        {{ $icon }}
    </div>
    @endif
    <p class="empty-state-title">{{ $title }}</p>
    @if($hint)
    <p class="empty-state-hint mt-1 max-w-sm mx-auto">{{ $hint }}</p>
    @endif
    @if($actionUrl && $actionLabel)
    <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        {{ $actionLabel }}
    </a>
    @endif
    {{ $slot }}
</div>
