@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-3']) }}>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-snug tracking-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
