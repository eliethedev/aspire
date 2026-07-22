@extends('layouts.admin')

@section('title', 'Invite New User')

@section('content')
<div class="max-w-5xl mx-auto px-6 space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('admin.invitations.index') }}" class="mr-4 text-dark hover:text-dark">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-dark">Invite New User</h1>
                    <p class="text-dark mt-1 text-sm">Create an invitation for a new user to join ASPIRE.</p>
                </div>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">
                <span class="inline-flex items-center px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Invitation-only flow
                </span>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-start">
        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="flex-1">
            <p class="text-green-800 dark:text-green-300 font-medium">{{ session('success') }}</p>
            <div class="mt-3 flex space-x-3">
                <a href="{{ route('admin.invitations.create') }}" class="text-sm text-green-700 dark:text-green-400 hover:text-green-800 dark:text-green-300 font-medium">
                    Send Another Invitation
                </a>
                <span class="text-green-300">|</span>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-green-700 dark:text-green-400 hover:text-green-800 dark:text-green-300 font-medium">
                    View Users
                </a>
            </div>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 dark:text-green-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card">
        <form method="POST" action="{{ route('admin.invitations.store') }}" id="invitationForm" class="space-y-0">
            @csrf

            <!-- Step Indicator -->
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div id="step-1-indicator" class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">1</div>
                            <span class="ml-2 text-sm font-medium text-blue-600 dark:text-blue-400">Basic Info</span>
                        </div>
                        <div class="w-16 h-0.5 bg-gray-200 mx-4"></div>
                        <div id="step-2-indicator" class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 dark:text-gray-400 dark:text-gray-500 flex items-center justify-center text-sm font-medium">2</div>
                            <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500">Role Details</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Basic Info Tab -->
                <div id="content-basic" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-dark mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="Juan Dela Cruz">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-dark mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="juan.delacruz@deped.gov.ph">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-dark mb-1">
                                Mobile Number
                            </label>
                            <input type="text" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}"
                                   class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="09123456789">
                            @error('mobile_number')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-dark mb-1">
                                Date of Birth
                            </label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-dark mb-1">
                                Gender
                            </label>
                            <select id="gender" name="gender"
                                    class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Select gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-dark mb-1">
                                Employee ID / DepEd ID
                            </label>
                            <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id') }}"
                                   class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   placeholder="DEPED-2024-001">
                            @error('employee_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- Role Details Tab -->
                <div id="content-role" class="tab-content hidden">
                    <div class="space-y-6">
                        <!-- Role Selection -->
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <label class="block text-sm font-medium text-dark mb-3">
                                User Role <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="teacher" required
                                           onchange="handleRoleChange(this.value)"
                                           class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:bg-blue-900/20 transition-all hover:border-gray-300">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500 peer-checked:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                            <span class="text-sm font-medium">Teacher</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="supervisor"
                                           onchange="handleRoleChange(this.value)"
                                           class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:bg-blue-900/20 transition-all hover:border-gray-300">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500 peer-checked:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            <span class="text-sm font-medium">Supervisor</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="school_head"
                                           onchange="handleRoleChange(this.value)"
                                           class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:bg-blue-900/20 transition-all hover:border-gray-300">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500 peer-checked:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span class="text-sm font-medium">School Head</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative cursor-pointer">
                                    <input type="radio" name="role" value="admin"
                                           onchange="handleRoleChange(this.value)"
                                           class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:bg-blue-900/20 transition-all hover:border-gray-300">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 dark:text-gray-500 peer-checked:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium">Admin</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('role')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- School Assignment -->
                        <div>
                            <label for="school_id" class="block text-sm font-medium text-dark mb-1">
                                School Assignment <span id="school-required" class="text-red-500 hidden">*</span>
                            </label>
                            <select id="school_id" name="school_id"
                                    class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">No School (System Admin)</option>
                                @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="school-helper" class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Required for Teacher, Supervisor, and School Head roles</p>
                            @error('school_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role-Specific Fields -->
                        <div id="role-specific-fields" class="hidden">
                            <!-- Teacher Fields -->
                            <div id="teacher-fields" class="hidden space-y-4">
                                <h4 class="text-sm font-medium text-dark border-b pb-2">Teacher-Specific Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="grade_level" class="block text-sm font-medium text-dark mb-1">Grade Level</label>
                                        <select id="grade_level" name="grade_level"
                                                class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Select grade level</option>
                                            <option value="elementary" {{ old('grade_level') == 'elementary' ? 'selected' : '' }}>Elementary</option>
                                            <option value="junior_high" {{ old('grade_level') == 'junior_high' ? 'selected' : '' }}>Junior High School</option>
                                            <option value="senior_high" {{ old('grade_level') == 'senior_high' ? 'selected' : '' }}>Senior High School</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="subject_area_taught" class="block text-sm font-medium text-dark mb-1">Subject Area Taught</label>
                                        <input type="text" id="subject_area_taught" name="subject_area_taught" value="{{ old('subject_area_taught') }}"
                                               class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="e.g., Mathematics, Science">
                                    </div>
                                    <div>
                                        <label for="teaching_position" class="block text-sm font-medium text-dark mb-1">Teaching Position</label>
                                        <select id="teaching_position" name="teaching_position"
                                                class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Select position</option>
                                            <option value="teacher_i" {{ old('teaching_position') == 'teacher_i' ? 'selected' : '' }}>Teacher I</option>
                                            <option value="teacher_ii" {{ old('teaching_position') == 'teacher_ii' ? 'selected' : '' }}>Teacher II</option>
                                            <option value="teacher_iii" {{ old('teaching_position') == 'teacher_iii' ? 'selected' : '' }}>Teacher III</option>
                                            <option value="master_teacher_i" {{ old('teaching_position') == 'master_teacher_i' ? 'selected' : '' }}>Master Teacher I</option>
                                            <option value="master_teacher_ii" {{ old('teaching_position') == 'master_teacher_ii' ? 'selected' : '' }}>Master Teacher II</option>
                                            <option value="master_teacher_iii" {{ old('teaching_position') == 'master_teacher_iii' ? 'selected' : '' }}>Master Teacher III</option>
                                            <option value="master_teacher_iv" {{ old('teaching_position') == 'master_teacher_iv' ? 'selected' : '' }}>Master Teacher IV</option>
                                            <option value="master_teacher_v" {{ old('teaching_position') == 'master_teacher_v' ? 'selected' : '' }}>Master Teacher V</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="teacher_load" class="block text-sm font-medium text-dark mb-1">Teacher's Load</label>
                                        <input type="number" id="teacher_load" name="teacher_load" value="{{ old('teacher_load') }}" min="0" max="50"
                                               class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="Number of classes">
                                    </div>
                                </div>
                            </div>

                            <!-- Supervisor Fields -->
                            <div id="supervisor-fields" class="hidden space-y-4">
                                <h4 class="text-sm font-medium text-dark border-b pb-2">Supervisor-Specific Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="division_district_assigned" class="block text-sm font-medium text-dark mb-1">Division / District Assigned</label>
                                        <input type="text" id="division_district_assigned" name="division_district_assigned" value="{{ old('division_district_assigned') }}"
                                               class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="e.g., Division of City Schools">
                                    </div>
                                    <div>
                                        <label for="area_of_specialization" class="block text-sm font-medium text-dark mb-1">Area of Specialization</label>
                                        <input type="text" id="area_of_specialization" name="area_of_specialization" value="{{ old('area_of_specialization') }}"
                                               class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="e.g., Mathematics, Curriculum">
                                    </div>
                                    <div>
                                        <label for="supervisory_level" class="block text-sm font-medium text-dark mb-1">Supervisory Level</label>
                                        <select id="supervisory_level" name="supervisory_level"
                                                class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Select level</option>
                                            <option value="division" {{ old('supervisory_level') == 'division' ? 'selected' : '' }}>Division Level</option>
                                            <option value="district" {{ old('supervisory_level') == 'district' ? 'selected' : '' }}>District Level</option>
                                            <option value="regional" {{ old('supervisory_level') == 'regional' ? 'selected' : '' }}>Regional Level</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="supervisor_position" class="block text-sm font-medium text-dark mb-1">Position</label>
                                        <input type="text" id="supervisor_position" name="supervisor_position" value="{{ old('supervisor_position') }}"
                                               class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                               placeholder="e.g., Education Program Supervisor">
                                    </div>
                                </div>
                            </div>

                            <!-- School Head Fields -->
                            <div id="school-head-fields" class="hidden space-y-4">
                                <h4 class="text-sm font-medium text-dark border-b pb-2">School Head-Specific Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="position_level" class="block text-sm font-medium text-dark mb-1">Position Level</label>
                                        <select id="position_level" name="position_level"
                                                class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Select position level</option>
                                            <option value="principal_i" {{ old('position_level') == 'principal_i' ? 'selected' : '' }}>Principal I</option>
                                            <option value="principal_ii" {{ old('position_level') == 'principal_ii' ? 'selected' : '' }}>Principal II</option>
                                            <option value="principal_iii" {{ old('position_level') == 'principal_iii' ? 'selected' : '' }}>Principal III</option>
                                            <option value="principal_iv" {{ old('position_level') == 'principal_iv' ? 'selected' : '' }}>Principal IV</option>
                                            <option value="head_teacher" {{ old('position_level') == 'head_teacher' ? 'selected' : '' }}>Head Teacher</option>
                                            <option value="assistant_principal" {{ old('position_level') == 'assistant_principal' ? 'selected' : '' }}>Assistant Principal</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="current_designation" class="block text-sm font-medium text-dark mb-1">Current Designation</label>
                                        <select id="current_designation" name="current_designation"
                                                class="w-full px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="">Select designation</option>
                                            <option value="principal" {{ old('current_designation') == 'principal' ? 'selected' : '' }}>Principal</option>
                                            <option value="officer_in_charge" {{ old('current_designation') == 'officer_in_charge' ? 'selected' : '' }}>Officer-in-Charge</option>
                                            <option value="head_teacher" {{ old('current_designation') == 'head_teacher' ? 'selected' : '' }}>Head Teacher</option>
                                            <option value="assistant_principal" {{ old('current_designation') == 'assistant_principal' ? 'selected' : '' }}>Assistant Principal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Message -->
                        <div>
                            <label for="custom_message" class="block text-sm font-medium text-dark mb-1">
                                Custom Message (Optional)
                            </label>
                            <textarea id="custom_message" name="custom_message" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                      placeholder="Add a personal message to include in the invitation email...">{{ old('custom_message') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">This message will be included in the invitation email sent to the user.</p>
                            @error('custom_message')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-xl flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.invitations.index') }}"
                       class="px-4 py-2 text-dark bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                        Cancel
                    </a>
                    <button type="button" id="backBtn" onclick="goToStep(1)" class="hidden px-4 py-2 text-dark bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </button>
                </div>
                <button type="button" id="nextBtn" onclick="goToStep(2)" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <span>Next</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button type="submit" id="submitBtn" class="hidden px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <svg id="submitIcon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span id="submitText">Send Invitation</span>
                    <svg id="loadingIcon" class="hidden w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>  
</div>

<script>
let currentStep = 1;

function goToStep(step) {
    // Validate step 1 before proceeding
    if (currentStep === 1 && step === 2) {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        
        if (!name || !email) {
            alert('Please fill in the required fields (Full Name and Email Address)');
            return;
        }
    }

    currentStep = step;

    // Hide all step contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Show current step content
    if (step === 1) {
        document.getElementById('content-basic').classList.remove('hidden');
    } else if (step === 2) {
        document.getElementById('content-role').classList.remove('hidden');
    }

    // Update step indicators
    const step1Indicator = document.getElementById('step-1-indicator');
    const step2Indicator = document.getElementById('step-2-indicator');

    if (step === 1) {
        step1Indicator.querySelector('div').classList.remove('bg-gray-200', 'text-gray-500 dark:text-gray-400 dark:text-gray-500');
        step1Indicator.querySelector('div').classList.add('bg-blue-600', 'text-white');
        step1Indicator.querySelector('span').classList.remove('text-gray-500 dark:text-gray-400 dark:text-gray-500');
        step1Indicator.querySelector('span').classList.add('text-blue-600 dark:text-blue-400');

        step2Indicator.querySelector('div').classList.remove('bg-blue-600', 'text-white');
        step2Indicator.querySelector('div').classList.add('bg-gray-200', 'text-gray-500 dark:text-gray-400 dark:text-gray-500');
        step2Indicator.querySelector('span').classList.remove('text-blue-600 dark:text-blue-400');
        step2Indicator.querySelector('span').classList.add('text-gray-500 dark:text-gray-400 dark:text-gray-500');
    } else {
        step1Indicator.querySelector('div').classList.remove('bg-blue-600', 'text-white');
        step1Indicator.querySelector('div').classList.add('bg-green-50 dark:bg-green-900/200', 'text-white');
        step1Indicator.querySelector('span').classList.remove('text-blue-600 dark:text-blue-400');
        step1Indicator.querySelector('span').classList.add('text-green-600 dark:text-green-400');

        step2Indicator.querySelector('div').classList.remove('bg-gray-200', 'text-gray-500 dark:text-gray-400 dark:text-gray-500');
        step2Indicator.querySelector('div').classList.add('bg-blue-600', 'text-white');
        step2Indicator.querySelector('span').classList.remove('text-gray-500 dark:text-gray-400 dark:text-gray-500');
        step2Indicator.querySelector('span').classList.add('text-blue-600 dark:text-blue-400');
    }

    // Update navigation buttons
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    if (step === 1) {
        backBtn.classList.add('hidden');
        nextBtn.classList.remove('hidden');
        submitBtn.classList.add('hidden');
    } else {
        backBtn.classList.remove('hidden');
        nextBtn.classList.add('hidden');
        submitBtn.classList.remove('hidden');
    }
}

function handleRoleChange(role) {
    const roleSpecificFields = document.getElementById('role-specific-fields');
    const schoolRequired = document.getElementById('school-required');
    const schoolHelper = document.getElementById('school-helper');
    const schoolId = document.getElementById('school_id');

    // Hide all role-specific field groups
    document.getElementById('teacher-fields').classList.add('hidden');
    document.getElementById('supervisor-fields').classList.add('hidden');
    document.getElementById('school-head-fields').classList.add('hidden');

    // Remove irrelevant fields from form submission
    const teacherFields = document.querySelectorAll('#teacher-fields input, #teacher-fields select');
    const supervisorFields = document.querySelectorAll('#supervisor-fields input, #supervisor-fields select');
    const schoolHeadFields = document.querySelectorAll('#school-head-fields input, #school-head-fields select');

    teacherFields.forEach(field => field.disabled = true);
    supervisorFields.forEach(field => field.disabled = true);
    schoolHeadFields.forEach(field => field.disabled = true);

    if (role === 'teacher' || role === 'supervisor' || role === 'school_head') {
        // Show role-specific fields
        roleSpecificFields.classList.remove('hidden');

        // Make school required
        schoolRequired.classList.remove('hidden');
        schoolHelper.textContent = 'Required for this role';
        schoolId.required = true;

        // Enable relevant fields
        if (role === 'teacher') {
            document.getElementById('teacher-fields').classList.remove('hidden');
            teacherFields.forEach(field => field.disabled = false);
        } else if (role === 'supervisor') {
            document.getElementById('supervisor-fields').classList.remove('hidden');
            supervisorFields.forEach(field => field.disabled = false);
        } else if (role === 'school_head') {
            document.getElementById('school-head-fields').classList.remove('hidden');
            schoolHeadFields.forEach(field => field.disabled = false);
        }
    } else {
        // Hide role-specific fields for admin
        roleSpecificFields.classList.add('hidden');

        // Make school optional
        schoolRequired.classList.add('hidden');
        schoolHelper.textContent = 'Optional for system admins';
        schoolId.required = false;
    }
}

// Form submission handling
document.getElementById('invitationForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const loadingIcon = document.getElementById('loadingIcon');

    // Show loading state
    submitBtn.disabled = true;
    submitIcon.classList.add('hidden');
    submitText.textContent = 'Sending...';
    loadingIcon.classList.remove('hidden');
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('input[name="role"]:checked');
    if (roleSelect) {
        handleRoleChange(roleSelect.value);
    }
});
</script>
@endsection