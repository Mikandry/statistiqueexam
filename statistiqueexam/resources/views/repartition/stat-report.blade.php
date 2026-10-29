<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Statistique N / N-1</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">

    @unless($pdfMode ?? false)
        @include('partials.head-assets')
    @endunless

    <style>
        body { font-family: var(--app-font-sans); }
        .bar-track { background: #eef2ff; }
        .bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
        .bar-fill.negative { background: linear-gradient(90deg, #f97316, #ef4444); }
        .ai-report-shell {
            color: #111827;
        }
        .ai-eyebrow {
            align-items: center;
            color: #4f46e5;
            display: inline-flex;
            font-size: 11px;
            font-weight: 900;
            gap: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .ai-eyebrow:before,
        .ai-eyebrow:after {
            background: #4f46e5;
            content: "";
            display: block;
            height: 2px;
            width: 44px;
        }
        .ai-report-hero {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            overflow: hidden;
            position: relative;
        }
        .ai-hero-layout {
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(280px, .95fr) minmax(360px, 1.25fr);
            padding: 34px;
        }
        .ai-photo-frame {
            background: #0f172a;
            border-radius: 78px 78px 24px 24px;
            min-height: 430px;
            overflow: hidden;
            position: relative;
        }
        .ai-photo-frame img {
            display: block;
            height: 430px;
            object-fit: cover;
            width: 100%;
        }
        .ai-floating-badge {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(226,232,240,.9);
            border-radius: 18px;
            bottom: 26px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
            left: 28px;
            padding: 14px 18px;
            position: absolute;
            right: 28px;
        }
        .ai-hero-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }
        .ai-hero-title {
            font-size: 58px;
            font-weight: 950;
            line-height: .98;
            margin: 18px 0 18px;
            max-width: 840px;
        }
        .ai-hero-title strong {
            color: #4338ca;
            font-weight: inherit;
        }
        .ai-hero-text {
            color: #475569;
            font-size: 16px;
            line-height: 1.7;
            max-width: 680px;
        }
        .ai-hero-metrics {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 28px;
        }
        .ai-metric-tile {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px;
        }
        .ai-metric-label {
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .ai-metric-value {
            color: #0f172a;
            font-size: 28px;
            font-weight: 950;
            margin-top: 8px;
            white-space: nowrap;
        }
        .ai-metric-note {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin-top: 4px;
        }
        .ai-visual-strip {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .ai-visual-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            overflow: hidden;
        }
        .ai-visual-card img {
            display: block;
            height: 220px;
            object-fit: cover;
            width: 100%;
        }
        .ai-visual-body {
            padding: 18px 20px 20px;
        }
        .ai-visual-title {
            color: #0f172a;
            font-size: 20px;
            font-weight: 950;
        }
        .ai-visual-kicker {
            color: #4f46e5;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .ai-visual-text {
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            margin-top: 10px;
        }
        .ai-insight-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: minmax(0, 1fr) minmax(320px, .72fr);
        }
        .ai-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 24px;
        }
        .ai-panel.dark {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }
        .ai-panel-title {
            color: inherit;
            font-size: 22px;
            font-weight: 950;
            margin-bottom: 16px;
        }
        .ai-chart-row {
            margin-top: 13px;
        }
        .ai-chart-head {
            align-items: center;
            color: #475569;
            display: flex;
            font-size: 12px;
            font-weight: 800;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .ai-track {
            background: #eef2ff;
            border-radius: 999px;
            height: 12px;
            overflow: hidden;
        }
        .ai-fill {
            background: linear-gradient(90deg, #14b8a6, #4f46e5);
            border-radius: inherit;
            height: 100%;
        }
        .ai-analysis-list {
            display: grid;
            gap: 14px;
        }
        .ai-analysis-item {
            border-left: 4px solid #4f46e5;
            padding-left: 15px;
        }
        .ai-analysis-item.green {
            border-left-color: #10b981;
        }
        .ai-analysis-item.amber {
            border-left-color: #f59e0b;
        }
        .ai-analysis-title {
            color: #0f172a;
            font-size: 16px;
            font-weight: 950;
        }
        .ai-analysis-text {
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            margin-top: 4px;
        }
        .ai-mini-stat {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 18px;
            padding: 16px;
        }
        .ai-mini-stat + .ai-mini-stat {
            margin-top: 12px;
        }
        .ai-mini-label {
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .ai-mini-value {
            color: #fff;
            font-size: 30px;
            font-weight: 950;
            margin-top: 8px;
        }
        .stat-report-cover {
            background:
                linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 64, 175, .92) 52%, rgba(20, 184, 166, .88)),
                #0f172a;
            border-radius: 28px;
            color: #fff;
            overflow: hidden;
            padding: 34px;
            position: relative;
        }
        .stat-report-cover:before {
            background:
                linear-gradient(90deg, rgba(255,255,255,.12) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255,255,255,.10) 1px, transparent 1px);
            background-size: 34px 34px;
            content: "";
            inset: 0;
            opacity: .22;
            position: absolute;
        }
        .stat-cover-content {
            display: grid;
            gap: 28px;
            grid-template-columns: minmax(0, 1.12fr) minmax(320px, .88fr);
            position: relative;
            z-index: 1;
        }
        .stat-eyebrow {
            align-items: center;
            color: #bae6fd;
            display: inline-flex;
            font-size: 11px;
            font-weight: 950;
            gap: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .stat-eyebrow:before {
            background: #5eead4;
            border-radius: 999px;
            content: "";
            display: inline-block;
            height: 8px;
            width: 8px;
        }
        .stat-cover-title {
            font-size: 52px;
            font-weight: 950;
            letter-spacing: 0;
            line-height: 1.02;
            margin: 18px 0 16px;
            max-width: 780px;
        }
        .stat-cover-text {
            color: #dbeafe;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.75;
            max-width: 760px;
        }
        .stat-kpi-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 28px;
        }
        .stat-kpi-card {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 18px;
            padding: 18px;
        }
        .stat-kpi-label {
            color: #bfdbfe;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .14em;
            text-transform: uppercase;
        }
        .stat-kpi-value {
            color: #fff;
            font-size: 30px;
            font-weight: 950;
            line-height: 1.1;
            margin-top: 8px;
            white-space: nowrap;
        }
        .stat-kpi-note {
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 750;
            margin-top: 6px;
        }
        .stat-period-card {
            background: rgba(255,255,255,.95);
            border-radius: 24px;
            color: #0f172a;
            padding: 24px;
        }
        .stat-period-row {
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            padding: 13px 0;
        }
        .stat-period-row:last-child {
            border-bottom: 0;
        }
        .stat-period-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .stat-period-value {
            color: #0f172a;
            font-size: 16px;
            font-weight: 950;
            text-align: right;
        }
        .stat-section-grid {
            display: grid;
            gap: 22px;
            grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .06);
            overflow: hidden;
        }
        .stat-card-head {
            align-items: flex-start;
            background: linear-gradient(180deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 22px 24px;
        }
        .stat-card-title {
            color: #0f172a;
            font-size: 19px;
            font-weight: 950;
            margin: 0;
        }
        .stat-card-subtitle {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
            margin-top: 5px;
        }
        .stat-card-body {
            padding: 22px 24px 24px;
        }
        .stat-badge {
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            border-radius: 999px;
            color: #0e7490;
            flex-shrink: 0;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .12em;
            padding: 8px 11px;
            text-transform: uppercase;
        }
        .stat-chart-row {
            margin-top: 14px;
        }
        .stat-chart-label {
            align-items: center;
            color: #334155;
            display: flex;
            font-size: 12px;
            font-weight: 850;
            justify-content: space-between;
            margin-bottom: 7px;
        }
        .stat-chart-track {
            background: #e2e8f0;
            border-radius: 999px;
            height: 14px;
            overflow: hidden;
        }
        .stat-chart-fill {
            background: linear-gradient(90deg, #0ea5e9, #14b8a6);
            border-radius: inherit;
            height: 100%;
        }
        .stat-chart-fill.warn {
            background: linear-gradient(90deg, #f97316, #ef4444);
        }
        .stat-insight-grid {
            display: grid;
            gap: 14px;
        }
        .stat-insight {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #0ea5e9;
            border-radius: 16px;
            padding: 15px 16px;
        }
        .stat-insight.green {
            border-left-color: #14b8a6;
        }
        .stat-insight.amber {
            border-left-color: #f59e0b;
        }
        .stat-insight-title {
            color: #0f172a;
            font-size: 14px;
            font-weight: 950;
        }
        .stat-insight-text {
            color: #64748b;
            font-size: 12px;
            font-weight: 650;
            line-height: 1.55;
            margin-top: 5px;
        }
        .stat-mini-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .stat-mini {
            background: #0f172a;
            border-radius: 18px;
            color: #fff;
            padding: 18px;
        }
        .stat-mini:nth-child(2) {
            background: #164e63;
        }
        .stat-mini:nth-child(3) {
            background: #115e59;
        }
        .stat-mini:nth-child(4) {
            background: #312e81;
        }
        .stat-mini-label {
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .stat-mini-value {
            font-size: 22px;
            font-weight: 950;
            margin-top: 8px;
            word-break: break-word;
        }
        .no-print { display: {{ ($pdfMode ?? false) ? 'none' : 'block' }}; }
        .pdf-only { display: {{ ($pdfMode ?? false) ? 'block' : 'none' }}; }
        @media (max-width: 1100px) {
            .ai-hero-layout,
            .ai-insight-grid,
            .ai-visual-strip,
            .stat-cover-content,
            .stat-section-grid {
                grid-template-columns: 1fr;
            }
            .ai-hero-title {
                font-size: 42px;
            }
            .stat-cover-title {
                font-size: 38px;
            }
            .stat-kpi-grid,
            .stat-mini-grid {
                grid-template-columns: 1fr;
            }
        }
        @media print {
            .no-print { display: none !important; }
            .pdf-only { display: block !important; }
            main {
                padding: 0 !important;
            }
            .ai-report-hero,
            .ai-panel,
            .ai-visual-card,
            .stat-report-cover,
            .stat-card {
                break-inside: avoid;
                box-shadow: none !important;
            }
            .ai-hero-title {
                font-size: 42px;
            }
            .ai-photo-frame,
            .ai-photo-frame img {
                height: 330px;
                min-height: 330px;
            }
            .stat-cover-title {
                font-size: 34px;
            }
            .stat-report-cover,
            .stat-card {
                border-radius: 14px;
            }
        }
    </style>
</head>
<body class="h-full text-slate-900">
    <div class="flex min-h-screen">
        <div class="no-print">
            @include('partials.sidebar')
        </div>

        <main class="flex-1 min-w-0 overflow-auto p-4 lg:p-8">
            <div class="max-w-[1600px] mx-auto space-y-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                            <span>Examens</span>
                            <span class="text-slate-300">/</span>
                            <span class="text-indigo-600">Rapport Statistique</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Rapport N / N-1</h1>
                        <p class="mt-1 text-slate-500 font-medium">Comparaison des centres, candidats, salles, PE et GE.</p>
                    </div>

                    <div class="no-print flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-slate-200 transition-all hover:bg-slate-800" href="{{ route('repartition.dashboard', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Retour Dashboard
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 transition-all hover:bg-emerald-100" href="{{ route('repartition.stats.report.centres.excel', ['annee_n' => $filters['annee_n'], 'annee_n1' => $filters['annee_n1'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Excel Centres
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition-all hover:bg-rose-100" href="{{ route('repartition.stats.report.word', ['annee_n' => $filters['annee_n'], 'annee_n1' => $filters['annee_n1'], 'annee_n1_dren' => $filters['annee_n1_dren'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Word
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-4 py-3 text-sm font-bold text-cyan-700 transition-all hover:bg-cyan-100" href="{{ route('repartition.stats.report.pdf', ['annee_n' => $filters['annee_n'], 'annee_n1' => $filters['annee_n1'], 'annee_n1_dren' => $filters['annee_n1_dren'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export PDF
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 transition-all hover:bg-blue-100" href="{{ route('repartition.stats.report.simple.word', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export Word Simple
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-purple-50 px-4 py-3 text-sm font-bold text-purple-700 transition-all hover:bg-purple-100" href="{{ route('repartition.stats.report.simple.pdf', ['annee' => $filters['annee_n'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren'], 'cisco' => $filters['cisco']]) }}">
                            Export PDF Simple
                        </a>
                    </div>
                </div>

                @if(session('status'))
                    <div class="no-print rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="no-print rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">{{ $errors->first() }}</div>
                @endif

                @if(session('import_rejects'))
                    <div class="no-print rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <div class="font-bold mb-2">Rejets d'import (extraits)</div>
                        <ul class="list-disc pl-5">
                            @foreach(session('import_rejects') as $reject)
                                <li>{{ $reject }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="no-print rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-7 items-end">
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N</label>
                            <select name="annee_n" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesCurrent as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N-1</label>
                            <select name="annee_n1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesImport as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n1'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année N-1 (DREN)</label>
                            <select name="annee_n1_dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                @foreach($anneesImportDren as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee_n1_dren'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Type Examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</label>
                            <select name="dren" id="drenFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO</label>
                            <select name="cisco" id="ciscoFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                                <option value="">Tous</option>
                                @foreach($ciscos ?? [] as $cisco)
                                    <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700">Appliquer</button>
                    </form>

                    @if(auth()->user()?->isAdmin())
                        <div class="border-t border-slate-100 pt-4">
                            <form method="POST" action="{{ route('repartition.stats.report.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                @csrf
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Import statistiques N-1 (CSV)</label>
                                    <input type="file" name="stats_file" accept=".csv,.txt" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <p class="mt-1 text-xs text-slate-500">Colonnes attendues: annee, type_examen, dren, cisco, centre_correction, centre_ecrit, total_salles, total_candidats.</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Année (optionnel)</label>
                                    <input type="text" name="annee_import" placeholder="2024-2025" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Type examen (optionnel)</label>
                                    <select name="type_examen_import" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        <option value="">Auto (colonne)</option>
                                        <option value="BEPC">BEPC</option>
                                        <option value="CEPE">CEPE</option>
                                    </select>
                                </div>
                                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Importer</button>
                            </form>
                            <form method="POST" action="{{ route('repartition.stats.report.import-dren') }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                @csrf
                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Import récap DREN N-1 (CSV)</label>
                                    <input type="file" name="dren_recap_file" accept=".csv,.txt" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <p class="mt-1 text-xs text-slate-500">Colonnes: annee, cisco, total_candidats, total_salles, ALL/allemand, ANG/anglais, ESP/espagnol, option_b. (La DREN est inférée depuis le nom de la Cisco)</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Année (optionnel)</label>
                                    <input type="text" name="annee_import_dren" placeholder="2024-2025" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Type examen (optionnel)</label>
                                    <select name="type_examen_import_dren" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        <option value="">Auto (filtre actuel)</option>
                                        <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                        <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                    </select>
                                </div>
                                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Importer</button>
                            </form>
                        </div>
                    @endif
                </div>

                @php
                    $currentCandidats = (int) ($globalStats['current_candidats'] ?? 0);
                    $previousCandidats = (int) ($globalStats['previous_candidats'] ?? 0);
                    $deltaCandidats = $currentCandidats - $previousCandidats;
                    $trendLabel = $deltaCandidats > 0 ? 'progression' : ($deltaCandidats < 0 ? 'recul' : 'stabilité');
                    $currentSalles = (int) ($globalStats['current_salles'] ?? 0);
                    $previousSalles = (int) ($globalStats['previous_salles'] ?? 0);
                    $deltaSalles = $currentSalles - $previousSalles;
                    $progressCandidats = $previousCandidats > 0 ? round(($deltaCandidats / $previousCandidats) * 100, 1) : ($currentCandidats > 0 ? 100 : 0);
                    $progressSalles = $previousSalles > 0 ? round(($deltaSalles / $previousSalles) * 100, 1) : ($currentSalles > 0 ? 100 : 0);
                    $recapDrenSorted = collect($recapByDren ?? [])->sortByDesc('total_candidats')->values();
                    $recapDrenMax = $recapDrenSorted->first();
                    $recapDrenMin = $recapDrenSorted->last();
                    $topDrenForChart = $recapDrenSorted->take(8)->values();
                    $topDrenMaxCandidates = max(1, (int) $topDrenForChart->max('total_candidats'));
                    $recapCiscoSorted = collect($recapByCisco ?? [])->sortByDesc('total_candidats')->values();
                    $recapCiscoMax = $recapCiscoSorted->first();
                    $recapCiscoMin = $recapCiscoSorted->last();
                    $topCiscoForChart = $recapCiscoSorted->take(6)->values();
                    $topCiscoMaxCandidates = max(1, (int) $topCiscoForChart->max('total_candidats'));
                    $peGeSorted = collect($peGeByDren ?? [])->sortByDesc('total_pe')->values();
                    $peMax = $peGeSorted->first();
                    $geMax = collect($peGeByDren ?? [])->sortByDesc('total_ge')->values()->first();
                    $diffSorted = collect($diffByDrenChart ?? [])->sortByDesc('value')->values();
                    $diffMax = $diffSorted->first();
                    $diffMin = $diffSorted->last();
                    $newCentres = collect($comparisonRows ?? [])->where('status', 'Nouveau centre')->count();
                    $removedCentres = collect($comparisonRows ?? [])->where('status', "Centre n'existe plus")->count();
                    $topLangue = collect($languesComparisonChart ?? [])->sortByDesc('value')->values()->first();
                @endphp

                <section class="ai-report-shell space-y-8">
                    <div class="stat-report-cover">
                        <div class="stat-cover-content">
                            <div>
                                <div class="stat-eyebrow">Rapport statistique officiel</div>
                                <h2 class="stat-cover-title">{{ $filters['type_examen'] === 'ALL' ? 'BEPC & CEPE' : $filters['type_examen'] }} · Session {{ $filters['annee_n'] ?: 'N' }}</h2>
                                <p class="stat-cover-text">
                                    Lecture synthétique de la répartition des examens: volumes de candidats, progression N/N-1,
                                    pression sur les salles, concentration territoriale par DREN/CISCO et suivi des besoins spécifiques.
                                </p>
                                <div class="stat-kpi-grid">
                                    <div class="stat-kpi-card">
                                        <div class="stat-kpi-label">Candidats N</div>
                                        <div class="stat-kpi-value">{{ number_format($currentCandidats, 0, ',', ' ') }}</div>
                                        <div class="stat-kpi-note">{{ $filters['annee_n'] ?: '-' }}</div>
                                    </div>
                                    <div class="stat-kpi-card">
                                        <div class="stat-kpi-label">Variation candidats</div>
                                        <div class="stat-kpi-value">{{ $progressCandidats >= 0 ? '+' : '' }}{{ number_format($progressCandidats, 1, ',', ' ') }}%</div>
                                        <div class="stat-kpi-note">{{ $deltaCandidats >= 0 ? '+' : '' }}{{ number_format($deltaCandidats, 0, ',', ' ') }} vs N-1</div>
                                    </div>
                                    <div class="stat-kpi-card">
                                        <div class="stat-kpi-label">Salles N</div>
                                        <div class="stat-kpi-value">{{ number_format($currentSalles, 0, ',', ' ') }}</div>
                                        <div class="stat-kpi-note">{{ $deltaSalles >= 0 ? '+' : '' }}{{ number_format($deltaSalles, 0, ',', ' ') }} salles</div>
                                    </div>
                                </div>
                            </div>
                            <div class="stat-period-card">
                                <div class="stat-period-row">
                                    <span class="stat-period-label">Année courante</span>
                                    <span class="stat-period-value">{{ $filters['annee_n'] ?: '-' }}</span>
                                </div>
                                <div class="stat-period-row">
                                    <span class="stat-period-label">Référence N-1</span>
                                    <span class="stat-period-value">{{ $filters['annee_n1'] ?: '-' }}</span>
                                </div>
                                <div class="stat-period-row">
                                    <span class="stat-period-label">Périmètre</span>
                                    <span class="stat-period-value">{{ $filters['dren'] ?: 'Toutes DREN' }}{{ $filters['cisco'] ? ' / '.$filters['cisco'] : '' }}</span>
                                </div>
                                <div class="stat-period-row">
                                    <span class="stat-period-label">Tendance</span>
                                    <span class="stat-period-value {{ $deltaCandidats < 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ ucfirst($trendLabel) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-section-grid">
                        <div class="stat-card">
                            <div class="stat-card-head">
                                <div>
                                    <h2 class="stat-card-title">Classement des DREN par candidats</h2>
                                    <p class="stat-card-subtitle">Graphique exportable en Word/PDF, basé sur les volumes consolidés de l'année N.</p>
                                </div>
                                <span class="stat-badge">Top {{ $topDrenForChart->count() }}</span>
                            </div>
                            <div class="stat-card-body">
                                @forelse($topDrenForChart as $item)
                                    @php $width = min(100, ((int) ($item['total_candidats'] ?? 0) * 100 / $topDrenMaxCandidates)); @endphp
                                    <div class="stat-chart-row">
                                        <div class="stat-chart-label">
                                            <span>{{ $item['dren'] }}</span>
                                            <span>{{ number_format((int) ($item['total_candidats'] ?? 0), 0, ',', ' ') }}</span>
                                        </div>
                                        <div class="stat-chart-track"><div class="stat-chart-fill" style="width: {{ $width }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Aucune donnée DREN disponible.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-head">
                                <div>
                                    <h2 class="stat-card-title">Indicateurs clés</h2>
                                    <p class="stat-card-subtitle">Points de lecture rapides pour orienter la décision.</p>
                                </div>
                            </div>
                            <div class="stat-card-body">
                                <div class="stat-mini-grid">
                                    <div class="stat-mini">
                                        <div class="stat-mini-label">DREN dominante</div>
                                        <div class="stat-mini-value">{{ $recapDrenMax['dren'] ?? '-' }}</div>
                                    </div>
                                    <div class="stat-mini">
                                        <div class="stat-mini-label">CISCO chargée</div>
                                        <div class="stat-mini-value">{{ $recapCiscoMax['cisco'] ?? '-' }}</div>
                                    </div>
                                    <div class="stat-mini">
                                        <div class="stat-mini-label">Besoins spéciaux</div>
                                        <div class="stat-mini-value">{{ number_format((int) ($globalStats['total_handicap'] ?? 0), 0, ',', ' ') }}</div>
                                    </div>
                                    <div class="stat-mini">
                                        <div class="stat-mini-label">Évolution salles</div>
                                        <div class="stat-mini-value">{{ $progressSalles >= 0 ? '+' : '' }}{{ number_format($progressSalles, 1, ',', ' ') }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-section-grid">
                        <div class="stat-card">
                            <div class="stat-card-head">
                                <div>
                                    <h2 class="stat-card-title">CISCO les plus chargées</h2>
                                    <p class="stat-card-subtitle">Vue opérationnelle pour prioriser les renforts locaux.</p>
                                </div>
                                <span class="stat-badge">CISCO</span>
                            </div>
                            <div class="stat-card-body">
                                @forelse($topCiscoForChart as $item)
                                    @php $width = min(100, ((int) ($item['total_candidats'] ?? 0) * 100 / $topCiscoMaxCandidates)); @endphp
                                    <div class="stat-chart-row">
                                        <div class="stat-chart-label">
                                            <span>{{ $item['dren'] }} / {{ $item['cisco'] }}</span>
                                            <span>{{ number_format((int) ($item['total_candidats'] ?? 0), 0, ',', ' ') }}</span>
                                        </div>
                                        <div class="stat-chart-track"><div class="stat-chart-fill" style="width: {{ $width }}%"></div></div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Aucune donnée CISCO disponible.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-head">
                                <div>
                                    <h2 class="stat-card-title">Synthèse d'analyse</h2>
                                    <p class="stat-card-subtitle">Commentaires automatiques générés à partir des chiffres du rapport.</p>
                                </div>
                            </div>
                            <div class="stat-card-body">
                                <div class="stat-insight-grid">
                                    <div class="stat-insight">
                                        <div class="stat-insight-title">Lecture nationale</div>
                                        <p class="stat-insight-text">
                                            La session {{ $filters['annee_n'] ?: 'N' }} présente une {{ $trendLabel }} de
                                            {{ $deltaCandidats >= 0 ? '+' : '' }}{{ number_format($deltaCandidats, 0, ',', ' ') }} candidats,
                                            soit {{ $progressCandidats >= 0 ? '+' : '' }}{{ number_format($progressCandidats, 1, ',', ' ') }}%.
                                        </p>
                                    </div>
                                    <div class="stat-insight green">
                                        <div class="stat-insight-title">Priorité territoriale</div>
                                        <p class="stat-insight-text">
                                            {{ $recapDrenMax['dren'] ?? '-' }} concentre le volume le plus important, avec
                                            {{ number_format((int) ($recapDrenMax['total_candidats'] ?? 0), 0, ',', ' ') }} candidats.
                                        </p>
                                    </div>
                                    <div class="stat-insight amber">
                                        <div class="stat-insight-title">Organisation et inclusion</div>
                                        <p class="stat-insight-text">
                                            Les {{ number_format((int) ($globalStats['total_handicap'] ?? 0), 0, ',', ' ') }} candidats à besoins spécifiques
                                            restent visibles pour anticiper les aménagements des centres.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Candidats Année N</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($currentCandidats, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Année: {{ $filters['annee_n'] ?: '-' }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Candidats Année N-1</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($previousCandidats, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Année: {{ $filters['annee_n1'] ?: '-' }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Salles Année N</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format($currentSalles, 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Écart: {{ $deltaSalles >= 0 ? '+' : '' }}{{ number_format($deltaSalles, 0, ',', ' ') }}</div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Besoins Spéciaux</p>
                        <div class="mt-2 text-3xl font-black text-slate-900">{{ number_format((int) ($globalStats['total_handicap'] ?? 0), 0, ',', ' ') }}</div>
                        <div class="mt-2 text-xs text-slate-500">Candidats spécifiques déclarés</div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-extrabold text-slate-900">Interprétation Globale</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        La comparaison N/N-1 montre une {{ $trendLabel }} globale de
                        {{ $deltaCandidats >= 0 ? '+' : '' }}{{ number_format($deltaCandidats, 0, ',', ' ') }} candidats.
                        L'année N est {{ $filters['annee_n'] ?: '-' }}, et l'année N-1 est {{ $filters['annee_n1'] ?: '-' }}.
                    </p>
                    @if($recapDrenMax && $recapDrenMin && $recapDrenMax['dren'] !== $recapDrenMin['dren'])
                        <p class="mt-2 text-sm text-slate-600">
                            La DREN {{ $recapDrenMax['dren'] }} concentre le plus de candidats
                            ({{ number_format($recapDrenMax['total_candidats'], 0, ',', ' ') }}), tandis que
                            {{ $recapDrenMin['dren'] }} est la plus faible
                            ({{ number_format($recapDrenMin['total_candidats'], 0, ',', ' ') }}).
                        </p>
                    @endif
                    @if($recapCiscoMax && $recapCiscoMin && $recapCiscoMax['cisco'] !== $recapCiscoMin['cisco'])
                        <p class="mt-2 text-sm text-slate-600">
                            Au niveau CISCO, {{ $recapCiscoMax['dren'] }} / {{ $recapCiscoMax['cisco'] }} affiche la charge la plus élevée,
                            alors que {{ $recapCiscoMin['dren'] }} / {{ $recapCiscoMin['cisco'] }} est la plus basse.
                        </p>
                    @endif
                    @if($peMax || $geMax)
                        <p class="mt-2 text-sm text-slate-600">
                            La pression logistique est la plus forte en PE à {{ $peMax['dren'] ?? '-' }}
                            ({{ number_format((int) ($peMax['total_pe'] ?? 0), 0, ',', ' ') }} PE),
                            et en GE à {{ $geMax['dren'] ?? '-' }}
                            ({{ number_format((int) ($geMax['total_ge'] ?? 0), 0, ',', ' ') }} GE).
                        </p>
                    @endif
                    @if($diffMax || $diffMin)
                        <p class="mt-2 text-sm text-slate-600">
                            L'écart le plus positif se situe à {{ $diffMax['label'] ?? '-' }}
                            ({{ number_format((int) ($diffMax['value'] ?? 0), 0, ',', ' ') }} candidats),
                            tandis que la plus forte baisse est observée à {{ $diffMin['label'] ?? '-' }}
                            ({{ number_format((int) ($diffMin['value'] ?? 0), 0, ',', ' ') }} candidats).
                        </p>
                    @endif
                    @if($newCentres > 0 || $removedCentres > 0)
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $newCentres }} centre(s) sont nouveaux en année N et
                            {{ $removedCentres }} centre(s) n'existent plus par rapport à N-1.
                        </p>
                    @endif
                    @if($topLangue && ($showLangueComparison ?? false))
                        <p class="mt-2 text-sm text-slate-600">
                            Pour le BEPC, la langue la plus demandée est {{ $topLangue['label'] }}
                            avec {{ number_format((int) $topLangue['value'], 0, ',', ' ') }} candidats.
                        </p>
                    @endif
                </div>

                @if(!($pdfMode ?? false))
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                            <h2 class="text-lg font-extrabold text-slate-900">Comparaison Centres (N vs N-1)</h2>
                            <p class="text-xs text-slate-500">Cette section est exportée en Excel uniquement.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-100">
                                        <th class="px-3 py-2 text-left">DREN</th>
                                        <th class="px-3 py-2 text-left">CISCO</th>
                                        <th class="px-3 py-2 text-left">C. correction</th>
                                        <th class="px-3 py-2 text-left">C. écrit</th>
                                        <th class="px-3 py-2 text-left">Examen</th>
                                        <th class="px-3 py-2 text-right">Candidats N</th>
                                        <th class="px-3 py-2 text-right">Candidats N-1</th>
                                        <th class="px-3 py-2 text-right">Écart</th>
                                        <th class="px-3 py-2 text-right">Progression</th>
                                        <th class="px-3 py-2 text-left">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comparisonRows as $row)
                                        <tr class="border-t">
                                            <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                            <td class="px-3 py-2">{{ $row['cisco'] }}</td>
                                            <td class="px-3 py-2">{{ $row['centre_correction'] }}</td>
                                            <td class="px-3 py-2">{{ $row['centre_ecrit'] }}</td>
                                            <td class="px-3 py-2">{{ $row['type_examen'] }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                                {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                {{ $row['progression_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['progression_candidats'], 1, ',', ' ') }}%
                                            </td>
                                            <td class="px-3 py-2 font-semibold text-indigo-700">{{ $row['status'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-3 py-6 text-center text-slate-500">Aucune comparaison disponible.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: les centres marqués "Nouveau centre" n'existaient pas en N-1. "Centre n'existe plus" signifie absence en N.
                        </div>
                    </div>
                @endif

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Comparaison DREN (N vs N-1)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">Candidats N</th>
                                    <th class="px-3 py-2 text-right">Candidats N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    <th class="px-3 py-2 text-right">Salles N</th>
                                    <th class="px-3 py-2 text-right">Salles N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    @if($filters['type_examen'] !== 'CEPE')
                                        <th class="px-3 py-2 text-right">ANG N</th>
                                        <th class="px-3 py-2 text-right">ANG N-1</th>
                                        <th class="px-3 py-2 text-right">ESP N</th>
                                        <th class="px-3 py-2 text-right">ESP N-1</th>
                                        <th class="px-3 py-2 text-right">ALL N</th>
                                        <th class="px-3 py-2 text-right">ALL N-1</th>
                                        <th class="px-3 py-2 text-right">Option B N</th>
                                        <th class="px-3 py-2 text-right">Option B N-1</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drenComparison as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_salles'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_salles'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_salles'], 0, ',', ' ') }}
                                        </td>
                                        @if($filters['type_examen'] !== 'CEPE')
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_anglais'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_anglais'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_espagnol'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_espagnol'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_allemand'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_allemand'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['current_option_b'], 0, ',', ' ') }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($row['previous_option_b'], 0, ',', ' ') }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="px-3 py-6 text-center text-slate-500">Aucune comparaison DREN disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                        Interprétation: la comparaison DREN met en évidence la croissance ou la baisse globale par région, ainsi que les langues BEPC dominantes.
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Comparaison CISCO (N vs N-1)</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-left">CISCO</th>
                                    <th class="px-3 py-2 text-right">Candidats N</th>
                                    <th class="px-3 py-2 text-right">Candidats N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                    <th class="px-3 py-2 text-right">Salles N</th>
                                    <th class="px-3 py-2 text-right">Salles N-1</th>
                                    <th class="px-3 py-2 text-right">Écart</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ciscoComparison as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ $row['cisco'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_candidats'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_candidats'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_candidats'], 0, ',', ' ') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['current_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['previous_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right {{ $row['ecart_salles'] < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $row['ecart_salles'] >= 0 ? '+' : '' }}{{ number_format($row['ecart_salles'], 0, ',', ' ') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-6 text-center text-slate-500">Aucune comparaison CISCO disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                        Interprétation: les CISCO en hausse nécessitent un renforcement local, ceux en baisse doivent être suivis pour éviter les déséquilibres.
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-extrabold text-slate-900">Différence par DREN</h2>
                        @php
                            $maxDiff = max(1, (int) collect($diffByDrenChart)->max('value'));
                        @endphp
                        <div class="mt-4 space-y-3">
                            @forelse($diffByDrenChart as $item)
                                @php
                                    $value = (int) ($item['value'] ?? 0);
                                    $width = min(100, abs($value) * 100 / $maxDiff);
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                        <span>{{ $item['label'] }}</span>
                                        <span class="{{ $value < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $value >= 0 ? '+' : '' }}{{ number_format($value, 0, ',', ' ') }}</span>
                                    </div>
                                    <div class="bar-track h-2 rounded-full mt-1">
                                        <div class="bar-fill {{ $value < 0 ? 'negative' : '' }} h-2 rounded-full" style="width: {{ $width }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucune donnée pour le graphique.</p>
                            @endforelse
                        </div>
                        @if($diffMax && $diffMin && ($diffMax['label'] ?? '') !== ($diffMin['label'] ?? ''))
                            <p class="mt-4 text-xs text-slate-500">
                                Interprétation: {{ $diffMax['label'] }} tire la hausse globale,
                                tandis que {{ $diffMin['label'] }} enregistre le recul le plus marqué.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-extrabold text-slate-900">Langues & Options (BEPC)</h2>
                        @if($showLangueComparison)
                            @php
                                $maxLang = max(1, (int) collect($languesComparisonChart)->max('value'));
                                $maxOpt = max(1, (int) collect($optionsComparisonChart)->max('value'));
                            @endphp
                            <div class="space-y-4">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Langues</div>
                                    <div class="mt-3 space-y-2">
                                        @forelse($languesComparisonChart as $item)
                                            <div>
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                                    <span>{{ $item['label'] }}</span>
                                                    <span>{{ number_format((int) $item['value'], 0, ',', ' ') }}</span>
                                                </div>
                                                <div class="bar-track h-2 rounded-full mt-1">
                                                    <div class="bar-fill h-2 rounded-full" style="width: {{ min(100, ((int) $item['value'] * 100 / $maxLang)) }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-sm text-slate-500">Aucune langue disponible.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400">Options A / B</div>
                                    <div class="mt-3 space-y-2">
                                        @foreach($optionsComparisonChart as $item)
                                            <div>
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                                                    <span>{{ $item['label'] }}</span>
                                                    <span>{{ number_format((int) $item['value'], 0, ',', ' ') }}</span>
                                                </div>
                                                <div class="bar-track h-2 rounded-full mt-1">
                                                    <div class="bar-fill h-2 rounded-full" style="width: {{ min(100, ((int) $item['value'] * 100 / $maxOpt)) }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @if($topLangue)
                                <p class="mt-4 text-xs text-slate-500">
                                    Interprétation: la langue dominante est {{ $topLangue['label'] }},
                                    indiquant la préférence principale des candidats BEPC.
                                </p>
                            @endif
                        @else
                            <p class="text-sm text-slate-500">Disponible pour BEPC uniquement.</p>
                        @endif
                    </div>
                </div>

                @if($pdfDownload ?? false)
                    <div class="rounded-3xl border border-cyan-100 bg-cyan-50 px-6 py-5 text-sm font-semibold text-cyan-900">
                        Le PDF contient la synthèse, les indicateurs et les graphiques principaux. Les tableaux détaillés DREN/CISCO/PE-GE restent disponibles dans la page web et dans l'export Word pour garder un téléchargement rapide.
                    </div>
                @endif

                @if(!($pdfDownload ?? false))
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif par DREN</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">Candidats</th>
                                    <th class="px-3 py-2 text-right">Salles</th>
                                    <th class="px-3 py-2 text-right">C. correction</th>
                                    <th class="px-3 py-2 text-right">C. écrit</th>
                                    <th class="px-3 py-2 text-right">Handicap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recapByDren as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_correction'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ecrit'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_handicap'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">Aucune donnée DREN.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recapDrenMax && $recapDrenMin)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: {{ $recapDrenMax['dren'] }} domine le volume régional, tandis que
                            {{ $recapDrenMin['dren'] }} reste le plus faible, ce qui guide les priorités de renfort.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif par CISCO</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-left">CISCO</th>
                                    <th class="px-3 py-2 text-right">Candidats</th>
                                    <th class="px-3 py-2 text-right">Salles</th>
                                    <th class="px-3 py-2 text-right">C. correction</th>
                                    <th class="px-3 py-2 text-right">C. écrit</th>
                                    <th class="px-3 py-2 text-right">Handicap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recapByCisco as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ $row['cisco'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_correction'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ecrit'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_handicap'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-6 text-center text-slate-500">Aucune donnée CISCO.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recapCiscoMax && $recapCiscoMin)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: {{ $recapCiscoMax['dren'] }} / {{ $recapCiscoMax['cisco'] }} concentre la charge,
                            alors que {{ $recapCiscoMin['dren'] }} / {{ $recapCiscoMin['cisco'] }} reste le plus léger.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif PE / GE par DREN</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-100">
                                    <th class="px-3 py-2 text-left">DREN</th>
                                    <th class="px-3 py-2 text-right">PE</th>
                                    <th class="px-3 py-2 text-right">GE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peGeByDren as $row)
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-semibold">{{ $row['dren'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_pe'], 0, ',', ' ') }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['total_ge'], 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-6 text-center text-slate-500">Aucune donnée PE/GE.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($peMax || $geMax)
                        <div class="px-6 py-4 text-xs text-slate-500 border-t border-slate-100">
                            Interprétation: la DREN {{ $peMax['dren'] ?? '-' }} porte le plus d'organisation en salles,
                            et {{ $geMax['dren'] ?? '-' }} le plus de GE, ce qui nécessite un encadrement renforcé.
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </main>
    </div>

    @if(!($pdfMode ?? false))
        <script>
            const ciscosByDren = @json($ciscosByDren ?? []);
            function syncCiscoFilterOptions() {
                const drenSelect = document.getElementById('drenFilter');
                const ciscoSelect = document.getElementById('ciscoFilter');
                if (!drenSelect || !ciscoSelect) return;
                const selectedDren = drenSelect.value || '';
                const selectedCisco = ciscoSelect.value || '';
                const ciscos = selectedDren ? (ciscosByDren[selectedDren] || []) : Object.values(ciscosByDren).flat();
                const uniqueCiscos = [...new Set(ciscos)].sort((a, b) => a.localeCompare(b, 'fr'));

                ciscoSelect.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Tous';
                ciscoSelect.appendChild(defaultOption);

                uniqueCiscos.forEach((cisco) => {
                    const option = document.createElement('option');
                    option.value = cisco;
                    option.textContent = cisco;
                    if (cisco === selectedCisco) option.selected = true;
                    ciscoSelect.appendChild(option);
                });

                if (selectedCisco && !uniqueCiscos.includes(selectedCisco)) {
                    ciscoSelect.value = '';
                }
            }
            document.getElementById('drenFilter')?.addEventListener('change', syncCiscoFilterOptions);
            document.addEventListener('DOMContentLoaded', syncCiscoFilterOptions);
        </script>
    @endif
</body>
</html>
