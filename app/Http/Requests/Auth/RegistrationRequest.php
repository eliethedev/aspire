<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public registration is allowed for teachers only
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'school_id' => ['required', 'exists:schools,id'],
            'department' => ['required', 'string', 'max:255'],
            'years_of_service' => ['required', 'integer', 'min:0', 'max:50'],
            'employee_number' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_number'],
            'mobile_number' => ['required', 'string', 'regex:/^09[0-9]{9}$/', 'max:11'],
            'prc_license_number' => ['nullable', 'string', 'max:20', 'unique:teachers,prc_license_number'],
            'position' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'Please select your school.',
            'school_id.exists' => 'The selected school is invalid.',
            'department.required' => 'Please specify your department.',
            'years_of_service.required' => 'Please specify your years of service.',
            'years_of_service.min' => 'Years of service cannot be negative.',
            'years_of_service.max' => 'Years of service cannot exceed 50.',
            'mobile_number.required' => 'Mobile number is required for SMS notifications.',
            'mobile_number.regex' => 'Mobile number must start with 09 and be 11 digits (e.g., 09123456789).',
            'mobile_number.max' => 'Mobile number must be exactly 11 digits.',
            'employee_number.unique' => 'This employee number is already registered.',
            'prc_license_number.unique' => 'This PRC license number is already registered.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'school_id' => 'school',
            'years_of_service' => 'years of service',
            'employee_number' => 'employee number',
            'mobile_number' => 'mobile number',
            'prc_license_number' => 'PRC license number',
            'position' => 'position',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Ensure no role is being submitted (security measure)
            if ($this->has('role')) {
                $validator->errors()->add('role', 'Role assignment is not allowed during registration.');
            }

            // Additional security: Check for suspicious patterns
            $this->checkForSuspiciousPatterns($validator);
        });
    }

    /**
     * Check for suspicious patterns in registration data.
     */
    private function checkForSuspiciousPatterns($validator): void
    {
        $email = $this->input('email');
        $name = $this->input('name');

        // Check for admin-like patterns in email or name
        $suspiciousPatterns = [
            'admin',
            'administrator',
            'root',
            'super',
            'supervisor',
            'head',
            'manager',
            'moderator'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains(strtolower($email), $pattern) || 
                str_contains(strtolower($name), $pattern)) {
                $validator->errors()->add('security', 
                    'Registration with administrative identifiers is not allowed through public registration.');
                break;
            }
        }
    }
}
