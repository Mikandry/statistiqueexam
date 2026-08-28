<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de l'Organisation des Examens</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')

    <style>
        :root {
            --bg: #f4f3ef;
            --surface: #ffffff;
            --surface-2: #f9f8f5;
            --border: #e8e5df;
            --border-strong: #d4cfc6;
            --text-primary: #1a1916;
            --text-secondary: #6b6760;
            --text-muted: #a09d97;
            --accent: #2d5be3;
            --accent-light: #eef1fc;
            --accent-mid: #c3cef8;
            --amber: #d97706;
            --emerald: #059669;
            --ink: #0f0e0c;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--app-font-sans);
            background-color: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .page-wrapper {
            max-width: 1700px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        .sidebar-slot {
            flex-shrink: 0;
            z-index: 10;
        }

        main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        /* ── Page header ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-eyebrow {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }

        .eyebrow-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
        }

        .eyebrow-label {
            font-family: var(--app-font-sans);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
        }

        h1 {
            font-family: var(--app-font-display);
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.35rem;
            font-weight: 400;
        }

        /* ── Back button ── */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.1rem;
            border-radius: 10px;
            border: 1.5px solid var(--border-strong);
            background: var(--surface);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.18s ease;
            white-space: nowrap;
            letter-spacing: 0.01em;
        }

        .btn-back:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45,91,227,0.12);
        }

        .btn-back:hover svg {
            transform: translateX(-2px);
        }

        .btn-back svg {
            transition: transform 0.18s ease;
        }

        /* ── Filter card ── */
        .filter-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s ease;
        }

        .filter-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 8px 24px rgba(0,0,0,0.05);
        }

        .filter-card-inner {
            padding: 1.75rem 2rem;
        }

        .filter-section-label {
            font-family: var(--app-font-display);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (max-width: 768px) {
            .filter-grid { grid-template-columns: 1fr; }
            h1 { font-size: 1.75rem; }
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .field-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-secondary);
            padding-left: 0.25rem;
        }

        .field-select {
            width: 100%;
            padding: 0.65rem 2.5rem 0.65rem 0.875rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background-color: var(--surface-2);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23a09d97' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.6rem center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
            -webkit-appearance: none;
            appearance: none;
            font-family: var(--app-font-sans);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.15s ease;
            outline: none;
        }

        .field-select:focus {
            border-color: var(--accent);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(45,91,227,0.1);
        }

        .field-select:hover {
            border-color: var(--border-strong);
        }

        /* ── Filter footer ── */
        .filter-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 2rem 1.5rem;
        }

        .btn-reset {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: color 0.15s ease, background 0.15s ease;
            letter-spacing: 0.01em;
        }

        .btn-reset:hover {
            color: var(--text-primary);
            background: var(--surface-2);
        }

        .btn-apply {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: var(--app-font-sans);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.18s ease;
            letter-spacing: 0.01em;
        }

        .btn-apply:hover {
            background: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(45,91,227,0.3);
        }

        .btn-apply:active {
            transform: translateY(0);
        }

        /* ── Table card ── */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }

        .table-title {
            font-family: var(--app-font-display);
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
        }

        .table-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: var(--accent-light);
            color: var(--accent);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.3rem 0.8rem;
            border-radius: 100px;
            border: 1px solid var(--accent-mid);
        }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead tr { background: var(--surface-2); }

        thead th {
            padding: 0.75rem 1.5rem;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        tbody tr { transition: background 0.12s ease; }
        tbody tr:hover { background: var(--accent-light); }

        tbody td {
            padding: 0.9rem 1.5rem;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border);
        }

        tbody tr:last-child td { border-bottom: none; }

        .td-dren {
            font-family: var(--app-font-display);
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        tbody tr:hover .td-dren { color: var(--accent); }

        .td-cisco { color: var(--text-secondary); font-weight: 500; }

        .td-correction { display: flex; align-items: center; gap: 0.5rem; }

        .correction-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--amber);
            flex-shrink: 0;
        }

        /* ── Tfoot ── */
        tfoot tr { background: var(--ink); }

        tfoot td {
            padding: 1rem 1.5rem;
            font-family: var(--app-font-display);
            border-bottom: none !important;
        }

        .tf-label {
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 0.15rem;
        }

        .tf-value {
            font-size: 1rem;
            font-weight: 700;
        }

        .tf-white  { color: #ffffff; }
        .tf-muted  { color: rgba(255,255,255,0.65); }
        .tf-amber  { color: #fbbf24; }
        .tf-emerald { color: #34d399; }
    </style>

</head>

<body>

<div class="page-wrapper">

    <!-- Sidebar -->
    <div class="sidebar-slot">
        @include('partials.sidebar')
    </div>

    <main>

        <!-- Page header -->
        <div class="page-header">
            <div>
                <div class="page-eyebrow">
                    <span class="eyebrow-dot"></span>
                    <span class="eyebrow-label">SOE · Répartition</span>
                </div>
                <h1>Décision de Centre</h1>
                <p class="page-subtitle">Gestion et consultation des centres de correction et d'écrit par zone.</p>
            </div>

            <a class="btn-back" href="{{ route('repartition.dashboard') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Tableau de bord
            </a>
        </div>

        <!-- Filter card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('decision.centre') }}">
                <div class="filter-card-inner">
                    <p class="filter-section-label">Filtres de recherche</p>
                    <div class="filter-grid">

                        <div class="field-group">
                            <label class="field-label" for="type_examen">Type d'Examen</label>
                            <select id="type_examen" name="type_examen" class="field-select">
                                <option value="">Tous les examens</option>
                                <option value="BEPC" {{ ($typeExamen ?? '') == 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ ($typeExamen ?? '') == 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="dren">DREN</label>
                            <select id="dren" name="dren" class="field-select">
                                <option value="">Toutes les directions</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren->id }}" {{ ($drenId ?? '') == $dren->id ? 'selected' : '' }}>
                                        {{ $dren->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="cisco">CISCO</label>
                            <select id="cisco" name="cisco" class="field-select">
                                <option value="">Tous les districts</option>
                                @foreach($ciscos as $cisco)
                                    <option value="{{ $cisco->id }}" {{ ($ciscoId ?? '') == $cisco->id ? 'selected' : '' }}>
                                        {{ $cisco->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="annee">Année scolaire</label>
                            <input list="annees-list" id="annee" name="annee" class="field-select" value="{{ $selectedAnnee ?? '' }}" placeholder="{{ $suggestedNextYear ?? (date('Y')+1) }}">
                            <datalist id="annees-list">
                                @foreach(($annees ?? []) as $annee)
                                    <option value="{{ $annee }}">{{ $annee }}</option>
                                @endforeach
                            </datalist>
                            <button type="button" id="apply-next-year" class="btn-reset" style="margin-top:6px;">Nouvelle session (année suivante)</button>
                        </div>

                    </div>
                </div>

                <div class="filter-footer">
                    <a href="{{ route('decision.centre') }}" class="btn-reset">Réinitialiser</a>
                    <button type="submit" class="btn-apply">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>

        <!-- Table card -->
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">Centres importés</span>
                <span class="table-badge">
                    {{ count($tableData) }} Lignes
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>DREN</th>
                            <th>CISCO</th>
                            <th>Centre Correction</th>
                            <th>Centre Écrit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableData as $row)
                            <tr>
                                <td class="td-dren">{{ $row['dren'] }}</td>
                                <td class="td-cisco">{{ $row['cisco'] }}</td>
                                <td>
                                    <div class="td-correction">
                                        <span class="correction-dot"></span>
                                        {{ $row['correction'] }}
                                    </div>
                                </td>
                                <td>{{ $row['ecrit'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>
                                <div class="tf-label">Total DREN</div>
                                <div class="tf-value tf-white">{{ $totalDren }}</div>
                            </td>
                            <td>
                                <div class="tf-label">Total CISCO</div>
                                <div class="tf-value tf-muted">{{ $totalCisco }}</div>
                            </td>
                            <td>
                                <div class="tf-label">Centres Correction</div>
                                <div class="tf-value tf-amber">{{ $totalCorrection }}</div>
                            </td>
                            <td>
                                <div class="tf-label">Centres Écrit</div>
                                <div class="tf-value tf-emerald">{{ $totalEcrit }}</div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Centres Saisis et Non Saisis -->
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">Centres Saisis et Non Saisis</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nom du Centre</th>
                            <th>Région</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centresSaisis as $centre)
                            <tr>
                                <td>{{ $centre['nom'] }}</td>
                                <td>{{ $centre['region'] }}</td>
                                <td>Saisi</td>
                            </tr>
                        @endforeach
                        @foreach($centresNonSaisis as $centre)
                            <tr>
                                <td>{{ $centre['nom'] }}</td>
                                <td>{{ $centre['region'] }}</td>
                                <td>Non Saisi</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const selects = ["type_examen", "dren", "annee"];
    selects.forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener("change", function(){
                if(id === "dren" && document.getElementById("cisco")) {
                    document.getElementById("cisco").value = "";
                }
                this.form.submit();
            });
        }
    });

    // next year button
    const nextBtn = document.getElementById('apply-next-year');
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            const input = document.getElementById('annee');
            if (!input) return;
            const next = '{{ $suggestedNextYear ?? ((int) date("Y") + 1) }}';
            input.value = next;
            this.form ? this.form.submit() : document.querySelector('form').submit();
        });
    }
});
</script>

</body>
</html>
