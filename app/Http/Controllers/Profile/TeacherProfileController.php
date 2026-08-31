<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherProfileUpdateRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['teacher', 'teacherProfile', 'profile']);

        $subjects = Subject::query()->orderBy('name')->get();

        return view('teacher.profile', compact('user', 'subjects'));
    }

    public function update(TeacherProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Update user basic info
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update or create user profile (common fields)
        $profileData = [
            'mobile_number' => $validated['mobile_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address_barangay' => $validated['address_barangay'] ?? null,
            'address_municipality' => $validated['address_municipality'] ?? null,
            'address_province' => $validated['address_province'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
            'prc_license_number' => $validated['prc_license_number'] ?? null,
            'highest_educational_attainment' => $validated['highest_educational_attainment'] ?? null,
            'major_specialization' => $validated['major_specialization'] ?? null,
            'years_of_teaching_experience' => $validated['years_of_teaching_experience'] ?? null,
            'date_of_entry_to_deped' => $validated['date_of_entry_to_deped'] ?? null,
            'employment_status' => $validated['employment_status'] ?? null,
        ];

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        // Update teacher record
        if ($user->teacher) {
            $careerStage = $validated['career_stage']
                ?? \App\Enums\TeacherCareerStage::fromPosition($validated['position'] ?? null)?->value
                ?? $user->teacher->career_stage;

            $user->teacher->update([
                'department' => $validated['department'] ?? $user->teacher->department,
                'position' => $validated['position'] ?? $user->teacher->position,
                'career_stage' => $careerStage,
                'grade_level' => $validated['grade_level'] ?? $user->teacher->grade_level,
            ]);

            // Sync subjects handled by the teacher (many-to-many). Free-text
            // additions are created on the fly so new subjects keep working.
            $subjectIds = collect($validated['subjects'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach (explode(',', (string) ($validated['new_subjects'] ?? '')) as $rawName) {
                $name = trim($rawName);
                if ($name === '') {
                    continue;
                }

                $subject = Subject::firstOrCreate(['name' => $name]);
                $subjectIds[] = $subject->id;
            }

            if ($subjectIds !== []) {
                $user->teacher->subjects()->sync(array_values(array_unique($subjectIds)));
            }
        }

        // Update or create teacher profile (extended fields)
        $teacherProfileData = [
            'grade_level' => $validated['grade_level'] ?? null,
            'subject_area_taught' => $validated['subject_area_taught'] ?? null,
            'teaching_position' => $validated['teaching_position'] ?? null,
            'strand_specialization' => $validated['strand_specialization'] ?? null,
            'has_advisory_class' => $validated['has_advisory_class'] ?? false,
            'advisory_section' => $validated['advisory_section'] ?? null,
            'teacher_load' => $validated['teacher_load'] ?? null,
            'certification_training' => $validated['certification_training'] ?? null,
            'department' => $validated['department'] ?? null,
            'default_room' => $validated['default_room'] ?? null,
        ];

        $user->teacherProfile()->updateOrCreate(['user_id' => $user->id], $teacherProfileData);

        return redirect()->route('teacher.profile.edit')->with('status', 'profile-updated');
    }
}
