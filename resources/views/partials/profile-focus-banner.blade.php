{{--
    Profile-incomplete banner with field-specific jump targets.

    Reads session('profile_focus') — a list of {field, label, tab, input} —
    flashed by EnsureProfileComplete. Each pill jumps straight to the tab and
    input that needs attention. Falls back to the legacy session('profile_missing')
    string list when no focus data exists.

    Must be rendered inside the page's root x-data scope, which must provide:
        goToField(tab, inputId)
--}}
@if (session('status') === 'profile-incomplete' || session('profile_incomplete'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 rounded-xl p-5 flex items-start gap-4">
        <div class="w-10 h-10 shrink-0 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold">Complete your profile to continue</h3>
            <p class="text-sm mt-1">A few essential details are required before you can continue. Select an item to jump straight to the field that needs input.</p>
            @php $focusItems = session('profile_focus', []); @endphp
            @if (!empty($focusItems))
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($focusItems as $item)
                        <button type="button" @click="goToField('{{ $item['tab'] }}', '{{ $item['input'] }}')"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $item['label'] }}
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        </button>
                    @endforeach
                </div>
            @elseif (session('profile_missing'))
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach (session('profile_missing') as $field)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300">
                            {{ str_replace('_', ' ', ucfirst($field)) }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif

<style>
    .profile-missing-field {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, .25) !important;
    }
    .profile-missing-flash {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .25) !important;
        transition: box-shadow .2s ease, border-color .2s ease;
    }
</style>
