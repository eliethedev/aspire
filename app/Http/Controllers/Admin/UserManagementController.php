<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class UserManagementController extends Controller
{
    /**
     * Constructor - apply middleware for authorization.
     */
    public function __construct()
    {
        // Apply user management permission middleware
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\CheckUserManagementPermission::class);
    }

    /**
     * Display a listing of users for admin management.
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with(['school', 'teacher'])
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
            ->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    /**
     * Store a newly created user with higher privileges.
     * Only authorized admins can create users with roles other than 'teacher'.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => [
                'required',
                Rule::in(['teacher', 'supervisor', 'school_head', 'division_admin', 'super_admin'])
            ],
            'school_id' => ['required', 'exists:schools,id'],
            'department' => ['required_if:role,teacher', 'nullable', 'string', 'max:255'],
            'years_of_service' => ['required_if:role,teacher', 'nullable', 'integer', 'min:0', 'max:50'],
        ]);

        // Additional authorization checks for higher roles
        $this->authorizeRoleCreation($request->role, $request->school_id);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'school_id' => $validated['school_id'],
        ]);

        // Create teacher profile if role is teacher
        if ($validated['role'] === 'teacher') {
            $user->teacher()->create([
                'department' => $validated['department'],
                'years_of_service' => $validated['years_of_service'],
                'school_id' => $validated['school_id'],
            ]);
        }

        // Assign role using Spatie Laravel Permission
        $user->assignRole($validated['role']);

        event(new Registered($user));

        return response()->json($user, 201);
    }

    /**
     * Update the specified user's role and information.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => [
                'sometimes',
                'required',
                Rule::in(['teacher', 'supervisor', 'school_head', 'division_admin', 'super_admin'])
            ],
            'school_id' => ['sometimes', 'required', 'exists:schools,id'],
            'department' => ['required_if:role,teacher', 'nullable', 'string', 'max:255'],
            'years_of_service' => ['required_if:role,teacher', 'nullable', 'integer', 'min:0', 'max:50'],
        ]);

        // Authorization check for role changes
        if (isset($validated['role']) && $validated['role'] !== $user->role) {
            $this->authorizeRoleCreation($validated['role'], $validated['school_id'] ?? $user->school_id);
        }

        $user->update($validated);

        // Update teacher profile if role is teacher
        if ($validated['role'] === 'teacher' || $user->role === 'teacher') {
            if ($user->teacher) {
                $user->teacher()->update([
                    'department' => $validated['department'] ?? $user->teacher->department,
                    'years_of_service' => $validated['years_of_service'] ?? $user->teacher->years_of_service,
                    'school_id' => $validated['school_id'] ?? $user->teacher->school_id,
                ]);
            } elseif (isset($validated['department']) && isset($validated['years_of_service'])) {
                $user->teacher()->create([
                    'department' => $validated['department'],
                    'years_of_service' => $validated['years_of_service'],
                    'school_id' => $validated['school_id'] ?? $user->school_id,
                ]);
            }
        }

        // Update Spatie role if changed
        if (isset($validated['role']) && $validated['role'] !== $user->role) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deletion of the last super admin
        if ($user->role === 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the last super admin user'
                ], 422);
            }
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Authorize role creation based on current user's permissions.
     */
    private function authorizeRoleCreation(string $role, int $schoolId): void
    {
        $currentUser = request()->user();

        switch ($role) {
            case 'teacher':
                // Anyone with 'manage users' permission can create teachers
                if (!$currentUser->can('manage users')) {
                    abort(403, 'You do not have permission to create teachers.');
                }
                break;

            case 'supervisor':
                // School heads and above can create supervisors
                if (!$currentUser->hasRole(['school_head', 'division_admin', 'super_admin'])) {
                    abort(403, 'Only school heads and above can create supervisors.');
                }
                
                // School heads can only create supervisors in their own school
                if ($currentUser->hasRole('school_head') && $currentUser->school_id !== $schoolId) {
                    abort(403, 'You can only create supervisors in your own school.');
                }
                break;

            case 'school_head':
                // Division admins and super admins can create school heads
                if (!$currentUser->hasRole(['division_admin', 'super_admin'])) {
                    abort(403, 'Only division admins and super admins can create school heads.');
                }
                break;

            case 'division_admin':
                // Only super admins can create division admins
                if (!$currentUser->hasRole('super_admin')) {
                    abort(403, 'Only super admins can create division admins.');
                }
                break;

            case 'super_admin':
                // Only super admins can create other super admins
                if (!$currentUser->hasRole('super_admin')) {
                    abort(403, 'Only super admins can create super admins.');
                }
                break;

            default:
                abort(422, 'Invalid role specified.');
        }
    }
}
