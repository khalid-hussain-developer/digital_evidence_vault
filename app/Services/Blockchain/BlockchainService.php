<?php

namespace App\Services\Blockchain;

use Illuminate\Support\Facades\Http;

/**
 * Reads transaction state directly from the blockchain via a JSON-RPC
 * provider (Infura/Alchemy) — this is how the backend independently
 * confirms what a transaction submitted through the user's own MetaMask
 * actually did, rather than trusting whatever the browser reports.
 *
 * This service never signs or submits a transaction — it only reads.
 */
class BlockchainService
{
    /**
     * Look up a transaction's on-chain receipt.
     *
     * Returns null when the RPC provider isn't configured yet, the call
     * fails, or the transaction hasn't been mined yet (still pending) —
     * callers should treat null as "not confirmed yet," never as failure.
     *
     * @return array{status: 'confirmed'|'failed', block_number: int}|null
     */
    public function fetchReceipt(string $transactionHash): ?array
    {
        $rpcUrl = config('blockchain.rpc_url');

        if (! $rpcUrl) {
            return null;
        }

        $response = Http::post($rpcUrl, [
            'jsonrpc' => '2.0',
            'method' => 'eth_getTransactionReceipt',
            'params' => [$transactionHash],
            'id' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $receipt = $response->json('result');

        if (! $receipt || ! isset($receipt['status'], $receipt['blockNumber'])) {
            return null;
        }

        return [
            'status' => hexdec($receipt['status']) === 1 ? 'confirmed' : 'failed',
            'block_number' => hexdec($receipt['blockNumber']),
        ];
    }
}
