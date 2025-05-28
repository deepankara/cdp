<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SmsTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_sms::template');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('view_sms::template');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_sms::template');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('update_sms::template');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('delete_sms::template');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_sms::template');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('force_delete_sms::template');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_sms::template');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('restore_sms::template');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_sms::template');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, SmsTemplate $smsTemplate): bool
    {
        return $user->can('replicate_sms::template');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_sms::template');
    }
}
