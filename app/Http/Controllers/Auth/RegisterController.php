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
            'role' => ['required', 'in:teacher,supervisor,school_head'],
            'department' => ['nullable', 'string', 'max:255'],
            'years_of_service' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // If role is teacher, create teacher profile
        if ($request->role === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
                'department' => $request->department,
                'years_of_service' => $request->years_of_service,
            ]);
        }

        // Auto-login the user
        Auth::login($user);

        // Redirect based on role
        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole(User $user)
    {
        switch ($user->role) {
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'supervisor':
                return redirect()->route('supervisor.dashboard');
            case 'school_head':
                return redirect()->route('school_head.dashboard');
            default:
                return redirect()->route('home');
        }
    }
}
