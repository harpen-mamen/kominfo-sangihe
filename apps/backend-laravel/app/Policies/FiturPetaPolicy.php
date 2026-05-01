<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FiturPeta;
use Illuminate\Auth\Access\HandlesAuthorization;

class FiturPetaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FiturPeta');
    }

    public function view(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('View:FiturPeta');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FiturPeta');
    }

    public function update(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('Update:FiturPeta');
    }

    public function delete(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('Delete:FiturPeta');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FiturPeta');
    }

    public function restore(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('Restore:FiturPeta');
    }

    public function forceDelete(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('ForceDelete:FiturPeta');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FiturPeta');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FiturPeta');
    }

    public function replicate(AuthUser $authUser, FiturPeta $fiturPeta): bool
    {
        return $authUser->can('Replicate:FiturPeta');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FiturPeta');
    }

}