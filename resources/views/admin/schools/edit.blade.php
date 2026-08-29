@extends('layouts.admin')

@section('title', 'Edit School')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.schools.index') }}"
               class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:border-gray-300 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit School</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $school->name }}</p>
            </div>
        </div>
    </div>

    <!-- School Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $school->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $school->domain ?? $school->subdomain ?? 'No domain set' }}</p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($school->is_active)
                            bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
                        @else
                            bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                        @endif">
                        @if($school->is_active)
                            Active
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            Trial
                        @else
                            Inactive
                        @endif
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $school->users_count ?? 0 }} users
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- School Information -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">School Information</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', $school->name) }}" required
                               class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                               {{ $errors->has('name') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}"
                               placeholder="Enter school name">
                        @error('name')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug"
                               value="{{ old('slug', $school->slug) }}" required
                               class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                               {{ $errors->has('slug') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}"
                               placeholder="school-identifier">
                        @error('slug')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Unique identifier for URL (e.g., "manila-science-high-school")</p>
                    </div>
                </div>
            </div>

            <!-- Domain Settings -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Domain Settings</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Domain <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                        </label>
                        <input type="text" id="domain" name="domain"
                               value="{{ old('domain', $school->domain) }}"
                               class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                               {{ $errors->has('domain') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}"
                               placeholder="example.com">
                        @error('domain')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Primary domain for this school</p>
                    </div>

                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Subdomain <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                        </label>
                        <input type="text" id="subdomain" name="subdomain"
                               value="{{ old('subdomain', $school->subdomain) }}"
                               class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                               {{ $errors->has('subdomain') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}"
                               placeholder="manila-science">
                        @error('subdomain')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Subdomain for this school</p>
                    </div>
                </div>
            </div>

            <!-- School Status -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">School Status</h2>
                </div>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('is_active', $school->is_active) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Active School</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Enable this school for user access and functionality</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all cursor-pointer">
                        <input type="checkbox" id="has_trial" name="has_trial" value="1"
                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ old('has_trial', $school->has_trial) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Trial Period</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Set trial period for this school</p>
                        </div>
                    </label>

                    <div id="trial_date_wrapper" class="ml-9 {{ old('has_trial', $school->has_trial) ? '' : 'hidden' }}">
                        <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Trial End Date
                        </label>
                        <input type="date" id="trial_ends_at" name="trial_ends_at"
                               value="{{ old('trial_ends_at', $school->trial_ends_at?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow max-w-xs
                               {{ $errors->has('trial_ends_at') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}">
                        @error('trial_ends_at')
                        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">When trial period ends (leave empty for no trial)</p>
                    </div>
                </div>
            </div>

            <!-- School Settings (JSON) -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">School Settings</h2>
                </div>
                <div>
                    <label for="settings" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Settings (JSON) <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span>
                    </label>
                    <textarea id="settings" name="settings" rows="6"
                              class="w-full px-4 py-3 rounded-lg border text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow font-mono
                              {{ $errors->has('settings') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300 dark:border-gray-600' }}"
                              placeholder='{"theme": "light", "features": ["observations", "reports"]}'>{{ old('settings', $school->settings) }}</textarea>
                    @error('settings')
                    <p class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">JSON object with school-specific configuration settings.</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.schools.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-sm shadow-sm shadow-indigo-200 transition-all">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update School
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Delete Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Delete School</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Permanently remove this school and all associated data.</p>
            </div>
            <form method="POST"
                  action="{{ route('admin.schools.destroy', $school) }}"
                  onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                    Delete School
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trialCheckbox = document.getElementById('has_trial');
    const trialWrapper = document.getElementById('trial_date_wrapper');

    if (trialCheckbox && trialWrapper) {
        trialCheckbox.addEventListener('change', function () {
            trialWrapper.classList.toggle('hidden', !this.checked);
        });
    }
});
</script>
@endpush
@endsection
