@extends('layouts.admin')

@section('title', 'Invitation Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.invitations.index') }}" class="mr-4 text-dark hover:text-dark">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-dark">Invitation Details</h1>
                <p class="text-dark mt-1">View invitation information and status.</p>
            </div>
        </div>
    </div>

    <!-- Invitation Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
                <h3 class="text-lg font-medium text-dark mb-4 pb-2 border-b">User Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Name</label>
                        <p class="text-dark font-medium">{{ $invitation->user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Email</label>
                        <p class="text-dark">{{ $invitation->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Role</label>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800">
                            {{ ucfirst($invitation->role) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">School</label>
                        <p class="text-dark">{{ $invitation->school?->name ?? 'Not assigned' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Invited By</label>
                        <p class="text-dark">{{ $invitation->invitedBy->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Invitation Date</label>
                        <p class="text-dark">{{ $invitation->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
                <h3 class="text-lg font-medium text-dark mb-4 pb-2 border-b">Status Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Current Status</label>
                        @if($invitation->is_used)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                Accepted
                            </span>
                        @elseif($invitation->isExpired())
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800">
                                Expired
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Expires At</label>
                        <p class="text-dark">{{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    @if($invitation->accepted_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Accepted At</label>
                            <p class="text-dark">{{ $invitation->accepted_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Resend Count</label>
                        <p class="text-dark">{{ $invitation->resend_count }}</p>
                    </div>
                    @if($invitation->last_sent_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Last Sent At</label>
                            <p class="text-dark">{{ $invitation->last_sent_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User Profile Information (if available) -->
            @if($invitation->user->profile)
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
                    <h3 class="text-lg font-medium text-dark mb-4 pb-2 border-b">Profile Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($invitation->user->profile->mobile_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Mobile Number</label>
                                <p class="text-dark">{{ $invitation->user->profile->mobile_number }}</p>
                            </div>
                        @endif
                        @if($invitation->user->profile->employee_id)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Employee ID</label>
                                <p class="text-dark">{{ $invitation->user->profile->employee_id }}</p>
                            </div>
                        @endif
                        @if($invitation->user->profile->prc_license_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">PRC License Number</label>
                                <p class="text-dark">{{ $invitation->user->profile->prc_license_number }}</p>
                            </div>
                        @endif
                        @if($invitation->user->profile->highest_educational_attainment)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Educational Attainment</label>
                                <p class="text-dark">{{ $invitation->user->profile->highest_educational_attainment }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
                <h3 class="text-lg font-medium text-dark mb-4 pb-2 border-b">Actions</h3>
                <div class="space-y-3">
                    @if(!$invitation->is_used && !$invitation->isExpired())
                        <form method="POST" action="{{ route('admin.invitations.resend', $invitation) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-redo mr-2"></i>Resend Invitation
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.invitations.cancel', $invitation) }}" onsubmit="return confirm('Are you sure you want to cancel this invitation?');">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <i class="fas fa-times mr-2"></i>Cancel Invitation
                            </button>
                        </form>
                    @endif
                    
                    @if($invitation->is_used)
                        <a href="{{ route('admin.users.show', $invitation->user) }}" 
                           class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-center">
                            <i class="fas fa-user mr-2"></i>View User Profile
                        </a>
                    @endif

                    @if(!$invitation->is_used)
                        <form method="POST" action="{{ route('admin.invitations.destroy', $invitation) }}" onsubmit="return confirm('Are you sure you want to delete this invitation?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Invitation
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Technical Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
                <h3 class="text-lg font-medium text-dark mb-4 pb-2 border-b">Technical Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">Token</label>
                        <p class="text-dark font-mono text-xs break-all">{{ $invitation->token }}</p>
                    </div>
                    @if($invitation->ip_address)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">IP Address</label>
                            <p class="text-dark">{{ $invitation->ip_address }}</p>
                        </div>
                    @endif
                    @if($invitation->user_agent)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-1">User Agent</label>
                            <p class="text-dark text-xs break-all">{{ $invitation->user_agent }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
