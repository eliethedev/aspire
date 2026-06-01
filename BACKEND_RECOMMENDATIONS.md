# Backend Recommendations for User Invitation System

## Overview
This document provides recommendations for handling the improved User Invitation Form backend, including controller logic, model structure, and data storage strategies for role-specific information.

---

## 1. Database Schema Recommendations

### Users Table (Common Fields)
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable(); // Nullable for invitation flow
    $table->string('mobile_number')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->enum('gender', ['male', 'female', 'other'])->nullable();
    $table->string('employee_id')->nullable();
    
    // Address fields
    $table->string('address_barangay')->nullable();
    $table->string('address_municipality')->nullable();
    $table->string('address_province')->nullable();
    
    // Professional fields
    $table->string('prc_license_number')->nullable();
    $table->string('highest_educational_attainment')->nullable();
    $table->string('major_specialization')->nullable();
    $table->integer('years_of_teaching_experience')->nullable();
    $table->date('date_of_entry_to_deped')->nullable();
    $table->enum('employment_status', ['permanent', 'provisional', 'contractual', 'substitute'])->nullable();
    
    // Role assignment
    $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
    $table->enum('role', ['teacher', 'supervisor', 'school_head', 'admin']);
    
    // Invitation flow
    $table->string('invitation_token')->nullable()->unique();
    $table->timestamp('invitation_sent_at')->nullable();
    $table->timestamp('invitation_accepted_at')->nullable();
    $table->timestamp('invitation_expires_at')->nullable();
    
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

### Option A: JSON Column for Role-Specific Data (Recommended for Simplicity)
Add a `profile_data` JSON column to the users table:

```php
$table->json('profile_data')->nullable();
```

**Advantages:**
- Simple to implement
- Flexible schema evolution
- Easy to query with Laravel's JSON casting
- Single table for all user data

**Disadvantages:**
- Harder to enforce data integrity at database level
- More complex queries for filtering by role-specific fields

### Option B: Separate Role-Specific Tables (Recommended for Data Integrity)
Create dedicated tables for each role:

```php
// Teacher profiles
Schema::create('teacher_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('grade_level', ['elementary', 'junior_high', 'senior_high'])->nullable();
    $table->string('subject_area_taught')->nullable();
    $table->enum('teaching_position', ['teacher_i', 'teacher_ii', 'teacher_iii', 'master_teacher_i', 'master_teacher_ii', 'master_teacher_iii', 'master_teacher_iv', 'master_teacher_v'])->nullable();
    $table->integer('teacher_load')->nullable();
    $table->timestamps();
});

// Supervisor profiles
Schema::create('supervisor_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('division_district_assigned')->nullable();
    $table->string('area_of_specialization')->nullable();
    $table->enum('supervisory_level', ['division', 'district', 'regional'])->nullable();
    $table->string('position')->nullable();
    $table->timestamps();
});

// School Head profiles
Schema::create('school_head_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('position_level', ['principal_i', 'principal_ii', 'principal_iii', 'principal_iv', 'head_teacher', 'assistant_principal'])->nullable();
    $table->enum('current_designation', ['principal', 'officer_in_charge', 'head_teacher', 'assistant_principal'])->nullable();
    $table->timestamps();
});
```

**Advantages:**
- Strong data integrity
- Clear separation of concerns
- Easier to add role-specific validations
- Better query performance for role-specific data

**Disadvantages:**
- More complex migrations
- Requires joins for queries
- More tables to maintain

---

## 2. Model Structure

### User Model (with JSON approach)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'date_of_birth',
        'gender',
        'employee_id',
        'address_barangay',
        'address_municipality',
        'address_province',
        'prc_license_number',
        'highest_educational_attainment',
        'major_specialization',
        'years_of_teaching_experience',
        'date_of_entry_to_deped',
        'employment_status',
        'school_id',
        'role',
        'profile_data',
        'invitation_token',
        'invitation_sent_at',
        'invitation_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'date_of_entry_to_deped' => 'date',
        'years_of_teaching_experience' => 'integer',
        'invitation_sent_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'profile_data' => 'array', // JSON casting
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Role-specific accessors
    public function getTeacherDataAttribute()
    {
        return $this->profile_data['teacher'] ?? null;
    }

    public function getSupervisorDataAttribute()
    {
        return $this->profile_data['supervisor'] ?? null;
    }

    public function getSchoolHeadDataAttribute()
    {
        return $this->profile_data['school_head'] ?? null;
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeWithSchool($query)
    {
        return $query->with('school');
    }

    public function scopePendingInvitation($query)
    {
        return $query->whereNotNull('invitation_token')
                     ->whereNull('password');
    }
}
```

### User Model (with separate tables approach)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'date_of_birth',
        'gender',
        'employee_id',
        'address_barangay',
        'address_municipality',
        'address_province',
        'prc_license_number',
        'highest_educational_attainment',
        'major_specialization',
        'years_of_teaching_experience',
        'date_of_entry_to_deped',
        'employment_status',
        'school_id',
        'role',
        'invitation_token',
        'invitation_sent_at',
        'invitation_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'date_of_entry_to_deped' => 'date',
        'years_of_teaching_experience' => 'integer',
        'invitation_sent_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function supervisorProfile()
    {
        return $this->hasOne(SupervisorProfile::class);
    }

    public function schoolHeadProfile()
    {
        return $this->hasOne(SchoolHeadProfile::class);
    }

    public function getRoleProfileAttribute()
    {
        return match($this->role) {
            'teacher' => $this->teacherProfile,
            'supervisor' => $this->supervisorProfile,
            'school_head' => $this->schoolHeadProfile,
            default => null,
        };
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeWithSchool($query)
    {
        return $query->with('school');
    }

    public function scopePendingInvitation($query)
    {
        return $query->whereNotNull('invitation_token')
                     ->whereNull('password');
    }
}
```

---

## 3. Controller Recommendations

### InvitationController@store
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            // Common fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'employee_id' => 'nullable|string|max:255',
            
            // Address fields
            'address_barangay' => 'nullable|string|max:255',
            'address_municipality' => 'nullable|string|max:255',
            'address_province' => 'nullable|string|max:255',
            
            // Professional fields
            'prc_license_number' => 'nullable|string|max:255',
            'highest_educational_attainment' => 'nullable|string|max:255',
            'major_specialization' => 'nullable|string|max:255',
            'years_of_teaching_experience' => 'nullable|integer|min:0|max:50',
            'date_of_entry_to_deped' => 'nullable|date',
            'employment_status' => 'nullable|in:permanent,provisional,contractual,substitute',
            
            // Role assignment
            'role' => 'required|in:teacher,supervisor,school_head,admin',
            'school_id' => [
                'nullable',
                Rule::exists('schools', 'id'),
                function ($attribute, $value, $fail) use ($request) {
                    $role = $request->input('role');
                    if (in_array($role, ['teacher', 'supervisor', 'school_head']) && empty($value)) {
                        $fail('School assignment is required for this role.');
                    }
                },
            ],
            
            // Custom message
            'custom_message' => 'nullable|string|max:1000',
            
            // Teacher-specific fields
            'grade_level' => 'required_if:role,teacher|nullable|in:elementary,junior_high,senior_high',
            'subject_area_taught' => 'required_if:role,teacher|nullable|string|max:255',
            'teaching_position' => 'required_if:role,teacher|nullable|in:teacher_i,teacher_ii,teacher_iii,master_teacher_i,master_teacher_ii,master_teacher_iii,master_teacher_iv,master_teacher_v',
            'teacher_load' => 'required_if:role,teacher|nullable|integer|min:0|max:50',
            
            // Supervisor-specific fields
            'division_district_assigned' => 'required_if:role,supervisor|nullable|string|max:255',
            'area_of_specialization' => 'required_if:role,supervisor|nullable|string|max:255',
            'supervisory_level' => 'required_if:role,supervisor|nullable|in:division,district,regional',
            'supervisor_position' => 'required_if:role,supervisor|nullable|string|max:255',
            
            // School Head-specific fields
            'position_level' => 'required_if:role,school_head|nullable|in:principal_i,principal_ii,principal_iii,principal_iv,head_teacher,assistant_principal',
            'current_designation' => 'required_if:role,school_head|nullable|in:principal,officer_in_charge,head_teacher,assistant_principal',
        ]);

        try {
            DB::beginTransaction();

            // Generate invitation token
            $invitationToken = Str::random(60);
            $invitationExpiresAt = now()->addDays(7); // Token expires in 7 days

            // Prepare role-specific data
            $profileData = [];
            
            if ($request->role === 'teacher') {
                $profileData['teacher'] = [
                    'grade_level' => $request->grade_level,
                    'subject_area_taught' => $request->subject_area_taught,
                    'teaching_position' => $request->teaching_position,
                    'teacher_load' => $request->teacher_load,
                ];
            } elseif ($request->role === 'supervisor') {
                $profileData['supervisor'] = [
                    'division_district_assigned' => $request->division_district_assigned,
                    'area_of_specialization' => $request->area_of_specialization,
                    'supervisory_level' => $request->supervisory_level,
                    'position' => $request->supervisor_position,
                ];
            } elseif ($request->role === 'school_head') {
                $profileData['school_head'] = [
                    'position_level' => $request->position_level,
                    'current_designation' => $request->current_designation,
                ];
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'employee_id' => $request->employee_id,
                'address_barangay' => $request->address_barangay,
                'address_municipality' => $request->address_municipality,
                'address_province' => $request->address_province,
                'prc_license_number' => $request->prc_license_number,
                'highest_educational_attainment' => $request->highest_educational_attainment,
                'major_specialization' => $request->major_specialization,
                'years_of_teaching_experience' => $request->years_of_teaching_experience,
                'date_of_entry_to_deped' => $request->date_of_entry_to_deped,
                'employment_status' => $request->employment_status,
                'school_id' => $request->school_id,
                'role' => $request->role,
                'profile_data' => $profileData,
                'invitation_token' => $invitationToken,
                'invitation_sent_at' => now(),
                'invitation_expires_at' => $invitationExpiresAt,
            ]);

            // Send invitation email
            $user->sendInvitationEmail($request->custom_message);

            DB::commit();

            return redirect()
                ->route('admin.invitations.create')
                ->with('success', 'Invitation sent successfully to ' . $user->email);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to send invitation. Please try again.']);
        }
    }
}
```

### Alternative: Using Separate Tables
```php
public function store(Request $request)
{
    // ... validation same as above ...

    try {
        DB::beginTransaction();

        $invitationToken = Str::random(60);
        $invitationExpiresAt = now()->addDays(7);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // ... other common fields ...
            'school_id' => $request->school_id,
            'role' => $request->role,
            'invitation_token' => $invitationToken,
            'invitation_sent_at' => now(),
            'invitation_expires_at' => $invitationExpiresAt,
        ]);

        // Create role-specific profile
        if ($request->role === 'teacher') {
            $user->teacherProfile()->create([
                'grade_level' => $request->grade_level,
                'subject_area_taught' => $request->subject_area_taught,
                'teaching_position' => $request->teaching_position,
                'teacher_load' => $request->teacher_load,
            ]);
        } elseif ($request->role === 'supervisor') {
            $user->supervisorProfile()->create([
                'division_district_assigned' => $request->division_district_assigned,
                'area_of_specialization' => $request->area_of_specialization,
                'supervisory_level' => $request->supervisory_level,
                'position' => $request->supervisor_position,
            ]);
        } elseif ($request->role === 'school_head') {
            $user->schoolHeadProfile()->create([
                'position_level' => $request->position_level,
                'current_designation' => $request->current_designation,
            ]);
        }

        // Send invitation email
        $user->sendInvitationEmail($request->custom_message);

        DB::commit();

        return redirect()
            ->route('admin.invitations.create')
            ->with('success', 'Invitation sent successfully to ' . $user->email);

    } catch (\Exception $e) {
        DB::rollBack();
        
        return back()
            ->withInput()
            ->withErrors(['error' => 'Failed to send invitation. Please try again.']);
    }
}
```

---

## 4. Email Notification

### Create Invitation Mailable
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $customMessage;
    public $invitationUrl;

    public function __construct($user, $customMessage = null)
    {
        $this->user = $user;
        $this->customMessage = $customMessage;
        $this->invitationUrl = route('invitation.accept', ['token' => $user->invitation_token]);
    }

    public function build()
    {
        return $this->markdown('emails.invitation')
                    ->subject('You\'re Invited to Join ASPIRE')
                    ->with([
                        'user' => $this->user,
                        'customMessage' => $this->customMessage,
                        'invitationUrl' => $this->invitationUrl,
                    ]);
    }
}
```

### Add to User Model
```php
public function sendInvitationEmail($customMessage = null)
{
    $this->notify(new \App\Notifications\UserInvited($customMessage));
}
```

---

## 5. Recommendation Summary

### For Small to Medium Applications (< 10,000 users)
**Use JSON Column Approach (Option A)**
- Simpler implementation
- Faster development
- Easier to maintain
- Sufficient performance for most use cases

### For Large Applications (> 10,000 users) or Complex Role Requirements
**Use Separate Tables Approach (Option B)**
- Better data integrity
- More scalable
- Easier to add complex validations
- Better query performance for role-specific filtering

### Additional Recommendations

1. **Validation**: Always validate role-specific fields based on the selected role
2. **Security**: Ensure invitation tokens are cryptographically secure and expire appropriately
3. **Email**: Use queue for sending emails to avoid blocking the request
4. **Logging**: Log invitation creation and acceptance events
5. **Testing**: Write comprehensive tests for each role type
6. **Documentation**: Document the role-specific fields and their validation rules
7. **API**: Consider creating API endpoints for programmatic invitation creation
8. **Audit Trail**: Track who created each invitation and when

---

## 6. Migration Example (JSON Approach)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add profile_data column if it doesn't exist
            if (!Schema::hasColumn('users', 'profile_data')) {
                $table->json('profile_data')->nullable()->after('role');
            }
            
            // Add invitation columns if they don't exist
            if (!Schema::hasColumn('users', 'invitation_token')) {
                $table->string('invitation_token')->nullable()->unique()->after('profile_data');
            }
            if (!Schema::hasColumn('users', 'invitation_sent_at')) {
                $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
            }
            if (!Schema::hasColumn('users', 'invitation_accepted_at')) {
                $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_sent_at');
            }
            if (!Schema::hasColumn('users', 'invitation_expires_at')) {
                $table->timestamp('invitation_expires_at')->nullable()->after('invitation_accepted_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_data',
                'invitation_token',
                'invitation_sent_at',
                'invitation_accepted_at',
                'invitation_expires_at',
            ]);
        });
    }
};
```
