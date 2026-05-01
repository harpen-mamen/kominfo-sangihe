<?php

namespace App\Support;

use App\Models\User;

class FilamentWorkspace
{
    public static function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function key(): ?string
    {
        $user = self::user();

        return $user ? AdminScope::workspaceKey($user) : null;
    }

    public static function isKominfo(): bool
    {
        return ($user = self::user()) ? AdminScope::isKominfo($user) : false;
    }

    public static function isDepartment(): bool
    {
        return ($user = self::user()) ? AdminScope::isDepartment($user) : false;
    }

    public static function isSubdistrict(): bool
    {
        return ($user = self::user()) ? AdminScope::isSubdistrict($user) : false;
    }

    public static function canAccessWorkflow(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessIndicators(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canManageIndicators(): bool
    {
        return in_array(self::key(), ['opd', 'kominfo'], true);
    }

    public static function canAccessSectoralWorkbench(): bool
    {
        return in_array(self::key(), ['opd', 'kominfo'], true);
    }

    public static function canAccessPublicContent(): bool
    {
        return self::isKominfo();
    }

    public static function canManageNews(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canManageSubdistrictAgenda(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessUsers(): bool
    {
        return self::isKominfo();
    }

    public static function canAccessMasterData(): bool
    {
        return self::isKominfo();
    }

    public static function canAccessReferenceSources(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessMapFeatures(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessMapLayers(): bool
    {
        return self::isKominfo();
    }

    public static function canAccessStatisticsSummary(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessDukcapilReport(): bool
    {
        $user = self::user();

        return self::isKominfo() || (self::isDepartment() && (bool) $user?->isDukcapil());
    }

    public static function canAccessRegionalReports(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }

    public static function canAccessPublicDocuments(): bool
    {
        return in_array(self::key(), ['kecamatan', 'opd', 'kominfo'], true);
    }
}
