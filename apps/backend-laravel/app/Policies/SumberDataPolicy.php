<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SumberData;
use Illuminate\Auth\Access\HandlesAuthorization;

class SumberDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SumberData');
    }

    public function view(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('View:SumberData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SumberData');
    }

    public function update(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('Update:SumberData');
    }

    public function delete(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('Delete:SumberData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SumberData');
    }

    public function restore(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('Restore:SumberData');
    }

    public function forceDelete(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('ForceDelete:SumberData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SumberData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SumberData');
    }

    public function replicate(AuthUser $authUser, SumberData $sumberData): bool
    {
        return $authUser->can('Replicate:SumberData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SumberData');
    }

}