@extends($layout)

@section('title', $message->subject)
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar"><div class="mock-crumbs">Support <span>/</span> <b>Message</b></div><div class="mock-actions"><a href="{{ route('support.index') }}" class="mock-btn">&larr; Back to My Messages</a></div></div>
<div class="mock-title"><div><h1>{{ $message->subject }}</h1></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <section class="mock-panel">
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
    </section>

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
