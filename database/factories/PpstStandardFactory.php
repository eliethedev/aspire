<?php

namespace Database\Factories;

use App\Models\PpstStandard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PpstStandard>
 */
class PpstStandardFactory extends Factory
{
    protected $model = PpstStandard::class;

    public function definition(): array
    {
        $domain = fake()->randomElement([
            'Domain 1: Content Knowledge and Pedagogy',
            'Domain 2: Learning Environment',
            'Domain 3: Diversity of Learners',
            'Domain 4: Curriculum and Planning',
            'Domain 5: Assessment and Reporting',
            'Domain 6: Community Linkages and Professional Engagement',
            'Domain 7: Personal Growth and Professional Development',
        ]);

        return [
            'domain' => $domain,
            'strand' => fake()->numerify('#.#'),
            'indicator_code' => fake()->unique()->numerify('#.#.#'),
            'description' => fake()->sentence(10),
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
