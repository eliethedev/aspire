<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'supervisor':
                return redirect()->route('supervisor.dashboard');
            case 'school_head':
                return redirect()->route('school-head.dashboard');
            default:
                return redirect()->route('home');
        }
    }
}
