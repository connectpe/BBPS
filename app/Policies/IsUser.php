<?php

namespace App\Policies;

use App\Models\User;

class IsUser
{
    /**
     * Create a new policy instance.
     */
    public function access(User $user): bool
    {
        return (int) $user->role_id === 1;
    }
}
