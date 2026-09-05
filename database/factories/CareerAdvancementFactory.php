<?php

namespace Database\Factories;

use App\Models\CareerAdvancement;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CareerAdvancement>
 */
class CareerAdvancementFactory extends Factory
{
    protected $model = CareerAdvancement::class;

    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'supervisor_id' => User::factory(),
            'from_career_stage' => 'teacher_i_iii',
            'to_career_stage' => 'teacher_iv_vii',
            'type' => CareerAdvancement::TYPE_ALLOW,
            'status' => CareerAdvancement::STATUS_PENDING_APPROVAL,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => CareerAdvancement::STATUS_PENDING_APPROVAL]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => CareerAdvancement::STATUS_APPROVED,
            'school_head_approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => CareerAdvancement::STATUS_REJECTED,
            'school_head_rejected_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => CareerAdvancement::STATUS_CANCELLED]);
    }
}