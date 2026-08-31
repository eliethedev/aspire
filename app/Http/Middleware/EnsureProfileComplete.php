<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Map each role to its profile edit route.
     */
    protected array $route = [
        'teacher' => 'teacher.profile.edit',
        'supervisor' => 'supervisor.profile.edit',
        'school_head' => 'school-head.profile.edit',
    ];

    /**
     * Role-specific fields that must be populated by the user before they can
     * use observation-related features. Fields that are set by an admin at
     * invitation time (career stage, position/designation, etc.) are excluded —
     * the user should not be responsible for them.
     */
    protected function requiredFor(string $role, $user): array
    {
        return match ($role) {
            'teacher' => [
                'mobile_number' => $user->profile?->mobile_number,
                'employment_status' => $user->profile?->employment_status,
                'grade_level' => $user->teacher?->grade_level ?? $user->teacherProfile?->grade_level,
                'default_room' => $user->teacherProfile?->default_room,
            ],
            'supervisor' => [
                'mobile_number' => $user->profile?->mobile_number,
                'division_district_assigned' => $user->supervisorProfile?->division_district_assigned,
                'area_of_specialization' => $user->supervisorProfile?->area_of_specialization,
                'supervisory_level' => $user->supervisorProfile?->supervisory_level,
            ],
            'school_head' => [
                'mobile_number' => $user->profile?->mobile_number,
                'school_type' => $user->schoolHeadProfile?->school_type,
                'grade_level' => $user->schoolHeadProfile?->grade_level,
                'subject' => $user->schoolHeadProfile?->subject,
            ],
            default => [],
        };
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $role = $user->role;

        // Only enforce for the roles that have observation-critical profiles.
        if (! isset($this->route[$role])) {
            return $next($request);
        }

        // If this request is already the profile page (viewing or saving it),
        // let it through so the user can actually complete the form.
        if ($request->route() && in_array($request->route()->getName(), [
            $this->route[$role],
            str_replace('.edit', '.update', $this->route[$role]),
        ], true)) {
            return $next($request);
        }

        // Eager-load the role relation(s) needed to inspect profile completeness.
        $with = match ($role) {
            'teacher' => ['profile', 'teacher', 'teacherProfile'],
            'supervisor' => ['profile', 'supervisor', 'supervisorProfile'],
            'school_head' => ['profile', 'schoolHeadProfile'],
            default => [],
        };
        if ($with !== []) {
            $user->loadMissing($with);
        }

        $missing = $this->missingFields($user, $role);

        if ($missing !== []) {
            session()->flash('profile_incomplete', true);
            session()->flash('profile_missing', $missing);

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Please complete your profile before continuing.',
                    'missing_fields' => $missing,
                    'redirect' => route($this->route[$role]),
                ], 302);
            }

            return redirect()->route($this->route[$role])
                ->with('status', 'profile-incomplete');
        }

        return $next($request);
    }

    protected function missingFields($user, string $role): array
    {
        $missing = [];

        foreach ($this->requiredFor($role, $user) as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $missing[] = $key;
            } elseif ($value === null) {
                $missing[] = $key;
            }
        }

        return $missing;
    }
}
