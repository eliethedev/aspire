@extends($layout)

@section('title', $message->subject)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('support.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">&larr; Back to My Messages</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $message->subject }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $message->type === 'bug' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : ($message->type === 'suggestion' ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400') }}">
                {{ $message->typeLabel() }}
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $message->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($message->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400') }}">
                {{ $message->statusLabel() }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">{{ $message->created_at->format('M d, Y h:i A') }}</span>
        </div>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $message->message }}</p>
    </div>

    @if($message->admin_note)
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-5 mt-4">
            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1">Admin Response</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $message->admin_note }}</p>
            @if($message->resolved_at)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Updated {{ $message->resolved_at->format('M d, Y h:i A') }}</p>
            @endif
        </div>
    @endif
</div>
@endsection
