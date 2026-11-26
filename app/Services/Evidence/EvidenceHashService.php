<?php

namespace App\Services\Evidence;

class EvidenceHashService
{
    /**
     * Compute the SHA-256 fingerprint of a file on local disk.
     *
     * This always runs on the server against the actual file content. The
     * frontend never supplies a hash, and none is ever trusted from it —
     * a hash submitted by a client could claim anything.
     */
    public function hashFile(string $absolutePath): string
    {
        return hash_file('sha256', $absolutePath);
    }
}
