<?php

namespace App\Policies;

use App\Models\LhdnCredential;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LhdnCredentialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return !$user->is_super_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LhdnCredential $lhdnCredential): bool
    {
        return $user->company_id === $lhdnCredential->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return !$user->is_super_admin && $user->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LhdnCredential $lhdnCredential): bool
    {
        return $user->company_id === $lhdnCredential->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LhdnCredential $lhdnCredential): bool
    {
        return $user->company_id === $lhdnCredential->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LhdnCredential $lhdnCredential): bool
    {
        return $user->company_id === $lhdnCredential->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LhdnCredential $lhdnCredential): bool
    {
        return $user->company_id === $lhdnCredential->company_id;
    }
}
