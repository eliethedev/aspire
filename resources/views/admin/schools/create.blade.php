@extends('layouts.admin')

@section('title', 'Create New School')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.schools.index') }}"
               class="p-2 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create New School</h1>
                <p class="text-gray-500 mt-1">Add a new educational institution to the system.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.schools.store') }}" class="space-y-6">
        @csrf

        <!-- School Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">School Information</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        School Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                           {{ $errors->has('name') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                           placeholder="Enter school name">
                    @error('name')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                           {{ $errors->has('slug') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                           placeholder="school-identifier">
                    @error('slug')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Unique identifier for URL (e.g., "manila-science-high-school")</p>
                </div>
            </div>
        </div>

        <!-- Domain Settings -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Domain Settings</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Domain
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="domain" name="domain" value="{{ old('domain') }}"
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                           {{ $errors->has('domain') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                           placeholder="example.com">
                    @error('domain')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Primary domain for this school</p>
                </div>

                <div>
                    <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Subdomain
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="subdomain" name="subdomain" value="{{ old('subdomain') }}"
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow
                           {{ $errors->has('subdomain') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                           placeholder="manila-science">
                    @error('subdomain')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Subdomain for this school</p>
                </div>
            </div>
        </div>

        <!-- School Status -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">School Status</h2>
            </div>

            <div class="space-y-4">
                <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all cursor-pointer">
                    <input type="checkbox" id="is_active" name="is_active" value="1" 
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ old('is_active') ? 'checked' : '' }}>
                    <div>
                        <span class="text-sm font-medium text-gray-900">Active School</span>
                        <p class="text-xs text-gray-500">Enable this school for user access and functionality</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all cursor-pointer">
                    <input type="checkbox" id="has_trial" name="has_trial" value="1" 
                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           {{ old('has_trial') ? 'checked' : '' }}>
                    <div>
                        <span class="text-sm font-medium text-gray-900">Trial Period</span>
                        <p class="text-xs text-gray-500">Set trial period for new schools</p>
                    </div>
                </label>

                <div id="trial_date_wrapper" class="ml-9 {{ old('has_trial') ? '' : 'hidden' }}">
                    <label for="trial_ends_at" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Trial End Date
                    </label>
                    <input type="date" id="trial_ends_at" name="trial_ends_at" value="{{ old('trial_ends_at') }}"
                           class="w-full px-4 py-2.5 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow max-w-xs
                           {{ $errors->has('trial_ends_at') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}">
                    @error('trial_ends_at')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">When trial period ends (leave empty for no trial)</p>
                </div>
            </div>
        </div>

        <!-- School Settings (JSON) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">School Settings</h2>
            </div>

            <div>
                <label for="settings" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Settings (JSON)
                    <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea id="settings" name="settings" rows="6" 
                          class="w-full px-4 py-3 rounded-lg border text-gray-900 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-shadow font-mono
                          {{ $errors->has('settings') ? 'border-red-400 ring-1 ring-red-100' : 'border-gray-300' }}"
                          placeholder='{"theme": "light", "features": ["observations", "reports"]}'>{{ old('settings') }}</textarea>
                @error('settings')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">JSON object with school-specific configuration settings.</p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.schools.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-sm shadow-sm shadow-indigo-200 transition-all">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create School
                </span>
            </button>
        </div>
    </form>
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
