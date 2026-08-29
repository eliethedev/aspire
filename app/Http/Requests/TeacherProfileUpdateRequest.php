<?php

namespace App\Http\Requests;

use App\Enums\TeacherCareerStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'address_barangay' => ['nullable', 'string', 'max:255'],
            'address_municipality' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'prc_license_number' => ['nullable', 'string', 'max:255'],
            'highest_educational_attainment' => ['nullable', 'string', 'max:255'],
            'major_specialization' => ['nullable', 'string', 'max:255'],
            'years_of_teaching_experience' => ['nullable', 'integer', 'min:0'],
            'date_of_entry_to_deped' => ['nullable', 'date'],
            'employment_status' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'new_subjects' => ['nullable', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'career_stage' => ['nullable', Rule::enum(TeacherCareerStage::class)],
            'subject_area_taught' => ['nullable', 'string', 'max:255'],
            'teaching_position' => ['nullable', 'string', 'max:255'],
            'strand_specialization' => ['nullable', 'string', 'max:255'],
            'has_advisory_class' => ['nullable', 'boolean'],
            'advisory_section' => ['nullable', 'string', 'max:255'],
            'teacher_load' => ['nullable', 'integer', 'min:0'],
            'certification_training' => ['nullable', 'string'],
            'default_room' => ['nullable', 'string', 'max:255'],
        ];
    }
}
