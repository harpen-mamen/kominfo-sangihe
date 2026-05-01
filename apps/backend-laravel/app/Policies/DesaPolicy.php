<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Desa;
use Illuminate\Auth\Access\HandlesAuthorization;

class DesaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Desa');
    }

    public function view(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('View:Desa');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Desa');
    }

    public function update(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('Update:Desa');
    }

    public function delete(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('Delete:Desa');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Desa');
    }

    public function restore(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('Restore:Desa');
    }

    public function forceDelete(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('ForceDelete:Desa');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Desa');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Desa');
    }

    public function replicate(AuthUser $authUser, Desa $desa): bool
    {
        return $authUser->can('Replicate:Desa');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Desa');
    }

}