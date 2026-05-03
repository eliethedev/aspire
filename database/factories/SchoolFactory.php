<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        $slug = str()->slug($name);
        
        return [
            'name' => $name,
            'slug' => $slug,
            'domain' => $this->faker->optional(0.3)->domainName(),
            'subdomain' => $this->faker->optional(0.7)->lexify('?????'),
            'settings' => [
                'features' => [
                    'observations' => true,
                    'cot_ratings' => true,
                    'predictions' => true,
                    'feedback' => true,
                ],
                'limits' => [
                    'max_users' => $this->faker->numberBetween(10, 500),
                    'max_observations_per_month' => $this->faker->numberBetween(100, 5000),
                ],
            ],
            'is_active' => true,
            'trial_ends_at' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 year'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->addDays(30),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_ends_at' => now()->subDays(1),
        ]);
    }
}
