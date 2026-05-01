<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DokumenPublik;
use Illuminate\Auth\Access\HandlesAuthorization;

class DokumenPublikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DokumenPublik');
    }

    public function view(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('View:DokumenPublik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DokumenPublik');
    }

    public function update(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('Update:DokumenPublik');
    }

    public function delete(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('Delete:DokumenPublik');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DokumenPublik');
    }

    public function restore(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('Restore:DokumenPublik');
    }

    public function forceDelete(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('ForceDelete:DokumenPublik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DokumenPublik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DokumenPublik');
    }

    public function replicate(AuthUser $authUser, DokumenPublik $dokumenPublik): bool
    {
        return $authUser->can('Replicate:DokumenPublik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DokumenPublik');
    }

}