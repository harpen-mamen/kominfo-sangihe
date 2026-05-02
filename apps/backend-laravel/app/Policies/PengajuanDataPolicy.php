<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengajuanData;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengajuanDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PengajuanData');
    }

    public function view(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        if ($authUser instanceof User && AdminScope::isSubdistrict($authUser)) {
            return $authUser->can('View:PengajuanData')
                && (int) $pengajuanData->kecamatan_id === (int) $authUser->kecamatan_id;
        }

        return $authUser->can('View:PengajuanData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PengajuanData');
    }

    public function update(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        if (! $pengajuanData->canInputValues()) {
            return false;
        }

        if ($authUser instanceof User && AdminScope::isSubdistrict($authUser)) {
            return $authUser->can('Update:PengajuanData')
                && (int) $pengajuanData->kecamatan_id === (int) $authUser->kecamatan_id;
        }

        return $authUser->can('Update:PengajuanData');
    }

    public function delete(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        return $authUser->can('Delete:PengajuanData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PengajuanData');
    }

    public function restore(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        return $authUser->can('Restore:PengajuanData');
    }

    public function forceDelete(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        return $authUser->can('ForceDelete:PengajuanData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PengajuanData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PengajuanData');
    }

    public function replicate(AuthUser $authUser, PengajuanData $pengajuanData): bool
    {
        return $authUser->can('Replicate:PengajuanData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PengajuanData');
    }

}
