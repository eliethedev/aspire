<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * Determine which teachers under a supervisor's school require attention and
 * why. Used both on the supervisor dashboard ("Teachers that need attention")
 * and on the teachers list ("needs attention" UI indicators).
 *
 * A teacher is flagged as needing attention when any of the following hold:
 *   - low_score    : average of scored observations is below 4 ("Satisfactory")
 *   - declining    : the most recent scored observation is lower than the prior one
 *   - unobserved   : the teacher has no scored observation yet (never evaluated)
 *   - pending_action: an observation is mid-pipeline waiting on the supervisor
 */
class TeacherAttentionService
{
    public const ATTENTION_THRESHOLD = 4.0;

    /**
     * Compute attention diagnostics for every teacher in the supervisor's school.
     *
     * @return array<string, array{teacher: Teacher, flags: array<int, array{key: string, label: string, description: string, icon: string, tone: string}>, summary: string, level: string}>
     */
    public function forSchool(int $schoolId): array
    {
        $teachers = Teacher::with(['user', 'school', 'subjects'])
            ->whereHas('user', fn ($q) => $q->where('school_id', $schoolId))
            ->get();

        $map = [];
        foreach ($teachers as $teacher) {
            $map[$teacher->id] = $teacher;
        }

        $observations = Observation::where('observee_type', Teacher::class)
            ->whereIn('observee_id', $map ? array_keys($map) : [0])
            ->orderBy('observation_date')
            ->get(['observee_id', 'status', 'overall_score', 'observation_date']);

        $grouped = $observations->groupBy('observee_id');

        $result = [];
        foreach ($map as $teacher) {
            $flags = $this->flagsFor($teacher, $grouped->get($teacher->id, collect()));
            $result[$teacher->id] = [
                'teacher' => $teacher,
                'flags' => $flags,
                'summary' => $this->summarize($flags),
                'level' => $this->level($flags),
            ];
        }

        return $result;
    }

    /**
     * Whether a single teacher (given their scored/pending observations) needs attention.
     */
    public function needsAttention(Teacher $teacher): bool
    {
        return count($this->flagsFor($teacher, $teacher->observations()->get(['status', 'overall_score', 'observation_date']))) > 0;
    }

    /**
     * @return array<int, array{key: string, label: string, description: string, icon: string, tone: string}>
     */
    protected function flagsFor(Teacher $teacher, Collection $observations): array
    {
        $flags = [];

        $scored = $observations->whereNotNull('overall_score')->values();
        $pending = $observations->whereNotIn('status', ['completed', 'cancelled']);

        if ($scored->isEmpty() && $pending->isEmpty()) {
            $flags[] = [
                'key' => 'unobserved',
                'label' => 'Needs first observation',
                'description' => 'No evaluation on record yet.',
                'icon' => 'fas fa-user-plus',
                'tone' => 'amber',
            ];
        } else {
            if ($scored->isNotEmpty()) {
                $average = round((float) $scored->avg('overall_score'), 2);
                if ($average < self::ATTENTION_THRESHOLD) {
                    $flags[] = [
                        'key' => 'low_score',
                        'label' => 'Below satisfactory',
                        'description' => "Average COT score of {$average} across {$scored->count()} scored evaluation(s).",
                        'icon' => 'fas fa-triangle-exclamation',
                        'tone' => 'red',
                    ];
                }

                $latest = $scored->last();
                $previous = $scored->count() >= 2 ? $scored[$scored->count() - 2] : null;
                if ($previous !== null && (float) $latest->overall_score < (float) $previous->overall_score) {
                    $flags[] = [
                        'key' => 'declining',
                        'label' => 'Declining trend',
                        'description' => 'Latest score dropped vs. the previous evaluation.',
                        'icon' => 'fas fa-arrow-trend-down',
                        'tone' => 'orange',
                    ];
                }
            }

            if ($pending->isNotEmpty()) {
                $flags[] = [
                    'key' => 'pending_action',
                    'label' => 'Needs your action',
                    'description' => 'An observation is waiting in the pipeline ('.ucwords(str_replace('_', ' ', $pending->first()->status)).').',
                    'icon' => 'fas fa-list-check',
                    'tone' => 'blue',
                ];
            }
        }

        return $flags;
    }

    protected function summarize(array $flags): string
    {
        if (empty($flags)) {
            return 'On track';
        }

        $labels = array_map(fn ($f) => $f['label'], $flags);

        return implode(' + ', $labels);
    }

    protected function level(array $flags): string
    {
        if (empty($flags)) {
            return 'ok';
        }

        $tones = array_column($flags, 'tone');

        return in_array('red', $tones, true) ? 'high' : (in_array('orange', $tones, true) ? 'medium' : 'low');
    }
}
