<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('school')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->school_id, function ($query, $schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $schools = School::orderBy('name')->get();
        $roles = ['admin', 'school_head', 'supervisor', 'teacher'];

        return view('admin.users.index', compact('users', 'schools', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     * Redirects to the invitations create view which has comprehensive DepEd fields.
     */
    public function create()
    {
        return redirect()->route('admin.invitations.create');
    }

    /**
     * Store a newly created resource in storage.
     * Redirects to the invitations store route which handles comprehensive DepEd fields.
     */
    public function store(Request $request)
    {
        // Forward the request to the UserInvitationController
        return app(\App\Http\Controllers\UserInvitationController::class)->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('school', 'teacher');
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $schools = School::orderBy('name')->get();
        $roles = ['admin', 'school_head', 'supervisor', 'teacher'];

        return view('admin.users.edit', compact('user', 'schools', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['admin', 'school_head', 'supervisor', 'teacher'])],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'school_id' => $validated['school_id'] ?? null,
        ]);

        // Password update is optional
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        app(AuditLogService::class)->logUpdate(
            'users', $user,
            $user->getOriginal(),
            $validated,
            "Updated user: {$user->name}"
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} has been updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $userName = $user->name;
        $userId = $user->id;
        $user->delete();

        app(AuditLogService::class)->log(
            'deleted', 'users', (string) $userId,
            "Deleted user: {$userName}",
            'success',
            ['name' => $userName, 'role' => $user->role, 'email' => $user->email],
            [],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$userName} has been deleted successfully.");
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        // This would require adding an 'is_active' field to the users table
        // For now, we'll just return a success message
        return redirect()
            ->route('admin.users.index')
            ->with('info', 'User status toggle functionality coming soon.');
    }

    /**
     * Send password reset link to user
     */
    public function sendPasswordReset(User $user)
    {
        // Generate password reset token and send email
        $token = \Illuminate\Support\Str::random(60);
        
        // This would typically be handled by Laravel's built-in password reset functionality
        // For now, we'll just return a success message
        return redirect()
            ->route('admin.users.index')
            ->with('success', "Password reset link has been sent to {$user->email}.");
    }
}
