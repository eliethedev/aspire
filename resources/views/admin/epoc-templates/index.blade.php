@extends('layouts.admin')

@section('title', 'EPOC Templates')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <b>EPOC Templates</b></div><div class="mock-actions">
        <a href="{{ route('admin.epoc-templates.create') }}" class="mock-btn primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </a>
    </div></div>
    <div class="mock-title"><div><h1>EPOC Templates</h1><p class="text-gray-500 mt-1">Manage the domains and indicators rated in post-observation conference (EPOC) evaluations.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $template)
            @php
                $domainCount = $template->indicators->pluck('domain')->unique()->count();
            @endphp
            <section class="mock-panel template-card {{ $template->is_active ? 'ring-2 ring-indigo-400' : '' }}">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</h3>
                            <p class="text-sm text-gray-500">SY {{ $template->school_year }}</p>
                        </div>
                        @if($template->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Active</span>
                        @endif
                    </div>
                    @if($template->description)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $template->description }}</p>
                    @endif
                    <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 mb-4">
                        <span>{{ $domainCount }} domain(s)</span>
                        <span>{{ $template->indicators->count() }} indicator(s)</span>
                        <span>v{{ $template->version }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.epoc-templates.edit', $template) }}"
                           class="flex-1 text-center px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 rounded-lg transition-colors">Edit</a>
                        @if(!$template->is_active)
                            <form method="POST" action="{{ route('admin.epoc-templates.activate', $template) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 rounded-lg transition-colors"
                                        onclick="return confirm('Activate this EPOC template for SY {{ $template->school_year }}? This will deactivate others for the same school year.')">Activate</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.epoc-templates.destroy', $template) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 rounded-lg transition-colors"
                                    onclick="return confirm('Delete this EPOC template? This action cannot be undone.')">Delete</button>
                        </form>
                    </div>
                </div>
            </section>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm">
                <p class="text-gray-500 font-medium">No EPOC templates yet.</p>
                <p class="text-sm text-gray-400 mt-1">Create your first template, or load the default DepEd CID domains.</p>
                <a href="{{ route('admin.epoc-templates.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Create Template</a>
            </div>
        @endforelse
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-100 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">How EPOC Templates Work</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
            <li>Each template holds the <strong>domains and indicators</strong> rated in the post-observation conference (EPOC) evaluation sheet.</li>
            <li>Only <strong>one template per school year</strong> can be active at a time.</li>
            <li>New EPOC evaluations use the active template matching their school year — or the built-in DepEd defaults when none is active.</li>
            <li>Saved evaluations keep their own copy of the domain and indicator text, so editing a template never rewrites history.</li>
            <li>Duplicate a template to create next year's version, then modify as needed.</li>
        </ul>
    </div>
</div>
@endsection
