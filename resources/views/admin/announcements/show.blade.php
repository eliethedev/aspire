@extends('layouts.admin')

@section('title', $announcement->title)

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8 space-y-8">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center">
                <a href="{{ route('admin.announcements.index') }}" class="mr-4 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $announcement->title }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">
                        {{ $announcement->isSent() ? 'Sent' : 'Draft' }} announcement
                        @if($announcement->sender)
                            by {{ $announcement->sender->name }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($announcement->isDraft())
                <a href="{{ route('admin.announcements.edit', $announcement) }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 transition-colors text-sm">
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.announcements.send', $announcement) }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Send this announcement?')"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                        Send Now
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                    @if($announcement->isSent()) bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                    @else bg-yellow-100 text-yellow-800 @endif">
                    {{ $announcement->isSent() ? 'Sent' : 'Draft' }}
                </span>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Delivery Method</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                    @if($announcement->type === 'in_app') bg-blue-100 dark:bg-blue-900/30 text-blue-800
                    @elseif($announcement->type === 'email') bg-purple-100 dark:bg-purple-900/30 text-purple-800
                    @else bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 @endif">
                    @if($announcement->type === 'in_app') In-App Notification
                    @elseif($announcement->type === 'email') Email
                    @else In-App + Email @endif
                </span>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Target Users</h3>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                    @if($announcement->targetsAll())
                        All Users
                    @else
                        {{ collect($announcement->target_roles)->map(fn($r) => ucwords(str_replace('_', ' ', $r)))->join(', ') }}
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sent At</h3>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                    {{ $announcement->isSent() ? $announcement->sent_at->format('F j, Y g:i A') : 'Not yet sent' }}
                </p>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Created</h3>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $announcement->created_at->format('F j, Y g:i A') }}</p>
            </div>
            @if($announcement->link)
            <div>
                <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Link</h3>
                <a href="{{ $announcement->link }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 mt-1 inline-block">
                    {{ $announcement->link }}
                </a>
            </div>
            @endif
        </div>

        <div class="border-t pt-6">
            <h3 class="text-sm font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Message</h3>
            <div class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap">
                {{ $announcement->message }}
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.announcements.index') }}"
           class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100">
            &larr; Back to Announcements
        </a>
        @if($announcement->isDraft())
        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Delete this draft?')"
                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-800">
                Delete Draft
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
