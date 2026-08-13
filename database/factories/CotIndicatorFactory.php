<?php

namespace Database\Factories;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CotIndicator>
 */
class CotIndicatorFactory extends Factory
{
    protected $model = CotIndicator::class;

    public function definition(): array
    {
        return [
            'version_id' => CotIndicatorVersion::factory(),
            'code' => fake()->bothify('?#.#.#'),
            'description' => fake()->sentence(10),
            'domain' => fake()->randomElement([
                'Content Knowledge and Pedagogy',
                'Learning Environment',
                'Diversity of Learners',
                'Curriculum and Planning',
                'Assessment and Reporting',
                'Community Linkages and Professional Engagement',
                'Personal Growth and Professional Development',
            ]),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
