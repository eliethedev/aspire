<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the user from the route parameter
        $user = User::find($this->route('id'));
        
        if (!$user) {
            return false;
        }

        // Check if the hash matches the user's email
        if (!hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the user for the request.
     */
    public function user($guard = null)
    {
        return User::find($this->route('id'));
    }
}
