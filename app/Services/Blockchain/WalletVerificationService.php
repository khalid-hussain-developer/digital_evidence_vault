<?php

namespace App\Services\Blockchain;

use App\Models\User;
use Elliptic\EC;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use kornrunner\Keccak;
use Throwable;

/**
 * Proves a user controls a MetaMask wallet before the application trusts
 * any address they claim as theirs — the "signed nonce challenge" from the
 * brief's security requirements.
 *
 * The flow: generate a one-time challenge message, the user signs it in
 * MetaMask (which never exposes their private key to us), and we verify
 * the signature here using the same ECDSA math Ethereum itself uses to
 * recover a signer's address. We never ask for, receive, or store a
 * private key at any point.
 */
class WalletVerificationService
{
    private const NONCE_TTL_MINUTES = 10;

    /**
     * Create a fresh challenge for the user to sign in MetaMask, and
     * remember the nonce server-side so verify() can check against it.
     */
    public function generateChallenge(User $user): string
    {
        $nonce = Str::random(32);

        Cache::put($this->cacheKey($user), $nonce, now()->addMinutes(self::NONCE_TTL_MINUTES));

        return $this->challengeMessage($user, $nonce);
    }

    /**
     * Verify a signature against the user's currently pending challenge.
     * On success, the wallet address is recorded on the user as verified.
     */
    public function verify(User $user, string $claimedAddress, string $signatureHex): bool
    {
        $nonce = Cache::get($this->cacheKey($user));

        if (! $nonce) {
            return false;
        }

        $message = $this->challengeMessage($user, $nonce);
        $recoveredAddress = $this->recoverAddress($message, $signatureHex);

        if (! $recoveredAddress || ! hash_equals(strtolower($claimedAddress), strtolower($recoveredAddress))) {
            return false;
        }

        Cache::forget($this->cacheKey($user));

        $user->forceFill([
            'wallet_address' => $recoveredAddress,
            'wallet_verified_at' => now(),
        ])->save();

        return true;
    }

    private function challengeMessage(User $user, string $nonce): string
    {
        return "Digital Evidence Vault wallet verification\n".
            "Account: {$user->email}\n".
            "Nonce: {$nonce}\n".
            'This signature only proves wallet ownership. It authorizes nothing else.';
    }

    private function cacheKey(User $user): string
    {
        return "wallet-verification-nonce:{$user->id}";
    }

    /**
     * Recover the address that produced an Ethereum personal_sign signature
     * over the given message, following the exact scheme MetaMask uses:
     * keccak256("\x19Ethereum Signed Message:\n" + len(message) + message),
     * then ECDSA public-key recovery, then keccak256(publicKey)'s last 20 bytes.
     */
    private function recoverAddress(string $message, string $signatureHex): ?string
    {
        $signatureHex = str_starts_with($signatureHex, '0x') ? substr($signatureHex, 2) : $signatureHex;

        if (strlen($signatureHex) !== 130) {
            return null;
        }

        $r = substr($signatureHex, 0, 64);
        $s = substr($signatureHex, 64, 64);
        $v = hexdec(substr($signatureHex, 128, 2));
        $recoveryParam = $v >= 27 ? $v - 27 : $v;

        if (! in_array($recoveryParam, [0, 1], true)) {
            return null;
        }

        $prefixed = "\x19Ethereum Signed Message:\n".strlen($message).$message;
        $messageHash = Keccak::hash($prefixed, 256);

        try {
            $publicKeyPoint = (new EC('secp256k1'))->recoverPubKey($messageHash, ['r' => $r, 's' => $s], $recoveryParam);
        } catch (Throwable) {
            return null;
        }

        // Uncompressed public key is "04" + 32-byte X + 32-byte Y; the
        // address is the last 20 bytes of keccak256(X || Y) — the leading
        // "04" marker byte is not part of the hash.
        $publicKeyBody = substr($publicKeyPoint->encode('hex', false), 2);
        $addressHash = Keccak::hash(hex2bin($publicKeyBody), 256);

        return '0x'.substr($addressHash, -40);
    }
}
