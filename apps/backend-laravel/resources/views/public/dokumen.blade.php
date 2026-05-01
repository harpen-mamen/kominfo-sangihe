<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - Kominfo Sangihe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">Portal Publik</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight">{{ $title }}</h1>
            </div>
            <a href="/" class="text-sm font-medium text-sky-700 hover:text-sky-900">Kembali ke Beranda</a>
        </div>

        <form method="get" class="mb-8 grid gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            <select name="tahun" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua tahun</option>
                @foreach ($tahunOptions as $id => $label)
                    <option value="{{ $id }}" @selected((string) request('tahun') === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="jenis_dokumen" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua jenis</option>
                @foreach ($jenisOptions as $id => $label)
                    <option value="{{ $id }}" @selected(request('jenis_dokumen') === $id)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="kecamatan_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua kecamatan</option>
                @foreach ($kecamatanOptions as $id => $label)
                    <option value="{{ $id }}" @selected((string) request('kecamatan_id') === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="desa_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Semua desa</option>
                @foreach ($desaOptions as $id => $label)
                    <option value="{{ $id }}" @selected((string) request('desa_id') === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Filter</button>
        </form>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($documents as $document)
                <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                        <span>{{ $jenisOptions[$document->jenis_dokumen] ?? $document->jenis_dokumen }}</span>
                        <span>{{ $document->tahun ?: '-' }}</span>
                    </div>
                    <h2 class="mt-3 text-lg font-semibold leading-snug">{{ $document->judul }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $document->ringkasan ?: 'Dokumen resmi yang telah diterbitkan untuk publik.' }}</p>
                    <dl class="mt-4 space-y-1 text-sm text-slate-600">
                        <div class="flex justify-between gap-4"><dt>Kecamatan</dt><dd>{{ $document->kecamatan?->nama ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt>Desa</dt><dd>{{ $document->desa?->nama ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt>OPD</dt><dd>{{ $document->opd?->nama ?? '-' }}</dd></div>
                    </dl>
                    <div class="mt-5 flex gap-3">
                        <a href="{{ Storage::disk('public')->url($document->file_path) }}" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat</a>
                        <a href="{{ Storage::disk('public')->url($document->file_path) }}" download class="rounded-lg bg-sky-700 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-800">Download</a>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                    Belum ada dokumen terbit untuk filter ini.
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $documents->links() }}</div>
    </main>
</body>
</html>
