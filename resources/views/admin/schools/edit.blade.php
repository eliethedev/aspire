@extends('layouts.admin')

@section('title', 'Edit School')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.schools.index') }}" class="mr-4 text-white hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Edit School</h1>
                <p class="text-white mt-1">{{ $school->name }}</p>
            </div>
        </div>
    </div>

    <!-- School Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-s glass-card p-6 mb-6">
        <div class="flex items-start space-x-6">
            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2h-3a2 2 0 00-2-2v14a2 2 0 002 2h3a2 2 0 002 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-white">{{ $school->name }}</h3>
                <p class="text-white">{{ $school->domain ?? $school->subdomain }}</p>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($school->is_active)
                            bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-slate-100 text-white
                        @endif">
                        @if($school->is_active)
                            Active
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            Trial
                        @else
                            Inactive
                        @endif
                    </span>
                    <span class="text-sm text-white">
                        {{ $school->users_count ?? 0 }} users
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-s glass-card p-6">
        <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- School Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 p-2 rounded-lg glass-card">School Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white mb-1">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" 
                               value="{{ old('name', $school->name) }}" required
                               class="w-full px-3 text-white py-2 glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('name') ? 'border-red-500' : '' }}"
                               placeholder="Enter school name">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-white mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug" 
                               value="{{ old('slug', $school->slug) }}" required
                               class="w-full px-3 text-white py- glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('slug') ? 'border-red-500' : '' }}"
                               placeholder="school-identifier">
                        @error('slug')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Unique identifier for URL (e.g., "manila-science-high-school")</p>
                    </div>
                </div>
            </div>

            <!-- Domain Settings -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 p-2 rounded-lg glass-card">Domain Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="domain" class="block text-sm font-medium text-white mb-1">
                            Domain
                        </label>
                        <input type="text" id="domain" name="domain" 
                               value="{{ old('domain', $school->domain) }}"
                               class="w-full px-3 text-white py- glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('domain') ? 'border-red-500' : '' }}"
                               placeholder="example.com">
                        @error('domain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Primary domain for this school (optional)</p>
                    </div>
                    
                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-white mb-1">
                            Subdomain
                        </label>
                        <input type="text" id="subdomain" name="subdomain" 
                               value="{{ old('subdomain', $school->subdomain) }}"
                               class="w-full px-3 text-white py- glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('subdomain') ? 'border-red-500' : '' }}"
                               placeholder="manila-science">
                        @error('subdomain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Subdomain for this school (optional)</p>
                    </div>
                </div>
            </div>

            <!-- School Status -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 p-2 rounded-lg glass-card">School Status</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               class="w-4 h-4 text- glass-card rounded focus:ring-blue-500 focus:border-blue-500
                               {{ old('is_active') ? 'checked' : '' }}">
                        <label for="is_active" class="ml-2 text-sm font-medium text-white">
                            Active School
                        </label>
                        <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 ml-5">Enable this school for user access and functionality</p>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="has_trial" name="has_trial" value="1" 
                               class="w-4 h-4 text- glass-card rounded focus:ring-blue-500 focus:border-blue-500
                               {{ old('has_trial') ? 'checked' : '' }}">
                        <label for="has_trial" class="ml-2 text-sm font-medium text-white">
                            Trial Period
                        </label>
                        <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 ml-2">Set trial period for this school</p>
                    </div>
                    
                    @if(old('has_trial', $school->has_trial))
                    <div class="mt-4">
                        <label for="trial_ends_at" class="block text-sm font-medium text-white mb-1">
                            Trial End Date
                        </label>
                        <input type="date" id="trial_ends_at" name="trial_ends_at" 
                               value="{{ old('trial_ends_at', $school->trial_ends_at?->format('Y-m-d')) }}"
                               class="w-full px-3 text-white py- glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('trial_ends_at') ? 'border-red-500' : '' }}">
                        @error('trial_ends_at')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 ml-5">When trial period ends (leave empty for no trial)</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- School Settings (JSON) -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 p-2 rounded-lg glass-card">School Settings</h2>
                <div class="bg-slate-5 glass-card rounded-lg p-4 mb-4">
                    <p class="text-sm text-white mb-2">Configuration settings in JSON format (optional)</p>
                    <textarea id="settings" name="settings" rows="6" 
                              class="w-full px-3 text-white py-2 glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                              placeholder='{"theme": "light", "features": ["observations", "reports"]}' 
                              >{{ old('settings', $school->settings) }}</textarea>
                    @error('settings')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-white">JSON object with school-specific settings</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.schools.index') }}" 
                       class="px-4 py-2 text-white bg-white glass-card rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                </div>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Update School
                </button>
            </div>
        </form>
        
        <!-- Delete Form (separate from update form) -->
        <div class="mt-4 flex justify-end">
            <form method="POST" 
                  action="{{ route('admin.schools.destroy', $school) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 text-red-600 dark:text-red-400 bg-danger border border-red-300 rounded-lg hover:bg-red-50 dark:bg-red-900/20 transition-colors">
                    Delete School
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
