{{-- resources/views/filament/pages/input-data/_styles.blade.php --}}

@once
    <style>
        .kid-page {
            --kid-card: #ffffff;
            --kid-card-soft: #f8fafc;
            --kid-border: #e5e7eb;
            --kid-border-strong: #cbd5e1;
            --kid-text: #0f172a;
            --kid-muted: #64748b;
            --kid-muted-strong: #334155;
            --kid-primary: #0284c7;
            --kid-primary-dark: #0369a1;
            --kid-primary-soft: #e0f2fe;
            --kid-success-soft: #ecfdf5;
            --kid-success-text: #047857;
            --kid-danger-soft: #fef2f2;
            --kid-danger-text: #991b1b;
            --kid-warning-soft: #fffbeb;
            --kid-warning-text: #92400e;

            width: 100%;
            max-width: 1180px;
            color: var(--kid-text);
            font-size: 14px;
            line-height: 1.5;
        }

        html.dark .kid-page,
        .dark .kid-page {
            --kid-card: #0f172a;
            --kid-card-soft: #111827;
            --kid-border: rgba(148, 163, 184, 0.22);
            --kid-border-strong: rgba(148, 163, 184, 0.36);
            --kid-text: #f8fafc;
            --kid-muted: #cbd5e1;
            --kid-muted-strong: #e2e8f0;
            --kid-primary: #38bdf8;
            --kid-primary-dark: #0ea5e9;
            --kid-primary-soft: rgba(14, 165, 233, 0.14);
            --kid-success-soft: rgba(6, 78, 59, 0.28);
            --kid-success-text: #a7f3d0;
            --kid-danger-soft: rgba(127, 29, 29, 0.26);
            --kid-danger-text: #fecaca;
            --kid-warning-soft: rgba(120, 53, 15, 0.25);
            --kid-warning-text: #fde68a;
        }

        .kid-page * {
            box-sizing: border-box;
        }

        .kid-stack {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .kid-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 24px;
            border: 1px solid var(--kid-border);
            border-radius: 22px;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 36%),
                var(--kid-card);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        }

        .kid-eyebrow {
            margin: 0 0 6px;
            color: var(--kid-primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .kid-title {
            margin: 0;
            color: var(--kid-text);
            font-size: 24px;
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .kid-subtitle {
            margin: 8px 0 0;
            max-width: 720px;
            color: var(--kid-muted);
        }

        .kid-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(130px, 1fr));
            gap: 10px;
            min-width: 280px;
        }

        .kid-summary-item {
            padding: 14px 16px;
            border: 1px solid var(--kid-border);
            border-radius: 16px;
            background: var(--kid-card-soft);
        }

        .kid-summary-label {
            display: block;
            margin-bottom: 4px;
            color: var(--kid-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .kid-summary-value {
            display: block;
            color: var(--kid-text);
            font-size: 16px;
            font-weight: 850;
        }

        .kid-card {
            overflow: hidden;
            border: 1px solid var(--kid-border);
            border-radius: 22px;
            background: var(--kid-card);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05);
        }

        .kid-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--kid-border);
            background: var(--kid-card-soft);
        }

        .kid-card-title {
            margin: 0;
            color: var(--kid-text);
            font-size: 16px;
            font-weight: 850;
        }

        .kid-card-desc {
            margin: 4px 0 0;
            color: var(--kid-muted);
            font-size: 13px;
        }

        .kid-card-body {
            padding: 24px;
        }

        .kid-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 18px;
        }

        .kid-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 18px;
        }

        .kid-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .kid-label {
            color: var(--kid-muted-strong);
            font-size: 13px;
            font-weight: 800;
        }

        .kid-control {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid var(--kid-border-strong);
            border-radius: 12px;
            background: var(--kid-card);
            color: var(--kid-text);
            font-size: 14px;
            outline: none;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .kid-control:focus {
            border-color: var(--kid-primary);
            box-shadow: 0 0 0 4px var(--kid-primary-soft);
        }

        .kid-control:disabled {
            cursor: not-allowed;
            opacity: .65;
            background: var(--kid-card-soft);
        }

        .kid-control::placeholder {
            color: var(--kid-muted);
        }

        .kid-info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .kid-info-item {
            padding: 14px;
            border: 1px solid var(--kid-border);
            border-radius: 16px;
            background: var(--kid-card-soft);
        }

        .kid-info-label {
            display: block;
            margin-bottom: 5px;
            color: var(--kid-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .kid-info-value {
            color: var(--kid-text);
            font-weight: 850;
        }

        .kid-help {
            margin-top: 18px;
            padding: 14px 16px;
            border: 1px solid var(--kid-border);
            border-radius: 16px;
            background: var(--kid-primary-soft);
            color: var(--kid-muted-strong);
        }

        .kid-alert {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid transparent;
            font-weight: 700;
        }

        .kid-alert-success {
            background: var(--kid-success-soft);
            color: var(--kid-success-text);
            border-color: rgba(16, 185, 129, .22);
        }

        .kid-alert-danger {
            background: var(--kid-danger-soft);
            color: var(--kid-danger-text);
            border-color: rgba(239, 68, 68, .22);
        }

        .kid-alert-warning {
            background: var(--kid-warning-soft);
            color: var(--kid-warning-text);
            border-color: rgba(245, 158, 11, .26);
        }

        .kid-alert ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .kid-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--kid-border);
            border-radius: 18px;
            background: var(--kid-card);
        }

        .kid-table {
            width: 100%;
            min-width: 760px;
            border-collapse: collapse;
            color: var(--kid-text);
        }

        .kid-table thead {
            background: var(--kid-card-soft);
        }

        .kid-table th {
            padding: 14px 16px;
            border-bottom: 1px solid var(--kid-border);
            color: var(--kid-muted-strong);
            text-align: left;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kid-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--kid-border);
            color: var(--kid-text);
            vertical-align: middle;
        }

        .kid-table tbody tr:last-child td {
            border-bottom: none;
        }

        .kid-table tbody tr:hover {
            background: rgba(14, 165, 233, .045);
        }

        html.dark .kid-table tbody tr:hover,
        .dark .kid-table tbody tr:hover {
            background: rgba(14, 165, 233, .08);
        }

        .kid-row-number {
            width: 54px;
            color: var(--kid-muted);
            font-weight: 800;
        }

        .kid-strong {
            color: var(--kid-text);
            font-weight: 850;
        }

        .kid-muted {
            color: var(--kid-muted);
            font-size: 12px;
        }

        .kid-unit {
            color: var(--kid-muted-strong);
            font-weight: 800;
            white-space: nowrap;
        }

        .kid-empty {
            padding: 42px 24px;
            text-align: center;
            color: var(--kid-muted);
        }

        .kid-empty-title {
            margin: 0;
            color: var(--kid-text);
            font-size: 16px;
            font-weight: 850;
        }

        .kid-empty-text {
            margin: 8px auto 0;
            max-width: 520px;
            color: var(--kid-muted);
        }

        .kid-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            padding: 18px 24px;
            border-top: 1px solid var(--kid-border);
            background: var(--kid-card-soft);
        }

        .kid-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 18px;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 850;
            cursor: pointer;
            text-decoration: none;
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }

        .kid-button:hover {
            transform: translateY(-1px);
        }

        .kid-button-primary {
            background: var(--kid-primary);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(2, 132, 199, .22);
        }

        .kid-button-primary:hover {
            background: var(--kid-primary-dark);
        }

        .kid-button-secondary {
            background: var(--kid-card);
            color: var(--kid-text);
            border-color: var(--kid-border-strong);
        }

        .kid-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            background: var(--kid-primary-soft);
            color: var(--kid-primary-dark);
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }

        .kid-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 220px;
            padding: 28px;
            border: 2px dashed var(--kid-border-strong);
            border-radius: 22px;
            background: var(--kid-card-soft);
            text-align: center;
        }

        .kid-dropzone-title {
            margin: 0;
            color: var(--kid-text);
            font-size: 18px;
            font-weight: 850;
        }

        .kid-dropzone-desc {
            margin: 0;
            max-width: 560px;
            color: var(--kid-muted);
        }

        @media (max-width: 960px) {
            .kid-header {
                flex-direction: column;
            }

            .kid-summary {
                width: 100%;
            }

            .kid-grid,
            .kid-grid-3,
            .kid-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .kid-header,
            .kid-card-header,
            .kid-card-body,
            .kid-actions {
                padding: 16px;
            }

            .kid-title {
                font-size: 20px;
            }

            .kid-summary {
                grid-template-columns: 1fr;
            }

            .kid-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .kid-button {
                width: 100%;
            }
        }
    </style>
@endonce