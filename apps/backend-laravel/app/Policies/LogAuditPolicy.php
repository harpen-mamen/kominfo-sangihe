<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LogAudit;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogAuditPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LogAudit');
    }

    public function view(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('View:LogAudit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LogAudit');
    }

    public function update(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('Update:LogAudit');
    }

    public function delete(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('Delete:LogAudit');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LogAudit');
    }

    public function restore(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('Restore:LogAudit');
    }

    public function forceDelete(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('ForceDelete:LogAudit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LogAudit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LogAudit');
    }

    public function replicate(AuthUser $authUser, LogAudit $logAudit): bool
    {
        return $authUser->can('Replicate:LogAudit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LogAudit');
    }

}