<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Evidence;
use App\Models\User;

class EvidencePolicy
{
    /**
     * Every authenticated user may view the evidence list — the controller
     * scopes which records actually appear (see Evidence::scopeVisibleTo()).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Evidence $evidence): bool
    {
        return $user->isAdmin() || $evidence->case->isAssignedTo($user);
    }

    /**
     * Downloading the original file requires the same visibility as viewing
     * its details — there is no separate, looser download permission.
     */
    public function download(User $user, Evidence $evidence): bool
    {
        return $this->view($user, $evidence);
    }

    /**
     * Determine whether the user may register this evidence's commitment
     * on the blockchain. Same rule as uploading evidence into the case
     * (Admin, or an Investigator assigned to it) — registering is part of
     * the same custody responsibility as uploading.
     */
    public function registerOnBlockchain(User $user, Evidence $evidence): bool
    {
        return $user->isAdmin()
            || ($user->hasRole(UserRole::Investigator) && $evidence->case->isAssignedTo($user));
    }
}
