<?php

namespace Database\Factories;

use App\Enums\BlockchainTransactionStatus;
use App\Enums\BlockchainTransactionType;
use App\Models\BlockchainTransaction;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockchainTransaction>
 */
class BlockchainTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'evidence_id' => Evidence::factory(),
            'initiated_by' => User::factory(),
            'transaction_hash' => '0x'.bin2hex(random_bytes(32)),
            'transaction_type' => BlockchainTransactionType::Registration,
            'network' => 'sepolia',
            'block_number' => null,
            'transaction_status' => BlockchainTransactionStatus::Submitted,
        ];
    }

    public function confirmed(int $blockNumber = 1000000): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_status' => BlockchainTransactionStatus::Confirmed,
            'block_number' => $blockNumber,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_status' => BlockchainTransactionStatus::Failed,
        ]);
    }
}
