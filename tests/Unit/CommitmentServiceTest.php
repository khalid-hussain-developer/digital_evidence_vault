<?php

use App\Services\Blockchain\CommitmentService;

test('computeCommitment matches a value independently verified against ethers.js', function () {
    // This exact input/output pair was cross-checked outside this test
    // suite using ethers.js's own AbiCoder and keccak256 — the same
    // library the frontend uses to talk to the contract — to confirm
    // this PHP implementation produces byte-identical commitments to
    // what Solidity will compute on-chain.
    $hash = '0x'.str_repeat('11', 32);
    $salt = '0x'.str_repeat('22', 32);
    $expectedCommitment = '0x3e92e0db88d6afea9edc4eedf62fffa4d92bcdfc310dccbe943747fe8302e871';

    $commitment = (new CommitmentService)->computeCommitment($hash, $salt);

    expect($commitment)->toBe($expectedCommitment);
});

test('generateSalt and generateOnChainEvidenceId each return a random 32-byte hex value', function () {
    $service = new CommitmentService;

    $salt = $service->generateSalt();
    $onChainId = $service->generateOnChainEvidenceId();

    expect($salt)->toStartWith('0x')->and(strlen($salt))->toBe(66);
    expect($onChainId)->toStartWith('0x')->and(strlen($onChainId))->toBe(66);
    expect($salt)->not->toBe($onChainId);
    expect($service->generateSalt())->not->toBe($salt);
});

test('the same hash and salt always produce the same commitment, and a different salt changes it', function () {
    $service = new CommitmentService;
    $hash = '0x'.str_repeat('aa', 32);
    $salt = $service->generateSalt();

    $commitmentA = $service->computeCommitment($hash, $salt);
    $commitmentB = $service->computeCommitment($hash, $salt);
    $commitmentC = $service->computeCommitment($hash, $service->generateSalt());

    expect($commitmentA)->toBe($commitmentB);
    expect($commitmentA)->not->toBe($commitmentC);
});
