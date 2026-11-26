<?php

use App\Models\User;
use App\Services\Blockchain\WalletVerificationService;
use Elliptic\EC;
use kornrunner\Keccak;

/**
 * Signs a message exactly the way MetaMask's personal_sign / signMessage
 * does, using a raw private key — standing in for "the user signing in
 * MetaMask" in tests, since we can't drive a real browser wallet here.
 *
 * @return array{address: string, signature: string}
 */
function signAsWallet(string $privateKeyHex, string $message): array
{
    $ec = new EC('secp256k1');
    $keyPair = $ec->keyFromPrivate($privateKeyHex, 'hex');

    $publicKeyBody = substr($keyPair->getPublic(false, 'hex'), 2);
    $address = '0x'.substr(Keccak::hash(hex2bin($publicKeyBody), 256), -40);

    $prefixed = "\x19Ethereum Signed Message:\n".strlen($message).$message;
    $messageHash = Keccak::hash($prefixed, 256);

    $signature = $keyPair->sign($messageHash);

    $r = str_pad($signature->r->toString(16), 64, '0', STR_PAD_LEFT);
    $s = str_pad($signature->s->toString(16), 64, '0', STR_PAD_LEFT);
    $v = str_pad(dechex(27 + $signature->recoveryParam), 2, '0', STR_PAD_LEFT);

    return [
        'address' => $address,
        'signature' => '0x'.$r.$s.$v,
    ];
}

test('a user can verify ownership of a wallet with a correctly signed challenge', function () {
    $user = User::factory()->create();
    $privateKey = bin2hex(random_bytes(32));

    $service = app(WalletVerificationService::class);
    $message = $service->generateChallenge($user);

    $wallet = signAsWallet($privateKey, $message);

    $verified = $service->verify($user, $wallet['address'], $wallet['signature']);

    expect($verified)->toBeTrue();
    expect($user->wallet_address)->toBe($wallet['address']);
    expect($user->wallet_verified_at)->not->toBeNull();
    expect($user->hasVerifiedWallet())->toBeTrue();
});

test('a signature from a different wallet than the one claimed is rejected', function () {
    $user = User::factory()->create();

    $service = app(WalletVerificationService::class);
    $message = $service->generateChallenge($user);

    $signerWallet = signAsWallet(bin2hex(random_bytes(32)), $message);
    $claimedAddress = '0x'.bin2hex(random_bytes(20));

    $verified = $service->verify($user, $claimedAddress, $signerWallet['signature']);

    expect($verified)->toBeFalse();
    expect($user->wallet_verified_at)->toBeNull();
});

test('verification fails once the challenge has already been used', function () {
    $user = User::factory()->create();
    $privateKey = bin2hex(random_bytes(32));

    $service = app(WalletVerificationService::class);
    $message = $service->generateChallenge($user);
    $wallet = signAsWallet($privateKey, $message);

    expect($service->verify($user, $wallet['address'], $wallet['signature']))->toBeTrue();
    expect($service->verify($user, $wallet['address'], $wallet['signature']))->toBeFalse();
});

test('the challenge endpoint returns a message for an authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('wallet.challenge'));

    $response->assertOk();
    $response->assertJsonStructure(['message']);
    expect($response->json('message'))->toContain($user->email);
});

test('the verify endpoint links the wallet to the user on a valid signature', function () {
    $user = User::factory()->create();
    $privateKey = bin2hex(random_bytes(32));

    $challengeResponse = $this->actingAs($user)->getJson(route('wallet.challenge'));
    $wallet = signAsWallet($privateKey, $challengeResponse->json('message'));

    $response = $this->actingAs($user)->postJson(route('wallet.verify'), $wallet);

    $response->assertOk();
    $response->assertJson(['verified' => true, 'wallet_address' => $wallet['address']]);
});

test('the verify endpoint rejects an invalid signature', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson(route('wallet.challenge'));

    $response = $this->actingAs($user)->postJson(route('wallet.verify'), [
        'address' => '0x'.bin2hex(random_bytes(20)),
        'signature' => '0x'.bin2hex(random_bytes(65)),
    ]);

    $response->assertStatus(422);
    $response->assertJson(['verified' => false]);
});
