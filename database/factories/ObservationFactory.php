<?php

namespace Database\Factories;

use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Observation>
 */
class ObservationFactory extends Factory
{
    protected $model = Observation::class;

    public function definition(): array
    {
        $observer = User::factory()->create(['role' => 'supervisor']);
        $observee = Teacher::factory()->create();

        return [
            'observer_id' => $observer->id,
            'observer_type' => User::class,
            'observee_id' => $observee->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->addDay(),
            'stage' => 'pre_observation_planning',
            'status' => 'scheduled',
            'overall_score' => null,
            'notes' => null,
            'school_year' => (now()->format('Y')) . '-' . (now()->format('Y') + 1),
            'quarter' => 1,
            'observation_number' => 1,
            'subject' => 'Mathematics',
            'grade_level' => '7',
            'observation_mode' => 'in_person',
            'evidence_files' => null,
            'form_template_id' => null,
            'cancellation_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'confirmation_status' => 'pending',
            'rejection_reason' => null,
            'rejection_notes' => null,
            'confirmed_at' => null,
            'rejected_at' => null,
        ];
    }

    public function forObserver(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'observer_id' => $user->id,
            'observer_type' => User::class,
        ]);
    }

    public function forObservee(Model $observee): static
    {
        return $this->state(fn (array $attributes) => [
            'observee_id' => $observee->id,
            'observee_type' => $observee::class,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'confirmation_status' => 'pending',
            'cancellation_reason' => 'conflict_in_schedule',
            'cancelled_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'stage' => 'post_conference',
            'status' => 'completed',
        ]);
    }
}
