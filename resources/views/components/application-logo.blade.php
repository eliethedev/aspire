{{-- ASPIRE theme-aware logo: white theme for light mode, dark theme for dark mode --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0']) }}>
    <img src="{{ asset('images/whitelogotheme.jpg') }}"
         alt="ASPIRE — Learn • Grow • Serve"
         class="block dark:hidden h-full w-auto max-w-full object-contain rounded-md" />
    <img src="{{ asset('images/darklogotheme.jpg') }}"
         alt="ASPIRE — Learn • Grow • Serve"
         class="hidden dark:block h-full w-auto max-w-full object-contain rounded-md" />
</span>
