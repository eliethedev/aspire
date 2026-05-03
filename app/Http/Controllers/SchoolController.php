<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schools = School::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->paginate($request->per_page ?? 15);

        return response()->json($schools);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json($school, 201);
    }

    public function show(School $school): JsonResponse
    {
        $school->load(['users', 'schoolUsers']);

        return response()->json($school);
    }

    public function update(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('schools', 'slug')->ignore($school->id)],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('schools', 'domain')->ignore($school->id)],
            'subdomain' => ['nullable', 'string', 'max:255', Rule::unique('schools', 'subdomain')->ignore($school->id)],
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'nullable|date',
        ]);

        $school->update($validated);

        return response()->json($school);
    }

    public function destroy(School $school): JsonResponse
    {
        $school->delete();

        return response()->json(null, 204);
    }

    public function addUser(Request $request, School $school): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:teacher,supervisor,school_head,admin',
            'is_active' => 'boolean',
        ]);

        $schoolUser = $school->schoolUsers()->create($validated);

        return response()->json($schoolUser, 201);
    }

    public function removeUser(School $school, $userId): JsonResponse
    {
        $school->schoolUsers()->where('user_id', $userId)->delete();

        return response()->json(null, 204);
    }

    public function users(School $school): JsonResponse
    {
        $users = $school->schoolUsers()
            ->with('user')
            ->paginate();

        return response()->json($users);
    }
}
