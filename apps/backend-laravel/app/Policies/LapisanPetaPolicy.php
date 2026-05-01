<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LapisanPeta;
use Illuminate\Auth\Access\HandlesAuthorization;

class LapisanPetaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LapisanPeta');
    }

    public function view(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('View:LapisanPeta');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LapisanPeta');
    }

    public function update(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('Update:LapisanPeta');
    }

    public function delete(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('Delete:LapisanPeta');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LapisanPeta');
    }

    public function restore(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('Restore:LapisanPeta');
    }

    public function forceDelete(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('ForceDelete:LapisanPeta');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LapisanPeta');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LapisanPeta');
    }

    public function replicate(AuthUser $authUser, LapisanPeta $lapisanPeta): bool
    {
        return $authUser->can('Replicate:LapisanPeta');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LapisanPeta');
    }

}