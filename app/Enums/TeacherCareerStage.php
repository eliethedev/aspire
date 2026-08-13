<?php

namespace App\Enums;

/**
 * Career stages of DepEd teaching personnel.
 *
 * The career stage drives which COT instrument (indicator set + rating scale)
 * is used for a teacher's observations. It is orthogonal to the permission
 * role stored on `users.role` (teacher/supervisor/school_head/admin) and is
 * derived from `teachers.position`.
 */
enum TeacherCareerStage: string
{
    case TEACHER_I_III = 'teacher_i_iii';
    case TEACHER_IV_VII = 'teacher_iv_vii';
    case MASTER_TEACHER_I_II = 'master_teacher_i_ii';
    case MASTER_TEACHER_III_V = 'master_teacher_iii_v';

    /**
     * All stages in official precedence order.
     *
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return [
            self::TEACHER_I_III,
            self::TEACHER_IV_VII,
            self::MASTER_TEACHER_I_II,
            self::MASTER_TEACHER_III_V,
        ];
    }

    /**
     * Human-readable label shown to users.
     */
    public function label(): string
    {
        return config("career_stages.stages.{$this->value}", $this->name);
    }

    /**
     * Free-text position strings that map onto this stage.
     *
     * @return array<int, string>
     */
    public function positionAliases(): array
    {
        return config("career_stages.position_aliases.{$this->value}", []);
    }

    /**
     * Resolve the career stage from a free-text position string.
     * Returns null when the position is not a recognised teaching stage
     * (e.g. head teacher, school head, or blank).
     */
    public static function fromPosition(?string $position): ?self
    {
        if ($position === null || trim($position) === '') {
            return null;
        }

        $normalized = strtolower(trim((string) preg_replace('/[^A-Za-z0-9 ]/', ' ', $position)));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        foreach (self::ordered() as $stage) {
            foreach ($stage->positionAliases() as $alias) {
                $aliasNormalized = strtolower(trim((string) preg_replace('/[^A-Za-z0-9 ]/', ' ', $alias)));
                if ($normalized === $aliasNormalized) {
                    return $stage;
                }
            }
        }

        return null;
    }

    /**
     * Select options for form dropdowns: value => label.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::ordered() as $stage) {
            $options[$stage->value] = $stage->label();
        }

        return $options;
    }
}
