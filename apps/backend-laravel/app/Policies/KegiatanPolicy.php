<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class KegiatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Kegiatan');
    }

    public function view(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('View:Kegiatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kegiatan');
    }

    public function update(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('Update:Kegiatan');
    }

    public function delete(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('Delete:Kegiatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Kegiatan');
    }

    public function restore(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('Restore:Kegiatan');
    }

    public function forceDelete(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('ForceDelete:Kegiatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kegiatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kegiatan');
    }

    public function replicate(AuthUser $authUser, Kegiatan $kegiatan): bool
    {
        return $authUser->can('Replicate:Kegiatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kegiatan');
    }

}