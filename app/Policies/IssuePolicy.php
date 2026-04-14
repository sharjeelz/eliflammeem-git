<?php

namespace App\Policies;

use App\Models\CentralUser;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class IssuePolicy
{
    /** CentralUsers (Nova super-admins) have full access to everything. */
    public function before(Authenticatable $user, string $ability): ?bool
    {
        if ($user instanceof CentralUser) {
            return true;
        }

        return null;
    }

    /** Admin/branch-manager/staff can list issues (data scoping is done in the query). */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'branch_manager', 'staff']);
    }

    /** Who can see a specific issue. */
    public function view(User $user, Issue $issue): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            if ($issue->is_anonymous) {
                return false;
            }
            $branchIds = $user->branches->pluck('id')->toArray();

            return in_array($issue->branch_id, $branchIds, true);
        }

        if ($user->hasRole('staff')) {
            return $issue->assigned_user_id === $user->id;
        }

        return false;
    }

    /** Who can reassign an issue (does not validate the assignee — that stays in the controller). */
    public function assign(User $user, Issue $issue): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            if ($issue->is_anonymous) {
                return false;
            }
            $branchIds = $user->branches->pluck('id')->toArray();

            return in_array($issue->branch_id, $branchIds, true);
        }

        return false; // staff cannot assign
    }

    /** Who can move an issue to a new status. */
    public function updateStatus(User $user, Issue $issue, string $to = ''): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Only admins can reopen closed issues.
        if ($issue->status === 'closed') {
            return false;
        }

        if ($user->hasRole('branch_manager')) {
            if ($issue->is_anonymous) {
                return false;
            }
            $branchIds = $user->branches->pluck('id')->toArray();

            return in_array($issue->branch_id, $branchIds, true);
        }

        if ($user->hasRole('staff')) {
            // Staff can only resolve issues — closing is reserved for admin, branch manager, or the contact.
            return $issue->assigned_user_id === $user->id && $to !== 'closed';
        }

        return false;
    }

    /** Who can change the priority of an issue. */
    public function updatePriority(User $user, Issue $issue): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            if ($issue->is_anonymous) {
                return false;
            }
            $branchIds = $user->branches->pluck('id')->toArray();

            return in_array($issue->branch_id, $branchIds, true);
        }

        return false;
    }

    /** Who can delete a message on an issue. Admin: any message. Others: only their own. */
    public function deleteMessage(User $user, Issue $issue): bool
    {
        return $user->hasRole(['admin', 'branch_manager', 'staff']);
    }

    /** Who can add a comment to an issue. */
    public function comment(User $user, Issue $issue): bool
    {
        // Nobody but admin can touch a closed issue.
        if ($issue->status === 'closed' && ! $user->hasRole('admin')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('branch_manager')) {
            if ($issue->is_anonymous) {
                return false;
            }
            $branchIds = $user->branches->pluck('id')->toArray();

            return in_array($issue->branch_id, $branchIds, true);
        }

        if ($user->hasRole('staff')) {
            return (int) $issue->assigned_user_id === $user->id;
        }

        return false;
    }
}
