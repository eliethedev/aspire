@extends('layouts.admin')

@section('title', 'Edit Supervisor')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <!-- Header -->
    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <a href="{{ route('admin.supervisors.index') }}" class="hover:underline">Supervisors</a> <span>/</span> <b>Edit Supervisor</b></div><div class="mock-actions"><a href="{{ route('admin.supervisors.index') }}" class="mock-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to List
            </a></div></div>
<div class="mock-title"><div><h1>Edit Supervisor</h1><p class="text-gray-600 dark:text-gray-400 mt-1">{{ $supervisor->user?->name ?? 'Deleted user' }}</p>
    </div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <!-- Supervisor Info Card -->
    <section class="mock-panel">
        <div class="flex items-center gap-4 p-6">
            <div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xl font-bold">
                {{ strtoupper(substr($supervisor->user?->name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 truncate">{{ $supervisor->user?->name ?? 'Deleted user' }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ $supervisor->user?->email ?? '—' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                        {{ $supervisor->position ?? 'No Position' }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $supervisor->school?->name ?? 'No School' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Form -->
    <section class="mock-panel p-6">
        <form method="POST" action="{{ route('admin.supervisors.update', $supervisor) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- User Information -->
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">User Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fullName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fullName" name="fullName"
                               value="{{ old('fullName', $supervisor->user?->name) }}" required
                               class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('fullName') ? 'border-red-500' : '' }}"
                               placeholder="Juan Dela Cruz">
                        @error('fullName')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $supervisor->user?->email) }}" required
                               class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('email') ? 'border-red-500' : '' }}"
                               placeholder="supervisor@school.edu.ph">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Supervisor Information -->
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Supervisor Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="schoolId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            School <span class="text-red-500">*</span>
                        </label>
                        <select id="schoolId" name="schoolId" required
                                class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('schoolId') ? 'border-red-500' : '' }}">
                            <option value="">Select School</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}"
                                    {{ old('schoolId', $supervisor->school_id) == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('schoolId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="employeeId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            DepEd / Employee ID
                        </label>
                        <input type="text" id="employeeId" name="employeeId"
                               value="{{ old('employeeId', $supervisor->employee_id) }}"
                               class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('employeeId') ? 'border-red-500' : '' }}"
                               placeholder="1234567">
                        @error('employeeId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Position / Designation <span class="text-red-500">*</span>
                        </label>
                        <select id="position" name="position" required
                                class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('position') ? 'border-red-500' : '' }}">
                            <option value="">Select Position</option>
                            <option value="Principal" {{ old('position', $supervisor->position) == 'Principal' ? 'selected' : '' }}>Principal</option>
                            <option value="Assistant Principal" {{ old('position', $supervisor->position) == 'Assistant Principal' ? 'selected' : '' }}>Assistant Principal</option>
                            <option value="Master Teacher" {{ old('position', $supervisor->position) == 'Master Teacher' ? 'selected' : '' }}>Master Teacher</option>
                            <option value="Head Teacher" {{ old('position', $supervisor->position) == 'Head Teacher' ? 'selected' : '' }}>Head Teacher</option>
                            <option value="Supervisor" {{ old('position', $supervisor->position) == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="Other" {{ old('position', $supervisor->position) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('position')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('status') ? 'border-red-500' : '' }}">
                            <option value="active" {{ old('status', $supervisor->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $supervisor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.supervisors.index') }}"
                       class="px-4 py-2 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Supervisor
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Delete Form Outside Main Form -->
    <section class="mock-panel">
        <div class="flex flex-wrap items-center justify-between gap-3 p-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Danger Zone</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Once you delete a supervisor, you can't restore it.</p>
            </div>
            <form method="POST"
                  action="{{ route('admin.supervisors.destroy', $supervisor) }}"
                  onsubmit="return confirm('Are you sure you want to delete this supervisor? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete Supervisor
                </button>
            </form>
        </div>
    </section>
</div>
@endsection
