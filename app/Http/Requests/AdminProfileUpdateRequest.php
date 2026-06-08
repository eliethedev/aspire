<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminProfileUpdateRequest extends FormRequest
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
        ];
    }
}
