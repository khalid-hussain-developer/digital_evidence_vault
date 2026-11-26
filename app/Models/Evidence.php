<?php

namespace App\Models;

use App\Enums\EvidenceStatus;
use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'evidence_id',
    'case_id',
    'uploaded_by',
    'current_custodian_id',
    'original_filename',
    'stored_filename',
    'file_path',
    'mime_type',
    'file_size',
    'sha256_hash',
    'registration_status',
    'on_chain_evidence_id',
    'commitment_salt',
    'blockchain_commitment',
])]
class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory;

    /**
     * Use the public evidence identifier in URLs instead of the internal
     * auto-increment id, so sensitive records aren't exposed as sequential
     * numbers in links and logs.
     */
    public function getRouteKeyName(): string
    {
        return 'evidence_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_status' => EvidenceStatus::class,
            'file_size' => 'integer',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_custodian_id');
    }

    public function blockchainTransactions(): HasMany
    {
        return $this->hasMany(BlockchainTransaction::class, 'evidence_id');
    }

    /**
     * Limit the query to evidence belonging to cases the given user can see.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('case', function (Builder $query) use ($user) {
            $query->visibleTo($user);
        });
    }
}
