<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schools = School::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($status === 'trial') {
                    $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now());
                }
            })
            ->withCount('users')
            ->paginate($request->per_page ?? 15);

        return view('admin.schools.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schools.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:schools,slug',
            'domain' => 'nullable|string|max:255|unique:schools,domain',
            'subdomain' => 'nullable|string|max:255|unique:schools,subdomain',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'nullable|date',
        ]);

        $school = School::create($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', "School '{$school->name}' has been created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(School $school)
    {
        $school->loadCount('users');
        
        // Count users by role if possible
        $usersCount = $school->users_count ?? 0;
        $teachersCount = 0;
        $supervisorsCount = 0;
        
        if ($school->relationLoaded('users')) {
            $users = $school->users()->with('user')->get();
            $teachersCount = $users->where('user.role', 'teacher')->count();
            $supervisorsCount = $users->where('user.role', 'supervisor')->count();
        }
        
        $school->teachers_count = $teachersCount;
        $school->supervisors_count = $supervisorsCount;
        $school->users_count = $usersCount;

        return view('admin.schools.show', compact('school'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        $school->has_trial = !is_null($school->trial_ends_at);
        
        return view('admin.schools.edit', compact('school'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('schools', 'slug')->ignore($school->id)],
            'domain' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('schools', 'domain')->ignore($school->id)],
            'subdomain' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('schools', 'subdomain')->ignore($school->id)],
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'nullable|date',
        ]);

        $school->update($validated);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', "School '{$school->name}' has been updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $schoolName = $school->name;
        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('success', "School '{$schoolName}' has been deleted successfully.");
    }

    /**
     * Add user to school
     */
    public function addUser(Request $request, School $school)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:teacher,supervisor,school_head,admin',
            'is_active' => 'boolean',
        ]);

        $schoolUser = $school->schoolUsers()->create($validated);

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', 'User has been added to school successfully.');
    }

    /**
     * Remove user from school
     */
    public function removeUser(School $school, $userId)
    {
        $school->schoolUsers()->where('user_id', $userId)->delete();

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', 'User has been removed from school successfully.');
    }

    /**
     * Show users for a school
     */
    public function users(School $school)
    {
        $users = $school->schoolUsers()
            ->with('user')
            ->paginate();

        return view('admin.schools.users', compact('school', 'users'));
    }
}
