<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Services\RateeProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolHeadBrowsingController extends Controller
{
    /**
     * Display list of school heads.
     */
    public function schoolHeads(Request $request)
    {
        $user = Auth::user();

        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user.school', 'school'])
            ->withCount(['observations as total_observations' => function ($q) {
                $q->where('observee_type', SchoolHeadProfile::class);
            }])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate($request->per_page ?? 15);

        return view('supervisor.teachers.school-heads', compact('schoolHeads'));
    }

    /**
     * Display observation history for a specific school head.
     */
    public function schoolHeadObservationHistory(SchoolHeadProfile $schoolHead)
    {
        $this->assertSchoolHeadBelongsToSupervisorSchool($schoolHead);

        $observations = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning'])
            ->latest()
            ->paginate(10);

        return view('supervisor.teachers.school-head-observations', compact('schoolHead', 'observations'));
    }

    /**
     * Display the ratee profile for a school head.
     *
     * Read-only decision support: it summarises existing observation data and
     * never modifies the school head's status, position or career stage.
     */
    public function schoolHeadProfile(SchoolHeadProfile $schoolHead)
    {
        $this->assertSchoolHeadBelongsToSupervisorSchool($schoolHead);

        $schoolHead->load(['user.school', 'school']);

        $observations = Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning', 'cotIndicatorVersion'])
            ->latest()
            ->paginate(10);

        $rateeProfile = app(RateeProfileService::class)->for($schoolHead);

        return view('supervisor.teachers.school-head-profile', compact('schoolHead', 'observations', 'rateeProfile'));
    }

    /**
     * A supervisor may only view school heads of the school they belong to.
     * Supervisors without a school are unrestricted.
     */
    private function assertSchoolHeadBelongsToSupervisorSchool(SchoolHeadProfile $schoolHead): void
    {
        $user = Auth::user();

        if (! $user->school_id) {
            return;
        }

        if ($schoolHead->school_id !== $user->school_id && $schoolHead->user?->school_id !== $user->school_id) {
            abort(403, 'This school head does not belong to your school.');
        }
    }
}
