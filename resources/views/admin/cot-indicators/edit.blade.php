@extends('layouts.admin')

@section('title', 'Manage COT Indicator Version')

@push('styles')
<style>
    .indicator-row { transition: all 0.2s ease; }
    .indicator-row:hover { border-color: #a5b4fc; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 space-y-6">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('admin.cot-indicators.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">COT Indicators</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">{{ $cotIndicatorVersion->label }}</li>
        </ol>
    </nav>

     @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
        <ul class="text-sm text-red-800 dark:text-red-300 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Version Settings -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 lg:sticky lg:top-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Version Settings</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        @if($cotIndicatorVersion->isArchived()) bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                        @elseif($cotIndicatorVersion->isPublished()) bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                        @else bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 @endif">
                        {{ $cotIndicatorVersion->statusLabel() }}
                    </span>
                </div>

                @if(!$cotIndicatorVersion->canEdit())
                    <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-sm text-amber-700 dark:text-amber-300">
                        This version is <strong>{{ strtolower($cotIndicatorVersion->statusLabel()) }}</strong> and immutable. Move it back to draft to revise its contents.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.cot-indicators.update', $cotIndicatorVersion) }}" class="space-y-4"
                      @if($cotIndicatorVersion->canEdit())
                      x-data="versionForm()"
                      x-init="framework = @js(old('framework', $cotIndicatorVersion->framework ?? 'ppst')); track = @js(old('career_track', $cotIndicatorVersion->career_track ?? 'classroom_teaching')); position = @js(old('ratee_position', $cotIndicatorVersion->ratee_position ?? 'teacher_i_iii')); onPositionChange();"
                      @endif>
                    @csrf @method('PUT')
                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                        <input type="text" name="label" id="label" value="{{ old('label', $cotIndicatorVersion->label) }}"
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               {{ $cotIndicatorVersion->canEdit() ? '' : 'disabled' }}>
                    </div>
                    <div>
                        <label for="school_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Year</label>
                        <select name="school_year" id="school_year"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                {{ $cotIndicatorVersion->canEdit() ? '' : 'disabled' }}>
                            @foreach($schoolYears as $value => $label)
                                <option value="{{ $value }}" {{ old('school_year', $cotIndicatorVersion->school_year) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($cotIndicatorVersion->canEdit())
                        <div>
                            <label for="framework" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Framework</label>
                            <select name="framework" id="framework" x-model="framework" @change="onFrameworkChange()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @foreach($frameworks as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="career_track" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Career Track</label>
                            <select name="career_track" id="career_track" x-model="track" @change="onTrackChange()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Select career track</option>
                                @foreach ($tracks[old('framework', $cotIndicatorVersion->framework ?? 'ppst')] ?? [] as $trackValue => $trackLabel)
                                    <option value="{{ $trackValue }}"
                                        {{ old('career_track', $cotIndicatorVersion->career_track) == $trackValue ? 'selected' : '' }}>
                                        {{ $trackLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="ratee_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ratee Position</label>
                            <select name="ratee_position" id="ratee_position" x-model="position" @change="onPositionChange()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Select ratee position</option>
                                @php
                                    $effectiveFramework = old('framework', $cotIndicatorVersion->framework);
                                    $effectiveTrack = old('career_track', $cotIndicatorVersion->career_track);
                                @endphp
                                @isset($positions[$effectiveFramework][$effectiveTrack])
                                    @foreach ($positions[$effectiveFramework][$effectiveTrack] as $positionOption)
                                        <option value="{{ $positionOption['value'] }}"
                                            {{ old('ratee_position', $cotIndicatorVersion->ratee_position) == $positionOption['value'] ? 'selected' : '' }}>
                                            {{ $positionOption['label'] }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div>
                            <label for="career_stage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Career Stage</label>
                            <div class="w-full px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800 text-sm text-indigo-800 dark:text-indigo-300"
                                 :class="stageDisplay ? '' : 'text-gray-400 dark:text-gray-500'">
                                <span x-text="stageDisplay || 'Automatically determined — select a ratee position'"></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Read-only. Changes to the framework or position recalculate the stage.</p>
                        </div>
                        <div>
                            <label for="instrument" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instrument</label>
                            <select name="instrument" id="instrument"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @foreach($instruments as $value => $label)
                                    <option value="{{ $value }}" {{ old('instrument', $cotIndicatorVersion->instrument ?? 'cot') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="ratee_role" :value="rateeRole">
                        <input type="hidden" name="career_stage" :value="careerStage">
                    @else
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Framework</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cotIndicatorVersion->frameworkLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Career Track</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cotIndicatorVersion->careerTrackLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Ratee Position</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cotIndicatorVersion->rateePositionLabel() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Career Stage</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cotIndicatorVersion->careerStageLabel() ?? 'All career stages (generic)' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Instrument</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cotIndicatorVersion->instrumentLabel() }}</span>
                            </div>
                            <input type="hidden" name="ratee_role" value="{{ $cotIndicatorVersion->ratee_role }}">
                            <input type="hidden" name="career_stage" value="{{ $cotIndicatorVersion->career_stage }}">
                        </div>
                    @endif

                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="is_default" value="1" {{ $cotIndicatorVersion->is_default ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $cotIndicatorVersion->canEdit() ? '' : 'disabled' }}>
                            Default version
                        </label>
                    </div>
                    @if($cotIndicatorVersion->canEdit())
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Save Settings</button>
                    @endif
                </form>

                <hr class="my-6 border-gray-200 dark:border-gray-700">

                <div class="space-y-2">
                    @if($cotIndicatorVersion->isDraft())
                        <form method="POST" action="{{ route('admin.cot-indicators.publish', $cotIndicatorVersion) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm transition-colors"
                                    onclick="return confirm('Publish this version for SY {{ $cotIndicatorVersion->school_year }}? It becomes the default and is immutable afterwards.')">
                                Publish Version
                            </button>
                        </form>
                    @elseif($cotIndicatorVersion->isPublished())
                        <form method="POST" action="{{ route('admin.cot-indicators.unpublish', $cotIndicatorVersion) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm transition-colors"
                                    onclick="return confirm('Move this version back to draft so its contents can be revised? Historical observations keep using their pinned version.')">
                                Move Back to Draft
                            </button>
                        </form>
                    @endif
                    @if($cotIndicatorVersion->isPublished())
                        <form method="POST" action="{{ route('admin.cot-indicators.archive', $cotIndicatorVersion) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm transition-colors"
                                    onclick="return confirm('Archive this version? It stays readable for historical observations but is no longer used for new ones.')">
                                Archive Version
                            </button>
                        </form>
                    @endif
                    @if($cotIndicatorVersion->canEdit())
                        <form method="POST" action="{{ route('admin.cot-indicators.destroy', $cotIndicatorVersion) }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm transition-colors"
                                    onclick="return confirm('Delete this draft version and all its indicators? This cannot be undone.')">
                                Delete Version
                            </button>
                        </form>
                    @endif
                </div>

                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">
                    {{ $cotIndicatorVersion->observations()->count() }} observation(s) are pinned to this version.
                </p>
            </div>
        </div>

        <!-- Indicators -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Indicators ({{ $cotIndicatorVersion->indicators->count() }})</h2>
                    @if($cotIndicatorVersion->canEdit())
                        <span class="text-xs text-gray-400 dark:text-gray-500">Edit a row and click <strong>Save</strong>. Use the arrows to reorder.</span>
                    @endif
                </div>

                @if($cotIndicatorVersion->canEdit())
                    <div class="space-y-3">
                        @forelse($cotIndicatorVersion->indicators as $indicator)
                            <div class="indicator-row border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col gap-1 pt-1">
                                        <form method="POST" action="{{ route('admin.cot-indicators.indicators.move', [$cotIndicatorVersion, $indicator]) }}">
                                            @csrf
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit"
                                                    class="p-1 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 disabled:opacity-30 disabled:cursor-not-allowed"
                                                    title="Move up" {{ $loop->first ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.cot-indicators.indicators.move', [$cotIndicatorVersion, $indicator]) }}">
                                            @csrf
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit"
                                                    class="p-1 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 disabled:opacity-30 disabled:cursor-not-allowed"
                                                    title="Move down" {{ $loop->last ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('admin.cot-indicators.indicators.update', $cotIndicatorVersion) }}" class="flex-1">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="id" value="{{ $indicator->id }}">
                                        <input type="hidden" name="ppst_standard_id" value="{{ $indicator->ppst_standard_id }}">
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Code</label>
                                                <input type="text" name="code" value="{{ old('code', $indicator->code) }}" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required>
                                            </div>
                                            <div class="md:col-span-9">
                                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Domain</label>
                                                <input type="text" name="domain" value="{{ old('domain', $indicator->domain) }}" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required>
                                            </div>
                                            <div class="md:col-span-12">
                                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                                                <textarea name="description" rows="2" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required>{{ old('description', $indicator->description) }}</textarea>
                                            </div>
                                            <div class="md:col-span-12 flex items-center justify-between">
                                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $indicator->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    Available for new observations
                                                </label>
                                                <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">Save</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <form method="POST" action="{{ route('admin.cot-indicators.indicators.destroy', $cotIndicatorVersion) }}" class="mt-3 inline-block">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $indicator->id }}">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:bg-red-900/30 rounded-lg transition-colors"
                                            onclick="return confirm('Remove this indicator from the version? Historical ratings keep their snapshot.')">Remove</button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400 dark:text-gray-500">
                                <p class="font-medium">No indicators yet.</p>
                                <p class="text-sm mt-1">Add the first indicator below.</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($cotIndicatorVersion->indicators as $indicator)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 rounded">{{ $indicator->code }}</span>
                                            @if(!$indicator->is_active)
                                                <span class="text-xs px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded">Inactive</span>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">{{ $indicator->description }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $indicator->domain }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($cotIndicatorVersion->indicators->isEmpty())
                            <div class="text-center py-10 text-gray-400 dark:text-gray-500">
                                <p class="font-medium">No indicators yet.</p>
                                <p class="text-sm mt-1">This version has no indicators.</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($cotIndicatorVersion->canEdit())
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Indicator</h3>
                        <form method="POST" action="{{ route('admin.cot-indicators.indicators.store', $cotIndicatorVersion) }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            @csrf
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Code</label>
                                <input type="text" name="code" placeholder="e.g. 6.1.2" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required>
                            </div>
                            <div class="md:col-span-9">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Domain</label>
                                <input type="text" name="domain" placeholder="e.g. Domain 6: Community Linkages..." class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required>
                            </div>
                            <div class="md:col-span-12">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Description</label>
                                <textarea name="description" rows="2" placeholder="Indicator description" class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 text-sm text-gray-900 dark:text-gray-100" required></textarea>
                            </div>
                            <div class="md:col-span-12 flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    Active
                                </label>
                                <button type="submit" class="px-4 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">Add Indicator</button>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700" x-data="ppstPicker()">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <i class="fas fa-book-open text-indigo-500 text-xs"></i>
                                Add from PPST Standards
                            </h3>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $ppstStandards->count() }} in library</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Pick indicators straight from the PPST library. Already-added codes are disabled.</p>

                        <div class="relative mb-3">
                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input type="text" x-model="search" @input="applyFilters()" placeholder="Search by code or keyword — e.g. 1.1.2 or literacy"
                                   class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="mb-3 flex flex-wrap items-center gap-1.5 text-xs">
                            <span class="text-gray-400 dark:text-gray-500 mr-1">Domain:</span>
                            <button type="button" @click="domainFilter='all'; applyFilters()" class="px-2 py-1 rounded-full border text-xs font-medium"
                                    :class="domainFilter === 'all' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700'">All</button>
                            @foreach($ppstDomains as $domain => $strands)
                                <button type="button" @click="domainFilter = @js($domain); applyFilters()"
                                        class="px-2 py-1 rounded-full border text-xs font-medium truncate max-w-[180px]"
                                        :class="domainFilter === @js($domain) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700'">
                                    {{ Str::limit($domain, 22) }}
                                </button>
                            @endforeach
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="visibleCount"></span> of <span x-text="totalCount"></span> visible
                        </p>

                        <div x-show="visibleCount === 0 && totalCount > 0" x-cloak class="text-center py-6 text-gray-400 dark:text-gray-500 text-sm">
                            No matching standards.
                        </div>

                        <form method="POST" action="{{ route('admin.cot-indicators.indicators.from-standard', $cotIndicatorVersion) }}" x-ref="pickerForm">
                            @csrf
                            <input type="hidden" name="ppst_standard_id" x-model="selectedStandardId">
                            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                @forelse($ppstDomains as $domain => $strands)
                                    <div data-picker-domain data-domain="{{ $domain }}">
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1 {{ $loop->first ? '' : 'mt-3' }}">{{ $domain }}</p>
                                        @foreach($strands as $strand => $indicators)
                                            @foreach($indicators as $standard)
                                                @php $used = $ppstUsedCodes->has($standard->indicator_code); @endphp
                                                <button type="button" data-picker-row
                                                        data-code="{{ $standard->indicator_code }}"
                                                        data-domain="{{ $standard->domain }}"
                                                        data-description="{{ $standard->description }}"
                                                        {{ $used ? 'disabled' : '' }}
                                                        @click="addStandard({{ $standard->id }}, {{ $standard->is_active ? 'true' : 'false' }}, {{ $used ? 'true' : 'false' }})"
                                                        class="w-full text-left flex items-start gap-2.5 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <span class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded font-mono text-[11px] font-semibold {{ $standard->is_active ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">{{ $standard->indicator_code }}</span>
                                                    <span class="flex-1 min-w-0 text-xs text-gray-700 dark:text-gray-300 leading-snug">{{ $standard->description }}</span>
                                                    @if($used)
                                                        <span class="shrink-0 text-[11px] font-medium text-emerald-600 dark:text-emerald-400"><i class="fas fa-check-circle"></i></span>
                                                    @else
                                                        <span class="shrink-0 text-[11px] font-medium text-indigo-600 dark:text-indigo-400"><i class="fas fa-plus"></i></span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 dark:text-gray-500">No PPST standards in the library yet.</p>
                                @endforelse
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($cotIndicatorVersion->canEdit())
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

    function ppstPicker() {
        return {
            search: '',
            domainFilter: 'all',
            selectedStandardId: '',
            totalCount: 0,
            visibleCount: 0,
            init() {
                this.totalCount = document.querySelectorAll('[data-picker-row]').length;
                this.visibleCount = this.totalCount;
            },
            applyFilters() {
                const q = this.search.trim().toLowerCase();
                const d = this.domainFilter;
                let visible = 0;

                document.querySelectorAll('[data-picker-row]').forEach(row => {
                    const code = (row.dataset.code || '').toLowerCase();
                    const domain = row.dataset.domain || '';
                    const desc = (row.dataset.description || '').toLowerCase();

                    let show = true;
                    if (q && !(code.includes(q) || domain.toLowerCase().includes(q) || desc.includes(q))) show = false;
                    if (d !== 'all' && domain !== d) show = false;

                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                document.querySelectorAll('[data-picker-domain]').forEach(section => {
                    const hasVisible = Array.from(section.querySelectorAll('[data-picker-row]')).some(r => r.style.display !== 'none');
                    section.style.display = hasVisible ? '' : 'none';
                });

                this.visibleCount = visible;
            },
            addStandard(id, active, used) {
                if (used || !active) return;
                this.selectedStandardId = id;
                this.$nextTick(() => this.$refs.pickerForm.submit());
            },
        };
    }
</script>
@endpush
@endif
@endsection
