<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolHeadProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SchoolHeadProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['schoolHeadProfile', 'profile']);

        return view('school-head.profile', compact('user'));
    }

    public function update(SchoolHeadProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user basic info
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create user profile (common fields)
        $profileData = [
            'mobile_number' => $validated['mobile_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address_barangay' => $validated['address_barangay'] ?? null,
            'address_municipality' => $validated['address_municipality'] ?? null,
            'address_province' => $validated['address_province'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
            'prc_license_number' => $validated['prc_license_number'] ?? null,
            'highest_educational_attainment' => $validated['highest_educational_attainment'] ?? null,
            'major_specialization' => $validated['major_specialization'] ?? null,
            'years_of_teaching_experience' => $validated['years_of_teaching_experience'] ?? null,
            'date_of_entry_to_deped' => $validated['date_of_entry_to_deped'] ?? null,
            'employment_status' => $validated['employment_status'] ?? null,
        ];

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        // Update or create school head profile
        $schoolHeadData = [
            'position_level' => $validated['position_level'] ?? null,
            'administrative_experience_years' => $validated['administrative_experience_years'] ?? null,
            'leadership_training' => $validated['leadership_training'] ?? null,
            'current_designation' => $validated['current_designation'] ?? null,
            'number_of_teachers_supervised' => $validated['number_of_teachers_supervised'] ?? null,
            'school_type' => $validated['school_type'] ?? null,
            'additional_roles' => $validated['additional_roles'] ?? null,
            'position' => $validated['position'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
        ];

        $user->schoolHeadProfile()->updateOrCreate(['user_id' => $user->id], $schoolHeadData);

        return redirect()->route('school-head.profile.edit')->with('status', 'profile-updated');
    }
}
