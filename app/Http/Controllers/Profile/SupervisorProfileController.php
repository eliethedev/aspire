<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupervisorProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisorProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['supervisor', 'supervisorProfile', 'profile']);

        return view('supervisor.profile', compact('user'));
    }

    public function update(SupervisorProfileUpdateRequest $request): RedirectResponse
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

        // Update supervisor record
        if ($user->supervisor) {
            $user->supervisor->update([
                'phone_number' => $validated['phone_number'] ?? $user->supervisor->phone_number,
                'employee_id' => $validated['employee_id'] ?? $user->supervisor->employee_id,
                'position' => $validated['position'] ?? $user->supervisor->position,
            ]);
        }

        // Update or create supervisor profile (extended fields)
        $supervisorProfileData = [
            'division_district_assigned' => $validated['division_district_assigned'] ?? null,
            'area_of_specialization' => $validated['area_of_specialization'] ?? null,
            'supervisory_level' => $validated['supervisory_level'] ?? null,
            'previous_teaching_experience_years' => $validated['previous_teaching_experience_years'] ?? null,
            'administrative_experience_years' => $validated['administrative_experience_years'] ?? null,
            'key_responsibilities' => $validated['key_responsibilities'] ?? null,
            'position' => $validated['position'] ?? null,
        ];

        $user->supervisorProfile()->updateOrCreate(['user_id' => $user->id], $supervisorProfileData);

        return redirect()->route('supervisor.profile.edit')->with('status', 'profile-updated');
    }
}
