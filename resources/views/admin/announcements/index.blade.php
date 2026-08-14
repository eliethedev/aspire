@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-4">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Announcements</h1>
                <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Create and send announcements to users.</p>
            </div>
            <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Announcement
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search"
                           class="w-full px-3 py-2 border text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Search by title or message..."
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 border text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Drafts</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors">
                        Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'status']))
            <div class="flex items-center mt-2">
                <a href="{{ route('admin.announcements.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                    Clear filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sent By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($announcements as $announcement)
                    <tr class="hover:bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium">{{ $announcement->title }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 truncate max-w-xs">{{ Str::limit($announcement->message, 80) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($announcement->type === 'in_app') bg-blue-100 dark:bg-blue-900/30 text-blue-800
                                @elseif($announcement->type === 'email') bg-purple-100 dark:bg-purple-900/30 text-purple-800
                                @else bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 @endif">
                                @if($announcement->type === 'in_app') In-App
                                @elseif($announcement->type === 'email') Email
                                @else Both @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">
                                @if($announcement->targetsAll())
                                    All Users
                                @else
                                    {{ collect($announcement->target_roles)->map(fn($r) => ucwords(str_replace('_', ' ', $r)))->join(', ') }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($announcement->isSent()) bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $announcement->isSent() ? 'Sent' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $announcement->sender?->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">
                                {{ $announcement->isSent() ? $announcement->sent_at->format('M j, Y g:i A') : $announcement->created_at->format('M j, Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.announcements.show', $announcement) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 text-sm font-medium">
                                    View
                                </a>
                                @if($announcement->isDraft())
                                <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:text-blue-900 text-sm font-medium">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.announcements.send', $announcement) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Send this announcement to all targeted users?')"
                                            class="text-green-600 dark:text-green-400 hover:text-green-900 text-sm font-medium">
                                        Send
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Delete this draft?')"
                                            class="text-red-600 dark:text-red-400 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">No announcements found.</p>
                            <a href="{{ route('admin.announcements.create') }}" class="mt-3 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                Create your first announcement
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>
</div>
@endsection
