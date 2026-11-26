# Digital Evidence Vault

A web application for managing digital evidence, built with Laravel. Investigators
can create cases, upload evidence files, and track custody. Every evidence file is
fingerprinted with a SHA-256 hash, and a tamper-evident commitment of that hash is
recorded on the Ethereum Sepolia testnet, so any later change to a file can be
detected by comparing hashes.

## Key Features

- Role-based access (Admin, Investigator, Forensic Officer, Reviewer)
- Case and evidence management with custody tracking
- Private, authenticated-only file storage (local disk or Cloudflare R2)
- Server-side SHA-256 hashing of every uploaded file
- Blockchain commitments on Ethereum Sepolia for tamper detection
- Wallet-based identity verification (MetaMask / ECDSA signatures)
- Full audit trail of evidence and custody events

## Tech Stack

- **Backend:** PHP 8.5, Laravel 13
- **Database:** MySQL
- **Frontend:** Blade, Bootstrap 5, Vite
- **Blockchain:** Solidity, Ethereum Sepolia testnet, ethers.js
- **Testing:** Pest

## Getting Started

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Set DB_* values in .env, then:
php artisan migrate --seed

npm run build
php artisan serve
```

Log in with one of the seeded demo accounts (see `database/seeders/DatabaseSeeder.php`),
password `password` for all of them.

## Blockchain Setup

See [`blockchain/README.md`](blockchain/README.md) for compiling and deploying the
`EvidenceVault` smart contract, and for the Sepolia RPC / contract address `.env`
variables required to enable on-chain registration.

## Running Tests

```bash
php artisan test
```
