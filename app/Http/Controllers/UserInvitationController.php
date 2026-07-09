<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserInvitationController extends Controller
{
    protected InvitationService $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Display a listing of invitations.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Invitation::with(['user', 'invitedBy', 'school'])
            ->latest();

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'used':
                    $query->used();
                    break;
                case 'expired':
                    $query->expired();
                    break;
            }
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $invitations = $query->paginate(20);
        $statistics = $this->invitationService->getStatistics();
        $schools = \App\Models\School::where('is_active', true)->get();

        return view('admin.invitations.index', compact('invitations', 'statistics', 'schools'));
    }

    /**
     * Show the form for creating a new invitation.
     */
    public function create()
    {
        $schools = \App\Models\School::where('is_active', true)->get();
        return view('admin.invitations.create', compact('schools'));
    }

    /**
     * Store a newly created invitation in storage.
     */
    public function store(Request $request)
    {
        // Core validation rules (apply to all roles)
        $coreRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(['teacher', 'supervisor', 'school_head', 'admin'])],
            'school_id' => 'nullable|exists:schools,id',
            // Core profile fields
            'mobile_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address_barangay' => 'nullable|string|max:255',
            'address_municipality' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:50|unique:user_profiles,employee_id',
            'prc_license_number' => 'nullable|string|max:50|unique:user_profiles,prc_license_number',
            'highest_educational_attainment' => 'nullable|string|max:255',
            'major_specialization' => 'nullable|string|max:255',
            'years_of_teaching_experience' => 'nullable|integer|min:0|max:50',
            'date_of_entry_to_deped' => 'nullable|date',
            'employment_status' => 'nullable|in:permanent,provisional,contractual,substitute',
        ];

        // Teacher-specific validation
        $teacherRules = [
            'grade_level' => 'nullable|in:elementary,junior_high,senior_high',
            'subject_area_taught' => 'nullable|string|max:255',
            'teaching_position' => 'nullable|in:teacher_i,teacher_ii,teacher_iii,master_teacher_i,master_teacher_ii,master_teacher_iii,master_teacher_iv,master_teacher_v',
            'strand_specialization' => 'nullable|string|max:255',
            'has_advisory_class' => 'nullable|boolean',
            'advisory_section' => 'nullable|string|max:255',
            'teacher_load' => 'nullable|integer|min:0|max:50',
            'certification_training' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ];

        // Supervisor-specific validation
        $supervisorRules = [
            'division_district_assigned' => 'nullable|string|max:255',
            'area_of_specialization' => 'nullable|string|max:255',
            'supervisory_level' => 'nullable|in:division,district,regional',
            'previous_teaching_experience_years' => 'nullable|integer|min:0|max:50',
            'administrative_experience_years' => 'nullable|integer|min:0|max:50',
            'key_responsibilities' => 'nullable|string',
            'position' => 'nullable|string|max:255',
        ];

        // School Head-specific validation
        $schoolHeadRules = [
            'position_level' => 'nullable|in:principal_i,principal_ii,principal_iii,principal_iv,head_teacher,assistant_principal',
            'administrative_experience_years' => 'nullable|integer|min:0|max:50',
            'leadership_training' => 'nullable|string|max:255',
            'current_designation' => 'nullable|in:principal,officer_in_charge,head_teacher,assistant_principal',
            'number_of_teachers_supervised' => 'nullable|integer|min:0',
            'school_type' => 'nullable|in:elementary,secondary,integrated,senior_high',
            'additional_roles' => 'nullable|string',
            'position' => 'nullable|string|max:255',
        ];

        // Merge rules based on role
        $rules = $coreRules;
        if ($request->role === 'teacher') {
            $rules = array_merge($rules, $teacherRules);
        } elseif ($request->role === 'supervisor') {
            $rules = array_merge($rules, $supervisorRules);
        } elseif ($request->role === 'school_head') {
            $rules = array_merge($rules, $schoolHeadRules);
            $rules['school_id'] = 'required|exists:schools,id'; // School head must have a school
        }

        $request->validate($rules);

        try {
            $invitation = $this->invitationService->createInvitation(
                $request->all(),
                Auth::user()
            );

            // Send invitation email
            $invitation->user->notify(new \App\Notifications\UserInvitation($invitation));

            app(AuditLogService::class)->log(
                'invitation_created', 'invitations', (string) $invitation->id,
                "Invitation sent to {$invitation->email} for role {$invitation->role}",
                'success',
                [],
                $invitation->toArray(),
                ['invitation_email' => $invitation->email, 'invitation_role' => $invitation->role],
            );

            return redirect()
                ->route('admin.invitations.index')
                ->with('success', 'Invitation sent successfully to ' . $invitation->email);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Display the specified invitation.
     */
    public function show(\App\Models\Invitation $invitation)
    {
        $invitation->load(['user', 'invitedBy', 'school']);
        return view('admin.invitations.show', compact('invitation'));
    }

    /**
     * Resend an invitation.
     */
    public function resend(\App\Models\Invitation $invitation)
    {
        try {
            $invitation = $this->invitationService->resendInvitation($invitation);

            // Send new invitation email
            $invitation->user->notify(new \App\Notifications\UserInvitation($invitation));

            app(AuditLogService::class)->log(
                'invitation_resent', 'invitations', (string) $invitation->id,
                "Invitation resent to {$invitation->email}",
                'success',
                [],
                ['resend_count' => $invitation->resend_count, 'expires_at' => $invitation->expires_at->toIso8601String()],
            );

            return back()->with('success', 'Invitation resent successfully to ' . $invitation->email);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(\App\Models\Invitation $invitation)
    {
        try {
            $this->invitationService->cancelInvitation($invitation);

            app(AuditLogService::class)->log(
                'invitation_cancelled', 'invitations', (string) $invitation->id,
                "Invitation for {$invitation->email} was cancelled",
                'success',
                ['invitation_email' => $invitation->email, 'invitation_role' => $invitation->role],
                [],
            );

            return back()->with('success', 'Invitation cancelled successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Remove the specified invitation from storage.
     */
    public function destroy(\App\Models\Invitation $invitation)
    {
        if ($invitation->is_used) {
            return back()->withErrors(['invitation' => 'Cannot delete a used invitation.']);
        }

        $invitationEmail = $invitation->email;
        $invitation->delete();

        app(AuditLogService::class)->log(
            'deleted', 'invitations', (string) $invitation->id,
            "Deleted invitation for {$invitationEmail}",
            'success',
            ['email' => $invitationEmail, 'role' => $invitation->role],
            [],
        );

        return back()->with('success', 'Invitation deleted successfully.');
    }
}
