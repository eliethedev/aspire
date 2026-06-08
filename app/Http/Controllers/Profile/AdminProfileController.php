<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');

        return view('admin.profile', compact('user'));
    }

    public function update(AdminProfileUpdateRequest $request): RedirectResponse
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

        return redirect()->route('admin.profile.edit')->with('status', 'profile-updated');
    }
}
