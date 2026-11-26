<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseRecord>
 */
class CaseRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_number' => 'CASE-'.fake()->unique()->numerify('####-###'),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => CaseStatus::Open,
            'created_by' => User::factory(),
        ];
    }

    public function status(CaseStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
