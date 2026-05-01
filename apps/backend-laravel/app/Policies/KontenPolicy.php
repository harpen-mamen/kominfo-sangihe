<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Konten;
use Illuminate\Auth\Access\HandlesAuthorization;

class KontenPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Konten');
    }

    public function view(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('View:Konten');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Konten');
    }

    public function update(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('Update:Konten');
    }

    public function delete(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('Delete:Konten');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Konten');
    }

    public function restore(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('Restore:Konten');
    }

    public function forceDelete(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('ForceDelete:Konten');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Konten');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Konten');
    }

    public function replicate(AuthUser $authUser, Konten $konten): bool
    {
        return $authUser->can('Replicate:Konten');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Konten');
    }

}