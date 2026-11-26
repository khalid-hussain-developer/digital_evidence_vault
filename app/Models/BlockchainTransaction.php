<?php

namespace App\Models;

use App\Enums\BlockchainTransactionStatus;
use App\Enums\BlockchainTransactionType;
use Database\Factories\BlockchainTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'evidence_id',
    'initiated_by',
    'transaction_hash',
    'transaction_type',
    'network',
    'block_number',
    'transaction_status',
])]
class BlockchainTransaction extends Model
{
    /** @use HasFactory<BlockchainTransactionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_type' => BlockchainTransactionType::class,
            'transaction_status' => BlockchainTransactionStatus::class,
            'block_number' => 'integer',
        ];
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class, 'evidence_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * The block explorer URL for this transaction, for linking out from
     * the UI — never used as a source of truth, only a convenience link.
     */
    public function explorerUrl(): string
    {
        return rtrim(config('blockchain.explorer_url'), '/')."/tx/{$this->transaction_hash}";
    }
}
