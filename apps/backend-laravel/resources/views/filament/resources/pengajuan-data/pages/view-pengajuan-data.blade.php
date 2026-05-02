<x-filament-panels::page>
    @includeIf('filament.pages._kominfo-page-style')

    @php
        $record = $this->record;
        $review = $this->reviewData;
        $statusLabels = \App\Support\ResourceOptions::statusData();
        $status = (string) ($record->status ?? 'draft');
        $statusLabel = $statusLabels[$status] ?? $status;
        $statusClass = [
            'draft' => 'kom-status-badge-gray',
            'diajukan' => 'kom-status-badge-info',
            'revisi' => 'kom-status-badge-warning',
            'terverifikasi' => 'kom-status-badge-success',
            'ditolak' => 'kom-status-badge-danger',
            'terbit' => 'kom-status-badge-primary',
        ][$status] ?? 'kom-status-badge-gray';
        $percent = min(100, max(0, (float) ($review['percent'] ?? 0)));
        $isComplete = $percent >= 100;

        $summaryItems = [
            'Kecamatan' => $record->kecamatan?->nama ?? '-',
            'OPD' => $record->opd?->nama ?? '-',
            'Periode' => $record->periodeData?->label ?? '-',
            'Kelompok Indikator' => $record->kelompok_indikator ?: '-',
            'Status' => $statusLabel,
            'Dikirim oleh' => $record->dikirimOleh?->nama ?? $record->submittedBy?->nama ?? '-',
            'Tanggal kirim' => $record->tanggal_kirim?->format('d M Y H:i') ?? $record->submitted_at?->format('d M Y H:i') ?? '-',
            'Diverifikasi oleh' => $record->diverifikasiOleh?->nama ?? $record->verifiedBy?->nama ?? '-',
            'Tanggal verifikasi' => $record->tanggal_verifikasi?->format('d M Y H:i') ?? $record->verified_at?->format('d M Y H:i') ?? '-',
            'Tanggal terbit' => $record->tanggal_terbit?->format('d M Y H:i') ?? $record->published_at?->format('d M Y H:i') ?? '-',
        ];

        $noteItems = [
            ['label' => 'Catatan Kecamatan', 'value' => $record->catatan ?: '-', 'class' => 'kom-review-note'],
            ['label' => 'Catatan Revisi', 'value' => $record->catatan_revisi ?: '-', 'class' => 'kom-review-note kom-review-note-warning'],
            ['label' => 'Catatan Verifikasi', 'value' => $record->catatan_verifikasi ?: '-', 'class' => 'kom-review-note kom-review-note-success'],
        ];

        $kpis = [
            ['label' => 'Desa Aktif', 'value' => $review['desa_count'] ?? 0, 'tone' => ''],
            ['label' => 'Indikator Wajib', 'value' => $review['required_indicator_count'] ?? 0, 'tone' => ''],
            ['label' => 'Total Wajib', 'value' => $review['total_required'] ?? 0, 'tone' => ''],
            ['label' => 'Sudah Diisi', 'value' => $review['filled_required'] ?? 0, 'tone' => 'success'],
            ['label' => 'Belum Diisi', 'value' => $review['missing_required'] ?? 0, 'tone' => ($review['missing_required'] ?? 0) > 0 ? 'warning' : ''],
            ['label' => 'Kelengkapan', 'value' => rtrim(rtrim(number_format($percent, 1), '0'), '.') . '%', 'tone' => $isComplete ? 'success' : 'warning', 'large' => true],
        ];

        $statusBadgeClass = fn (string $rowStatus): string => match ($rowStatus) {
            'Terisi' => 'kom-status-badge-success',
            'Nol' => 'kom-status-badge-info',
            'Tidak tersedia' => 'kom-status-badge-gray',
            default => 'kom-status-badge-warning',
        };
    @endphp

    <div class="kom-page">
        <div class="kom-stack">
            <section class="kom-page-header">
                <div>
                    <p class="kom-eyebrow">Tinjauan Pengajuan</p>
                    <h1 class="kom-title">Tinjauan Data Mentah</h1>
                    <p class="kom-subtitle">
                        Periksa kelengkapan dan kualitas data sebelum diverifikasi atau diterbitkan.
                    </p>
                </div>

                <div class="kom-summary">
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Status</span>
                        <span class="kom-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Periode</span>
                        <span class="kom-summary-value">{{ $record->periodeData?->label ?? '-' }}</span>
                    </div>
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Kecamatan</span>
                        <span class="kom-summary-value">{{ $record->kecamatan?->nama ?? '-' }}</span>
                    </div>
                    <div class="kom-summary-item">
                        <span class="kom-summary-label">Kelompok</span>
                        <span class="kom-summary-value">{{ $record->kelompok_indikator ?: '-' }}</span>
                    </div>
                </div>
            </section>

            <section class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Ringkasan Pengajuan</h2>
                        <p class="kom-card-desc">Informasi utama pengajuan data mentah.</p>
                    </div>
                    <span class="kom-badge">#{{ $record->id }}</span>
                </div>
                <div class="kom-card-body">
                    <div class="kom-grid-3">
                        @foreach ($summaryItems as $label => $value)
                            <div class="kom-summary-item">
                                <span class="kom-summary-label">{{ $label }}</span>
                                @if ($label === 'Status')
                                    <span class="kom-status-badge {{ $statusClass }}">{{ $value }}</span>
                                @else
                                    <span class="kom-summary-value">{{ $value }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="kom-grid-3">
                @foreach ($noteItems as $note)
                    <article class="{{ $note['class'] }}">
                        <span class="kom-summary-label">{{ $note['label'] }}</span>
                        <p>{{ $note['value'] }}</p>
                    </article>
                @endforeach
            </div>

            <section class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Progress Kelengkapan</h2>
                        <p class="kom-card-desc">Kalkulasi data wajib berdasarkan desa aktif dan indikator wajib.</p>
                    </div>
                    <span class="kom-status-badge {{ $isComplete ? 'kom-status-badge-success' : 'kom-status-badge-warning' }}">
                        {{ $isComplete ? 'Lengkap' : 'Perlu Dilengkapi' }}
                    </span>
                </div>
                <div class="kom-card-body">
                    <div class="kom-grid-6">
                        @foreach ($kpis as $kpi)
                            <div class="kom-kpi {{ filled($kpi['tone']) ? 'kom-kpi-' . $kpi['tone'] : '' }}">
                                <span class="kom-summary-label">{{ $kpi['label'] }}</span>
                                <strong class="{{ ($kpi['large'] ?? false) ? 'kom-kpi-large' : '' }}">{{ $kpi['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>

                    <div class="kom-progress-bar" aria-label="Progress kelengkapan">
                        <div class="kom-progress-fill {{ $isComplete ? 'kom-progress-fill-success' : 'kom-progress-fill-warning' }}" style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            </section>

            <section class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Temuan Tinjauan</h2>
                        <p class="kom-card-desc">Daftar hal yang perlu diperhatikan sebelum keputusan workflow.</p>
                    </div>
                    <span class="kom-badge">{{ $review['findings']->count() }} Temuan</span>
                </div>
                <div class="kom-card-body">
                    @if ($review['findings']->isEmpty())
                        <div class="kom-alert kom-alert-success">
                            Belum ditemukan masalah pada data ini.
                        </div>
                    @else
                        <div class="kom-alert kom-alert-warning">
                            Ada beberapa temuan yang perlu ditinjau.
                        </div>

                        <div class="kom-finding-list">
                            @foreach ($review['findings'] as $finding)
                                <div class="kom-finding-item">{{ $finding }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Tabel Tinjauan Nilai Mentah</h2>
                        <p class="kom-card-desc">Detail nilai per indikator dan desa.</p>
                    </div>
                    <span class="kom-badge">{{ $review['detail_rows']->count() }} Baris</span>
                </div>
                <div class="kom-card-body">
                    <div class="kom-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Indikator</th>
                                    <th>Kategori</th>
                                    <th>Desa</th>
                                    <th>Nilai</th>
                                    <th>Satuan</th>
                                    <th>Status Isi</th>
                                    <th>Catatan</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($review['detail_rows'] as $row)
                                    <tr>
                                        <td class="kom-table-primary">{{ $row['indicator'] }}</td>
                                        <td>{{ $row['category'] ?: '-' }}</td>
                                        <td>{{ $row['desa'] }}</td>
                                        <td class="kom-table-primary">{{ $row['value'] ?? '-' }}</td>
                                        <td>{{ $row['satuan'] ?: '-' }}</td>
                                        <td>
                                            <span class="kom-status-badge {{ $statusBadgeClass($row['status']) }}">
                                                {{ $row['status'] }}
                                            </span>
                                        </td>
                                        <td>{{ $row['catatan'] ?: '-' }}</td>
                                        <td>{{ $row['updated_at']?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="kom-empty">
                                                <p class="kom-empty-title">Belum ada indikator/desa untuk ditinjau.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="kom-card">
                <div class="kom-card-header">
                    <div>
                        <h2 class="kom-card-title">Perbandingan Periode Sebelumnya</h2>
                        <p class="kom-card-desc">Perubahan nilai dibanding periode sebelumnya jika data tersedia.</p>
                    </div>
                    <span class="kom-badge">{{ $review['comparisons']->count() }} Baris</span>
                </div>
                <div class="kom-card-body">
                    @if (! $review['has_previous_period'] || $review['comparisons']->isEmpty())
                        <div class="kom-empty">
                            <p class="kom-empty-title">Data periode sebelumnya belum tersedia.</p>
                        </div>
                    @else
                        <div class="kom-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Indikator</th>
                                        <th>Desa</th>
                                        <th>Periode Ini</th>
                                        <th>Sebelumnya</th>
                                        <th>Selisih</th>
                                        <th>% Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($review['comparisons'] as $row)
                                        <tr>
                                            <td class="kom-table-primary">{{ $row['indicator'] }}</td>
                                            <td>{{ $row['desa'] }}</td>
                                            <td class="kom-table-primary">{{ $row['value'] ?? '-' }}</td>
                                            <td>{{ $row['previous_value'] ?? '-' }}</td>
                                            <td>{{ $row['difference'] ?? '-' }}</td>
                                            <td>{{ $row['percent_change'] !== null ? $row['percent_change'] . '%' : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
