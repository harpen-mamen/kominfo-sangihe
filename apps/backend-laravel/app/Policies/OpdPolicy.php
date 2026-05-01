<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Opd;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpdPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Opd');
    }

    public function view(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('View:Opd');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Opd');
    }

    public function update(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('Update:Opd');
    }

    public function delete(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('Delete:Opd');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Opd');
    }

    public function restore(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('Restore:Opd');
    }

    public function forceDelete(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('ForceDelete:Opd');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Opd');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Opd');
    }

    public function replicate(AuthUser $authUser, Opd $opd): bool
    {
        return $authUser->can('Replicate:Opd');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Opd');
    }

}