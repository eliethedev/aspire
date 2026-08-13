<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TeacherCareerStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\CareerProgressionAssessment;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CareerProgressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

        return view('admin.teachers.index', compact('teachers', 'schools'));
    }

    public function create()
    {
        $schools = School::all();

        return view('admin.teachers.create', compact('schools'));
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
            'career_stage' => $this->resolveCareerStage($validated),
        ]);

        app(AuditLogService::class)->logCreate(
            'teachers', $teacher,
            "Created teacher: {$user->name}"
        );

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('user.school');

        $service = app(CareerProgressionService::class);
        $careerContext = $service->contextFor($teacher);
        $careerEvidence = $service->evidenceFor($teacher);
        $careerReadiness = $service->readinessFor($teacher);

        return view('admin.teachers.show', compact(
            'teacher', 'careerContext', 'careerEvidence', 'careerReadiness'
        ))->with('careerRoute', route('admin.teachers.career-assessment', $teacher))
            ->with('canAssess', true);
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        $schools = School::all();

        return view('admin.teachers.edit', compact('teacher', 'schools'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $validated = $request->validated();

        $teacher->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'school_id' => $validated['school_id'],
        ]);

        if (! empty($validated['password'])) {
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
            'career_stage' => $this->resolveCareerStage($validated),
        ]);

        app(AuditLogService::class)->logUpdate(
            'teachers', $teacher,
            $teacher->getOriginal(),
            $validated,
            "Updated teacher: {$teacher->user->name}"
        );

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Record a career progression readiness assessment for a teacher.
     *
     * Support-only action: never changes the teacher's position or career
     * stage. Each save is appended to the assessment history.
     */
    public function storeCareerAssessment(Teacher $teacher, Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(CareerProgressionAssessment::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'assessed_at' => ['nullable', 'date'],
        ]);

        app(CareerProgressionService::class)->recordAssessment($teacher, $request->user(), $validated);

        return back()->with('success', 'Career progression readiness assessment saved.');
    }

    public function destroy(Teacher $teacher)
    {
        $user = $teacher->user;
        $userName = $user->name;
        $teacher->delete();
        $user->delete();

        app(AuditLogService::class)->log(
            'deleted', 'teachers', (string) $teacher->id,
            "Deleted teacher: {$userName}",
            'success',
            ['name' => $userName, 'email' => $user->email],
            [],
        );

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }

    /**
     * Prefer the explicitly selected career stage; fall back to inferring
     * it from the free-text position when the stage is left blank.
     */
    private function resolveCareerStage(array $validated): ?string
    {
        if (! empty($validated['career_stage'])) {
            return $validated['career_stage'];
        }

        return TeacherCareerStage::fromPosition($validated['position'] ?? null)?->value;
    }
}
