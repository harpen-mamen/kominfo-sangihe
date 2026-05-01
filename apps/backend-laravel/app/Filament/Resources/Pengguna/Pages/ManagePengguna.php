<?php

namespace App\Filament\Resources\Pengguna\Pages;

use App\Filament\Resources\Pengguna\PenggunaResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class ManagePengguna extends ManageRecords
{
    protected static string $resource = PenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(fn (array $data): array => static::mutateUserData($data))
                ->using(function (array $data, string $model): Model {
                  if (filled($data['kata_sandi'] ?? null)) {
    $data['kata_sandi'] = Hash::make((string) $data['kata_sandi']);
}

$record = $model::create($data);
                    static::syncShieldRole($record, $data);

                    return $record;
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateUserData(array $data): array
    {
        $role = (string) ($data['shield_role'] ?? 'admin_kecamatan');

        $data['role'] = $role;

        if ($role !== 'admin_kecamatan') {
            $data['kecamatan_id'] = null;
        }

        if ($role !== 'admin_opd') {
            $data['opd_id'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function syncShieldRole(Model $record, array $data): void
    {
        if (! $record instanceof User) {
            return;
        }

        $role = (string) ($data['shield_role'] ?? $data['role'] ?? 'admin_kecamatan');
        $record->syncRoles([$role]);
        $record->forceFill(['role' => $role])->saveQuietly();
    }
}
