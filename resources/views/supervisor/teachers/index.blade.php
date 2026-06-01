@extends('layouts.supervisor')

@section('title', 'Teachers')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-dark">Teachers</h1>
        <a href="{{ route('supervisor.observations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            New Observation
        </a>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6 mb-6">
        <form method="GET" action="{{ route('supervisor.teachers.index') }}" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search teachers..." 
                   class="flex-1 px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-dark placeholder-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Search
            </button>
        </form>
    </div>

    <!-- Teachers List -->
    <div class="bg-white rounded-xl shadow-sm glass-card overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark/80 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark/80 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark/80 uppercase tracking-wider">School</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark/80 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($teachers as $teacher)
                    <tr class="hover:bg-white/5">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-dark">{{ $teacher->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark/80">{{ $teacher->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark/80">{{ $teacher->school->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" 
                               class="text-blue-400 hover:text-blue-300">
                                Create Observation
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-dark/60">
                            No teachers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($teachers->hasPages())
        <div class="mt-6">
                            {{ $teachers->appends(request()->query())->links() }}
                        </div>
    @endif
</div>
@endsection
