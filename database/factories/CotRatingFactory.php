<?php

namespace Database\Factories;

use App\Models\CotRating;
use App\Models\Observation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CotRating>
 */
class CotRatingFactory extends Factory
{
    protected $model = CotRating::class;

    public function definition(): array
    {
        return [
            'observation_id' => Observation::factory(),
            'indicator_code' => fake()->bothify('COT-###-??'),
            'domain' => fake()->randomElement([
                'Content Knowledge and Pedagogy',
                'Learning Environment',
                'Diversity of Learners',
                'Curriculum and Planning',
                'Assessment and Reporting',
                'Community Linkages and Professional Engagement',
                'Personal Growth and Professional Development',
            ]),
            'indicator' => fake()->sentence(8),
            'rating' => fake()->randomElement([2, 3, 4, 5, 6]),
            'not_observed' => false,
            'comments' => fake()->sentence(),
        ];
    }

    public function notObserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => null,
            'not_observed' => true,
        ]);
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
            'not_observed' => false,
        ]);
    }
}
