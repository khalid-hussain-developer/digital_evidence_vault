<?php

namespace App\Services\Blockchain;

use kornrunner\Keccak;

/**
 * Computes the privacy-conscious commitment the brief describes (section
 * 11.2): the SHA-256 hash and a random salt stay off-chain in MySQL; only
 * keccak256(abi.encode(hash, salt)) is ever sent to the smart contract.
 *
 * Solidity's abi.encode of two bytes32 values is exactly their
 * concatenation — each is already a full 32-byte EVM word, so no padding
 * or length prefix is added. This was independently confirmed against
 * ethers.js's own AbiCoder before being relied on here: encoding two
 * known bytes32 values both ways produced byte-identical output, and
 * hashing that output through this class's keccak256 and through
 * ethers.js's keccak256 produced the same 32-byte result.
 */
class CommitmentService
{
    public function generateSalt(): string
    {
        return '0x'.bin2hex(random_bytes(32));
    }

    /**
     * A random identifier for the on-chain record, unrelated to the
     * application's human-readable evidence_id — it reveals nothing if
     * read from a public block explorer.
     */
    public function generateOnChainEvidenceId(): string
    {
        return '0x'.bin2hex(random_bytes(32));
    }

    public function computeCommitment(string $sha256HashHex, string $saltHex): string
    {
        $hashBytes = hex2bin($this->stripPrefix($sha256HashHex));
        $saltBytes = hex2bin($this->stripPrefix($saltHex));

        return '0x'.Keccak::hash($hashBytes.$saltBytes, 256);
    }

    private function stripPrefix(string $hex): string
    {
        return str_starts_with($hex, '0x') ? substr($hex, 2) : $hex;
    }
}
