@extends('layouts.admin')

@section('title', 'Contact Inquiries')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <b>Contact Inquiries</b></div><div class="flex items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 font-medium">
                {{ $counts['new'] }} new
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-medium">
                {{ $counts['in_progress'] }} in progress
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-medium">
                {{ $counts['resolved'] }} resolved
            </span>
        </div></div>
<div class="mock-title"><div><h1>Contact Inquiries</h1><p class="text-gray-500 dark:text-gray-400 mt-1">Messages from prospective teachers, school heads, and supervisors via the public contact form.</p>
    </div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <section class="mock-panel">
        <section class="mock-panel"><form method="GET" action="{{ route('admin.contact-inquiries.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Statuses</option>
                        @foreach(['new', 'in_progress', 'resolved'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Role</label>
                    <select name="role" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Roles</option>
                        @foreach(['teacher' => 'Teacher', 'school_head' => 'School Head', 'supervisor' => 'Supervisor', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Topic</label>
                    <select name="topic" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Topics</option>
                        @foreach(['account_access' => 'Account access', 'demo' => 'Request a demo', 'partnership' => 'Partnership', 'feedback' => 'Feedback', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ request('topic') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Search by name, email, school, subject...">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'role', 'topic', 'status']))
                    <a href="{{ route('admin.contact-inquiries.index') }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        Clear
                    </a>
                @endif
                <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">{{ $inquiries->total() }} inquiries</span>
            </div>
        </form></section>
    </section>

    <section class="mock-panel"><div class="mock-panel-head"><h2>Contact Inquiries</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">From</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Role</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Topic</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Subject</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Received</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($inquiry->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-gray-900 dark:text-gray-100 font-medium text-sm truncate">{{ $inquiry->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $inquiry->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $inquiry->role === 'teacher' ? 'bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400' : ($inquiry->role === 'school_head' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : ($inquiry->role === 'supervisor' ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400')) }}">
                                    {{ $inquiry->roleLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $inquiry->topicLabel() }}
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="text-gray-900 dark:text-gray-100 font-medium truncate">{{ $inquiry->subject }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $inquiry->school_name ?? $inquiry->message }}</p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $inquiry->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($inquiry->status === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                                    {{ $inquiry->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                {{ $inquiry->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">No contact inquiries found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($inquiries->hasPages())
        <div class="mt-6">{{ $inquiries->links() }}</div>
    @endif
</div>
@endsection
