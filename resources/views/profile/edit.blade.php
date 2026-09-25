@extends(match (auth()->user()->role) {
    'admin' => 'layouts.admin',
    'supervisor' => 'layouts.supervisor',
    default => 'layouts.teacher',
})

@section('title', 'Profile')
@include('partials.dashboard.mock-styles')

@section('content')
    <div class="py-12">
        <div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
<div class="mock-topbar"><div class="mock-crumbs">Profile <span>/</span> <b>Profile</b></div></div>
<div class="mock-title"><div><h1>Profile</h1><p>Manage your account, security and preferences.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

            <section class="mock-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="mock-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <section class="mock-panel">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        </div>
    </div>
@endsection
