<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RingkasanStatistik;
use Illuminate\Auth\Access\HandlesAuthorization;

class RingkasanStatistikPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RingkasanStatistik');
    }

    public function view(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('View:RingkasanStatistik');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RingkasanStatistik');
    }

    public function update(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('Update:RingkasanStatistik');
    }

    public function delete(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('Delete:RingkasanStatistik');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RingkasanStatistik');
    }

    public function restore(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('Restore:RingkasanStatistik');
    }

    public function forceDelete(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('ForceDelete:RingkasanStatistik');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RingkasanStatistik');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RingkasanStatistik');
    }

    public function replicate(AuthUser $authUser, RingkasanStatistik $ringkasanStatistik): bool
    {
        return $authUser->can('Replicate:RingkasanStatistik');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RingkasanStatistik');
    }

}