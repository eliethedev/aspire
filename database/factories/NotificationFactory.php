<?php

namespace Database\Factories;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $type = fake()->randomElement(NotificationType::cases());

        return [
            'user_id' => User::factory(),
            'type' => $type->value,
            'priority' => $type->defaultPriority()->value,
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(10),
            'link' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function ofType(NotificationType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type->value,
            'priority' => $type->defaultPriority()->value,
        ]);
    }

    public function ofPriority(NotificationPriority $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority->value,
        ]);
    }
}
