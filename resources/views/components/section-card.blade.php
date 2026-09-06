@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'page-card p-4 sm:p-6']) }}>
    @if($title || isset($actions))
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
        <div class="min-w-0">
            @if($title)
            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 leading-snug">{{ $title }}</h2>
            @endif
            @if($subtitle)
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    {{ $slot }}
</section>
