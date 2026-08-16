@extends('layouts.admin')

@section('title', 'COT Indicators')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-4">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">COT / PPST Indicators</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Manage versioned indicator sets used by classroom observations, AI feedback, and reports.</p>
            </div>
            <a href="{{ route('admin.cot-indicators.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Version
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($versions as $version)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 {{ $version->is_default ? 'ring-2 ring-indigo-400' : '' }}">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $version->label }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">SY {{ $version->school_year }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            @if($version->isArchived()) bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                            @elseif($version->isPublished()) bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                            @else bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 @endif">
                            {{ $version->statusLabel() }}
                        </span>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->indicators_count }} indicator(s)</span>
                        <span>{{ $version->observations_count }} observation(s)</span>
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->frameworkLabel() }}</span>
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->careerTrackLabel() }}</span>
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->rateePositionLabel() }}</span>
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->careerStageLabel() ?? 'All stages' }}</span>
                        <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ $version->instrumentLabel() }}</span>
                        @if($version->is_default)
                            <span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded">Default</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.cot-indicators.template', $version) }}"
                           title="Download a blank COT template for this version"
                           class="flex-1 text-center px-3 py-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:bg-blue-900/30 rounded-lg transition-colors">
                            Download Template
                        </a>
                        <a href="{{ route('admin.cot-indicators.edit', $version) }}"
                           class="flex-1 text-center px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 rounded-lg transition-colors">Manage</a>
                        @if($version->isDraft())
                            <form method="POST" action="{{ route('admin.cot-indicators.publish', $version) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:bg-green-900/30 rounded-lg transition-colors"
                                        onclick="return confirm('Publish this version for SY {{ $version->school_year }}? It becomes the default and is immutable afterwards.')">Publish</button>
                            </form>
                        @endif
                        @if($version->isPublished())
                            <form method="POST" action="{{ route('admin.cot-indicators.archive', $version) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:bg-amber-900/30 rounded-lg transition-colors"
                                        onclick="return confirm('Archive this version? It stays readable for historical observations but will no longer be used for new ones.')">Archive</button>
                            </form>
                        @endif
                        @if($version->canEdit())
                            <form method="POST" action="{{ route('admin.cot-indicators.destroy', $version) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:bg-red-900/30 rounded-lg transition-colors"
                                        onclick="return confirm('Delete this draft version? This action cannot be undone.')">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 dark:text-gray-400 font-medium">No COT indicator versions yet.</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Run <code>php artisan db:seed --class=CotIndicatorSeeder</code> or create your first version.</p>
                <a href="{{ route('admin.cot-indicators.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Create Version</a>
            </div>
        @endforelse
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-100 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">How COT Indicator Versions Work</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
            <li>A school year can host <strong>multiple</strong> instruments scoped by ratee role and career stage (e.g. generic teacher, Master Teacher I-II, school head).</li>
            <li>Versions flow through a <strong>draft → published → archived</strong> lifecycle.</li>
            <li>Only <strong>published</strong> versions are used for new observations. Published versions are <strong>immutable</strong> — move them back to draft to revise.</li>
            <li>Observations pin the version used at creation, so archived or revised versions never change past observations.</li>
            <li>COT ratings keep their own indicator snapshots, so historical scores are never altered by indicator edits.</li>
        </ul>
    </div>
</div>
@endsection
