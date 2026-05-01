<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PeriodeData;
use Illuminate\Auth\Access\HandlesAuthorization;

class PeriodeDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PeriodeData');
    }

    public function view(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('View:PeriodeData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PeriodeData');
    }

    public function update(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('Update:PeriodeData');
    }

    public function delete(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('Delete:PeriodeData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PeriodeData');
    }

    public function restore(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('Restore:PeriodeData');
    }

    public function forceDelete(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('ForceDelete:PeriodeData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PeriodeData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PeriodeData');
    }

    public function replicate(AuthUser $authUser, PeriodeData $periodeData): bool
    {
        return $authUser->can('Replicate:PeriodeData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PeriodeData');
    }

}