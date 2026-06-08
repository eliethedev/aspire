@extends('layouts.teacher')

@section('title', 'My Profile')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-700 rounded-2xl shadow-lg p-8">
        <div class="relative z-10">
            <h1 class="text-2xl font-bold text-white">My Profile</h1>
            <p class="text-gray-300 mt-1">Manage your personal and professional information.</p>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 flex items-center gap-3">
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

    <form method="POST" action="{{ route('school-head.profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- Basic Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
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
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Profile</h2>
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

            <h3 class="text-md font-medium text-gray-800 mt-6 mb-3">Address</h3>
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

            <h3 class="text-md font-medium text-gray-800 mt-6 mb-3">Professional Details</h3>
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

        <!-- School Head Information -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">School Head Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="position" :value="__('Position')" />
                    <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $user->schoolHeadProfile?->position)" />
                    <x-input-error class="mt-2" :messages="$errors->get('position')" />
                </div>
                <div>
                    <x-input-label for="position_level" :value="__('Position Level')" />
                    <select id="position_level" name="position_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Level</option>
                        <option value="principal_i" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'principal_i')>Principal I</option>
                        <option value="principal_ii" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'principal_ii')>Principal II</option>
                        <option value="principal_iii" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'principal_iii')>Principal III</option>
                        <option value="principal_iv" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'principal_iv')>Principal IV</option>
                        <option value="head_teacher" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'head_teacher')>Head Teacher</option>
                        <option value="assistant_principal" @selected(old('position_level', $user->schoolHeadProfile?->position_level) === 'assistant_principal')>Assistant Principal</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('position_level')" />
                </div>
                <div>
                    <x-input-label for="current_designation" :value="__('Current Designation')" />
                    <select id="current_designation" name="current_designation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Designation</option>
                        <option value="principal" @selected(old('current_designation', $user->schoolHeadProfile?->current_designation) === 'principal')>Principal</option>
                        <option value="officer_in_charge" @selected(old('current_designation', $user->schoolHeadProfile?->current_designation) === 'officer_in_charge')>Officer-in-Charge</option>
                        <option value="head_teacher" @selected(old('current_designation', $user->schoolHeadProfile?->current_designation) === 'head_teacher')>Head Teacher</option>
                        <option value="assistant_principal" @selected(old('current_designation', $user->schoolHeadProfile?->current_designation) === 'assistant_principal')>Assistant Principal</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('current_designation')" />
                </div>
                <div>
                    <x-input-label for="administrative_experience_years" :value="__('Administrative Experience (years)')" />
                    <x-text-input id="administrative_experience_years" name="administrative_experience_years" type="number" min="0" class="mt-1 block w-full" :value="old('administrative_experience_years', $user->schoolHeadProfile?->administrative_experience_years)" />
                    <x-input-error class="mt-2" :messages="$errors->get('administrative_experience_years')" />
                </div>
                <div>
                    <x-input-label for="school_type" :value="__('School Type')" />
                    <select id="school_type" name="school_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Type</option>
                        <option value="public" @selected(old('school_type', $user->schoolHeadProfile?->school_type) === 'public')>Public</option>
                        <option value="private" @selected(old('school_type', $user->schoolHeadProfile?->school_type) === 'private')>Private</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('school_type')" />
                </div>
                <div>
                    <x-input-label for="number_of_teachers_supervised" :value="__('Number of Teachers Supervised')" />
                    <x-text-input id="number_of_teachers_supervised" name="number_of_teachers_supervised" type="number" min="0" class="mt-1 block w-full" :value="old('number_of_teachers_supervised', $user->schoolHeadProfile?->number_of_teachers_supervised)" />
                    <x-input-error class="mt-2" :messages="$errors->get('number_of_teachers_supervised')" />
                </div>
                <div>
                    <x-input-label for="grade_level" :value="__('Grade Level')" />
                    <select id="grade_level" name="grade_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select Grade Level</option>
                        <option value="elementary" @selected(old('grade_level', $user->schoolHeadProfile?->grade_level) === 'elementary')>Elementary</option>
                        <option value="junior_high" @selected(old('grade_level', $user->schoolHeadProfile?->grade_level) === 'junior_high')>Junior High School</option>
                        <option value="senior_high" @selected(old('grade_level', $user->schoolHeadProfile?->grade_level) === 'senior_high')>Senior High School</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('grade_level')" />
                </div>
                <div>
                    <x-input-label for="subject" :value="__('Subject')" />
                    <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject', $user->schoolHeadProfile?->subject)" />
                    <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                </div>
            </div>
            <div class="mt-6">
                <x-input-label for="leadership_training" :value="__('Leadership Training')" />
                <textarea id="leadership_training" name="leadership_training" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('leadership_training', $user->schoolHeadProfile?->leadership_training) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('leadership_training')" />
            </div>
            <div class="mt-6">
                <x-input-label for="additional_roles" :value="__('Additional Roles')" />
                <textarea id="additional_roles" name="additional_roles" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('additional_roles', $user->schoolHeadProfile?->additional_roles) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('additional_roles')" />
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <x-primary-button>Save Profile</x-primary-button>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
