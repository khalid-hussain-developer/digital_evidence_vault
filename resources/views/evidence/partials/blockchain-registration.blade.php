@php
    $latestTransaction = $evidence->blockchainTransactions->first();
    $hasPendingTransaction = $latestTransaction && $latestTransaction->transaction_status === \App\Enums\BlockchainTransactionStatus::Submitted;
@endphp

<div class="card">
    <div class="card-body">
        <h3 class="h6 mb-3">Blockchain Registration</h3>

        <p class="mb-2">
            <span class="badge {{ $evidence->registration_status->badgeClass() }}">
                {{ $evidence->registration_status->label() }}
            </span>
        </p>

        @if ($evidence->blockchain_commitment)
            <dl class="row small mb-3">
                <dt class="col-5 text-secondary">Commitment</dt>
                <dd class="col-7 text-break d-flex align-items-center gap-2">
                    <code class="small">{{ \Illuminate\Support\Str::limit($evidence->blockchain_commitment, 18) }}</code>
                    <button
                        type="button"
                        class="btn btn-sm btn-link p-0 copy-button"
                        data-copy-value="{{ $evidence->blockchain_commitment }}"
                        title="Copy full commitment"
                    >
                        <i class="bi bi-clipboard"></i>
                    </button>
                </dd>
            </dl>
        @endif

        @forelse ($evidence->blockchainTransactions as $transaction)
            <div class="border rounded-3 p-2 mb-2 small">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge {{ $transaction->transaction_status->badgeClass() }}">
                        {{ $transaction->transaction_status->label() }}
                    </span>
                    <a href="{{ $transaction->explorerUrl() }}" target="_blank" rel="noopener">
                        View on Etherscan <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <code class="text-break">{{ \Illuminate\Support\Str::limit($transaction->transaction_hash, 22) }}</code>
                    <button
                        type="button"
                        class="btn btn-sm btn-link p-0 copy-button"
                        data-copy-value="{{ $transaction->transaction_hash }}"
                        title="Copy full transaction hash"
                    >
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>

                @if ($transaction->transaction_status === \App\Enums\BlockchainTransactionStatus::Submitted)
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary mt-2 blockchain-refresh-button"
                        data-refresh-url="{{ route('evidence.blockchain.transactions.refresh', [$evidence, $transaction]) }}"
                    >
                        <i class="bi bi-arrow-clockwise me-1"></i>Check Confirmation Status
                    </button>
                @endif
            </div>
        @empty
            <p class="small text-secondary mb-3">Not yet registered on the blockchain.</p>
        @endforelse

        @if ($canRegisterOnBlockchain && $evidence->registration_status !== \App\Enums\EvidenceStatus::Registered && ! $hasPendingTransaction)
            @if (! auth()->user()->hasVerifiedWallet())
                <p class="small text-secondary mb-0">
                    <a href="{{ route('profile.edit') }}">Link and verify a wallet</a> in Settings to register evidence on the blockchain.
                </p>
            @elseif (! $contractAddress)
                <p class="small text-secondary mb-0">The blockchain contract is not configured yet.</p>
            @else
                <button type="button" id="blockchain-register-button" class="btn btn-sm btn-primary">
                    <i class="bi bi-link-45deg me-1"></i>Register on Blockchain
                </button>
                <p id="blockchain-register-status" class="small mt-2 mb-0"></p>
            @endif
        @endif
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const csrfToken = @json(csrf_token());

            document.querySelectorAll('.blockchain-refresh-button').forEach((button) => {
                button.addEventListener('click', async () => {
                    window.setButtonLoading(button, true);

                    await window.refreshTransactionStatus({
                        refreshUrl: button.dataset.refreshUrl,
                        csrfToken,
                        onResult: (ok, result) => {
                            if (ok && result.status !== 'submitted') {
                                window.location.reload();
                                return;
                            }

                            window.setButtonLoading(button, false);
                        },
                    });
                });
            });

            const registerButton = document.getElementById('blockchain-register-button');
            if (!registerButton) {
                return;
            }

            const statusLine = document.getElementById('blockchain-register-status');

            registerButton.addEventListener('click', async () => {
                window.setButtonLoading(registerButton, true);

                await window.registerEvidenceOnBlockchain({
                    prepareUrl: @json(route('evidence.blockchain.prepare', $evidence)),
                    storeTransactionUrl: @json(route('evidence.blockchain.transactions.store', $evidence)),
                    contractAddress: @json($contractAddress),
                    csrfToken,
                    onStatus: (state, text) => {
                        statusLine.textContent = text;
                        statusLine.className = 'small mt-2 mb-0 ' + (
                            state === 'error' ? 'text-danger' : state === 'submitted' ? 'text-success' : 'text-secondary'
                        );

                        if (state === 'submitted') {
                            setTimeout(() => window.location.reload(), 1500);
                        } else if (state === 'error') {
                            window.setButtonLoading(registerButton, false);
                        }
                    },
                });
            });
        })();
    </script>
@endpush
