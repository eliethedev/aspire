@extends('layouts.teacher')

@section('title', 'Career Advancement Approvals')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6" x-data="careerApprovals">
    <x-page-header title="Career Advancement Approvals" subtitle="Review and approve supervisor recommendations for teacher career advancements." />

    <!-- Tabs -->
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('school-head.career.advancements.index', ['tab' => 'pending']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'pending' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
            Pending Approval
        </a>
        <a href="{{ route('school-head.career.advancements.index', ['tab' => 'history']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $tab === 'history' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
            Reviewed
        </a>
    </div>

    @if($advancements->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-10 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                {{ $tab === 'pending' ? 'No pending approvals' : 'No reviewed advancements' }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $tab === 'pending'
                    ? 'There are no supervisor recommendations awaiting your approval right now.'
                    : 'Reviewed career advancements will appear here.' }}
            </p>
        </div>
    @else
    <div class="space-y-4">
        @foreach($advancements as $advancement)
            @php
                $isPending = $advancement->isPendingApproval();
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 {{ $isPending ? 'bg-amber-50 dark:bg-amber-900/20' : ($advancement->isApproved() ? 'bg-emerald-50 dark:bg-emerald-900/20' : ($advancement->isCancelled() ? 'bg-gray-100 dark:bg-gray-800' : 'bg-red-50 dark:bg-red-900/20')) }}">
                        <span class="font-semibold text-sm {{ $isPending ? 'text-amber-600 dark:text-amber-400' : ($advancement->isApproved() ? 'text-emerald-600 dark:text-emerald-400' : ($advancement->isCancelled() ? 'text-gray-500 dark:text-gray-400' : 'text-red-600 dark:text-red-400')) }}">{{ strtoupper(substr($advancement->teacher->user->name, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $advancement->teacher->user->name }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $advancement->statusBadgeClass() }}">
                                {{ $advancement->statusLabel() }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $advancement->typeBadgeClass() }}">
                                {{ $advancement->typeLabel() }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <strong class="text-gray-700 dark:text-gray-300">{{ $advancement->fromStageLabel() }}</strong>
                            <svg class="w-3.5 h-3.5 inline mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            <strong class="text-indigo-600 dark:text-indigo-400">{{ $advancement->toStageLabel() }}</strong>
                        </p>
                        <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-400 dark:text-gray-500 mt-2">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.15a4 4 0 11-2-7.46 4 4 0 012 7.46z"/></svg>
                                Recommended by {{ $advancement->supervisor?->name ?? 'Supervisor' }}
                            </span>
                            <span>{{ $advancement->acted_at?->format('M d, Y') }}</span>
                            <span>{{ $advancement->teacher->user->email }}</span>
                        </div>
                        @if($advancement->remarks)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mr-1">Supervisor remarks:</span>{{ $advancement->remarks }}
                            </p>
                        @endif
                        @if($advancement->school_head_remarks)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-xs font-medium uppercase tracking-wider mr-1 {{ $advancement->isApproved() ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">School head remarks:</span>{{ $advancement->school_head_remarks }}
                            </p>
                        @endif
                    </div>

                    @if($isPending)
                    <div class="flex flex-col items-end gap-2 shrink-0">
                        <button type="button" @click="openApprove({{ $advancement->id }}, '{{ route('school-head.career.advancements.approve', $advancement) }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approve
                        </button>
                        <button type="button" @click="openReject({{ $advancement->id }}, '{{ route('school-head.career.advancements.reject', $advancement) }}')"
                                 class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 text-sm font-medium hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors border border-red-200 dark:border-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reject
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $advancements->links() }}
    </div>
    @endif

    <!-- Approve Modal -->
    <div x-show="approveUrl" x-cloak @keydown.escape.window="closeApprove()" class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeApprove()"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-start justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Approve Advancement</h3>
                    <button type="button" @click="closeApprove()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Approving will advance this teacher to the next career stage and notify them of the result.
                    </p>
                    <form method="POST" :action="approveUrl || '#'" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Remarks <span class="text-gray-400">(optional)</span></label>
                            <textarea name="remarks" rows="3" placeholder="Add context for this approval..." class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                            Approve & Advance Teacher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectUrl" x-cloak @keydown.escape.window="closeReject()" class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeReject()"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-start justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Reject Advancement</h3>
                    <button type="button" @click="closeReject()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Rejecting will keep the teacher at their current career stage.
                    </p>
                    <form method="POST" :action="rejectUrl || '#'" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Remarks <span class="text-gray-400">(optional)</span></label>
                            <textarea name="remarks" rows="3" placeholder="Explain why this was not approved..." class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                            Reject Advancement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('careerApprovals', () => ({
            approveUrl: null,
            rejectUrl: null,
            openApprove(id, url) {
                this.approveUrl = url;
            },
            closeApprove() {
                this.approveUrl = null;
            },
            openReject(id, url) {
                this.rejectUrl = url;
            },
            closeReject() {
                this.rejectUrl = null;
            },
        }));
    });
</script>
@endpush
