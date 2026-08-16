@extends('layouts.admin')

@section('title', $supportMessage->subject)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('admin.support-messages.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">&larr; Back to Support Messages</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $supportMessage->subject }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-4">
        <div class="flex items-center gap-2 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ strtoupper(substr($supportMessage->user?->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $supportMessage->user?->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 capitalize">{{ $supportMessage->user?->email ?? '' }} &middot; {{ str_replace('_', ' ', $supportMessage->user?->role ?? '') }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $supportMessage->type === 'bug' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : ($supportMessage->type === 'suggestion' ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400') }}">
                {{ $supportMessage->typeLabel() }}
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $supportMessage->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($supportMessage->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400') }}">
                {{ $supportMessage->statusLabel() }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">{{ $supportMessage->created_at->format('M d, Y h:i A') }}</span>
        </div>

        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $supportMessage->message }}</p>
    </div>

    <form method="POST" action="{{ route('admin.support-messages.update', $supportMessage) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status" required
                    class="mt-1 block w-full sm:w-64 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                <option value="open" {{ old('status', $supportMessage->status) === 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ old('status', $supportMessage->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ old('status', $supportMessage->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="admin_note" :value="__('Admin Response / Note')" />
            <textarea id="admin_note" name="admin_note" rows="4" maxlength="5000"
                      class="mt-1 block w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                      placeholder="Optional note or reply. This is shown to the user who sent the message.">{{ old('admin_note', $supportMessage->admin_note) }}</textarea>
            <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                Update Message
            </button>
        </div>
    </form>

    <div class="mt-4 flex items-center justify-end">
        <form method="POST" action="{{ route('admin.support-messages.destroy', $supportMessage) }}"
              onsubmit="return confirm('Delete this support message? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                Delete Message
            </button>
        </form>
    </div>
</div>
@endsection
