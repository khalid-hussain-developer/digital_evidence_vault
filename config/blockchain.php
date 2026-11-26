<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Network
    |--------------------------------------------------------------------------
    |
    | Sepolia's chain ID is fixed by Ethereum itself — it is not something
    | to guess or change, it identifies the network itself.
    |
    */

    'network' => 'sepolia',
    'chain_id' => 11155111,

    /*
    |--------------------------------------------------------------------------
    | RPC Provider
    |--------------------------------------------------------------------------
    |
    | The URL Laravel uses to independently read transaction receipts and
    | confirm what the browser submitted — never used to sign or send
    | transactions, which always happen in the user's own MetaMask.
    | From Infura or Alchemy's free tier. Left blank until configured.
    |
    */

    'rpc_url' => env('BLOCKCHAIN_RPC_URL'),

    /*
    |--------------------------------------------------------------------------
    | Contract
    |--------------------------------------------------------------------------
    |
    | Filled in after running blockchain/README.md's deployment steps.
    | The ABI is read from blockchain/contracts/EvidenceVault.abi.json,
    | not duplicated here.
    |
    */

    'contract_address' => env('BLOCKCHAIN_CONTRACT_ADDRESS'),
    'contract_abi_path' => base_path('blockchain/contracts/EvidenceVault.abi.json'),

    /*
    |--------------------------------------------------------------------------
    | Block Explorer
    |--------------------------------------------------------------------------
    |
    | Used only to build "view on Etherscan" links in the UI — never
    | queried by the backend for anything that matters.
    |
    */

    'explorer_url' => 'https://sepolia.etherscan.io',

];
