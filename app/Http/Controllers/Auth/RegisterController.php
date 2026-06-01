<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'school_id' => ['required', 'exists:schools,id'],
            'department' => ['required', 'string', 'max:255'],
            'years_of_service' => ['required', 'integer', 'min:0', 'max:50'],
            'mobile_number' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'max:11'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_number'],
            'prc_license_number' => ['nullable', 'string', 'max:20', 'unique:teachers,prc_license_number'],
            'position' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'school_id' => $request->school_id,
        ]);

        // Create teacher profile (registration is only for teachers)
        Teacher::create([
            'user_id' => $user->id,
            'department' => $request->department,
            'years_of_service' => $request->years_of_service,
            'school_id' => $request->school_id,
            'mobile_number' => $request->mobile_number,
            'employee_number' => $request->employee_number,
            'prc_license_number' => $request->prc_license_number,
            'position' => $request->position,
        ]);

        // Auto-login the user
        Auth::login($user);

        // Redirect to teacher dashboard
        return redirect()->route('teacher.dashboard');
    }
}
