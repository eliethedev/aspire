@extends('layouts.supervisor')

@section('title', 'Edit Feedback')

@push('styles')
<style>
    .list-input-item { transition: all 0.15s ease; }
    .list-input-item:hover { border-color: #a5b4fc; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400 dark:text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.feedback.index', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Feedback</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Edit Feedback</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">Edit Feedback</h1>
            <p class="text-dark-500 mt-1">
                {{ $observation->observee->user->name ?? 'Unknown' }}
                &middot; {{ $feedback->feedbackTypeLabel() }}
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $feedback->statusBadgeClass() }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $feedback->status === 'published' ? 'bg-green-50 dark:bg-green-900/200' : ($feedback->status === 'draft' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                    {{ ucfirst($feedback->status) }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($feedback->status === 'draft')
                <form method="POST" action="{{ route('supervisor.feedback.publish', [$observation, $feedback]) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Publish
                    </button>
                </form>
            @endif
            <a href="{{ route('supervisor.feedback.index', $observation) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 text-sm font-medium transition-colors">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('supervisor.feedback.update', [$observation, $feedback]) }}">
        @csrf
        @method('PATCH')

        <div class="space-y-6">
            <!-- Analysis -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Analysis</h2>
                    @if($feedback->generated_by === 'ai')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">AI-Generated</span>
                    @endif
                </div>
                <textarea name="analysis" rows="6"
                          class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                          placeholder="Write your analysis of the observation...">{{ old('analysis', $feedback->analysis) }}</textarea>
                @error('analysis')
                    <p class="mt-1 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Strengths -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Strengths</h2>
                </div>
                <div id="strengths-list" class="space-y-2.5">
                    @php $strengths = old('strengths', $feedback->strengths ?? ['']); @endphp
                    @foreach($strengths as $i => $strength)
                        <div class="list-input-item flex items-center gap-2">
                            <input type="text" name="strengths[]" value="{{ $strength }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                                   placeholder="Enter a strength...">
                            <button type="button" onclick="this.closest('.list-input-item').remove()"
                                    class="p-1.5 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addListItem('strengths-list', 'strengths[]', 'Enter a strength...')"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm text-green-600 dark:text-green-400 hover:text-green-700 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Strength
                </button>
            </div>

            <!-- Areas for Improvement -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Areas for Improvement</h2>
                </div>
                <div id="afi-list" class="space-y-2.5">
                    @php $afiItems = old('areas_for_improvement', $feedback->areas_for_improvement ?? ['']); @endphp
                    @foreach($afiItems as $i => $item)
                        <div class="list-input-item flex items-center gap-2">
                            <input type="text" name="areas_for_improvement[]" value="{{ $item }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                                   placeholder="Enter an area for improvement...">
                            <button type="button" onclick="this.closest('.list-input-item').remove()"
                                    class="p-1.5 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addListItem('afi-list', 'areas_for_improvement[]', 'Enter an area for improvement...')"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm text-amber-600 dark:text-amber-400 hover:text-amber-700 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Area for Improvement
                </button>
            </div>

            <!-- Recommendations -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recommendations</h2>
                </div>
                <div id="recs-list" class="space-y-2.5">
                    @php $recs = old('recommendations', $feedback->recommendations ?? ['']); @endphp
                    @foreach($recs as $i => $rec)
                        <div class="list-input-item flex items-center gap-2">
                            <input type="text" name="recommendations[]" value="{{ $rec }}"
                                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="Enter a recommendation...">
                            <button type="button" onclick="this.closest('.list-input-item').remove()"
                                    class="p-1.5 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addListItem('recs-list', 'recommendations[]', 'Enter a recommendation...')"
                        class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Recommendation
                </button>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    All changes are saved as a draft until published.
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function addListItem(listId, name, placeholder) {
        const list = document.getElementById(listId);
        const item = document.createElement('div');
        item.className = 'list-input-item flex items-center gap-2';
        item.innerHTML = `
            <input type="text" name="${name}" value=""
                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                   placeholder="${placeholder}">
            <button type="button" onclick="this.closest('.list-input-item').remove()"
                    class="p-1.5 text-gray-400 dark:text-gray-500 hover:text-red-500 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        list.appendChild(item);
        item.querySelector('input').focus();
    }
</script>
@endpush
@endsection
