<?php

namespace Database\Factories;

use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportMessage>
 */
class SupportMessageFactory extends Factory
{
    protected $model = SupportMessage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(SupportMessage::TYPES),
            'subject' => fake()->sentence(5),
            'message' => fake()->paragraph(3),
            'status' => SupportMessage::STATUS_OPEN,
            'admin_note' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    public function bug(): static
    {
        return $this->state(fn (array $attributes) => ['type' => SupportMessage::TYPE_BUG]);
    }

    public function feedback(): static
    {
        return $this->state(fn (array $attributes) => ['type' => SupportMessage::TYPE_FEEDBACK]);
    }

    public function suggestion(): static
    {
        return $this->state(fn (array $attributes) => ['type' => SupportMessage::TYPE_SUGGESTION]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => ['status' => SupportMessage::STATUS_OPEN]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => ['status' => SupportMessage::STATUS_IN_PROGRESS]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportMessage::STATUS_RESOLVED,
            'resolved_by' => User::factory()->admin(),
            'resolved_at' => now(),
        ]);
    }
}
