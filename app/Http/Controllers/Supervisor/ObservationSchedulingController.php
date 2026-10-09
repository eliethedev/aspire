<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\CotRating;
use App\Models\FormTemplate;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CotIndicatorService;
use App\Services\FormTemplateService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ObservationSchedulingController extends Controller
{
    use Concerns\AuthorizesObservations, Concerns\ResolvesSchoolYearTerm;

    protected NotificationService $notificationService;

    protected PHPMailerService $mailerService;

    protected FormTemplateService $formTemplateService;

    protected CotIndicatorService $cotIndicatorService;

    public function __construct(NotificationService $notificationService, PHPMailerService $mailerService, FormTemplateService $formTemplateService, CotIndicatorService $cotIndicatorService)
    {
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->formTemplateService = $formTemplateService;
        $this->cotIndicatorService = $cotIndicatorService;
    }

    /**
     * Show the form for creating a new observation.
     */
    public function createObservation()
    {
        $user = Auth::user();

        $schoolYear = $this->getCurrentSchoolYear();

        // Get teachers from the same school (using teacher's school_id directly)
        $teachers = Teacher::query()
            ->with(['user', 'school', 'subjects'])
            ->where('school_id', $user->school_id)
            ->get();

        // Get school heads (can filter by division/district later).
        // user.school is eager-loaded for the school_name fallback: some
        // profiles have no school_id of their own (user assigned later) and
        // would otherwise render as "No school assigned".
        $schoolHeads = SchoolHeadProfile::query()
            ->with(['user.school', 'school'])
            ->get();

        // Prepare teacher data for JavaScript (richer info for browse & preview)
        $teacherIds = $teachers->pluck('id');

        $recentObs = Observation::whereIn('observee_id', $teacherIds)
            ->where('observee_type', Teacher::class)
            ->latest()
            ->get()
            ->groupBy('observee_id');

        $teacherData = $teachers->filter(function ($teacher) {
            return $teacher->user !== null;
        })->map(function ($teacher) use ($recentObs) {
            $observations = $recentObs->get($teacher->id, collect())->take(5)->map(function ($obs) {
                return [
                    'id' => $obs->id,
                    'date' => $obs->observation_date ? $obs->observation_date->format('M d, Y') : 'No date',
                    'stage' => $obs->stage,
                    'status' => $obs->status,
                    'subject' => $obs->subject,
                    'score' => $obs->overall_score ? number_format($obs->overall_score, 2) : null,
                    'url' => route('supervisor.observations.show', $obs),
                ];
            });

            $totalObs = $recentObs->get($teacher->id, collect())->count();
            $completedObs = $recentObs->get($teacher->id, collect())->where('stage', 'post_conference')->count();
            $inProgressObs = $recentObs->get($teacher->id, collect())->whereIn('stage', ['pre_observation_planning', 'observation'])->count();

            return [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'email' => $teacher->user->email,
                'subject' => $teacher->subjectsLabel ?? 'Not set',
                'subjects' => $teacher->subjects->pluck('name')->values()->all(),
                'grade_level' => $teacher->grade_level ?? 'Not set',
                'grade_level_label' => $teacher->grade_level_label ?? 'Not set',
                'department' => $teacher->department ?? 'Not set',
                'position' => $teacher->position ?? 'Teacher',
                'position_label' => $teacher->position_label ?? 'Teacher',
                'career_stage' => $teacher->career_stage,
                'employee_number' => $teacher->employee_number ?: 'Not set',
                'school_name' => $teacher->school?->name ?? 'No school assigned',
                'profile_url' => route('supervisor.teachers.show', $teacher),
                'recent_observations' => $observations,
                'obs_stats' => [
                    'total' => $totalObs,
                    'completed' => $completedObs,
                    'in_progress' => $inProgressObs,
                ],
            ];
        })->values();

        // Prepare school head data for JavaScript
        $schoolHeadData = $schoolHeads->filter(function ($schoolHead) {
            return $schoolHead->user !== null;
        })->map(function ($schoolHead) {
            return [
                'id' => $schoolHead->id,
                // The observations.school_head_id column references users.id,
                // so assignment dropdowns must submit the user id, not the
                // profile id (which is used for observee selection).
                'user_id' => $schoolHead->user->id,
                'name' => $schoolHead->user->name,
                'email' => $schoolHead->user->email,
                'subject' => $schoolHead->subject ?? 'Not set',
                'grade_level' => $schoolHead->grade_level ?? 'Not set',
                'grade_level_label' => $schoolHead->grade_level_label ?? 'Not set',
                'position' => $schoolHead->position ?? $schoolHead->current_designation ?? 'School Head',
                'position_label' => $schoolHead->position_level ? $schoolHead->position_level_label : ($schoolHead->position ?? $schoolHead->current_designation ?? 'School Head'),
                'position_level' => $schoolHead->position_level ?? '—',
                'school_name' => $schoolHead->school_name ?? 'No school assigned',
            ];
        })->values();

        // Published COT instruments for the current school year (the
        // "template" selection step of the scheduling wizard). The ratee role
        // determines whether a template appears for teacher or school head
        // observations; a career stage only narrows the auto-resolve, so all
        // published templates for the school year are listed.
        $cotTemplates = CotIndicatorVersion::query()
            ->with(['indicators' => fn ($query) => $query->active()])
            ->withCount('indicators')
            ->where('school_year', $schoolYear)
            ->published()
            ->orderByDesc('is_default')
            ->orderBy('ratee_role')
            ->orderBy('label')
            ->get()
            ->map(fn (CotIndicatorVersion $version) => [
                'id' => $version->id,
                'label' => $version->label,
                'school_year' => $version->school_year,
                'is_default' => $version->is_default,
                'ratee_role' => $version->rateeRole(),
                'ratee_role_label' => $version->rateeRoleLabel(),
                'ratee_position' => $version->ratee_position,
                'career_stage' => $version->career_stage,
                'career_stage_label' => $version->careerStageLabel(),
                'framework_label' => $version->frameworkLabel(),
                'instrument_label' => $version->instrumentLabel(),
                // Reflects the active indicators loaded above, keeping the
                // badge consistent with the preview modal contents.
                'indicators_count' => $version->indicators->count(),
                'requires_post_conference' => $version->requiresPostConference(),
                // Indicators organized by domain for the preview modal.
                'indicator_groups' => $version->indicators
                    ->groupBy('domain')
                    ->map(fn ($group) => $group->map(fn (CotIndicator $indicator) => [
                        'code' => $indicator->code,
                        'description' => $indicator->description,
                    ])->values())
                    ->toArray(),
            ])
            ->values();

        // EPOC Rating Instrument domains & indicators (used for the template
        // preview in the create wizard for School Head observations).
        $epocDomains = [
            'Domain 1: Establishing a Warm and Clear Opening of the Post Observation Conference' => [
                "School Head acknowledges teacher's time (Thanks the teacher for allowing him/her to observe a class)",
                'School Head states the purpose of the conversation',
                'Talks in a voice that is warm, friendly and sincere',
            ],
            'Domain 2: Focus on what\'s going well' => [
                'Congratulates teachers for doing a job well (cite specific instances or teacher behavior/activities that are worth mentioning. Refer to the strengths noted)',
                'Asks the teacher to clearly state the objectives of the lesson',
                "Paraphrases and affirms the teacher's lesson objective (Asks what the pupils are able to demonstrate at the end of the lesson)",
                'Asks the teacher what she did to teach the lesson',
                'Asks teacher what made him/her happy about the delivery of the lesson. The SH listens intently to what the teacher is saying',
                'The SH affirms what the teacher considered as things that went well in the delivery of the lesson',
                'The SH extends the positive focus in addition to what the teacher identified as what went well, citing additional specific things referring to the strengths noted',
            ],
            'Domain 3: Identify Challenges Facing the Teacher' => [
                'The SH asks the teacher to tell which part of the lesson she thinks did not go well',
                "The SH paraphrases teacher's message to check whether they have the same understanding",
                'The SH enables the teacher to tell additional parts that did not go well by citing specific instances recorded in the strengths noted',
                'The SH avoids diversion and stays focused on the issues/data/documentation at hand when teacher makes caustic statements',
                "The SH is able to verify the teacher's perception about the identified areas for improvement",
            ],
            'Domain 4: Generating Ideas for Addressing Teacher\'s Challenges' => [
                'The SH guides the teacher in identifying possible strategies in addressing the challenges',
                'The SH helps solve the problem by offering ideas for improvement if and when the teacher is not able to do so',
                'The SH connects the teacher to available and appropriate resources to help address the challenges',
                'The SH avoids compromising statements that provide an excuse for poor performance',
            ],
            'Domain 5: Prioritizing the Next Steps' => [
                'The Teacher and the principal reviews ideas for improvement and assign priority to possible options',
            ],
            'Domain 6: Ending the Post Observation Conference' => [
                'The SH makes the teacher agree on the next steps by asking the teacher to choose whose help he/she would want to ask to assist in improving the identified challenges',
                'The SH enables the teacher to make a commitment regarding the next steps identified',
                'The SH thanks the teacher for the conversation',
            ],
        ];

        return view('supervisor.observations.create', compact('teacherData', 'schoolHeadData', 'schoolYear', 'cotTemplates', 'epocDomains'));
    }

    /**
     * Term alignment check for the schedule wizard: given an observee,
     * school year and term, report that observee's past (non-cancelled)
     * observations so the supervisor can see whether the term aligns
     * (e.g. the ratee was already observed in the chosen term).
     */
    public function termCheck(Request $request)
    {
        $validated = $request->validate([
            'observation_type' => ['required', 'in:teacher_observation,school_head_observation'],
            'observee_id' => ['required', 'integer', 'min:1'],
            'school_year' => ['nullable', 'string', 'max:20'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);

        $observeeType = $validated['observation_type'] === 'teacher_observation'
            ? Teacher::class
            : SchoolHeadProfile::class;
        $schoolYear = $validated['school_year'] ?: $this->getCurrentSchoolYear();
        $quarter = $validated['quarter'] ?? null;

        $observee = $observeeType === Teacher::class
            ? Teacher::with('user')->find($validated['observee_id'])
            : SchoolHeadProfile::with('user')->find($validated['observee_id']);

        if (! $observee) {
            return response()->json(['message' => 'Ratee not found.'], 404);
        }

        $observeeName = $observee->user?->name;

        $inYear = Observation::where('observee_id', $validated['observee_id'])
            ->where('observee_type', $observeeType)
            ->where('status', '!=', 'cancelled')
            ->where('school_year', $schoolYear)
            ->orderByDesc('observation_date')
            ->get();

        $termLabels = [1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term'];

        $inTerm = $quarter
            ? $inYear->where('quarter', (int) $quarter)->values()
            : collect();

        $termsSummary = $inYear->groupBy(fn ($o) => $o->quarter ?? 0)
            ->map(fn ($group, $q) => [
                'quarter' => (int) $q,
                'label' => $termLabels[(int) $q] ?? 'No term set',
                'count' => $group->count(),
            ])
            ->sortBy('quarter')
            ->values();

        return response()->json([
            'observee_name' => $observeeName,
            'school_year' => $schoolYear,
            'quarter' => $quarter ? (int) $quarter : null,
            'quarter_label' => $quarter ? ($termLabels[(int) $quarter] ?? "Term {$quarter}") : null,
            'in_term_count' => $inTerm->count(),
            'in_term' => $inTerm->take(5)->map(fn ($o) => [
                'id' => $o->id,
                'date' => $o->observation_date ? $o->observation_date->format('M d, Y') : 'No date',
                'stage' => ucwords(str_replace('_', ' ', $o->stage ?? '')),
                'status' => ucwords(str_replace('_', ' ', $o->status ?? '')),
                'subject' => $o->subject,
                'observation_number' => $o->observation_number ? (int) $o->observation_number : null,
                'url' => route('supervisor.observations.show', $o),
            ])->values(),
            // Distinct observation numbers already used in the selected term,
            // so the wizard can highlight them on the number picker.
            'used_numbers' => $inTerm->map(fn ($o) => $o->observation_number ? (int) $o->observation_number : null)
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'terms_summary' => $termsSummary,
            'total_in_year' => $inYear->count(),
        ]);
    }

    /**
     * Store a newly created observation.
     */
    public function storeObservation(Request $request)
    {
        // Normalize empty strings to null for nullable integer/enum fields so
        // Laravel's nullable rule treats them as absent rather than failing
        // the subsequent integer/in rules.
        foreach (['school_head_id', 'quarter', 'observation_number', 'form_template_id', 'cot_indicator_version_id'] as $field) {
            if ($request->input($field) === '' || $request->input($field) === null) {
                $request->merge([$field => null]);
            }
        }

        // Teacher observations bypass the Post-Observation Conference wizard
        // step entirely: discard any conference scheduling payload up front so
        // it skips validation, picks up no defaults, and never creates a
        // post-conference record.
        if ($request->input('observation_type') === 'teacher_observation') {
            $request->merge([
                'schedule_conference' => false,
                'conference_date' => null,
                'conference_start_time' => null,
                'conference_end_time' => null,
                'conference_location' => null,
                'conference_mode' => null,
            ]);
        }

        $validator = validator($request->all(), [
            'observation_type' => ['required', 'in:teacher_observation,school_head_observation'],
            'observee_id' => ['required'],
            'observation_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'school_year' => ['nullable', 'string'],
            'quarter' => ['nullable', 'integer', 'min:1', 'max:3'],
            'observation_number' => ['nullable', 'integer', 'min:1', 'max:2'],
            'subject' => ['nullable', 'string'],
            'grade_level' => ['nullable', 'string'],
            'observation_mode' => ['nullable', 'in:in_person,virtual,hybrid'],
            'schedule_type' => ['required', 'in:scheduled,immediate'],
            'form_template_id' => ['nullable', 'integer'],
            'cot_indicator_version_id' => ['nullable', 'integer'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule_conference' => ['nullable', 'boolean'],
            'conference_date' => ['nullable', 'date'],
            'conference_start_time' => ['nullable', 'date_format:H:i'],
            'conference_end_time' => ['nullable', 'date_format:H:i'],
            'conference_location' => ['nullable', 'string', 'max:255'],
            'conference_mode' => ['nullable', 'in:in_person,virtual,hybrid'],
            'school_head_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $schoolYear = $request->input('school_year') ?? $this->getCurrentSchoolYear();

            // Only validate end_time > start_time when both values are present.
            if ($request->filled('start_time') && $request->filled('end_time')) {
                if ($request->input('end_time') <= $request->input('start_time')) {
                    $validator->errors()->add('end_time', 'The end time must be after the start time.');
                }
            }

            // Only validate conference_end_time > conference_start_time when both are present.
            if ($request->filled('conference_start_time') && $request->filled('conference_end_time')) {
                if ($request->input('conference_end_time') <= $request->input('conference_start_time')) {
                    $validator->errors()->add('conference_end_time', 'The conference end time must be after the conference start time.');
                }
            }

            // The chosen observation template must belong to the school year
            // and apply to the selected observation type.
            if ($request->filled('form_template_id')) {
                $template = FormTemplate::find($request->input('form_template_id'));

                if (! $template) {
                    $validator->errors()->add('form_template_id', 'The selected observation template does not exist.');
                } elseif ($template->school_year !== $schoolYear) {
                    $validator->errors()->add('form_template_id', "The selected template is not available for the {$schoolYear} school year.");
                } elseif ($template->observation_type && $template->observation_type !== $request->input('observation_type')) {
                    $validator->errors()->add('form_template_id', 'The selected template does not apply to the chosen observation type.');
                }
            }

            // The chosen COT template must be published, belong to the school
            // year, and apply to the selected observation type.
            // School Head observations use the EPOC instrument instead, so no
            // COT template validation is performed for that observation type.
            if ($request->filled('cot_indicator_version_id') && $request->input('observation_type') !== 'school_head_observation') {
                $cotTemplate = CotIndicatorVersion::find($request->input('cot_indicator_version_id'));

                if (! $cotTemplate) {
                    $validator->errors()->add('cot_indicator_version_id', 'The selected COT template does not exist.');
                } elseif (! $cotTemplate->isPublished()) {
                    $validator->errors()->add('cot_indicator_version_id', 'The selected COT template is not published.');
                } elseif ($cotTemplate->school_year !== $schoolYear) {
                    $validator->errors()->add('cot_indicator_version_id', "The selected COT template is not available for the {$schoolYear} school year.");
                } else {
                    $expectedRole = $request->input('observation_type') === 'teacher_observation' ? 'teacher' : 'school_head';
                    if ($cotTemplate->rateeRole() !== $expectedRole) {
                        $validator->errors()->add('cot_indicator_version_id', 'The selected COT template does not apply to the chosen observation type.');
                    }
                }
            }

            // Simple duplicate check: the same supervisor must not schedule the
            // same ratee twice on the same date at the same start time.
            if ($request->input('schedule_type') === 'scheduled'
                && $request->filled('start_time')
                && $request->filled('observation_date')
                && $request->filled('observee_id')) {
                $observeeType = $request->input('observation_type') === 'teacher_observation'
                    ? Teacher::class
                    : SchoolHeadProfile::class;

                $conflict = Observation::where('observer_id', Auth::id())
                    ->where('observee_id', $request->input('observee_id'))
                    ->where('observee_type', $observeeType)
                    ->whereDate('observation_date', $request->input('observation_date'))
                    ->where('start_time', $request->input('start_time'))
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($conflict) {
                    $validator->errors()->add('start_time', 'You already have an observation scheduled for this ratee at this time.');
                }
            }
        });

        $validated = $validator->validated();

        // Determine observee type and ID based on observation type
        $observeeType = $validated['observation_type'] === 'teacher_observation'
            ? Teacher::class
            : SchoolHeadProfile::class;

        // Determine status based on schedule type
        $status = $validated['schedule_type'] === 'scheduled' ? 'scheduled' : 'in_progress';
        $stage = $validated['schedule_type'] === 'scheduled' ? 'pre_observation_planning' : 'observation';

        $schoolYear = $validated['school_year'] ?? $this->getCurrentSchoolYear();

        // Use the supervisor-selected template when provided; otherwise fall
        // back to the active template for the school year / observation type.
        $activeTemplate = ! empty($validated['form_template_id'])
            ? FormTemplate::find($validated['form_template_id'])
            : $this->formTemplateService->getActiveTemplate($schoolYear, $validated['observation_type']);

        // Use the supervisor-selected COT template when provided; otherwise
        // resolve the published version for the school year / observee.
        // School Head observations use the EPOC instrument instead, so no COT
        // template is resolved or stored for that observation type.
        $cotIndicatorVersion = $validated['observation_type'] === 'school_head_observation'
            ? null
            : (! empty($validated['cot_indicator_version_id'])
                ? CotIndicatorVersion::find($validated['cot_indicator_version_id'])
                : $this->cotIndicatorService->getVersionModel($schoolYear));

        if ($observeeType === Teacher::class) {
            $observee = Teacher::find($validated['observee_id']);
            if ($observee) {
                $cotIndicatorVersion = $this->cotIndicatorService->resolveVersionForObservee(
                    $schoolYear,
                    'teacher',
                    $observee->career_stage,
                ) ?? $cotIndicatorVersion;
            }
        } elseif ($validated['observation_type'] !== 'school_head_observation') {
            $cotIndicatorVersion = $this->cotIndicatorService->resolveVersionForObservee(
                $schoolYear,
                'school_head',
                null,
            ) ?? $cotIndicatorVersion;
        }

        $observation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $validated['observee_id'],
            'observee_type' => $observeeType,
            'observation_type' => $validated['observation_type'],
            'observation_date' => $validated['observation_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'stage' => $stage,
            'notes' => $validated['notes'] ?? null,
            'status' => $status,
            'school_year' => $schoolYear,
            'quarter' => $validated['quarter'] ?? $this->getCurrentTerm(),
            'observation_number' => $validated['observation_number'] ?? 1,
            'subject' => $validated['subject'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'observation_mode' => $validated['observation_mode'] ?? 'in_person',
            'form_template_id' => $activeTemplate?->id,
            'cot_indicator_version_id' => $cotIndicatorVersion?->id,
            'school_head_id' => $validated['school_head_id'] ?? null,
        ]);

        // Post-Observation Conference handling:
        // - Teacher observations: the wizard step is bypassed, so no
        //   post-conference record is created at scheduling time (any
        //   conference payload was already discarded before validation).
        // - School Head observations: driven by the selected PPSSH template's
        //   requires_post_conference flag. No duplicate manual toggle.
        $isSchoolHeadObs = $validated['observation_type'] === 'school_head_observation';
        if ($isSchoolHeadObs) {
            $requiresPostConference = $cotIndicatorVersion?->requiresPostConference() ?? true;
            if ($requiresPostConference) {
                $observation->postConference()->create([
                    'conference_date' => $validated['conference_date'] ?? null,
                    'start_time' => $validated['conference_start_time'] ?? null,
                    'end_time' => $validated['conference_end_time'] ?? null,
                    'location' => $validated['conference_location'] ?? null,
                    'mode' => $validated['conference_mode'] ?? 'in_person',
                ]);
            }
        } else {
            // Teacher flow: conference inputs were stripped above, so this
            // block intentionally creates nothing.
        }

        app(AuditLogService::class)->log(
            'created', 'observations', (string) $observation->getKey(),
            "Created observation for {$observation->observee_type} #{$observation->observee_id}",
            'success', [], $observation->toArray()
        );

        // Send notification if scheduled
        if ($status === 'scheduled') {
            $observee = $observation->observee;
            if ($observee && $observee->user) {
                $observeeUser = $observee->user;
                $formattedDate = $observation->observation_date?->format('M d, Y') ?? 'No date';
                $observationLink = match (true) {
                    $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                    $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                    default => route('supervisor.observations.show', $observation->id),
                };

                $timeLabel = $observation->start_time_label;
                if ($timeLabel && $observation->end_time_label) {
                    $timeLabel .= ' - '.$observation->end_time_label;
                }
                $locationLabel = $observation->location;

                // In-app notification
                $this->notificationService->notifyObservationScheduled(
                    $observeeUser,
                    $formattedDate,
                    $observationLink,
                    $timeLabel,
                    $locationLabel
                );

                // Email notification (non-blocking: failures must not prevent redirect)
                try {
                    $observerName = Auth::user()->name;
                    $subject = 'ASPIRE - Classroom Observation Scheduled';
                    $emailBody = $this->buildObservationScheduledEmail($observeeUser->name, $observerName, $formattedDate, $observation->observation_type, $observationLink, $timeLabel, $locationLabel);
                    $this->mailerService->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue observation scheduled email: '.$e->getMessage());
                }
            }

            // Notify school head if assigned
            if ($observation->school_head_id) {
                $schoolHeadUser = \App\Models\User::find($observation->school_head_id);
                if ($schoolHeadUser) {
                    $shLink = route('school-head.observations.show', $observation->id);
                    $this->notificationService->notify(
                        $schoolHeadUser,
                        \App\Enums\NotificationType::OBSERVATION,
                        'Observation Assignment',
                        'You have been assigned to be present during a '.($observation->observation_type === 'teacher_observation' ? 'teacher' : 'school head').' observation on '.($formattedDate ?? 'No date').'.',
                        null,
                        $shLink
                    );
                }
            }
        }

        // Redirect based on schedule type
        if ($validated['schedule_type'] === 'scheduled') {
            return redirect()->route('supervisor.observations.preObservationPlanning', $observation->id)
                ->with('success', 'Observation has been scheduled successfully.');
        } else {
            return redirect()->route('supervisor.observations.observation', $observation->id)
                ->with('success', 'Observation has been created. Start the COT evaluation now.');
        }
    }

    /**
     * Create a linked School Head PPSSH observation for the given teacher observation.
     * The new observation is of type school_head_observation and is linked via
     * related_observation_id to the primary teacher COT observation.
     * The school head is pre-filled from the teacher observation's school_head_id.
     */
    public function createLinkedPpsshObservation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if ($observation->observation_type !== 'teacher_observation') {
            return back()->with('error', 'This action is only available for teacher observations.');
        }

        if ($observation->isLinkedObservation() || Observation::where('related_observation_id', $observation->id)->exists()) {
            return back()->with('error', 'A linked PPSSH observation already exists for this observation.');
        }

        // Use the same school head that was assigned to the teacher observation.
        // observations.school_head_id stores users.id, while school head
        // observations use SchoolHeadProfile id as observee_id.
        $schoolHeadId = $observation->school_head_id;
        $schoolHeadProfile = $schoolHeadId ? SchoolHeadProfile::where('user_id', $schoolHeadId)->first() : null;

        if (! $schoolHeadProfile) {
            return back()->with('error', 'No school head profile found for the assigned school head.');
        }

        // Determine the active PPSSH template for the current school year
        $schoolYear = $observation->school_year ?? $this->getCurrentSchoolYear();
        $activeTemplate = $this->formTemplateService->getActiveTemplate($schoolYear, 'school_head_observation');

        // Determine the COT indicator version to use for the SH observation
        // Resolve a published PPSSH version for the school year
        $cotIndicatorVersion = CotIndicatorVersion::where('school_year', $schoolYear)
            ->published()
            ->where('ratee_role', 'school_head')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        $observeeType = SchoolHeadProfile::class;
        $observeeId = $schoolHeadProfile->id;

        // Create the linked PPSSH observation.
        // The school head observes no one here — the supervisor observes the
        // school head directly — so the child carries no co-observer of its
        // own (school_head_id stays null, avoiding a duplicated confirmation
        // row for the same person). The schedule confirmation carries over
        // from the parent co-observation: the school head already confirmed
        // that exact schedule.
        $inheritedConfirmation = ($observation->school_head_confirmation_status ?? 'pending') === 'confirmed';

        $linkedObservation = Observation::create([
            'observer_id' => Auth::id(),
            'observer_type' => User::class,
            'observee_id' => $observeeId,
            'observee_type' => $observeeType,
            'observation_type' => 'school_head_observation',
            'observation_date' => $observation->observation_date,
            'start_time' => $observation->start_time ?? null,
            'end_time' => $observation->end_time ?? null,
            'location' => $observation->location ?? null,
            'stage' => 'pre_observation_planning',
            'notes' => 'Linked PPSSH observation for '.($observation->subject ?? 'observation #'.$observation->id),
            'status' => 'in_progress',
            'confirmation_status' => $inheritedConfirmation ? 'confirmed' : 'pending',
            'confirmed_at' => $inheritedConfirmation ? ($observation->school_head_confirmed_at ?? now()) : null,
            'school_year' => $schoolYear,
            'quarter' => $observation->quarter ?? $this->getCurrentTerm(),
            'observation_number' => 1,
            'subject' => $observation->subject ?? null,
            'grade_level' => $observation->grade_level ?? null,
            'observation_mode' => 'in_person',
            'form_template_id' => $activeTemplate?->id,
            'cot_indicator_version_id' => $cotIndicatorVersion?->id,
            'school_head_id' => null,
            'related_observation_id' => $observation->id,
        ]);

        // Post-observation Conference handling for SH observation
        $isSchoolHeadObs = true;
        $requiresPostConference = true; // PPSSH templates typically require it
        if ($requiresPostConference) {
            $linkedObservation->postConference()->create([
                'conference_date' => null,
                'start_time' => null,
                'end_time' => null,
                'location' => null,
                'mode' => 'in_person',
            ]);
        }

        app(AuditLogService::class)->log(
            'created', 'observations', (string) $linkedObservation->getKey(),
            "Created linked PPSSH observation #{$linkedObservation->getKey()} for teacher observation #{$observation->getKey()}",
            'success', [], $linkedObservation->toArray()
        );

        // Notify the school head (observee) about their scheduled evaluation.
        $shUser = $schoolHeadProfile->user ?? User::find($schoolHeadId);
        if ($shUser) {
            $shLink = route('school-head.observations.show', $linkedObservation->id);
            $this->notificationService->notify(
                $shUser,
                \App\Enums\NotificationType::OBSERVATION,
                'School Head Evaluation Scheduled',
                'A school head (PPSSH) evaluation linked to teacher observation #'.$observation->getKey().' has been scheduled for you on '.($observation->observation_date?->format('M d, Y') ?? 'No date').'.'
                .($inheritedConfirmation ? ' Your confirmation from the co-observation carries over — you are marked confirmed.' : ' Please confirm your availability.'),
                null,
                $shLink
            );
        }

        // Notify the teacher (observee) that a linked PPSSH observation was created
        $teacher = $observation->observee;
        if ($teacher && $teacher->user) {
            $teacherLink = route('teacher.observations.show', $observation->id);
            $this->notificationService->notify(
                $teacher->user,
                \App\Enums\NotificationType::OBSERVATION,
                'Linked PPSSH Observation Created',
                'A School Head post-observation conference has been created and linked to your observation on ' . ($observation->observation_date?->format('M d, Y') ?? 'No date') . '.',
                null,
                $teacherLink
            );
        }

        return redirect()->route('supervisor.observations.preObservationPlanning', $linkedObservation->id)
            ->with('success', 'Linked PPSSH observation has been created successfully. Proceed to Pre-Observation Planning.');
    }

    /**
     * Display list of observations.
     */
    public function observations(Request $request)
    {
        $user = Auth::user();

        $query = Observation::query()
            ->with(['observee.user', 'postConference', 'schoolHead', 'epocEvaluation'])
            ->withCount([
                'cotRatings',
                'cotRatings as ai_ready_count' => fn ($q) => $q->whereHas('aiFeedback'),
            ])
            ->where('observer_id', $user->id);

        // Search (kept inside a single where-group so observer_id scope is preserved)
        if ($search = $request->search) {
            // Search by observee name (morphTo workaround)
            $teacherIds = Teacher::whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->pluck('id');

            $schoolHeadIds = SchoolHeadProfile::whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->pluck('id');

            $query->where(function ($outer) use ($search, $teacherIds, $schoolHeadIds) {
                $outer->where('subject', 'like', "%{$search}%")
                    ->orWhere('grade_level', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");

                if ($teacherIds->isNotEmpty()) {
                    $outer->orWhere(function ($q) use ($teacherIds) {
                        $q->where('observee_type', Teacher::class)
                            ->whereIn('observee_id', $teacherIds);
                    });
                }

                if ($schoolHeadIds->isNotEmpty()) {
                    $outer->orWhere(function ($q) use ($schoolHeadIds) {
                        $q->where('observee_type', SchoolHeadProfile::class)
                            ->whereIn('observee_id', $schoolHeadIds);
                    });
                }
            });
        }

        $observations = $query
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->stage, function ($q, $stage) {
                $q->where('stage', $stage);
            })
            ->when($request->observation_type, function ($q, $type) {
                $q->where('observation_type', $type);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('observation_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('observation_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        // Stats for the header
        $baseQuery = Observation::where('observer_id', $user->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_progress' => (clone $baseQuery)->whereIn('stage', ['pre_observation_planning', 'observation'])->where('status', '!=', 'cancelled')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        return view('supervisor.observations.index', compact('observations', 'stats'));
    }

    /**
     * Show observation details
     */
    public function showObservation(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $observation->load([
            'observee.user',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings.aiFeedback',
            'epocEvaluation.ratings',
            'cancelledBy',
            'schoolHead',
        ]);

        // Linked PPSSH evaluation of the co-observer (if the supervisor
        // created one from this teacher observation).
        $linkedObservation = Observation::where('related_observation_id', $observation->id)->first();

        return view('supervisor.observations.show', compact('observation', 'linkedObservation'));
    }

    /**
     * Show the cancellation form for an observation.
     */
    public function showCancelForm(Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->canCancel()) {
            return redirect()->route('supervisor.observations.show', $observation)
                ->with('error', 'This observation cannot be cancelled in its current state.');
        }

        return view('supervisor.observations.cancel', compact('observation'));
    }

    /**
     * Cancel an observation.
     */
    public function cancel(Request $request, Observation $observation)
    {
        $this->authorizeObservation($observation);

        if (! $observation->canCancel()) {
            return redirect()->route('supervisor.observations.show', $observation)
                ->with('error', 'This observation cannot be cancelled in its current state.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'in:teacher_request,supervisor_initiative,conflict_in_schedule,health_reason,insufficient_documentation,technical_issues,weather_emergency,other'],
            'cancellation_other_reason' => ['nullable', 'string', 'max:500'],
            'internal_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $validated['cancellation_reason'] === 'other'
            ? ($validated['cancellation_other_reason'] ?? 'Other')
            : $validated['cancellation_reason'];

        $observation->cancel($reason, $validated['internal_note'] ?? null);

        app(AuditLogService::class)->log(
            'cancelled', 'observations', (string) $observation->getKey(),
            "Observation #{$observation->getKey()} cancelled: {$reason}",
            'success', [], $observation->toArray(),
            ['cancellation_reason' => $reason]
        );

        // Notify the observee
        $observee = $observation->observee;
        if ($observee && $observee->user) {
            $observeeUser = $observee->user;
            $observationLink = match (true) {
                $observee instanceof Teacher => route('teacher.observations.show', $observation->id),
                $observee instanceof SchoolHeadProfile => route('school-head.observations.show', $observation->id),
                default => route('supervisor.observations.show', $observation->id),
            };

            $this->notificationService->notifyObservationCancelled(
                $observeeUser,
                $observationLink
            );

            $observerName = Auth::user()->name;
            $subject = 'ASPIRE - Observation Cancelled';
            $emailBody = $this->buildObservationCancelledEmail(
                $observeeUser->name,
                $observerName,
                $observation->observation_date?->format('M d, Y') ?? 'No date',
                $observation->observation_type,
                str_replace('_', ' ', ucwords($reason)),
                $observationLink
            );
            try {
                $this->mailerService->sendGenericEmailLater($observeeUser->email, $observeeUser->name, $subject, $emailBody);
            } catch (\Throwable $e) {
                Log::error('Failed to queue observation cancelled email: '.$e->getMessage());
            }
        }

        return redirect()->route('supervisor.observations.index')
            ->with('success', 'Observation has been cancelled successfully.');
    }

    private function buildObservationScheduledEmail(string $observeeName, string $observerName, string $date, string $observationType, string $link, ?string $time = null, ?string $location = null): string
    {
        $typeLabel = $observationType === 'teacher_observation' ? 'Teacher Observation' : 'School Head Observation';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Observation Scheduled</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e40af; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .detail-label { font-weight: 600; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
                    <p>Automated Supervision Platform for Instructional Reform & Excellence</p>
                </div>
                <div class='content'>
                    <h2>Hello {$observeeName},</h2>
                    <p>A classroom observation has been scheduled for you. Please review the details below:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 16px 0;'>
                        <tr><td class='detail-label'>Type:</td><td>{$typeLabel}</td></tr>
                        <tr><td class='detail-label'>Scheduled By:</td><td>{$observerName}</td></tr>
                        <tr><td class='detail-label'>Observation Date:</td><td>{$date}</td></tr>
                        " . ($time ? "<tr><td class='detail-label'>Time:</td><td>{$time}</td></tr>" : '') . "
                        " . ($location ? "<tr><td class='detail-label'>Location:</td><td>{$location}</td></tr>" : '') . "
                    </table>
                    <p style='margin-top: 20px;'><strong>What to expect:</strong></p>
                    <ul>
                        <li>Pre-Observation Planning: You may be required to submit a lesson plan and answer pre-observation questions.</li>
                        <li>Classroom Observation: The actual observation will take place on the scheduled date.</li>
                        <li>Post-Conference: A feedback session will follow after the observation.</li>
                    </ul>
                    <p>Please ensure you are prepared for the observation on the scheduled date. If you have any questions, contact your supervisor.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>&copy; 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function buildObservationCancelledEmail(string $observeeName, string $observerName, string $date, string $observationType, string $reason, string $link): string
    {
        $typeLabel = $observationType === 'teacher_observation' ? 'Teacher Observation' : 'School Head Observation';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Observation Cancelled</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f9fafb; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .detail-label { font-weight: 600; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>ASPIRE System</h1>
                    <p>Automated Supervision Platform for Instructional Reform & Excellence</p>
                </div>
                <div class='content'>
                    <h2>Hello {$observeeName},</h2>
                    <p>We regret to inform you that your {$typeLabel} scheduled for <strong>{$date}</strong> has been <strong>cancelled</strong>.</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 16px 0;'>
                        <tr><td class='detail-label'>Type:</td><td>{$typeLabel}</td></tr>
                        <tr><td class='detail-label'>Cancelled By:</td><td>{$observerName}</td></tr>
                        <tr><td class='detail-label'>Original Date:</td><td>{$date}</td></tr>
                        <tr><td class='detail-label'>Reason:</td><td>{$reason}</td></tr>
                    </table>
                    <p>If you have any questions, please contact your supervisor directly.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from the ASPIRE system. Please do not reply to this email.</p>
                    <p>&copy; 2026 ASPIRE - Department of Education Sagay City, Negros Occidental, Philippines</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
