<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NilaiDataMentah;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Auth\Access\HandlesAuthorization;

class NilaiDataMentahPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NilaiDataMentah');
    }

    public function view(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        if ($authUser instanceof User && AdminScope::isSubdistrict($authUser)) {
            return $authUser->can('View:NilaiDataMentah')
                && (int) $nilaiDataMentah->pengajuanData?->kecamatan_id === (int) $authUser->kecamatan_id;
        }

        return $authUser->can('View:NilaiDataMentah');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NilaiDataMentah');
    }

    public function update(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        if (! $nilaiDataMentah->pengajuanData?->canInputValues()) {
            return false;
        }

        if ($authUser instanceof User && AdminScope::isSubdistrict($authUser)) {
            return $authUser->can('Update:NilaiDataMentah')
                && (int) $nilaiDataMentah->pengajuanData?->kecamatan_id === (int) $authUser->kecamatan_id;
        }

        return $authUser->can('Update:NilaiDataMentah');
    }

    public function delete(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        return $authUser->can('Delete:NilaiDataMentah');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NilaiDataMentah');
    }

    public function restore(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        return $authUser->can('Restore:NilaiDataMentah');
    }

    public function forceDelete(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        return $authUser->can('ForceDelete:NilaiDataMentah');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NilaiDataMentah');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NilaiDataMentah');
    }

    public function replicate(AuthUser $authUser, NilaiDataMentah $nilaiDataMentah): bool
    {
        return $authUser->can('Replicate:NilaiDataMentah');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NilaiDataMentah');
    }

}
