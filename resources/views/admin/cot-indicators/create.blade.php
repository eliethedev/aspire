@extends('layouts.admin')

@section('title', 'New COT Indicator Version')

@section('content')
<div class="max-w-3xl mx-auto px-4 space-y-6">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('admin.cot-indicators.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">COT Indicators</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">New Version</li>
        </ol>
    </nav>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Version Details</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">The career stage is determined automatically from the framework, career track, and ratee position.</p>

        <form method="POST" action="{{ route('admin.cot-indicators.store') }}" class="space-y-4"
              x-data="versionForm()"
              x-init="framework = @js(old('framework', 'ppst')); track = @js(old('career_track')); position = @js(old('ratee_position')); onPositionChange();">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="school_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Year</label>
                    <select name="school_year" id="school_year"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('school_year') border-red-500 @enderror">
                        @foreach($schoolYears as $value => $label)
                            <option value="{{ $value }}" {{ old('school_year') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('school_year') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                    <input type="text" name="label" id="label" value="{{ old('label') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('label') border-red-500 @enderror"
                           placeholder="e.g. PPST COT 2026-2027">
                    @error('label') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="framework" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Framework</label>
                    <select name="framework" id="framework" x-model="framework" @change="onFrameworkChange()"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('framework') border-red-500 @enderror">
                        @foreach($frameworks as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('framework') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="career_track" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Career Track</label>
                    <select name="career_track" id="career_track" x-model="track" @change="onTrackChange()"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('career_track') border-red-500 @enderror">
                        <option value="">Select career track</option>
                        <template x-for="(trackLabel, trackValue) in availableTracks" :key="trackValue">
                            <option :value="trackValue" x-text="trackLabel"></option>
                        </template>
                    </select>
                    @error('career_track') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="ratee_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ratee Position</label>
                    <select name="ratee_position" id="ratee_position" x-model="position" @change="onPositionChange()"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('ratee_position') border-red-500 @enderror">
                        <option value="">Select ratee position</option>
                        <template x-for="positionOption in availablePositions" :key="positionOption.value">
                            <option :value="positionOption.value" x-text="positionOption.label"></option>
                        </template>
                    </select>
                    @error('ratee_position') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="career_stage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Career Stage</label>
                    <div id="career_stage_display"
                         class="w-full px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800 text-sm text-indigo-800 dark:text-indigo-300"
                         :class="stageDisplay ? '' : 'text-gray-400 dark:text-gray-500'">
                        <span x-text="stageDisplay || 'Automatically determined — select a ratee position'"></span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Read-only. ASPIRE derives the stage from the framework, track, and position.</p>
                    <input type="hidden" name="career_stage" :value="careerStage">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="instrument" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instrument</label>
                    <select name="instrument" id="instrument"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        @foreach($instruments as $value => $label)
                            <option value="{{ $value }}" {{ old('instrument', 'cot') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status" disabled
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 text-sm">
                        <option value="draft" selected>Draft</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Versions are created as drafts. Publish after adding indicators.</p>
                </div>
            </div>

            <input type="hidden" name="ratee_role" :value="rateeRole">

            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Set as default version
                </label>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">The default version is used when no school year is specified.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">
                    Create Version
                </button>
                <a href="{{ route('admin.cot-indicators.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function versionForm() {
        return {
            framework: '',
            track: '',
            position: '',
            careerStage: '',
            tracks: @json($tracks),
            positions: @json($positions),
            stageLabels: @json($stageLabels),

            get rateeRole() {
                return this.framework === 'ppssh' ? 'school_head' : 'teacher';
            },

            get availableTracks() {
                return this.framework ? (this.tracks[this.framework] || {}) : {};
            },

            get availablePositions() {
                if (!this.framework || !this.track) {
                    return [];
                }
                return (this.positions[this.framework] && this.positions[this.framework][this.track]) || [];
            },

            get stageDisplay() {
                return this.careerStage ? (this.stageLabels[this.careerStage] || this.careerStage) : '';
            },

            onFrameworkChange() {
                this.track = '';
                this.position = '';
                this.careerStage = '';
            },

            onTrackChange() {
                this.position = '';
                this.careerStage = '';
            },

            onPositionChange() {
                const match = this.availablePositions.find(p => p.value === this.position);
                this.careerStage = match ? match.career_stage : '';
            },
        };
    }
</script>
@endpush
@endsection
