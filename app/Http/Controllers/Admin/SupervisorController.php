<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class SupervisorController extends Controller
{
    /**
     * Display a listing of supervisors.
     */
    public function index(Request $request)
    {
        $supervisors = Supervisor::query()
            ->with(['user', 'school'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->school_id, function ($query, $schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->position, function ($query, $position) {
                $query->where('position', $position);
            })
            ->paginate($request->per_page ?? 15);

        $schools = School::where('is_active', true)->orderBy('name')->get();

        return view('admin.supervisors.index', compact('supervisors', 'schools'));
    }

    /**
     * Show the form for creating a new supervisor.
     */
    public function create()
    {
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('admin.supervisors.create', compact('schools'));
    }

    /**
     * Store a newly created supervisor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
            'schoolId' => ['required', 'exists:schools,id'],
            'employeeId' => ['nullable', 'string', 'max:50'],
            'position' => ['required', Rule::in([
                'Principal',
                'Assistant Principal',
                'Master Teacher',
                'Head Teacher',
                'Supervisor',
                'Other'
            ])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['fullName'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'role' => 'supervisor',
            'school_id' => $validated['schoolId'],
        ]);

        // Create supervisor profile
        $supervisor = $user->supervisor()->create([
            'school_id' => $validated['schoolId'],
            'phone_number' => $validated['phoneNumber'] ?? null,
            'employee_id' => $validated['employeeId'] ?? null,
            'position' => $validated['position'],
            'status' => $validated['status'],
        ]);

        event(new Registered($user));

        app(AuditLogService::class)->logCreate(
            'supervisors', $supervisor,
            "Created supervisor: {$user->name}"
        );

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor has been created successfully.');
    }

    /**
     * Display the specified supervisor.
     */
    public function show(Supervisor $supervisor)
    {
        $supervisor->load(['user', 'school']);
        return view('admin.supervisors.show', compact('supervisor'));
    }

    /**
     * Show the form for editing the specified supervisor.
     */
    public function edit(Supervisor $supervisor)
    {
        $supervisor->load(['user', 'school']);
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('admin.supervisors.edit', compact('supervisor', 'schools'));
    }

    /**
     * Update the specified supervisor.
     */
    public function update(Request $request, Supervisor $supervisor)
    {
        $validated = $request->validate([
            'fullName' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($supervisor->user_id)],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
            'schoolId' => ['sometimes', 'required', 'exists:schools,id'],
            'employeeId' => ['nullable', 'string', 'max:50'],
            'position' => ['sometimes', 'required', Rule::in([
                'Principal',
                'Assistant Principal',
                'Master Teacher',
                'Head Teacher',
                'Supervisor',
                'Other'
            ])],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
        ]);

        // Update user data
        $userData = [];
        if (isset($validated['fullName'])) {
            $userData['name'] = $validated['fullName'];
        }
        if (isset($validated['email'])) {
            $userData['email'] = $validated['email'];
        }
        if (isset($validated['schoolId'])) {
            $userData['school_id'] = $validated['schoolId'];
        }

        if (!empty($userData)) {
            $supervisor->user->update($userData);
        }

        // Update supervisor data
        $supervisorData = [];
        if (isset($validated['phoneNumber'])) {
            $supervisorData['phone_number'] = $validated['phoneNumber'];
        }
        if (isset($validated['employeeId'])) {
            $supervisorData['employee_id'] = $validated['employeeId'];
        }
        if (isset($validated['position'])) {
            $supervisorData['position'] = $validated['position'];
        }
        if (isset($validated['status'])) {
            $supervisorData['status'] = $validated['status'];
        }
        if (isset($validated['schoolId'])) {
            $supervisorData['school_id'] = $validated['schoolId'];
        }

        if (!empty($supervisorData)) {
            $supervisor->update($supervisorData);
        }

        app(AuditLogService::class)->logUpdate(
            'supervisors', $supervisor,
            array_merge($userData, $supervisorData),
            $validated,
            "Updated supervisor: {$supervisor->user->name}"
        );

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor has been updated successfully.');
    }

    /**
     * Remove the specified supervisor.
     */
    public function destroy(Supervisor $supervisor)
    {
        $user = $supervisor->user;
        $userName = $user->name;
        
        // Delete supervisor profile
        $supervisor->delete();
        
        // Delete user
        $user->delete();

        app(AuditLogService::class)->log(
            'deleted', 'supervisors', (string) $supervisor->id,
            "Deleted supervisor: {$userName}",
            'success',
            ['name' => $userName, 'email' => $user->email],
            [],
        );

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor has been deleted successfully.');
    }

    /**
     * Get list of schools for dropdown.
     */
    public function schools(): JsonResponse
    {
        $schools = School::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($schools);
    }

    /**
     * Get positions list.
     */
    public function positions(): JsonResponse
    {
        return response()->json([
            'Principal',
            'Assistant Principal',
            'Master Teacher',
            'Head Teacher',
            'Supervisor',
            'Other'
        ]);
    }
}