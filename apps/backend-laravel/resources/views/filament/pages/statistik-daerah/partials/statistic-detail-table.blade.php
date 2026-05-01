<section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $title ?? 'Detail Data' }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description ?? 'Data detail terbaru sesuai filter aktif.' }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3">Periode</th>
                    <th class="px-5 py-3">OPD</th>
                    <th class="px-5 py-3">Kelompok</th>
                    <th class="px-5 py-3">Indikator</th>
                    <th class="px-5 py-3">Kecamatan</th>
                    <th class="px-5 py-3">Desa</th>
                    <th class="px-5 py-3">Sumber Data</th>
                    <th class="px-5 py-3 text-right">Nilai</th>
                    <th class="px-5 py-3">Satuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $row)
                    <tr class="odd:bg-white even:bg-gray-50/70 hover:bg-cyan-50/60 dark:odd:bg-gray-900 dark:even:bg-gray-950/50 dark:hover:bg-cyan-950/20">
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['periode'] }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['opd'] }}</td>
                        <td class="px-5 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $row['kelompok'] }}</span></td>
                        <td class="px-5 py-3 font-medium text-gray-950 dark:text-white">{{ $row['indikator'] }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['kecamatan'] }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['desa'] }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['sumber_data'] }}</td>
                        <td class="px-5 py-3 text-right font-semibold tabular-nums text-gray-950 dark:text-white">{{ $this->formatNumber($row['nilai']) }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $row['satuan'] ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada data detail untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
