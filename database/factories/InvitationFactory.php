<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invited_by' => null,
            'school_id' => null,
            'token' => Invitation::generateToken(),
            'email' => fake()->unique()->safeEmail(),
            'role' => 'teacher',
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'is_used' => false,
            'resend_count' => 0,
            'last_sent_at' => null,
            'ip_address' => null,
            'user_agent' => null,
        ];
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_used' => true,
            'accepted_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
