<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Berita;
use Illuminate\Auth\Access\HandlesAuthorization;

class BeritaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Berita');
    }

    public function view(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('View:Berita');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Berita');
    }

    public function update(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('Update:Berita');
    }

    public function delete(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('Delete:Berita');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Berita');
    }

    public function restore(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('Restore:Berita');
    }

    public function forceDelete(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('ForceDelete:Berita');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Berita');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Berita');
    }

    public function replicate(AuthUser $authUser, Berita $berita): bool
    {
        return $authUser->can('Replicate:Berita');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Berita');
    }

}