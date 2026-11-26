# EvidenceVault Smart Contract

This folder is separate from the Laravel application (`app/`) because it is
a different language and toolchain — Solidity, compiled and deployed through
Remix IDE in your browser, not through Composer or Artisan.

## What's here

- `contracts/EvidenceVault.sol` — the contract itself.
- `tests/EvidenceVault_test.sol` — unit tests written for Remix's built-in
  "Solidity Unit Testing" plugin (not a command-line test runner — Remix
  provides `remix_tests.sol` automatically, nothing to install).
- `deployments/sepolia.json` — filled in by you after a real deployment
  (contract address, deployer address, transaction hash). It starts as a
  template with `"status": "not_yet_deployed"`.

## What you need before deploying

1. **MetaMask** — a browser wallet extension (https://metamask.io). Free.
2. **A Sepolia testnet wallet with test ETH.** Sepolia is Ethereum's test
   network — its ETH has no real value, so a faucet can give it to you for
   free. In MetaMask, switch the network to "Sepolia" (enable "show test
   networks" in MetaMask's settings if you don't see it), copy your wallet
   address, and request test ETH from a faucet such as
   `https://sepoliafaucet.com` or `https://www.alchemy.com/faucets/ethereum-sepolia`.
   A few faucets require a small amount of mainnet ETH or a free account —
   if one refuses you, try another.
3. **An RPC provider API key** (Infura or Alchemy, both have a free tier).
   This is *not* needed to deploy from Remix — MetaMask handles that. It's
   needed later so the Laravel backend can independently read transaction
   receipts from Sepolia to verify what the frontend submitted, without
   trusting the browser. Sign up, create a Sepolia app/project, and copy
   its HTTPS RPC URL (looks like `https://eth-sepolia.g.alchemy.com/v2/<key>`
   or `https://sepolia.infura.io/v3/<key>`).

## Compiling and testing in Remix

1. Go to https://remix.ethereum.org.
2. In the file explorer, create the same folder structure
   (`contracts/EvidenceVault.sol`, `tests/EvidenceVault_test.sol`) and paste
   in these two files' contents.
3. Open the "Solidity Compiler" tab, set the compiler version to `0.8.24`
   (or compatible), and compile `EvidenceVault.sol`. It should compile with
   no errors — this was already verified locally with `solc 0.8.24` before
   these files were added to the project.
4. Open the "Solidity Unit Testing" tab (left sidebar icon), select
   `tests/EvidenceVault_test.sol`, and click "Run tests". All five should
   pass. Remix's test plugin only runs inside Remix itself, so this step
   (done by you, inside Remix) is the real confirmation that the contract
   compiles and behaves correctly.

## Deploying to Sepolia

1. In Remix's "Deploy & Run Transactions" tab, set the Environment to
   "Injected Provider - MetaMask".
2. In the MetaMask popup, confirm you're connected and switched to the
   **Sepolia** network.
3. Select `EvidenceVault` as the contract and click "Deploy". MetaMask will
   ask you to confirm the transaction — this spends a small amount of your
   Sepolia test ETH as gas.
4. Once confirmed, copy the deployed contract's address and the transaction
   hash (both shown in Remix's terminal panel at the bottom) into
   `blockchain/deployments/sepolia.json`, and change `"status"` to
   `"deployed"`.
5. Copy the contract's ABI (Solidity Compiler tab → "ABI" button, or the
   `.abi` file if you compiled it with `solc`) — the Laravel backend will
   need it to decode event logs when verifying transaction receipts.

Nothing in the Laravel application reads this contract until the next
development phase wires it in — deploying it here is a prerequisite, not
the finish line.
