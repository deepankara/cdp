<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Rules;
use Illuminate\Auth\Access\HandlesAuthorization;

class RulesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_rules');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Rules $rules): bool
    {
        return $user->can('view_rules');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_rules');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rules $rules): bool
    {
        return $user->can('update_rules');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rules $rules): bool
    {
        return $user->can('delete_rules');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_rules');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Rules $rules): bool
    {
        return $user->can('force_delete_rules');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_rules');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Rules $rules): bool
    {
        return $user->can('restore_rules');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_rules');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Rules $rules): bool
    {
        return $user->can('replicate_rules');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_rules');
    }
}
