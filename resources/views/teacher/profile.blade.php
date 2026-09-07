@extends('layouts.teacher')

@section('title', 'My Profile')

@section('content')
@php
    $user = Auth::user();
    $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->last_name ?? '', 0, 1));
@endphp

<div class="max-w-5xl mx-auto" x-data="{ activeTab: 'basic' }">

    @if (session('status') === 'profile-incomplete' || session('profile_incomplete'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200 rounded-xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 shrink-0 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold">Complete your profile to continue</h3>
                <p class="text-sm mt-1">A few essential details are required before you can use observations. Please fill in the highlighted fields below.</p>
                @if (session('profile_missing'))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (session('profile_missing') as $field)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                {{ str_replace('_', ' ', ucfirst($field)) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Profile updated successfully.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.profile.update') }}">
        @csrf
        @method('patch')

        <div class="flex flex-col md:flex-row gap-6">

            <!-- Left Sidebar -->
            <div class="w-full md:w-64 shrink-0 space-y-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 text-center">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mx-auto mb-3">
                        <span class="text-indigo-600 dark:text-indigo-400 text-2xl font-bold">{{ $initials }}</span>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $user->name }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 mt-3">
                        Teacher
                    </span>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-2">
                    <nav class="space-y-0.5">
                        <button type="button" @click="activeTab = 'basic'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'basic' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Basic Information
                        </button>
                        <button type="button" @click="activeTab = 'personal'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'personal' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                            Personal Profile
                        </button>
                        <button type="button" @click="activeTab = 'address'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'address' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Address
                        </button>
                        <button type="button" @click="activeTab = 'professional'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'professional' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Professional Details
                        </button>
                        <button type="button" @click="activeTab = 'teaching'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'teaching' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Teaching Information
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Right Content -->
            <div class="flex-1 min-w-0">

                <!-- Basic Information -->
                <div x-show="activeTab === 'basic'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Basic Information</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your name and email address.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Full Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="email" :value="__('Email Address')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                    </div>
                </div>

                <!-- Personal Profile -->
                <div x-show="activeTab === 'personal'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Personal Profile</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your personal details and employment status.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="mobile_number" :value="__('Mobile Number')" />
                            <x-text-input id="mobile_number" name="mobile_number" type="text" class="mt-1 block w-full" :value="old('mobile_number', $user->profile?->mobile_number)" />
                            <x-input-error class="mt-2" :messages="$errors->get('mobile_number')" />
                        </div>
                        <div>
                            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                        </div>
                        <div>
                            <x-input-label for="gender" :value="__('Gender')" />
                            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Gender</option>
                                <option value="male" @selected(old('gender', $user->profile?->gender) === 'male')>Male</option>
                                <option value="female" @selected(old('gender', $user->profile?->gender) === 'female')>Female</option>
                                <option value="prefer_not_to_say" @selected(old('gender', $user->profile?->gender) === 'prefer_not_to_say')>Prefer not to say</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                        </div>
                        <div>
                            <x-input-label for="employment_status" :value="__('Employment Status')" />
                            <select id="employment_status" name="employment_status" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Status</option>
                                <option value="permanent" @selected(old('employment_status', $user->profile?->employment_status) === 'permanent')>Permanent</option>
                                <option value="temporary" @selected(old('employment_status', $user->profile?->employment_status) === 'temporary')>Temporary</option>
                                <option value="contractual" @selected(old('employment_status', $user->profile?->employment_status) === 'contractual')>Contractual</option>
                                <option value="part_time" @selected(old('employment_status', $user->profile?->employment_status) === 'part_time')>Part-Time</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('employment_status')" />
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div x-show="activeTab === 'address'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Address</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your residential address information.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="address_barangay" :value="__('Barangay')" />
                            <x-text-input id="address_barangay" name="address_barangay" type="text" class="mt-1 block w-full" :value="old('address_barangay', $user->profile?->address_barangay)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address_barangay')" />
                        </div>
                        <div>
                            <x-input-label for="address_municipality" :value="__('Municipality')" />
                            <x-text-input id="address_municipality" name="address_municipality" type="text" class="mt-1 block w-full" :value="old('address_municipality', $user->profile?->address_municipality)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address_municipality')" />
                        </div>
                        <div>
                            <x-input-label for="address_province" :value="__('Province')" />
                            <x-text-input id="address_province" name="address_province" type="text" class="mt-1 block w-full" :value="old('address_province', $user->profile?->address_province)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address_province')" />
                        </div>
                    </div>
                </div>

                <!-- Professional Details -->
                <div x-show="activeTab === 'professional'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Professional Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your educational background and work experience.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="employee_id" :value="__('Employee ID')" />
                            <x-text-input id="employee_id" name="employee_id" type="text" class="mt-1 block w-full" :value="old('employee_id', $user->profile?->employee_id)" />
                            <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                        </div>
                        <div>
                            <x-input-label for="prc_license_number" :value="__('PRC License Number')" />
                            <x-text-input id="prc_license_number" name="prc_license_number" type="text" class="mt-1 block w-full" :value="old('prc_license_number', $user->profile?->prc_license_number)" />
                            <x-input-error class="mt-2" :messages="$errors->get('prc_license_number')" />
                        </div>
                        <div>
                            <x-input-label for="highest_educational_attainment" :value="__('Highest Educational Attainment')" />
                            <select id="highest_educational_attainment" name="highest_educational_attainment" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select</option>
                                <option value="bachelor" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'bachelor')>Bachelor's Degree</option>
                                <option value="master" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'master')>Master's Degree</option>
                                <option value="doctorate" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'doctorate')>Doctorate Degree</option>
                                <option value="post_doctoral" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'post_doctoral')>Post-Doctoral</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('highest_educational_attainment')" />
                        </div>
                        <div>
                            <x-input-label for="major_specialization" :value="__('Major / Specialization')" />
                            <x-text-input id="major_specialization" name="major_specialization" type="text" class="mt-1 block w-full" :value="old('major_specialization', $user->profile?->major_specialization)" />
                            <x-input-error class="mt-2" :messages="$errors->get('major_specialization')" />
                        </div>
                        <div>
                            <x-input-label for="years_of_teaching_experience" :value="__('Years of Teaching Experience')" />
                            <x-text-input id="years_of_teaching_experience" name="years_of_teaching_experience" type="number" min="0" class="mt-1 block w-full" :value="old('years_of_teaching_experience', $user->profile?->years_of_teaching_experience)" />
                            <x-input-error class="mt-2" :messages="$errors->get('years_of_teaching_experience')" />
                        </div>
                        <div>
                            <x-input-label for="date_of_entry_to_deped" :value="__('Date of Entry to DepEd')" />
                            <x-text-input id="date_of_entry_to_deped" name="date_of_entry_to_deped" type="date" class="mt-1 block w-full" :value="old('date_of_entry_to_deped', $user->profile?->date_of_entry_to_deped?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('date_of_entry_to_deped')" />
                        </div>
                    </div>
                </div>

                <!-- Teaching Information -->
                <div x-show="activeTab === 'teaching'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Teaching Information</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your teaching role, subject, and classroom details.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="department" :value="__('Department')" />
                            <x-text-input id="department" name="department" type="text" class="mt-1 block w-full" :value="old('department', $user->teacher?->department ?? $user->teacherProfile?->department)" />
                            <x-input-error class="mt-2" :messages="$errors->get('department')" />
                        </div>
                        <div>
                            <x-input-label for="position" :value="__('Position')" />
                            <input type="hidden" name="position" value="{{ old('position', $user->teacher?->position) }}">
                            <input id="position" type="text" disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed"
                                   value="{{ old('position', $user->teacher?->position_label ?? $user->teacher?->position) }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set by the administrator when your account was created.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('position')" />
                        </div>
                        <div>
                            <x-input-label for="career_stage" :value="__('Career Stage')" />
                            <input type="hidden" name="career_stage" value="{{ old('career_stage', $user->teacher?->career_stage) }}">
                            <input id="career_stage" type="text" disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed"
                                   value="{{ $user->teacher?->careerStage()?->label() ?? '—' }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Determines which COT instrument applies to your observations. Set by the administrator.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('career_stage')" />
                        </div>
                        <div>
                            <x-input-label for="grade_level" :value="__('Grade Level')" />
                            <select id="grade_level" name="grade_level" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Grade Level</option>
                                <option value="elementary" @selected(old('grade_level', $user->teacher?->grade_level ?? $user->teacherProfile?->grade_level) === 'elementary')>Elementary</option>
                                <option value="junior_high" @selected(old('grade_level', $user->teacher?->grade_level ?? $user->teacherProfile?->grade_level) === 'junior_high')>Junior High School</option>
                                <option value="senior_high" @selected(old('grade_level', $user->teacher?->grade_level ?? $user->teacherProfile?->grade_level) === 'senior_high')>Senior High School</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('grade_level')" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="subjects" :value="__('Subjects Handled')" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select all subjects you currently teach. Add any that are not listed below.</p>
                            @php $checkedSubjects = old('subjects', $user->teacher?->subjects?->pluck('id')->all() ?? []); @endphp
                            <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach ($subjects as $subjectItem)
                                    <label class="flex items-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="subjects[]" value="{{ $subjectItem->id }}"
                                               class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-indigo-600 focus:ring-indigo-500 dark:ring-offset-gray-800"
                                               @checked(in_array($subjectItem->id, $checkedSubjects))>
                                        <span class="text-gray-700 dark:text-gray-200">{{ $subjectItem->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <x-text-input id="new_subjects" name="new_subjects" type="text" class="mt-1 block w-full"
                                              :value="old('new_subjects')"
                                              placeholder="Additional subjects, e.g. Robotics, Practical Research 2" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('subjects')" />
                            <x-input-error class="mt-2" :messages="$errors->get('new_subjects')" />
                        </div>
                        <div>
                            <x-input-label for="subject_area_taught" :value="__('Subject Area Taught')" />
                            <x-text-input id="subject_area_taught" name="subject_area_taught" type="text" class="mt-1 block w-full" :value="old('subject_area_taught', $user->teacherProfile?->subject_area_taught)" />
                            <x-input-error class="mt-2" :messages="$errors->get('subject_area_taught')" />
                        </div>
                        <div>
                            <x-input-label for="teaching_position" :value="__('Teaching Position')" />
                            <select id="teaching_position" name="teaching_position" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Position</option>
                                <option value="teacher_i" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'teacher_i')>Teacher I</option>
                                <option value="teacher_ii" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'teacher_ii')>Teacher II</option>
                                <option value="teacher_iii" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'teacher_iii')>Teacher III</option>
                                <option value="master_teacher_i" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'master_teacher_i')>Master Teacher I</option>
                                <option value="master_teacher_ii" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'master_teacher_ii')>Master Teacher II</option>
                                <option value="master_teacher_iii" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'master_teacher_iii')>Master Teacher III</option>
                                <option value="master_teacher_iv" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'master_teacher_iv')>Master Teacher IV</option>
                                <option value="special_education" @selected(old('teaching_position', $user->teacherProfile?->teaching_position) === 'special_education')>Special Education Teacher</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('teaching_position')" />
                        </div>
                        <div>
                            <x-input-label for="strand_specialization" :value="__('Strand Specialization (SHS)')" />
                            <x-text-input id="strand_specialization" name="strand_specialization" type="text" class="mt-1 block w-full" :value="old('strand_specialization', $user->teacherProfile?->strand_specialization)" />
                            <x-input-error class="mt-2" :messages="$errors->get('strand_specialization')" />
                        </div>
                        <div>
                            <x-input-label for="teacher_load" :value="__('Teacher Load (hours/week)')" />
                            <x-text-input id="teacher_load" name="teacher_load" type="number" min="0" class="mt-1 block w-full" :value="old('teacher_load', $user->teacherProfile?->teacher_load)" />
                            <x-input-error class="mt-2" :messages="$errors->get('teacher_load')" />
                        </div>
                        <div>
                            <x-input-label for="has_advisory_class" :value="__('Has Advisory Class')" />
                            <select id="has_advisory_class" name="has_advisory_class" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="0" @selected(!old('has_advisory_class', $user->teacherProfile?->has_advisory_class))>No</option>
                                <option value="1" @selected(old('has_advisory_class', $user->teacherProfile?->has_advisory_class))>Yes</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('has_advisory_class')" />
                        </div>
                        <div>
                            <x-input-label for="advisory_section" :value="__('Advisory Section')" />
                            <x-text-input id="advisory_section" name="advisory_section" type="text" class="mt-1 block w-full" :value="old('advisory_section', $user->teacherProfile?->advisory_section)" />
                            <x-input-error class="mt-2" :messages="$errors->get('advisory_section')" />
                        </div>
                        <div>
                            <x-input-label for="default_room" :value="__('Default Room / Location')" />
                            <x-text-input id="default_room" name="default_room" type="text" class="mt-1 block w-full" :value="old('default_room', $user->teacherProfile?->default_room)" placeholder="e.g. Room 201, Building A" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used as the default location for your observations.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('default_room')" />
                        </div>
                    </div>
                    <div class="mt-6">
                        <x-input-label for="certification_training" :value="__('Certifications & Training')" />
                        <textarea id="certification_training" name="certification_training" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('certification_training', $user->teacherProfile?->certification_training) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('certification_training')" />
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>Save Profile</x-primary-button>
                    <a href="{{ route('teacher.dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 underline">Cancel</a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
