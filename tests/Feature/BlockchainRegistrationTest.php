<?php

use App\Enums\EvidenceStatus;
use App\Enums\UserRole;
use App\Models\BlockchainTransaction;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function verifiedWalletUser(UserRole $role): User
{
    return User::factory()->role($role)->create([
        'wallet_address' => '0x'.bin2hex(random_bytes(20)),
        'wallet_verified_at' => now(),
    ]);
}

function evidenceFor(User $owner): Evidence
{
    $case = CaseRecord::factory()->create(['created_by' => $owner->id]);
    $case->assignedUsers()->attach($owner->id, ['assigned_by' => $owner->id]);

    return Evidence::factory()->create([
        'case_id' => $case->id,
        'uploaded_by' => $owner->id,
        'current_custodian_id' => $owner->id,
    ]);
}

test('an authorized user with a verified wallet can prepare evidence for registration', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);

    $response = $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));

    $response->assertOk();
    $response->assertJsonStructure(['onChainEvidenceId', 'commitment']);

    $evidence->refresh();
    expect($evidence->on_chain_evidence_id)->not->toBeNull();
    expect($evidence->commitment_salt)->not->toBeNull();
    expect($evidence->blockchain_commitment)->not->toBeNull();
});

test('preparing evidence twice returns the same commitment instead of generating a new one', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);

    $first = $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));
    $second = $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));

    expect($first->json())->toBe($second->json());
});

test('a user without a verified wallet can not prepare evidence for registration', function () {
    $investigator = User::factory()->role(UserRole::Investigator)->create();
    $evidence = evidenceFor($investigator);

    $response = $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));

    $response->assertStatus(422);
    expect($evidence->fresh()->on_chain_evidence_id)->toBeNull();
});

test('a user who is not assigned to the case can not prepare its evidence for registration', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $outsider = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);

    $this->actingAs($outsider)->postJson(route('evidence.blockchain.prepare', $evidence))->assertForbidden();
});

test('a forensic officer can not register evidence on the blockchain', function () {
    $officer = verifiedWalletUser(UserRole::ForensicOfficer);
    $evidence = evidenceFor($officer);

    $this->actingAs($officer)->postJson(route('evidence.blockchain.prepare', $evidence))->assertForbidden();
});

test('a submitted transaction hash is recorded but the evidence stays pending until confirmed', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);

    $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));

    $hash = '0x'.str_repeat('ab', 32);
    $response = $this->actingAs($investigator)->postJson(
        route('evidence.blockchain.transactions.store', $evidence),
        ['transaction_hash' => $hash]
    );

    $response->assertCreated();
    expect(BlockchainTransaction::where('transaction_hash', $hash)->exists())->toBeTrue();
    expect($evidence->fresh()->registration_status)->toBe(EvidenceStatus::Pending);
});

test('a transaction can not be recorded before the evidence has been prepared', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);

    $response = $this->actingAs($investigator)->postJson(
        route('evidence.blockchain.transactions.store', $evidence),
        ['transaction_hash' => '0x'.str_repeat('ab', 32)]
    );

    $response->assertStatus(422);
});

test('an invalid transaction hash is rejected', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);
    $this->actingAs($investigator)->postJson(route('evidence.blockchain.prepare', $evidence));

    $response = $this->actingAs($investigator)->postJson(
        route('evidence.blockchain.transactions.store', $evidence),
        ['transaction_hash' => 'not-a-real-hash']
    );

    $response->assertJsonValidationErrors('transaction_hash');
});

test('refreshing a confirmed transaction updates both the transaction and the evidence status', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);
    $transaction = BlockchainTransaction::factory()->create([
        'evidence_id' => $evidence->id,
        'initiated_by' => $investigator->id,
    ]);

    config(['blockchain.rpc_url' => 'https://example-rpc.test']);
    Http::fake([
        'example-rpc.test' => Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => ['status' => '0x1', 'blockNumber' => '0x64'],
        ]),
    ]);

    $response = $this->actingAs($investigator)->postJson(
        route('evidence.blockchain.transactions.refresh', [$evidence, $transaction])
    );

    $response->assertOk();
    $response->assertJson(['status' => 'confirmed', 'evidence_registration_status' => 'registered']);
    expect($evidence->fresh()->registration_status)->toBe(EvidenceStatus::Registered);
});

test('refreshing a still-pending transaction leaves its status unchanged', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidence = evidenceFor($investigator);
    $transaction = BlockchainTransaction::factory()->create([
        'evidence_id' => $evidence->id,
        'initiated_by' => $investigator->id,
    ]);

    config(['blockchain.rpc_url' => 'https://example-rpc.test']);
    Http::fake([
        'example-rpc.test' => Http::response(['jsonrpc' => '2.0', 'id' => 1, 'result' => null]),
    ]);

    $response = $this->actingAs($investigator)->postJson(
        route('evidence.blockchain.transactions.refresh', [$evidence, $transaction])
    );

    $response->assertOk();
    $response->assertJson(['status' => 'submitted']);
    expect($evidence->fresh()->registration_status)->toBe(EvidenceStatus::Pending);
});

test('a reviewer who can view the evidence may check a transaction status but could not have registered it', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $reviewer = verifiedWalletUser(UserRole::Reviewer);
    $evidence = evidenceFor($investigator);
    $evidence->case->assignedUsers()->attach($reviewer->id, ['assigned_by' => $investigator->id]);

    $transaction = BlockchainTransaction::factory()->create([
        'evidence_id' => $evidence->id,
        'initiated_by' => $investigator->id,
    ]);

    config(['blockchain.rpc_url' => 'https://example-rpc.test']);
    Http::fake(['example-rpc.test' => Http::response(['result' => null])]);

    $this->actingAs($reviewer)
        ->postJson(route('evidence.blockchain.transactions.refresh', [$evidence, $transaction]))
        ->assertOk();

    $this->actingAs($reviewer)
        ->postJson(route('evidence.blockchain.prepare', $evidence))
        ->assertForbidden();
});

test('a transaction belonging to a different evidence record can not be refreshed through this evidence', function () {
    $investigator = verifiedWalletUser(UserRole::Investigator);
    $evidenceA = evidenceFor($investigator);
    $evidenceB = evidenceFor($investigator);

    $transaction = BlockchainTransaction::factory()->create([
        'evidence_id' => $evidenceB->id,
        'initiated_by' => $investigator->id,
    ]);

    $this->actingAs($investigator)
        ->postJson(route('evidence.blockchain.transactions.refresh', [$evidenceA, $transaction]))
        ->assertNotFound();
});
