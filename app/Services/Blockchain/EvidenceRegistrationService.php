<?php

namespace App\Services\Blockchain;

use App\Enums\BlockchainTransactionStatus;
use App\Enums\BlockchainTransactionType;
use App\Enums\EvidenceStatus;
use App\Models\BlockchainTransaction;
use App\Models\Evidence;
use App\Models\User;

/**
 * Orchestrates registering an evidence file's commitment on the
 * EvidenceVault contract. The actual on-chain transaction is always
 * signed and submitted by the user's own MetaMask in the browser — this
 * service only prepares the data the browser needs, records what it
 * submitted, and later confirms what really happened on-chain.
 */
class EvidenceRegistrationService
{
    public function __construct(
        private readonly CommitmentService $commitmentService,
        private readonly BlockchainService $blockchainService,
    ) {}

    /**
     * Generate (or reuse) the commitment data this evidence needs before
     * a registration transaction can be submitted. Idempotent: calling
     * this again before a transaction exists returns the same values
     * instead of generating a new salt, so a page refresh never produces
     * a commitment that no longer matches what's shown in the UI.
     *
     * @return array{onChainEvidenceId: string, commitment: string}
     */
    public function prepare(Evidence $evidence): array
    {
        if (! $evidence->on_chain_evidence_id) {
            $salt = $this->commitmentService->generateSalt();

            $evidence->forceFill([
                'on_chain_evidence_id' => $this->commitmentService->generateOnChainEvidenceId(),
                'commitment_salt' => $salt,
                'blockchain_commitment' => $this->commitmentService->computeCommitment($evidence->sha256_hash, $salt),
            ])->save();
        }

        return [
            'onChainEvidenceId' => $evidence->on_chain_evidence_id,
            'commitment' => $evidence->blockchain_commitment,
        ];
    }

    /**
     * Record that a registration transaction has been submitted from the
     * browser. The evidence stays "Pending" until the transaction is
     * later confirmed — submitting a transaction is not the same as it
     * succeeding.
     */
    public function recordSubmittedTransaction(Evidence $evidence, User $initiator, string $transactionHash): BlockchainTransaction
    {
        return BlockchainTransaction::create([
            'evidence_id' => $evidence->id,
            'initiated_by' => $initiator->id,
            'transaction_hash' => $transactionHash,
            'transaction_type' => BlockchainTransactionType::Registration,
            'network' => config('blockchain.network'),
            'transaction_status' => BlockchainTransactionStatus::Submitted,
        ]);
    }

    /**
     * Re-check a submitted transaction against the real blockchain and
     * update its status. Does nothing if the transaction was already
     * resolved, or if the chain hasn't mined it yet.
     */
    public function refreshTransaction(BlockchainTransaction $transaction): BlockchainTransaction
    {
        if ($transaction->transaction_status !== BlockchainTransactionStatus::Submitted) {
            return $transaction;
        }

        $receipt = $this->blockchainService->fetchReceipt($transaction->transaction_hash);

        if (! $receipt) {
            return $transaction;
        }

        $confirmed = $receipt['status'] === 'confirmed';

        $transaction->update([
            'transaction_status' => $confirmed ? BlockchainTransactionStatus::Confirmed : BlockchainTransactionStatus::Failed,
            'block_number' => $receipt['block_number'],
        ]);

        if ($transaction->transaction_type === BlockchainTransactionType::Registration) {
            $transaction->evidence->update([
                'registration_status' => $confirmed ? EvidenceStatus::Registered : EvidenceStatus::Failed,
            ]);
        }

        return $transaction;
    }
}
