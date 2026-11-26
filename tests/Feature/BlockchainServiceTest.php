<?php

use App\Services\Blockchain\BlockchainService;
use Illuminate\Support\Facades\Http;

/**
 * These tests simulate the shape of a real Ethereum JSON-RPC response
 * using Http::fake() — they confirm this service parses a receipt
 * correctly, but they are not a live Sepolia confirmation. There is no
 * substitute for testing against the real network once an RPC URL and a
 * deployed contract exist.
 */
test('a confirmed transaction receipt is reported as confirmed', function () {
    config(['blockchain.rpc_url' => 'https://example-rpc.test']);

    Http::fake([
        'example-rpc.test' => Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'status' => '0x1',
                'blockNumber' => '0xf4240',
            ],
        ]),
    ]);

    $receipt = (new BlockchainService)->fetchReceipt('0xabc123');

    expect($receipt)->toBe(['status' => 'confirmed', 'block_number' => 1000000]);
});

test('a failed transaction receipt is reported as failed', function () {
    config(['blockchain.rpc_url' => 'https://example-rpc.test']);

    Http::fake([
        'example-rpc.test' => Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'status' => '0x0',
                'blockNumber' => '0xf4240',
            ],
        ]),
    ]);

    $receipt = (new BlockchainService)->fetchReceipt('0xabc123');

    expect($receipt['status'])->toBe('failed');
});

test('a transaction that has not been mined yet returns null, not an error', function () {
    config(['blockchain.rpc_url' => 'https://example-rpc.test']);

    Http::fake([
        'example-rpc.test' => Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => null,
        ]),
    ]);

    expect((new BlockchainService)->fetchReceipt('0xabc123'))->toBeNull();
});

test('an unreachable RPC provider returns null rather than throwing', function () {
    config(['blockchain.rpc_url' => 'https://example-rpc.test']);

    Http::fake([
        'example-rpc.test' => Http::response(['error' => 'internal error'], 500),
    ]);

    expect((new BlockchainService)->fetchReceipt('0xabc123'))->toBeNull();
});

test('no RPC call is made when the provider has not been configured yet', function () {
    config(['blockchain.rpc_url' => null]);

    Http::fake();

    expect((new BlockchainService)->fetchReceipt('0xabc123'))->toBeNull();
    Http::assertNothingSent();
});
