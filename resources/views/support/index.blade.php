@extends($layout)

@section('title', 'My Messages')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">My Messages</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Messages you have sent to the administrators.</p>
        </div>
        <a href="{{ route('support.create') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            New Message
        </a>
    </div>

    @if($messages->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">You have not sent any messages yet.</p>
            <a href="{{ route('support.create') }}" class="text-indigo-600 dark:text-indigo-400 text-sm font-medium hover:text-indigo-800">
                Send your first message
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($messages as $message)
                <a href="{{ route('support.show', $message) }}"
                   class="block bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 hover:border-indigo-300 dark:hover:border-indigo-800 transition-colors">
                    <div class="flex items-center justify-between gap-4 mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $message->type === 'bug' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : ($message->type === 'suggestion' ? 'bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400') }}">
                                {{ $message->typeLabel() }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $message->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($message->status === 'in_progress' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400') }}">
                                {{ $message->statusLabel() }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ $message->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $message->subject }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">{{ $message->message }}</p>
                </a>
            @endforeach
        </div>

        @if($messages->hasPages())
            <div class="mt-6">{{ $messages->links() }}</div>
        @endif
    @endif
</div>
@endsection
