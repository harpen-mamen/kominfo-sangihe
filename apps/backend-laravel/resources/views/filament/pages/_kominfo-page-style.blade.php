{{-- resources/views/filament/pages/_kominfo-page-style.blade.php --}}

@once
    <style>
        /*
        |--------------------------------------------------------------------------
        | Kominfo Custom Filament Page Style
        |--------------------------------------------------------------------------
        | File ini dipakai untuk merapikan semua custom blade page di:
        | resources/views/filament/pages
        |
        | Tujuan:
        | - tabel tidak polos
        | - input/select/textarea terlihat jelas
        | - warna teks aman di light/dark mode
        | - card, filter, chart, tombol lebih rapi
        | - tidak mengganggu table bawaan Filament sebanyak mungkin
        */

        .fi-page {
            --kom-card: #ffffff;
            --kom-card-soft: #f8fafc;
            --kom-border: #e5e7eb;
            --kom-border-strong: #cbd5e1;
            --kom-text: #0f172a;
            --kom-text-soft: #475569;
            --kom-muted: #64748b;
            --kom-primary: #0284c7;
            --kom-primary-dark: #0369a1;
            --kom-primary-soft: #e0f2fe;
            --kom-success-soft: #ecfdf5;
            --kom-success-text: #047857;
            --kom-warning-soft: #fffbeb;
            --kom-warning-text: #92400e;
            --kom-danger-soft: #fef2f2;
            --kom-danger-text: #991b1b;

            color: var(--kom-text);
        }

        html.dark .fi-page,
        .dark .fi-page {
            --kom-card: #0f172a;
            --kom-card-soft: #111827;
            --kom-border: rgba(148, 163, 184, 0.22);
            --kom-border-strong: rgba(148, 163, 184, 0.36);
            --kom-text: #f8fafc;
            --kom-text-soft: #e2e8f0;
            --kom-muted: #cbd5e1;
            --kom-primary: #38bdf8;
            --kom-primary-dark: #0ea5e9;
            --kom-primary-soft: rgba(14, 165, 233, 0.14);
            --kom-success-soft: rgba(6, 78, 59, 0.28);
            --kom-success-text: #a7f3d0;
            --kom-warning-soft: rgba(120, 53, 15, 0.25);
            --kom-warning-text: #fde68a;
            --kom-danger-soft: rgba(127, 29, 29, 0.26);
            --kom-danger-text: #fecaca;
        }

        .fi-page * {
            box-sizing: border-box;
        }

        /*
        |--------------------------------------------------------------------------
        | Typography umum
        |--------------------------------------------------------------------------
        */

        .fi-page h1,
        .fi-page h2,
        .fi-page h3,
        .fi-page h4,
        .fi-page h5,
        .fi-page h6 {
            color: var(--kom-text);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .fi-page p,
        .fi-page span,
        .fi-page label,
        .fi-page small,
        .fi-page div {
            color: inherit;
        }

        .fi-page label {
            font-weight: 700;
            color: var(--kom-text-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Layout helper untuk custom page
        |--------------------------------------------------------------------------
        */

        .kom-page {
            width: 100%;
            max-width: 1180px;
            color: var(--kom-text);
        }

        .kom-stack {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .kom-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding: 24px;
            border: 1px solid var(--kom-border);
            border-radius: 22px;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 36%),
                var(--kom-card);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        }

        .kom-eyebrow {
            margin: 0 0 6px;
            color: var(--kom-primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .kom-title {
            margin: 0;
            color: var(--kom-text);
            font-size: 24px;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .kom-subtitle {
            margin: 8px 0 0;
            max-width: 720px;
            color: var(--kom-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .kom-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(130px, 1fr));
            gap: 10px;
            min-width: 280px;
        }

        .kom-summary-item {
            padding: 14px 16px;
            border: 1px solid var(--kom-border);
            border-radius: 16px;
            background: var(--kom-card-soft);
        }

        .kom-summary-label {
            display: block;
            margin-bottom: 4px;
            color: var(--kom-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .kom-summary-value {
            display: block;
            color: var(--kom-text);
            font-size: 16px;
            font-weight: 850;
        }

        /*
        |--------------------------------------------------------------------------
        | Card umum
        |--------------------------------------------------------------------------
        */

        .kom-card,
        .fi-page .app-card,
        .fi-page .card,
        .fi-page section:not(.fi-section):not([class*="fi-"]) {
            overflow: hidden;
            border: 1px solid var(--kom-border);
            border-radius: 22px;
            background: var(--kom-card);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05);
        }

        .kom-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--kom-border);
            background: var(--kom-card-soft);
        }

        .kom-card-title {
            margin: 0;
            color: var(--kom-text);
            font-size: 16px;
            font-weight: 850;
        }

        .kom-card-desc {
            margin: 4px 0 0;
            color: var(--kom-muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .kom-card-body {
            padding: 24px;
        }

        .fi-page .kom-page-header {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 36%),
                var(--kom-card);
        }

        /*
        |--------------------------------------------------------------------------
        | Grid/filter
        |--------------------------------------------------------------------------
        */

        .kom-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 18px;
        }

        .kom-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 18px;
        }

        .kom-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, minmax(160px, 1fr));
            gap: 18px;
        }

        .kom-grid-6 {
            display: grid;
            grid-template-columns: repeat(6, minmax(130px, 1fr));
            gap: 14px;
        }

        .kom-filter-bar,
        .fi-page form > div:first-child:not(.fi-fo-component-ctn) {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
            margin-bottom: 18px;
        }

        /*
        |--------------------------------------------------------------------------
        | Input/select/textarea HTML polos
        |--------------------------------------------------------------------------
        */

        .fi-page input:not([type="checkbox"]):not([type="radio"]):not(.fi-input):not([class*="choices"]),
        .fi-page select:not(.fi-select-input):not([class*="choices"]),
        .fi-page textarea:not(.fi-input) {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid var(--kom-border-strong);
            border-radius: 12px;
            background: var(--kom-card);
            color: var(--kom-text);
            font-size: 14px;
            outline: none;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .fi-page input:not([type="checkbox"]):not([type="radio"]):focus,
        .fi-page select:focus,
        .fi-page textarea:focus {
            border-color: var(--kom-primary);
            box-shadow: 0 0 0 4px var(--kom-primary-soft);
        }

        .fi-page input::placeholder,
        .fi-page textarea::placeholder {
            color: var(--kom-muted);
        }

        .fi-page input:disabled,
        .fi-page select:disabled,
        .fi-page textarea:disabled {
            cursor: not-allowed;
            opacity: .65;
            background: var(--kom-card-soft);
        }

        .fi-page input[type="file"] {
            padding: 8px;
            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | Tombol HTML polos
        |--------------------------------------------------------------------------
        */

        .fi-page button:not(.fi-btn):not([class*="fi-"]),
        .fi-page a.button,
        .kom-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 18px;
            border: 1px solid transparent;
            border-radius: 12px;
            background: var(--kom-primary);
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 850;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(2, 132, 199, .22);
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }

        .fi-page button:not(.fi-btn):not([class*="fi-"]):hover,
        .fi-page a.button:hover,
        .kom-button:hover {
            transform: translateY(-1px);
            background: var(--kom-primary-dark);
        }

        .kom-button-secondary {
            background: var(--kom-card) !important;
            color: var(--kom-text) !important;
            border-color: var(--kom-border-strong) !important;
            box-shadow: none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Tabel HTML polos
        |--------------------------------------------------------------------------
        */

        .fi-page table:not(.fi-ta-table) {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            color: var(--kom-text);
            background: var(--kom-card);
        }

        .fi-page table:not(.fi-ta-table) thead {
            background: var(--kom-card-soft);
        }

        .fi-page table:not(.fi-ta-table) th {
            padding: 14px 16px;
            border-bottom: 1px solid var(--kom-border);
            color: var(--kom-text-soft);
            text-align: left;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .fi-page table:not(.fi-ta-table) td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--kom-border);
            color: var(--kom-text);
            vertical-align: middle;
        }

        .fi-page table:not(.fi-ta-table) tbody tr:last-child td {
            border-bottom: none;
        }

        .fi-page table:not(.fi-ta-table) tbody tr:hover {
            background: rgba(14, 165, 233, .045);
        }

        html.dark .fi-page table:not(.fi-ta-table) tbody tr:hover,
        .dark .fi-page table:not(.fi-ta-table) tbody tr:hover {
            background: rgba(14, 165, 233, .08);
        }

        .fi-page table:not(.fi-ta-table) input,
        .fi-page table:not(.fi-ta-table) select {
            min-width: 120px;
        }

        .kom-table-wrap,
        .fi-page .table-responsive,
        .fi-page .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--kom-border);
            border-radius: 18px;
            background: var(--kom-card);
        }

        /*
        |--------------------------------------------------------------------------
        | Badge/alert
        |--------------------------------------------------------------------------
        */

        .kom-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--kom-primary-soft);
            color: var(--kom-primary-dark);
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }

        .kom-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: max-content;
            max-width: 100%;
            min-height: 28px;
            padding: 5px 10px;
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 850;
            line-height: 1.1;
            white-space: nowrap;
        }

        .kom-status-badge-gray {
            background: var(--kom-card-soft);
            border-color: var(--kom-border);
            color: var(--kom-muted);
        }

        .kom-status-badge-info,
        .kom-status-badge-primary {
            background: var(--kom-primary-soft);
            border-color: rgba(14, 165, 233, .24);
            color: var(--kom-primary-dark);
        }

        .kom-status-badge-success {
            background: var(--kom-success-soft);
            border-color: rgba(16, 185, 129, .22);
            color: var(--kom-success-text);
        }

        .kom-status-badge-warning {
            background: var(--kom-warning-soft);
            border-color: rgba(245, 158, 11, .26);
            color: var(--kom-warning-text);
        }

        .kom-status-badge-danger {
            background: var(--kom-danger-soft);
            border-color: rgba(239, 68, 68, .22);
            color: var(--kom-danger-text);
        }

        .kom-alert {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid transparent;
            font-weight: 700;
        }

        /*
        |--------------------------------------------------------------------------
        | Review pengajuan data mentah
        |--------------------------------------------------------------------------
        */

        .kom-review-note {
            min-height: 136px;
            padding: 18px;
            border: 1px solid var(--kom-border);
            border-radius: 18px;
            background: var(--kom-card);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
        }

        .kom-review-note p {
            margin: 10px 0 0;
            color: var(--kom-text);
            font-size: 14px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .kom-review-note-warning {
            border-color: rgba(245, 158, 11, .26);
            background: var(--kom-warning-soft);
        }

        .kom-review-note-success {
            border-color: rgba(16, 185, 129, .22);
            background: var(--kom-success-soft);
        }

        .kom-kpi {
            min-height: 116px;
            padding: 16px;
            border: 1px solid var(--kom-border);
            border-radius: 18px;
            background: var(--kom-card-soft);
        }

        .kom-kpi strong {
            display: block;
            margin-top: 8px;
            color: var(--kom-text);
            font-size: 24px;
            font-weight: 900;
            line-height: 1.05;
        }

        .kom-kpi .kom-kpi-large {
            font-size: 30px;
        }

        .kom-kpi-success {
            background: var(--kom-success-soft);
            border-color: rgba(16, 185, 129, .22);
        }

        .kom-kpi-warning {
            background: var(--kom-warning-soft);
            border-color: rgba(245, 158, 11, .26);
        }

        .kom-progress-bar {
            height: 10px;
            margin-top: 18px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--kom-card-soft);
            border: 1px solid var(--kom-border);
        }

        .kom-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: var(--kom-primary);
            transition: width .2s ease;
        }

        .kom-progress-fill-success {
            background: #10b981;
        }

        .kom-progress-fill-warning {
            background: #f59e0b;
        }

        .kom-finding-list {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }

        .kom-finding-item {
            padding: 12px 14px;
            border: 1px solid rgba(245, 158, 11, .26);
            border-radius: 14px;
            background: var(--kom-warning-soft);
            color: var(--kom-warning-text);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }

        .kom-table-primary {
            color: var(--kom-text);
            font-weight: 850;
        }

        .kom-alert-success {
            background: var(--kom-success-soft);
            color: var(--kom-success-text);
            border-color: rgba(16, 185, 129, .22);
        }

        .kom-alert-warning {
            background: var(--kom-warning-soft);
            color: var(--kom-warning-text);
            border-color: rgba(245, 158, 11, .26);
        }

        .kom-alert-danger {
            background: var(--kom-danger-soft);
            color: var(--kom-danger-text);
            border-color: rgba(239, 68, 68, .22);
        }

        /*
        |--------------------------------------------------------------------------
        | Chart/canvas
        |--------------------------------------------------------------------------
        */

        .fi-page canvas {
            max-width: 100%;
        }

        .kom-chart-card,
        .fi-page .chart-card,
        .fi-page [id^="chart"],
        .fi-page canvas {
            color: var(--kom-text);
        }

        .kom-chart-box {
            min-height: 340px;
            padding: 20px;
            border: 1px solid var(--kom-border);
            border-radius: 20px;
            background: var(--kom-card);
        }

        /*
        |--------------------------------------------------------------------------
        | Empty state
        |--------------------------------------------------------------------------
        */

        .kom-empty {
            padding: 42px 24px;
            text-align: center;
            color: var(--kom-muted);
        }

        .kom-empty-title {
            margin: 0;
            color: var(--kom-text);
            font-size: 16px;
            font-weight: 850;
        }

        .kom-empty-text {
            margin: 8px auto 0;
            max-width: 520px;
            color: var(--kom-muted);
        }

        /*
        |--------------------------------------------------------------------------
        | Utility
        |--------------------------------------------------------------------------
        */

        .kom-text-strong {
            color: var(--kom-text);
            font-weight: 850;
        }

        .kom-text-muted {
            color: var(--kom-muted);
            font-size: 12px;
        }

        .kom-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding-top: 18px;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 960px) {
            .kom-page-header {
                flex-direction: column;
            }

            .kom-summary {
                width: 100%;
            }

            .kom-grid,
            .kom-grid-3,
            .kom-grid-4,
            .kom-grid-6 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .kom-page-header,
            .kom-card-header,
            .kom-card-body {
                padding: 16px;
            }

            .kom-title {
                font-size: 20px;
            }

            .kom-summary {
                grid-template-columns: 1fr;
            }

            .kom-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .kom-button,
            .fi-page button:not(.fi-btn):not([class*="fi-"]) {
                width: 100%;
            }
        }
    </style>
@endonce
