<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    /**
     * Create a new user invitation.
     */
    public function createInvitation(array $data, User $invitedBy): Invitation
    {
        return DB::transaction(function () use ($data, $invitedBy) {
            // Check if email already exists
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                throw ValidationException::withMessages([
                    'email' => 'A user with this email already exists.',
                ]);
            }

            // Check if there's a pending invitation for this email
            $pendingInvitation = Invitation::where('email', $data['email'])
                ->valid()
                ->first();
            
            if ($pendingInvitation) {
                throw ValidationException::withMessages([
                    'email' => 'A pending invitation already exists for this email.',
                ]);
            }

            // Create the user with invited status
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'school_id' => $data['school_id'] ?? null,
                'status' => 'invited',
                'password' => Hash::make(Str::random(32)), // Temporary password
            ]);

            // Create the invitation
            $invitation = Invitation::create([
                'user_id' => $user->id,
                'invited_by' => $invitedBy->id,
                'school_id' => $data['school_id'] ?? null,
                'token' => Invitation::generateToken(),
                'email' => $data['email'],
                'role' => $data['role'],
                'expires_at' => now()->addDays(7),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Create role-specific profile if needed
            $this->createRoleProfile($user, $data);

            return $invitation;
        });
    }

    /**
     * Validate an invitation token.
     */
    public function validateToken(string $token): ?Invitation
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            throw ValidationException::withMessages([
                'token' => 'Invalid invitation token.',
            ]);
        }

        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has already been used.',
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has expired.',
            ]);
        }

        return $invitation;
    }

    /**
     * Accept an invitation and set the user's password.
     */
    public function acceptInvitation(string $token, string $password, string $ipAddress = null, string $userAgent = null): User
    {
        return DB::transaction(function () use ($token, $password, $ipAddress, $userAgent) {
            $invitation = $this->validateToken($token);

            // Update user password and status
            $user = $invitation->user;
            $user->update([
                'password' => Hash::make($password),
                'status' => 'active',
                'password_set_at' => now(),
            ]);

            // Mark invitation as used
            $invitation->markAsUsed();
            $invitation->update([
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
            ]);

            // Log the user in
            Auth::login($user);

            return $user;
        });
    }

    /**
     * Resend an invitation.
     */
    public function resendInvitation(Invitation $invitation): Invitation
    {
        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation has already been used.',
            ]);
        }

        // Check resend limits (max 3 resends)
        if ($invitation->resend_count >= 3) {
            throw ValidationException::withMessages([
                'invitation' => 'Maximum resend limit reached. Please create a new invitation.',
            ]);
        }

        // Generate new token and extend expiry
        $invitation->update([
            'token' => Invitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->incrementResendCount();

        return $invitation;
    }

    /**
     * Cancel an invitation.
     */
    public function cancelInvitation(Invitation $invitation): void
    {
        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'invitation' => 'Cannot cancel a used invitation.',
            ]);
        }

        DB::transaction(function () use ($invitation) {
            // Mark invitation as used to invalidate it
            $invitation->update(['is_used' => true]);
            
            // Delete the associated user
            $invitation->user->delete();
        });
    }

    /**
     * Create role-specific profile for the user.
     */
    protected function createRoleProfile(User $user, array $data): void
    {
        // Create core user profile
        $userProfile = \App\Models\UserProfile::create([
            'user_id' => $user->id,
            'mobile_number' => $data['mobile_number'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address_barangay' => $data['address_barangay'] ?? null,
            'address_municipality' => $data['address_municipality'] ?? null,
            'address_province' => $data['address_province'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'prc_license_number' => $data['prc_license_number'] ?? null,
            'highest_educational_attainment' => $data['highest_educational_attainment'] ?? null,
            'major_specialization' => $data['major_specialization'] ?? null,
            'years_of_teaching_experience' => $data['years_of_teaching_experience'] ?? 0,
            'date_of_entry_to_deped' => $data['date_of_entry_to_deped'] ?? null,
            'employment_status' => $data['employment_status'] ?? null,
        ]);

        // Create role-specific profile
        switch ($user->role) {
            case 'teacher':
                \App\Models\TeacherProfile::create([
                    'user_id' => $user->id,
                    'grade_level' => $data['grade_level'] ?? null,
                    'subject_area_taught' => $data['subject_area_taught'] ?? null,
                    'teaching_position' => $data['teaching_position'] ?? null,
                    'strand_specialization' => $data['strand_specialization'] ?? null,
                    'has_advisory_class' => $data['has_advisory_class'] ?? false,
                    'advisory_section' => $data['advisory_section'] ?? null,
                    'teacher_load' => $data['teacher_load'] ?? 0,
                    'certification_training' => $data['certification_training'] ?? null,
                    'department' => $data['department'] ?? null,
                ]);
                break;
            case 'supervisor':
                \App\Models\SupervisorProfile::create([
                    'user_id' => $user->id,
                    'division_district_assigned' => $data['division_district_assigned'] ?? null,
                    'area_of_specialization' => $data['area_of_specialization'] ?? null,
                    'supervisory_level' => $data['supervisory_level'] ?? null,
                    'previous_teaching_experience_years' => $data['previous_teaching_experience_years'] ?? 0,
                    'administrative_experience_years' => $data['administrative_experience_years'] ?? 0,
                    'key_responsibilities' => $data['key_responsibilities'] ?? null,
                    'position' => $data['position'] ?? null,
                ]);
                break;
            case 'school_head':
                \App\Models\SchoolHeadProfile::create([
                    'user_id' => $user->id,
                    'school_id' => $data['school_id'] ?? null,
                    'position_level' => $data['position_level'] ?? null,
                    'administrative_experience_years' => $data['administrative_experience_years'] ?? 0,
                    'leadership_training' => $data['leadership_training'] ?? null,
                    'current_designation' => $data['current_designation'] ?? null,
                    'number_of_teachers_supervised' => $data['number_of_teachers_supervised'] ?? 0,
                    'school_type' => $data['school_type'] ?? null,
                    'additional_roles' => $data['additional_roles'] ?? null,
                    'position' => $data['position'] ?? null,
                ]);
                break;
        }
    }

    /**
     * Get invitation statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => Invitation::count(),
            'pending' => Invitation::pending()->count(),
            'used' => Invitation::used()->count(),
            'expired' => Invitation::expired()->count(),
        ];
    }
}
