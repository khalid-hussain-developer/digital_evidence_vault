<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\User;

class CaseRecordPolicy
{
    /**
     * Every authenticated user may view the case list — the controller
     * scopes which cases actually appear (see CaseRecord::scopeVisibleTo()).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CaseRecord $caseRecord): bool
    {
        return $user->isAdmin() || $caseRecord->isAssignedTo($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin, UserRole::Investigator);
    }

    public function update(User $user, CaseRecord $caseRecord): bool
    {
        return $user->isAdmin()
            || ($user->hasRole(UserRole::Investigator) && $caseRecord->isAssignedTo($user));
    }

    /**
     * Determine whether the user can add or remove assigned users on this case.
     */
    public function manageAssignments(User $user, CaseRecord $caseRecord): bool
    {
        return $this->update($user, $caseRecord);
    }

    /**
     * Determine whether the user can upload evidence into this case.
     */
    public function uploadEvidence(User $user, CaseRecord $caseRecord): bool
    {
        return $user->isAdmin()
            || ($user->hasRole(UserRole::Investigator) && $caseRecord->isAssignedTo($user));
    }
}
