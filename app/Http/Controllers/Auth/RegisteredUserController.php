<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Models\User;
use App\Models\School;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): \Illuminate\View\View
    {
        return view('register', [
            'schools' => School::select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegistrationRequest $request): RedirectResponse
    {
        // Force role to 'teacher' - prevents privilege escalation
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher', // Forced role - cannot be overridden
            'school_id' => $request->school_id,
        ]);

        // Create teacher profile
        $user->teacher()->create([
            'department' => $request->department,
            'years_of_service' => $request->years_of_service,
            'school_id' => $request->school_id,
            'employee_number' => $request->employee_number,
            'mobile_number' => $request->mobile_number,
            'prc_license_number' => $request->prc_license_number,
            'position' => $request->position,
        ]);

        // TODO: Assign role using Spatie Laravel Permission when fully configured
        // $user->assignRole('teacher');

        // Send email verification notification manually to avoid duplicates
        // (Laravel's Registered event would automatically send it for MustVerifyEmail users)
        $user->sendEmailVerificationNotification();

        // Don't login immediately - require email verification first
        // Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
