<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('school_head');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'department' => 'required|string|max:255',
            'years_of_service' => 'required|integer|min:0|max:50',
            'mobile_number' => 'nullable|string|max:20|regex:/^[+]?[0-9\s\-\(\)]+$/',
            'prc_license_number' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The teacher\'s full name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'A password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'school_id.required' => 'Please select a school.',
            'school_id.exists' => 'The selected school is invalid.',
            'department.required' => 'The department is required.',
            'years_of_service.required' => 'Years of service is required.',
            'years_of_service.min' => 'Years of service cannot be negative.',
            'years_of_service.max' => 'Years of service cannot exceed 50.',
            'mobile_number.regex' => 'Please provide a valid mobile number.',
        ];
    }
}
