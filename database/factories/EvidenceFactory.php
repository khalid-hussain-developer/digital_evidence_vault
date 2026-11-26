<?php

namespace Database\Factories;

use App\Enums\EvidenceStatus;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'evidence_id' => 'EVD-'.Str::upper(Str::random(10)),
            'case_id' => CaseRecord::factory(),
            'uploaded_by' => User::factory(),
            'current_custodian_id' => User::factory(),
            'original_filename' => fake()->word().'.pdf',
            'stored_filename' => Str::uuid().'.pdf',
            'file_path' => 'evidence/test/'.Str::uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(1024, 2_000_000),
            'sha256_hash' => hash('sha256', Str::random(32)),
            'registration_status' => EvidenceStatus::Pending,
        ];
    }
}
