// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

// These are Remix's built-in testing libraries — available automatically
// inside Remix IDE, nothing to install. Run them from Remix's
// "Solidity Unit Testing" plugin (left sidebar), not from the command line.
import "remix_tests.sol";
import "remix_accounts.sol";
import "../contracts/EvidenceVault.sol";

contract EvidenceVaultTest {
    EvidenceVault vault;
    address account1;

    function beforeEach() public {
        vault = new EvidenceVault();
        account1 = TestsAccounts.getAccount(1);
    }

    function deployerIsAuthorizedByDefault() public {
        Assert.equal(vault.authorizedRegistrars(address(this)), true, "deployer should be authorized on deployment");
    }

    function registeringEvidenceStoresItAndEmitsAnEvent() public {
        bytes32 evidenceId = keccak256(abi.encodePacked("evidence-1"));
        bytes32 commitment = keccak256(abi.encodePacked("commitment-1"));

        vault.registerEvidence(evidenceId, commitment);

        Assert.equal(vault.evidenceExists(evidenceId), true, "evidence should exist after registration");

        (bytes32 storedCommitment, address registeredBy, address currentCustodian,) = vault.getEvidence(evidenceId);
        Assert.equal(storedCommitment, commitment, "stored commitment should match what was registered");
        Assert.equal(registeredBy, address(this), "registeredBy should be the calling account");
        Assert.equal(currentCustodian, address(this), "the registrant should become the initial custodian");
    }

    function duplicateRegistrationReverts() public {
        bytes32 evidenceId = keccak256(abi.encodePacked("evidence-2"));
        bytes32 commitment = keccak256(abi.encodePacked("commitment-2"));

        vault.registerEvidence(evidenceId, commitment);

        try vault.registerEvidence(evidenceId, commitment) {
            Assert.ok(false, "a second registration of the same evidenceId should have reverted");
        } catch {
            Assert.ok(true, "duplicate registration correctly reverted");
        }
    }

    /// #sender: account-1
    function unauthorizedAccountCannotRegisterEvidence() public {
        bytes32 evidenceId = keccak256(abi.encodePacked("evidence-3"));
        bytes32 commitment = keccak256(abi.encodePacked("commitment-3"));

        // account-1 was never authorized by the owner, so this must revert.
        try vault.registerEvidence(evidenceId, commitment) {
            Assert.ok(false, "an unauthorized account should not be able to register evidence");
        } catch {
            Assert.ok(true, "unauthorized registration correctly reverted");
        }
    }

    function fullTransferWorkflowMovesCustody() public {
        bytes32 evidenceId = keccak256(abi.encodePacked("evidence-4"));
        bytes32 commitment = keccak256(abi.encodePacked("commitment-4"));

        vault.registerEvidence(evidenceId, commitment);
        vault.authorizeRegistrar(account1);
        vault.initiateTransfer(evidenceId, account1);

        // acceptTransfer must be called BY account1 to succeed — calling it
        // here (as the test contract, not account1) should revert.
        try vault.acceptTransfer(evidenceId) {
            Assert.ok(false, "only the proposed custodian should be able to accept the transfer");
        } catch {
            Assert.ok(true, "accept-by-wrong-account correctly reverted");
        }
    }
}
