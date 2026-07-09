@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div class="space-y-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-indigo-50 flex items-center justify-center shrink-0">
                <span class="text-indigo-600 text-3xl font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $user->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Administrator
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Member since {{ $user->created_at->format('M Y') }}
                    </span>
                </div>
            </div>
            <div class="hidden sm:flex flex-col items-center gap-1">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="text-xs text-gray-400 font-medium">Secured</span>
            </div>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Profile updated successfully.
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 flex items-start gap-3">
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

    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Account Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Account Information
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $basicComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $basicComplete ? 'Complete' : 'Incomplete' }}
                </span>
            </div>
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

        <!-- Personal Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    Personal Information
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $personalComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $personalComplete ? 'Has Data' : 'Empty' }}
                </span>
            </div>
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
                    <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Gender</option>
                        <option value="male" @selected(old('gender', $user->profile?->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $user->profile?->gender) === 'female')>Female</option>
                        <option value="prefer_not_to_say" @selected(old('gender', $user->profile?->gender) === 'prefer_not_to_say')>Prefer not to say</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                </div>
                <div>
                    <x-input-label for="employment_status" :value="__('Employment Status')" />
                    <select id="employment_status" name="employment_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Status</option>
                        <option value="permanent" @selected(old('employment_status', $user->profile?->employment_status) === 'permanent')>Permanent</option>
                        <option value="temporary" @selected(old('employment_status', $user->profile?->employment_status) === 'temporary')>Temporary</option>
                        <option value="contractual" @selected(old('employment_status', $user->profile?->employment_status) === 'contractual')>Contractual</option>
                        <option value="part_time" @selected(old('employment_status', $user->profile?->employment_status) === 'part_time')>Part-Time</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('employment_status')" />
                </div>
            </div>

            <div class="flex items-center gap-2.5 mt-6 mb-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <h3 class="text-md font-medium text-gray-800">Address</h3>
                @if($addressComplete)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-emerald-50">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                @else
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-gray-100">
                        <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
                    </span>
                @endif
            </div>
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

        <!-- Administrative Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Administrative Information
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $adminInfoComplete ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $adminInfoComplete ? 'Has Data' : 'Empty' }}
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="employee_id" :value="__('Employee / ID Number')" />
                    <x-text-input id="employee_id" name="employee_id" type="text" class="mt-1 block w-full" :value="old('employee_id', $user->profile?->employee_id)" />
                    <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                </div>
                <div>
                    <x-input-label for="position_title" :value="__('Position / Designation')" />
                    <x-text-input id="position_title" name="position_title" type="text" class="mt-1 block w-full" :value="old('position_title', $user->profile?->position_title)" placeholder="e.g., Administrative Officer, Division Chief" />
                    <x-input-error class="mt-2" :messages="$errors->get('position_title')" />
                </div>
                <div>
                    <x-input-label for="office_department" :value="__('Office / Department')" />
                    <x-text-input id="office_department" name="office_department" type="text" class="mt-1 block w-full" :value="old('office_department', $user->profile?->office_department)" placeholder="e.g., Curriculum Development, HR" />
                    <x-input-error class="mt-2" :messages="$errors->get('office_department')" />
                </div>
                <div>
                    <x-input-label for="prc_license_number" :value="__('Professional / Civil Service Eligibility')" />
                    <x-text-input id="prc_license_number" name="prc_license_number" type="text" class="mt-1 block w-full" :value="old('prc_license_number', $user->profile?->prc_license_number)" />
                    <x-input-error class="mt-2" :messages="$errors->get('prc_license_number')" />
                </div>
                <div>
                    <x-input-label for="highest_educational_attainment" :value="__('Highest Educational Attainment')" />
                    <select id="highest_educational_attainment" name="highest_educational_attainment" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select</option>
                        <option value="bachelor" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'bachelor')>Bachelor's Degree</option>
                        <option value="master" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'master')>Master's Degree</option>
                        <option value="doctorate" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'doctorate')>Doctorate Degree</option>
                        <option value="post_doctoral" @selected(old('highest_educational_attainment', $user->profile?->highest_educational_attainment) === 'post_doctoral')>Post-Doctoral</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('highest_educational_attainment')" />
                </div>
                <div>
                    <x-input-label for="major_specialization" :value="__('Field of Specialization')" />
                    <x-text-input id="major_specialization" name="major_specialization" type="text" class="mt-1 block w-full" :value="old('major_specialization', $user->profile?->major_specialization)" />
                    <x-input-error class="mt-2" :messages="$errors->get('major_specialization')" />
                </div>
                <div>
                    <x-input-label for="years_of_teaching_experience" :value="__('Years of Government Service')" />
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

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <x-primary-button>
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Save Profile
            </x-primary-button>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
