<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            // The bytes32 identifier this evidence is registered under
            // on-chain — random and unrelated to the human-readable
            // evidence_id, so it leaks nothing if seen in a public
            // blockchain explorer.
            $table->string('on_chain_evidence_id', 66)->nullable()->unique()->after('registration_status');

            // Kept off-chain and never shown to unauthorized users. Needed
            // to recompute the commitment during verification — without
            // it, even the correct file would not reproduce the same
            // commitment, since the salt is private per the brief's
            // privacy-conscious commitment design (section 11.2).
            $table->string('commitment_salt', 66)->nullable()->after('on_chain_evidence_id');

            // keccak256(abi.encode(sha256_hash, commitment_salt)) — the
            // only one of these three values that is actually stored
            // on-chain.
            $table->string('blockchain_commitment', 66)->nullable()->after('commitment_salt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->dropColumn(['on_chain_evidence_id', 'commitment_salt', 'blockchain_commitment']);
        });
    }
};
