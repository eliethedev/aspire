<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with('user.school')
            ->when($request->school_id, function ($query, $schoolId) {
                return $query->where('school_id', $schoolId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        $schools = School::all();

        return view('teachers.index', compact('teachers', 'schools'));
    }

    public function create()
    {
        $schools = School::all();
        return view('teachers.create', compact('schools'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'school_id' => $validated['school_id'],
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'school_id' => $validated['school_id'],
            'department' => $validated['department'],
            'years_of_service' => $validated['years_of_service'],
            'mobile_number' => $validated['mobile_number'] ?? null,
            'prc_license_number' => $validated['prc_license_number'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('user.school');
        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        $schools = School::all();
        return view('teachers.edit', compact('teacher', 'schools'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $validated = $request->validated();

        $teacher->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'school_id' => $validated['school_id'],
        ]);

        if (!empty($validated['password'])) {
            $teacher->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        $teacher->update([
            'school_id' => $validated['school_id'],
            'department' => $validated['department'],
            'years_of_service' => $validated['years_of_service'],
            'mobile_number' => $validated['mobile_number'] ?? null,
            'prc_license_number' => $validated['prc_license_number'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        $user = $teacher->user;
        $teacher->delete();
        $user->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }
}
