<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolHeadProfileUpdateRequest extends FormRequest
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
            'position_level' => ['nullable', 'string', 'max:255'],
            'current_designation' => ['nullable', 'string', 'max:255'],
            'administrative_experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'leadership_training' => ['nullable', 'string'],
            'number_of_teachers_supervised' => ['nullable', 'integer', 'min:0'],
            'school_type' => ['nullable', 'string', 'max:255'],
            'additional_roles' => ['nullable', 'string'],
            'position' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom, plain-language error messages that point the user at the exact
     * field that needs to be filled in.
     */
    public function messages(): array
    {
        return [
            'administrative_experience_years.integer' => 'Administrative experience must be a whole number.',
            'administrative_experience_years.min' => 'Administrative experience cannot be less than 0.',
            'administrative_experience_years.max' => 'Administrative experience cannot exceed 50 years.',
            'number_of_teachers_supervised.integer' => 'Number of teachers supervised must be a whole number.',
            'number_of_teachers_supervised.min' => 'Number of teachers supervised cannot be less than 0.',
        ];
    }
}
