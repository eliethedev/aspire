<?php

namespace Database\Factories;

use App\Models\CotIndicatorVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CotIndicatorVersion>
 */
class CotIndicatorVersionFactory extends Factory
{
    protected $model = CotIndicatorVersion::class;

    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2000, 2099);

        return [
            'school_year' => $year.'-'.($year + 1),
            'label' => fake()->sentence(3),
            'is_default' => false,
            'status' => CotIndicatorVersion::STATUS_DRAFT,
            'ratee_role' => CotIndicatorVersion::DEFAULT_RATEE_ROLE,
            'observer_roles' => CotIndicatorVersion::DEFAULT_OBSERVER_ROLES,
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'instrument' => CotIndicatorVersion::DEFAULT_INSTRUMENT,
            'career_stage' => null,
            'rating_scale' => null,
            'rating_scale_css' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CotIndicatorVersion::STATUS_PUBLISHED,
            'is_default' => true,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CotIndicatorVersion::STATUS_ARCHIVED,
            'is_default' => false,
        ]);
    }

    public function forCareerStage(string $careerStage): static
    {
        return $this->state(fn (array $attributes) => [
            'career_stage' => $careerStage,
        ]);
    }

    public function withRatingScale(array $scale, array $css = []): static
    {
        return $this->state(fn (array $attributes) => [
            'rating_scale' => $scale,
            'rating_scale_css' => $css ?: null,
        ]);
    }
}
