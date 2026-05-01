<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Kecamatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class KecamatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Kecamatan');
    }

    public function view(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('View:Kecamatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Kecamatan');
    }

    public function update(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('Update:Kecamatan');
    }

    public function delete(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('Delete:Kecamatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Kecamatan');
    }

    public function restore(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('Restore:Kecamatan');
    }

    public function forceDelete(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('ForceDelete:Kecamatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Kecamatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Kecamatan');
    }

    public function replicate(AuthUser $authUser, Kecamatan $kecamatan): bool
    {
        return $authUser->can('Replicate:Kecamatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Kecamatan');
    }

}