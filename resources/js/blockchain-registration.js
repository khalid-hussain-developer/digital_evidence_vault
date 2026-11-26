import { BrowserProvider, Contract } from 'ethers';
import evidenceVaultAbi from '../../blockchain/contracts/EvidenceVault.abi.json';

/**
 * Registers an evidence file's commitment on the EvidenceVault contract.
 *
 * The private key never touches this code or the server — MetaMask signs
 * and submits the transaction itself. This function only: asks the
 * server to prepare the commitment, asks MetaMask to submit it on-chain,
 * and tells the server the resulting transaction hash so it can later be
 * independently confirmed against the real blockchain.
 */
export async function registerEvidenceOnBlockchain({
    prepareUrl,
    storeTransactionUrl,
    contractAddress,
    csrfToken,
    onStatus,
}) {
    if (!window.ethereum) {
        onStatus('error', 'MetaMask was not detected in this browser.');
        return;
    }

    if (!contractAddress) {
        onStatus('error', 'No contract address is configured yet.');
        return;
    }

    try {
        onStatus('pending', 'Preparing the evidence commitment...');
        const prepareResponse = await fetch(prepareUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
        });
        const prepared = await prepareResponse.json();

        if (!prepareResponse.ok) {
            onStatus('error', prepared.message || 'Could not prepare this evidence for registration.');
            return;
        }

        onStatus('pending', 'Connecting to MetaMask...');
        const provider = new BrowserProvider(window.ethereum);
        const signer = await provider.getSigner();
        const contract = new Contract(contractAddress, evidenceVaultAbi, signer);

        onStatus('pending', 'Waiting for you to confirm the transaction in MetaMask...');
        const tx = await contract.registerEvidence(prepared.onChainEvidenceId, prepared.commitment);

        onStatus('pending', 'Transaction submitted, recording it...');
        const storeResponse = await fetch(storeTransactionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: JSON.stringify({ transaction_hash: tx.hash }),
        });

        if (!storeResponse.ok) {
            const body = await storeResponse.json();
            onStatus('error', body.message || 'Transaction was submitted, but could not be recorded.');
            return;
        }

        onStatus('submitted', `Submitted (${tx.hash}). Confirmation on Sepolia can take a minute or more.`);
    } catch (error) {
        if (error?.code === 'ACTION_REJECTED') {
            onStatus('error', 'You rejected the transaction in MetaMask.');
        } else {
            onStatus('error', error?.shortMessage || error?.message || 'Something went wrong registering this evidence.');
        }
    }
}

/**
 * Re-check a submitted transaction's real on-chain status.
 */
export async function refreshTransactionStatus({ refreshUrl, csrfToken, onResult }) {
    const response = await fetch(refreshUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
    });
    const result = await response.json();
    onResult(response.ok, result);
}

window.registerEvidenceOnBlockchain = registerEvidenceOnBlockchain;
window.refreshTransactionStatus = refreshTransactionStatus;
