<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;

class CalendarEventService
{
    private const COLORS = [
        'observation' => '#6366f1',
        'pre_conference' => '#8b5cf6',
        'post_conference' => '#10b981',
    ];

    public function forSupervisor(User $user): array
    {
        $observations = Observation::query()
            ->with(['observee.user', 'observer', 'preConference', 'postConference'])
            ->where('observer_id', $user->id)
            ->where('observer_type', User::class)
            ->orderBy('observation_date')
            ->get();

        return $this->events($observations, 'supervisor.observations.show');
    }

    public function forTeacher(User $user): array
    {
        $teacherId = $user->teacher?->id;

        if (! $teacherId) {
            return [];
        }

        $observations = Observation::query()
            ->with(['observee.user', 'observer', 'preConference', 'postConference'])
            ->where('observee_id', $teacherId)
            ->where('observee_type', Teacher::class)
            ->orderBy('observation_date')
            ->get();

        return $this->events($observations, 'teacher.observations.show');
    }

    public function forSchoolHead(User $user): array
    {
        $profile = $user->schoolHeadProfile;

        $observations = Observation::query()
            ->with(['observee.user', 'observer', 'preConference', 'postConference'])
            ->where(function ($q) use ($user, $profile) {
                $q->where('observer_id', $user->id)
                    ->where('observer_type', User::class);

                $q->orWhere('school_head_id', $user->id);

                if ($profile) {
                    $q->orWhere(function ($sub) use ($profile) {
                        $sub->where('observee_id', $profile->id)
                            ->where('observee_type', SchoolHeadProfile::class);
                    });
                }
            })
            ->orderBy('observation_date')
            ->get();

        return $this->events($observations, 'school-head.observations.show');
    }

    public function forAdmin(): array
    {
        $observations = Observation::query()
            ->with(['observee.user', 'observer', 'preConference', 'postConference'])
            ->orderBy('observation_date')
            ->get();

        return $this->events($observations, 'admin.observations.show');
    }

    private function events(Collection $observations, string $showRouteName): array
    {
        $events = [];

        foreach ($observations as $observation) {
            // Every observation appears on the calendar regardless of status
            // (scheduled, in progress, completed, or cancelled). Records
            // without a set date fall back to their creation date so they
            // are never silently dropped from the calendar.
            $eventDate = $observation->observation_date ?? $observation->created_at;

            if ($eventDate) {
                $events[] = $this->observationEvent($observation, $showRouteName, $eventDate);
            }

            if ($observation->relationLoaded('preConference') && $observation->preConference?->conference_date) {
                $events[] = $this->conferenceEvent($observation, $observation->preConference, 'pre', $showRouteName);
            }

            if ($observation->relationLoaded('postConference') && $observation->postConference?->conference_date) {
                $events[] = $this->conferenceEvent($observation, $observation->postConference, 'post', $showRouteName);
            }
        }

        usort($events, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return $events;
    }

    private function observationEvent(Observation $observation, string $showRouteName, mixed $date = null): array
    {
        $date ??= $observation->observation_date;

        return array_merge($this->basePayload($observation, $showRouteName), [
            'id' => 'observation-'.$observation->id,
            'type' => 'observation',
            'type_label' => 'Observation',
            'color' => self::COLORS['observation'],
            'date' => $date->format('Y-m-d'),
            'start_time' => $observation->start_time_label,
            'end_time' => $observation->end_time_label,
            'location' => $observation->location,
        ]);
    }

    private function conferenceEvent(Observation $observation, mixed $conference, string $kind, string $showRouteName): array
    {
        $type = $kind === 'pre' ? 'pre_conference' : 'post_conference';

        return array_merge($this->basePayload($observation, $showRouteName), [
            'id' => $type.'-'.$observation->id,
            'type' => $type,
            'type_label' => $kind === 'pre' ? 'Pre-Conference' : 'Post-Conference',
            'color' => self::COLORS[$type],
            'date' => $conference->conference_date->format('Y-m-d'),
            'start_time' => $conference->start_time_label,
            'end_time' => $conference->end_time_label,
            'location' => $conference->location ?: $observation->location,
        ]);
    }

    private function basePayload(Observation $observation, string $showRouteName): array
    {
        return [
            'title' => $this->principalName($observation, 'observee'),
            'subtitle' => $observation->isSchoolHeadObservation() ? 'School Head Observation' : 'Teacher Observation',
            'link' => route($showRouteName, $observation->id),
            'stage' => $observation->stage,
            'stage_label' => self::stageLabel($observation->stage),
            'status' => $observation->status,
            'status_label' => self::statusLabel($observation->status),
            'confirmation_status' => $observation->confirmation_status,
            'score' => $observation->overall_score,
            'observee_name' => $this->principalName($observation, 'observee'),
            'observer_name' => $observation->observer?->name ?? 'Unknown',
        ];
    }

    private function principalName(Observation $observation, string $side): string
    {
        $model = $side === 'observee' ? $observation->observee : $observation->observer;

        if (! $model) {
            return 'Unknown';
        }

        return data_get($model, 'user.name') ?: $model->name ?? 'Unknown';
    }

    private static function stageLabel(?string $stage): ?string
    {
        if (! $stage) {
            return null;
        }

        return match ($stage) {
            'pre_observation_planning' => 'Pre-Observation Planning',
            'pre_conference' => 'Pre-Conference',
            'observation' => 'Classroom Observation',
            'post_conference' => 'Post-Conference',
            default => ucfirst($stage),
        };
    }

    private static function statusLabel(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return match ($status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'cot_completed' => 'COT Completed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }
}