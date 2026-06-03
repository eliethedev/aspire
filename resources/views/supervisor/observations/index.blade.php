@extends('layouts.supervisor')

@section('title', 'Observations')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Observations</h1>
        <a href="{{ route('supervisor.observations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            New Observation
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6 mb-6">
        <form method="GET" action="{{ route('supervisor.observations.index') }}" class="flex gap-4 flex-wrap">
            <select name="observation_type" class="px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="teacher_observation" {{ request('observation_type') == 'teacher_observation' ? 'selected' : '' }}>Teacher Observations</option>
                <option value="school_head_observation" {{ request('observation_type') == 'school_head_observation' ? 'selected' : '' }}>School Head Observations</option>
            </select>
            <select name="status" class="px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <select name="stage" class="px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Stages</option>
                <option value="pre_observation_planning" {{ request('stage') == 'pre_observation_planning' ? 'selected' : '' }}>Pre-Observation Planning</option>
                <option value="pre_conference" {{ request('stage') == 'pre_conference' ? 'selected' : '' }}>Pre-Conference</option>
                <option value="observation" {{ request('stage') == 'observation' ? 'selected' : '' }}>Observation</option>
                <option value="post_conference" {{ request('stage') == 'post_conference' ? 'selected' : '' }}>Post-Conference</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Filter
            </button>
        </form>
    </div>

    <!-- Observations List -->
    <div class="bg-white rounded-xl shadow-sm glass-card overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Observee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Stage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white/80 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($observations as $observation)
                    <tr class="hover:bg-white/5">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-white">{{ $observation->teacher->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white/80">{{ $observation->observation_date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white/80">{{ ucfirst(str_replace('-', ' ', $observation->stage)) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $observation->status === 'completed' ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300' }}">
                                {{ ucfirst($observation->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('supervisor.observations.show', $observation) }}" class="text-blue-400 hover:text-blue-300 mr-3">View</a>
                            @if($observation->stage === 'pre_observation_planning')
                                <a href="{{ route('supervisor.observations.preObservationPlanning', $observation) }}" class="text-green-400 hover:text-green-300">Continue</a>
                            @elseif($observation->stage === 'pre_conference')
                                <a href="{{ route('supervisor.observations.preConference', $observation) }}" class="text-green-400 hover:text-green-300">Continue</a>
                            @elseif($observation->stage === 'observation')
                                <a href="{{ route('supervisor.observations.observation', $observation) }}" class="text-green-400 hover:text-green-300">Continue</a>
                            @elseif($observation->stage === 'post_conference' && $observation->status !== 'completed')
                                <a href="{{ route('supervisor.observations.postConference', $observation) }}" class="text-green-400 hover:text-green-300">Continue</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-white/60">
                            No observations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($observations->hasPages())
        <div class="mt-6">
            {{ $observations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

    <!-- Pagination -->
    @if($observations->hasPages())
        <div class="mt-6">
            {{ $observations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
