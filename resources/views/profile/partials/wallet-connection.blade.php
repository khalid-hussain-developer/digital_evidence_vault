<section>
    <header class="mb-3">
        <h2 class="h6 mb-1">{{ __('Blockchain Wallet') }}</h2>
        <p class="small text-secondary mb-0">
            {{ __('Linking a wallet lets you register and transfer evidence on the Ethereum Sepolia testnet. Your private key is never seen by this application. MetaMask signs a one-time message to prove you own the wallet.') }}
        </p>
    </header>

    @if ($user->hasVerifiedWallet())
        <p class="mb-2 d-flex align-items-center gap-2">
            <span class="badge border border-success text-success">Verified</span>
            <code>{{ $user->wallet_address }}</code>
            <button
                type="button"
                class="btn btn-sm btn-link p-0 copy-button"
                data-copy-value="{{ $user->wallet_address }}"
                title="Copy wallet address"
            >
                <i class="bi bi-clipboard"></i>
            </button>
        </p>
        <p class="small text-secondary mb-3">
            Verified on {{ $user->wallet_verified_at->format('M j, Y \a\t g:i A') }}.
        </p>
    @else
        <p class="mb-3"><span class="badge border border-secondary text-secondary">No wallet linked yet</span></p>
    @endif

    <button type="button" id="wallet-connect-button" class="btn btn-outline-primary">
        <i class="bi bi-wallet2 me-1"></i>
        {{ $user->hasVerifiedWallet() ? 'Re-verify or change wallet' : 'Connect & Verify Wallet with MetaMask' }}
    </button>

    <p id="wallet-status" class="small mt-2 mb-0"></p>

    @push('scripts')
        <script>
            (function () {
                const button = document.getElementById('wallet-connect-button');
                const status = document.getElementById('wallet-status');

                button.addEventListener('click', async () => {
                    window.setButtonLoading(button, true);

                    await window.connectAndVerifyWallet({
                        challengeUrl: @json(route('wallet.challenge')),
                        verifyUrl: @json(route('wallet.verify')),
                        csrfToken: @json(csrf_token()),
                        onStatus: (state, text) => {
                            status.textContent = text;
                            status.className = 'small mt-2 mb-0 ' + (
                                state === 'error' ? 'text-danger' : state === 'success' ? 'text-success' : 'text-secondary'
                            );

                            if (state === 'success') {
                                setTimeout(() => window.location.reload(), 1200);
                            }
                        },
                    });

                    window.setButtonLoading(button, false);
                });
            })();
        </script>
    @endpush
</section>
