@extends('layouts.admin')

@section('title', 'Create New School')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.schools.index') }}" class="mr-4 text-white hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Create New School</h1>
                <p class="text-white mt-1">Add a new educational institution to the system.</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('admin.schools.store') }}" class="space-y-8">
            @csrf
            
            <!-- School Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-glass-card">School Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white mb-1">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('name') ? 'border-red-500' : '' }}"
                               placeholder="Enter school name">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-white mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                               class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('slug') ? 'border-red-500' : '' }}"
                               placeholder="school-identifier">
                        @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Unique identifier for URL (e.g., "manila-science-high-school")</p>
                    </div>
                </div>
            </div>

            <!-- Domain Settings -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-glass-card">Domain Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="domain" class="block text-sm font-medium text-white mb-1">
                            Domain
                        </label>
                        <input type="text" id="domain" name="domain" value="{{ old('domain') }}"
                               class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('domain') ? 'border-red-500' : '' }}"
                               placeholder="example.com">
                        @error('domain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Primary domain for this school (optional)</p>
                    </div>
                    
                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-white mb-1">
                            Subdomain
                        </label>
                        <input type="text" id="subdomain" name="subdomain" value="{{ old('subdomain') }}"
                               class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('subdomain') ? 'border-red-500' : '' }}"
                               placeholder="manila-science">
                        @error('subdomain')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">Subdomain for this school (optional)</p>
                    </div>
                </div>
            </div>

            <!-- School Status -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-glass-card">School Status</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               class="w-4 h-4 text-blue-glass-card rounded focus:ring-blue-500 focus:border-blue-500"
                               {{ old('is_active') ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 text-sm font-medium text-white">
                            Active School
                        </label>
                        <p class="text-sm text-gray-600 ml-5">Enable this school for user access and functionality</p>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="has_trial" name="has_trial" value="1" 
                               class="w-4 h-4 text-blue-glass-card rounded focus:ring-blue-500 focus:border-blue-500
                               {{ old('has_trial') ? 'checked' : '' }}">
                        <label for="has_trial" class="ml-2 text-sm font-medium text-white">
                            Trial Period
                        </label>
                        <p class="text-sm text-gray-600 ml-5">Set trial period for new schools</p>
                    </div>
                    
                    @if(old('has_trial'))
                    <div class="mt-4">
                        <label for="trial_ends_at" class="block text-sm font-medium text-white mb-1">
                            Trial End Date
                        </label>
                        <input type="date" id="trial_ends_at" name="trial_ends_at" value="{{ old('trial_ends_at') }}"
                               class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('trial_ends_at') ? 'border-red-500' : '' }}">
                        @error('trial_ends_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-white">When trial period ends (leave empty for no trial)</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- School Settings (JSON) -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-glass-card">School Settings</h2>
                <div class="bg-slate-50 glass-card rounded-lg p-4 mb-4">
                    <p class="text-sm text-white mb-2">Configuration settings in JSON format (optional)</p>
                    <textarea id="settings" name="settings" rows="6" 
                              class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                              placeholder='{"theme": "light", "features": ["observations", "reports"]}' 
                              >{{ old('settings') }}</textarea>
                    @error('settings')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-white">JSON object with school-specific settings</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-glass-card">
                <a href="{{ route('admin.schools.index') }}" 
                   class="px-4 py-2 text-white bg-white glass-card rounded-lg hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create School
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
