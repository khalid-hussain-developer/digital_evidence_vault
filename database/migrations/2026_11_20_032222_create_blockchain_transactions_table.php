<?php

use App\Enums\BlockchainTransactionStatus;
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
        Schema::create('blockchain_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidence')->restrictOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->restrictOnDelete();
            $table->string('transaction_hash', 66);
            $table->string('transaction_type');
            $table->string('network')->default('sepolia');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->string('transaction_status')->default(BlockchainTransactionStatus::Submitted->value);
            $table->timestamps();

            $table->unique(['network', 'transaction_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_transactions');
    }
};
