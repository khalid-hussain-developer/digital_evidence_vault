// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

/// @title EvidenceVault
/// @notice Records tamper-resistant commitments for digital evidence files
/// and tracks custody transfers between authorized wallets. The contract
/// never stores file contents, filenames, case details, or any personal
/// information — only a random evidence identifier and a cryptographic
/// commitment computed off-chain (see the Laravel-side BlockchainService).
contract EvidenceVault {
    address public immutable owner;

    mapping(address => bool) public authorizedRegistrars;

    enum TransferState {
        None,
        Pending,
        Accepted
    }

    struct EvidenceRecord {
        bytes32 commitment;
        address registeredBy;
        address currentCustodian;
        uint256 registeredAt;
        bool exists;
    }

    struct PendingTransfer {
        address from;
        address to;
        TransferState state;
    }

    mapping(bytes32 => EvidenceRecord) private evidenceRecords;
    mapping(bytes32 => PendingTransfer) private pendingTransfers;

    event EvidenceRegistered(bytes32 indexed evidenceId, bytes32 commitment, address indexed registeredBy, uint256 timestamp);
    event TransferInitiated(bytes32 indexed evidenceId, address indexed from, address indexed to, uint256 timestamp);
    event TransferAccepted(bytes32 indexed evidenceId, address indexed from, address indexed to, uint256 timestamp);
    event RegistrarAuthorized(address indexed registrar);
    event RegistrarRevoked(address indexed registrar);

    error NotOwner();
    error NotAuthorized();
    error EvidenceAlreadyRegistered();
    error EvidenceNotFound();
    error NotCurrentCustodian();
    error NoPendingTransfer();
    error NotProposedCustodian();
    error InvalidAddress();

    modifier onlyOwner() {
        if (msg.sender != owner) revert NotOwner();
        _;
    }

    modifier onlyAuthorized() {
        if (!authorizedRegistrars[msg.sender]) revert NotAuthorized();
        _;
    }

    constructor() {
        owner = msg.sender;
        authorizedRegistrars[msg.sender] = true;
        emit RegistrarAuthorized(msg.sender);
    }

    /// @notice Grant an address permission to register and transfer evidence.
    /// In this application, a wallet is authorized only after its owner has
    /// proven control of it (signed-nonce verification) and holds an
    /// Investigator or Admin role in the Laravel application.
    function authorizeRegistrar(address registrar) external onlyOwner {
        if (registrar == address(0)) revert InvalidAddress();
        authorizedRegistrars[registrar] = true;
        emit RegistrarAuthorized(registrar);
    }

    function revokeRegistrar(address registrar) external onlyOwner {
        authorizedRegistrars[registrar] = false;
        emit RegistrarRevoked(registrar);
    }

    /// @notice Register a new evidence commitment on-chain.
    /// @param evidenceId A random, non-sequential identifier generated off-chain.
    /// @param commitment keccak256(abi.encode(sha256FileHash, randomSalt)), computed off-chain.
    function registerEvidence(bytes32 evidenceId, bytes32 commitment) external onlyAuthorized {
        if (evidenceRecords[evidenceId].exists) {
            revert EvidenceAlreadyRegistered();
        }

        evidenceRecords[evidenceId] = EvidenceRecord({
            commitment: commitment,
            registeredBy: msg.sender,
            currentCustodian: msg.sender,
            registeredAt: block.timestamp,
            exists: true
        });

        emit EvidenceRegistered(evidenceId, commitment, msg.sender, block.timestamp);
    }

    /// @notice Start transferring custody of an evidence record to another
    /// authorized wallet. Only the current custodian may initiate this.
    function initiateTransfer(bytes32 evidenceId, address to) external onlyAuthorized {
        EvidenceRecord storage record = evidenceRecords[evidenceId];

        if (!record.exists) {
            revert EvidenceNotFound();
        }

        if (record.currentCustodian != msg.sender) {
            revert NotCurrentCustodian();
        }

        if (to == address(0)) {
            revert InvalidAddress();
        }

        pendingTransfers[evidenceId] = PendingTransfer({from: msg.sender, to: to, state: TransferState.Pending});

        emit TransferInitiated(evidenceId, msg.sender, to, block.timestamp);
    }

    /// @notice Accept a pending custody transfer. Only the proposed new
    /// custodian may call this — the previous custodian cannot force it.
    function acceptTransfer(bytes32 evidenceId) external onlyAuthorized {
        PendingTransfer storage pending = pendingTransfers[evidenceId];

        if (pending.state != TransferState.Pending) {
            revert NoPendingTransfer();
        }

        if (pending.to != msg.sender) {
            revert NotProposedCustodian();
        }

        evidenceRecords[evidenceId].currentCustodian = msg.sender;
        pending.state = TransferState.Accepted;

        emit TransferAccepted(evidenceId, pending.from, msg.sender, block.timestamp);
    }

    function getEvidence(bytes32 evidenceId)
        external
        view
        returns (bytes32 commitment, address registeredBy, address currentCustodian, uint256 registeredAt)
    {
        EvidenceRecord memory record = evidenceRecords[evidenceId];

        if (!record.exists) {
            revert EvidenceNotFound();
        }

        return (record.commitment, record.registeredBy, record.currentCustodian, record.registeredAt);
    }

    function getCurrentCustodian(bytes32 evidenceId) external view returns (address) {
        EvidenceRecord memory record = evidenceRecords[evidenceId];

        if (!record.exists) {
            revert EvidenceNotFound();
        }

        return record.currentCustodian;
    }

    function evidenceExists(bytes32 evidenceId) external view returns (bool) {
        return evidenceRecords[evidenceId].exists;
    }
}
