<?php

namespace App\Http\Controllers;

use App\Models\BlockchainTransaction;
use App\Models\Evidence;
use App\Services\Blockchain\EvidenceRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockchainController extends Controller
{
    /**
     * Generate (or return the existing) commitment data for an evidence
     * record, for the browser to submit to the smart contract. No
     * blockchain transaction happens here — this only prepares the data.
     */
    public function prepare(Evidence $evidence, EvidenceRegistrationService $service): JsonResponse
    {
        Gate::authorize('registerOnBlockchain', $evidence);

        if (! request()->user()->hasVerifiedWallet()) {
            return response()->json([
                'message' => 'Link and verify a wallet in Settings before registering evidence on the blockchain.',
            ], 422);
        }

        return response()->json($service->prepare($evidence));
    }

    /**
     * Record a registration transaction the browser has already submitted
     * to MetaMask. The evidence remains "Pending" until this transaction
     * is independently confirmed — see refreshTransaction().
     */
    public function storeTransaction(Request $request, Evidence $evidence, EvidenceRegistrationService $service): JsonResponse
    {
        Gate::authorize('registerOnBlockchain', $evidence);

        if (! $evidence->on_chain_evidence_id) {
            return response()->json([
                'message' => 'This evidence has not been prepared for registration yet.',
            ], 422);
        }

        $validated = $request->validate([
            'transaction_hash' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{64}$/'],
        ]);

        $transaction = $service->recordSubmittedTransaction(
            $evidence,
            $request->user(),
            $validated['transaction_hash'],
        );

        return response()->json([
            'transaction' => [
                'id' => $transaction->id,
                'status' => $transaction->transaction_status->value,
                'transaction_hash' => $transaction->transaction_hash,
            ],
        ], 201);
    }

    /**
     * Independently re-check a submitted transaction's real on-chain
     * state via the RPC provider, and update its status if it has
     * resolved. Safe to call repeatedly while a transaction is pending.
     */
    public function refreshTransaction(
        Evidence $evidence,
        BlockchainTransaction $blockchainTransaction,
        EvidenceRegistrationService $service
    ): JsonResponse {
        Gate::authorize('view', $evidence);

        abort_unless($blockchainTransaction->evidence_id === $evidence->id, 404);

        $transaction = $service->refreshTransaction($blockchainTransaction);

        return response()->json([
            'status' => $transaction->transaction_status->value,
            'block_number' => $transaction->block_number,
            'evidence_registration_status' => $evidence->fresh()->registration_status->value,
        ]);
    }
}
