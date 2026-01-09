<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can update the target user.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        // Prevent self-edit through this check (though edit is allowed, this is for action display)
        // SuperAdmin can edit admins
        if ($authUser->isSuperAdmin()) {
            return $targetUser->role === 'admin';
        }
        
        // Admin can edit counters in their organization
        if ($authUser->isAdmin()) {
            return $targetUser->role === 'counter' 
                && $targetUser->organization_id === $authUser->organization_id;
        }
        
        return false;
    }

    /**
     * Determine if the user can delete the target user.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        // Prevent self-deletion
        if ($authUser->id === $targetUser->id) {
            return false;
        }
        
        // SuperAdmin can delete admins only
        if ($authUser->isSuperAdmin()) {
            return $targetUser->role === 'admin';
        }
        
        // Admin can delete counters in their organization
        if ($authUser->isAdmin()) {
            return $targetUser->role === 'counter' 
                && $targetUser->organization_id === $authUser->organization_id;
        }
        
        return false;
    }
}
