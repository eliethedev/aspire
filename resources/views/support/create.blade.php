@extends($layout)

@section('title', 'Report a Bug or Send Feedback')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar"><div class="mock-crumbs">Support <span>/</span> <b>Report a Bug or Send Feedback</b></div><div class="mock-actions"><div>
            
            
        </div>
        <a href="{{ route('support.index') }}" class="mock-btn">
            My Messages
        </a></div></div>
<div class="mock-title"><div><h1>Report a Bug or Send Feedback</h1><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your message goes directly to the system administrators.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <section class="mock-panel"><form method="POST" action="{{ route('support.store') }}" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="type" :value="__('Type')" />
            <select id="type" name="type" required
                    class="mt-1 block w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <option value="bug" {{ old('type') === 'bug' ? 'selected' : '' }}>Bug Report</option>
                <option value="feedback" {{ old('type', 'feedback') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                <option value="suggestion" {{ old('type') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="subject" :value="__('Subject')" />
            <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')"
                          required maxlength="255" placeholder="Short summary of the issue or feedback" />
            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="message" :value="__('Message')" />
            <textarea id="message" name="message" rows="6" required maxlength="5000"
                      class="mt-1 block w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                      placeholder="Describe the bug you encountered or share your feedback...">{{ old('message') }}</textarea>
            <x-input-error :messages="$errors->get('message')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('support.index') }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                Cancel
            </a>
            <x-primary-button>{{ __('Send Message') }}</x-primary-button>
        </div>
    </form></section>
</div>
@endsection
