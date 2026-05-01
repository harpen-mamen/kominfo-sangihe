<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\IndikatorData;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndikatorDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IndikatorData');
    }

    public function view(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('View:IndikatorData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IndikatorData');
    }

    public function update(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('Update:IndikatorData');
    }

    public function delete(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('Delete:IndikatorData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IndikatorData');
    }

    public function restore(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('Restore:IndikatorData');
    }

    public function forceDelete(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('ForceDelete:IndikatorData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IndikatorData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IndikatorData');
    }

    public function replicate(AuthUser $authUser, IndikatorData $indikatorData): bool
    {
        return $authUser->can('Replicate:IndikatorData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IndikatorData');
    }

}