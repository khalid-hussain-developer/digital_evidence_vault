import { BrowserProvider } from 'ethers';

/**
 * Connects to MetaMask, has it sign a one-time server-issued challenge,
 * and submits that signature for server-side verification. The wallet's
 * private key never leaves MetaMask and never reaches this application.
 */
export async function connectAndVerifyWallet({ challengeUrl, verifyUrl, csrfToken, onStatus }) {
    if (!window.ethereum) {
        onStatus('error', 'MetaMask was not detected in this browser.');
        return;
    }

    try {
        onStatus('pending', 'Connecting to MetaMask...');
        const provider = new BrowserProvider(window.ethereum);
        const signer = await provider.getSigner();
        const address = await signer.getAddress();

        onStatus('pending', 'Requesting a challenge to sign...');
        const challengeResponse = await fetch(challengeUrl, {
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        });

        if (!challengeResponse.ok) {
            onStatus('error', 'Could not fetch a verification challenge.');
            return;
        }

        const { message } = await challengeResponse.json();

        onStatus('pending', 'Waiting for your signature in MetaMask...');
        const signature = await signer.signMessage(message);

        onStatus('pending', 'Verifying signature...');
        const verifyResponse = await fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({ address, signature }),
        });

        const result = await verifyResponse.json();

        if (verifyResponse.ok && result.verified) {
            onStatus('success', `Wallet ${result.wallet_address} verified.`);
        } else {
            onStatus('error', result.message || 'Verification failed.');
        }
    } catch (error) {
        onStatus('error', error?.message || 'Something went wrong connecting to MetaMask.');
    }
}

window.connectAndVerifyWallet = connectAndVerifyWallet;
