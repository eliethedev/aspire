<?php

namespace App\Enums;

/**
 * Canonical grade level codes used across teacher profiles and observations.
 *
 * Observation and teacher grade_level columns are free-text strings, so
 * `labelFor()` falls back to the raw value (e.g. "Grade 7") for anything
 * that is not one of the known enum codes.
 */
enum GradeLevel: string
{
    case Elementary = 'elementary';
    case JuniorHigh = 'junior_high';
    case SeniorHigh = 'senior_high';

    public function label(): string
    {
        return match ($this) {
            self::Elementary => 'Elementary',
            self::JuniorHigh => 'Junior High School',
            self::SeniorHigh => 'Senior High School',
        };
    }

    /**
     * User-friendly label for a raw grade level value.
     * Returns null for blank values and the raw value for unknown codes.
     */
    public static function labelFor(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        return self::tryFrom($value)?->label() ?? $value;
    }
}