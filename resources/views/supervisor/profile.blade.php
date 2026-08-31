@extends('layouts.supervisor')

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
                <p class="text-sm mt-1">A few essential details are required before you can use the platform. Please fill in the highlighted fields below.</p>
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

    <form method="POST" action="{{ route('supervisor.profile.update') }}">
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
                        Supervisor
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
                        <button type="button" @click="activeTab = 'supervisory'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'supervisory' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            Supervisory Details
                        </button>
                        <button type="button" @click="activeTab = 'display'"
                                class="profile-tab w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-left"
                                :class="activeTab === 'display' ? 'active' : 'text-gray-600 dark:text-gray-400'">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Display & Accessibility
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

                <!-- Supervisory Details -->
                <div x-show="activeTab === 'supervisory'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Supervisory Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your supervisory role and experience information.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="position" :value="__('Position')" />
                            <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $user->supervisor?->position ?? $user->supervisorProfile?->position)" />
                            <x-input-error class="mt-2" :messages="$errors->get('position')" />
                        </div>
                        <div>
                            <x-input-label for="division_district_assigned" :value="__('Division / District Assigned')" />
                            <x-text-input id="division_district_assigned" name="division_district_assigned" type="text" class="mt-1 block w-full" :value="old('division_district_assigned', $user->supervisorProfile?->division_district_assigned)" />
                            <x-input-error class="mt-2" :messages="$errors->get('division_district_assigned')" />
                        </div>
                        <div>
                            <x-input-label for="area_of_specialization" :value="__('Area of Specialization')" />
                            <x-text-input id="area_of_specialization" name="area_of_specialization" type="text" class="mt-1 block w-full" :value="old('area_of_specialization', $user->supervisorProfile?->area_of_specialization)" />
                            <x-input-error class="mt-2" :messages="$errors->get('area_of_specialization')" />
                        </div>
                        <div>
                            <x-input-label for="supervisory_level" :value="__('Supervisory Level')" />
                            <select id="supervisory_level" name="supervisory_level" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Level</option>
                                <option value="division" @selected(old('supervisory_level', $user->supervisorProfile?->supervisory_level) === 'division')>Division Level</option>
                                <option value="district" @selected(old('supervisory_level', $user->supervisorProfile?->supervisory_level) === 'district')>District Level</option>
                                <option value="regional" @selected(old('supervisory_level', $user->supervisorProfile?->supervisory_level) === 'regional')>Regional Level</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('supervisory_level')" />
                        </div>
                        <div>
                            <x-input-label for="previous_teaching_experience_years" :value="__('Previous Teaching Experience (years)')" />
                            <x-text-input id="previous_teaching_experience_years" name="previous_teaching_experience_years" type="number" min="0" class="mt-1 block w-full" :value="old('previous_teaching_experience_years', $user->supervisorProfile?->previous_teaching_experience_years)" />
                            <x-input-error class="mt-2" :messages="$errors->get('previous_teaching_experience_years')" />
                        </div>
                        <div>
                            <x-input-label for="administrative_experience_years" :value="__('Administrative Experience (years)')" />
                            <x-text-input id="administrative_experience_years" name="administrative_experience_years" type="number" min="0" class="mt-1 block w-full" :value="old('administrative_experience_years', $user->supervisorProfile?->administrative_experience_years)" />
                            <x-input-error class="mt-2" :messages="$errors->get('administrative_experience_years')" />
                        </div>
                    </div>
                    <div class="mt-6">
                        <x-input-label for="key_responsibilities" :value="__('Key Responsibilities')" />
                        <textarea id="key_responsibilities" name="key_responsibilities" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('key_responsibilities', $user->supervisorProfile?->key_responsibilities) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('key_responsibilities')" />
                    </div>
                </div>

                <!-- Display & Accessibility (system-wide setting) -->
                <div x-show="activeTab === 'display'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Display & Accessibility</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">System-wide settings that affect all pages. Your choice is saved on this device.</p>

                    <div class="rounded-xl border-2 border-gray-200 dark:border-gray-700 p-5 bg-gray-50/50 dark:bg-gray-800/30">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Text size</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Choose <span class="font-semibold text-gray-900 dark:text-gray-100">Large</span> for easier reading. This helps older users and anyone who prefers bigger text. Applies instantly everywhere.</p>
                            </div>
                            <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">System setting</span>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="button" @click="$store.accessibility.setLarge(false)"
                                    :class="!$store.accessibility.large ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 ring-2 ring-gray-900 dark:ring-white' : 'bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50'"
                                    class="px-6 py-3 rounded-xl text-sm font-bold transition-colors min-h-[44px]">A Standard</button>
                            <button type="button" @click="$store.accessibility.setLarge(true)"
                                    :class="$store.accessibility.large ? 'bg-indigo-600 text-white ring-2 ring-indigo-600' : 'bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50'"
                                    class="px-6 py-3 rounded-xl text-base font-extrabold transition-colors min-h-[44px]">A+ Large — Easier to read</button>
                            <span class="text-sm font-medium" :class="$store.accessibility.large ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400'" x-text="$store.accessibility.large ? 'Large is active' : 'Standard is active'"></span>
                        </div>
                        <div class="mt-4 p-3 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Preview</p>
                            <p class="text-sm text-gray-900 dark:text-gray-100" :class="$store.accessibility.large ? 'text-base leading-relaxed' : ''">The quick brown fox jumps over the lazy dog — sample text at <span x-text="$store.accessibility.large ? 'large' : 'standard'"></span> size.</p>
                            <p class="mt-1 text-xs" :class="$store.accessibility.large ? 'text-sm text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400'">Tip: You can also toggle this from the top header (Aa button) on any page.</p>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>Save Profile</x-primary-button>
                    <a href="{{ route('supervisor.dashboard') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 underline">Cancel</a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
