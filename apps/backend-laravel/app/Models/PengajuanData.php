<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PengajuanData extends Model
{
    use HasFactory;

    /**
     * Nama tabel diasumsikan: pengajuan_data
     * Jika ternyata berbeda, samakan dengan schema database kamu.
     */
    protected $table = 'pengajuan_data';

    protected $fillable = [
        'kecamatan_id',
        'opd_id',
        'status',
        'catatan',
        'catatan_verifikasi',
        'tahun',
        'bulan',
        'dibuat_oleh',
        'diupdate_oleh',
        'payload',
        'periode_data_id',
        'verifikator_id',
        'tampil_publik',
    ];

    protected $casts = [
        'payload' => 'array',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'kecamatan_id' => 'integer',
        'opd_id' => 'integer',
        'periode_data_id' => 'integer',
        'verifikator_id' => 'integer',
        'tampil_publik' => 'boolean',
    ];

    // Status workflow (dipakai oleh Filament UI)
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_REVISI = 'revisi';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    // Dipakai oleh Widgets/Code yang melakukan ->with(['kecamatan','periodeData'])
    public function periodeData(): BelongsTo
    {
        return $this->belongsTo(PeriodeData::class, 'periode_data_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    /**
     * Placeholder relasi untuk compat.
     * Jika relasi asli berbeda, bisa kita sesuaikan setelah kamu tunjukkan schema tabelnya.
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    // Actions yang dipakai oleh PendudukResource (dan kemungkinan resources lain)
    public function kirimKeKominfo(): void
    {
        $this->status = static::STATUS_DIAJUKAN;
        $this->save();
    }

    public function setujui(int $userId, ?string $catatan = null): void
    {
        $this->status = static::STATUS_DISETUJUI;
        $this->verifikator_id = $userId;
        if ($catatan !== null) {
            $this->catatan_verifikasi = $catatan;
        }
        $this->save();
    }

    public function revisi(int $userId, string $catatan = ''): void
    {
        $this->status = static::STATUS_REVISI;
        $this->verifikator_id = $userId;
        $this->catatan_verifikasi = $catatan;
        $this->save();
    }

    public function tolak(int $userId, string $catatan = ''): void
    {
        $this->status = static::STATUS_DITOLAK;
        $this->verifikator_id = $userId;
        $this->catatan_verifikasi = $catatan;
        $this->save();
    }

    // Scope placeholder
    public function scopeKecamatan(Builder $query, ?int $kecamatanId): Builder
    {
        if ($kecamatanId) {
            return $query->where('kecamatan_id', $kecamatanId);
        }

        return $query;
    }
}

