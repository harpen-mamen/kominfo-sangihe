<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RiwayatTinjau;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiwayatTinjauPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RiwayatTinjau');
    }

    public function view(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('View:RiwayatTinjau');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RiwayatTinjau');
    }

    public function update(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('Update:RiwayatTinjau');
    }

    public function delete(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('Delete:RiwayatTinjau');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RiwayatTinjau');
    }

    public function restore(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('Restore:RiwayatTinjau');
    }

    public function forceDelete(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('ForceDelete:RiwayatTinjau');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RiwayatTinjau');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RiwayatTinjau');
    }

    public function replicate(AuthUser $authUser, RiwayatTinjau $riwayatTinjau): bool
    {
        return $authUser->can('Replicate:RiwayatTinjau');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RiwayatTinjau');
    }

}