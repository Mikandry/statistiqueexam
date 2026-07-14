<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Statistique Simple - {{ $filters['annee'] }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 15mm 25mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Trebuchet MS', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #2c3e50;
            background: white;
            padding: 0;
        }
        .page {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }
        .ai-cover {
            page-break-inside: avoid;
            margin-bottom: 1cm;
            padding: 0.85cm;
            background: #f8fafc;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
        }
        .ai-cover-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .ai-cover-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }
        .ai-cover-image-cell {
            width: 42%;
            padding-right: 0.75cm !important;
        }
        .ai-cover-image {
            width: 100%;
            height: 9.5cm;
            object-fit: cover;
            border-radius: 36px 36px 10px 10px;
        }
        .ai-label {
            color: #4338ca;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .ai-cover-title {
            color: #111827;
            font-size: 28pt;
            font-weight: 900;
            line-height: 1.05;
            margin-top: 0.35cm;
        }
        .ai-cover-title span {
            color: #4338ca;
        }
        .ai-cover-text {
            color: #475569;
            font-size: 10.5pt;
            line-height: 1.55;
            margin-top: 0.45cm;
        }
        .ai-metrics-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0.25cm;
            margin: 0.55cm -0.25cm 0;
        }
        .ai-metrics-table td {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.35cm;
            text-align: center;
            width: 33.33%;
        }
        .ai-metric-label {
            color: #64748b;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .ai-metric-value {
            color: #0f172a;
            font-size: 15pt;
            font-weight: 900;
            margin-top: 0.1cm;
        }
        .ai-metric-note {
            color: #64748b;
            font-size: 8pt;
            font-weight: bold;
            margin-top: 0.05cm;
        }
        .visual-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0.35cm;
            margin: 0.2cm -0.35cm 0.9cm;
            page-break-inside: avoid;
        }
        .visual-table td {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0;
            vertical-align: top;
            width: 33.33%;
        }
        .visual-image {
            width: 100%;
            height: 4.1cm;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .visual-body {
            padding: 0.35cm;
        }
        .visual-title {
            color: #0f172a;
            font-size: 12pt;
            font-weight: 900;
        }
        .visual-kicker {
            color: #4338ca;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 0.08cm;
            text-transform: uppercase;
        }
        .visual-text {
            color: #64748b;
            font-size: 8.5pt;
            line-height: 1.45;
            margin-top: 0.22cm;
        }
        .ai-section {
            page-break-inside: avoid;
            margin: 1cm 0;
            padding: 0.65cm;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }
        .ai-section.dark {
            background: #111827;
            color: white;
        }
        .ai-section-title {
            color: inherit;
            font-size: 15pt;
            font-weight: 900;
            margin-bottom: 0.35cm;
        }
        .ai-bar-row {
            margin-top: 0.24cm;
        }
        .ai-bar-head {
            color: #334155;
            font-size: 8.5pt;
            font-weight: bold;
            margin-bottom: 0.08cm;
        }
        .ai-bar-track {
            background: #eef2ff;
            border-radius: 20px;
            height: 0.22cm;
            overflow: hidden;
        }
        .ai-bar-fill {
            background: #4338ca;
            border-radius: 20px;
            height: 0.22cm;
        }
        .ai-analysis-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .ai-analysis-table td {
            border: 0;
            border-left: 4px solid #4338ca;
            padding: 0.2cm 0.25cm;
            vertical-align: top;
        }
        .ai-analysis-title {
            color: #111827;
            font-size: 10.5pt;
            font-weight: 900;
        }
        .ai-analysis-text {
            color: #64748b;
            font-size: 8.8pt;
            line-height: 1.45;
            margin-top: 0.08cm;
        }
        .header {
            text-align: center;
            margin-bottom: 2cm;
            padding-bottom: 1.5cm;
            border-bottom: 3px solid #1e40af;
        }
        .header-title {
            font-size: 24pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 0.5cm;
        }
        .header-subtitle {
            font-size: 13pt;
            color: #555;
            margin-bottom: 0.3cm;
        }
        .header-date {
            font-size: 10pt;
            color: #888;
            font-style: italic;
        }
        h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-top: 1.5cm;
            margin-bottom: 0.8cm;
            padding-bottom: 0.5cm;
            border-bottom: 2px solid #e0e7ff;
            page-break-after: avoid;
        }
        h3 {
            font-size: 12pt;
            font-weight: bold;
            color: #334155;
            margin-top: 1cm;
            margin-bottom: 0.5cm;
            page-break-after: avoid;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.8cm 0;
            page-break-inside: avoid;
        }
        th {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            font-weight: bold;
            padding: 0.8cm;
            text-align: left;
            font-size: 11pt;
        }
        td {
            padding: 0.6cm 0.8cm;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11pt;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tr:hover {
            background-color: #eff6ff;
        }
        .right {
            text-align: right;
            font-weight: 500;
        }
        .chart-container {
            page-break-inside: avoid;
            margin: 1.2cm 0;
            padding: 1cm;
            background: #f8fafc;
            border-left: 4px solid #1e40af;
        }
        .chart-container h3 {
            margin-top: 0;
            color: #1e40af;
        }
        svg {
            max-width: 100%;
            height: auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1cm;
            margin: 1cm 0;
            page-break-inside: avoid;
        }
        .stat-box {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1cm;
            border-radius: 4px;
            text-align: center;
            page-break-inside: avoid;
        }
        .stat-label {
            font-size: 10pt;
            opacity: 0.9;
            margin-bottom: 0.3cm;
        }
        .stat-value {
            font-size: 16pt;
            font-weight: bold;
        }
        .footer {
            margin-top: 2.5cm;
            padding-top: 1.5cm;
            border-top: 2px solid #e0e7ff;
            text-align: center;
            font-size: 10pt;
            color: #888;
        }
        .signature {
            margin-top: 1.5cm;
            text-align: right;
            font-style: italic;
            color: #555;
            font-size: 11pt;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body {
                padding: 0.5cm;
            }
            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @php
            $imagePaths = [
                public_path('images/stat-report/stat4.jpeg'),
                public_path('images/stat-report/stat2.jpeg'),
                public_path('images/stat-report/stat1.jpeg'),
            ];
            $reportImages = collect($imagePaths)->map(function ($path) {
                if (! is_file($path)) {
                    return '';
                }

                return 'data:image/jpeg;base64,'.base64_encode(file_get_contents($path));
            })->values()->all();
            $topDrenForChart = collect($recapByDren ?? [])->sortByDesc('total_candidats')->take(6)->values();
            $topDrenMaxCandidates = max(1, (int) $topDrenForChart->max('total_candidats'));
            $topDren = $topDrenForChart->first();
            $totalHandicapGlobal = collect($handicapByType ?? [])->sum();
        @endphp

        <div class="ai-cover">
            <table class="ai-cover-table">
                <tr>
                    <td class="ai-cover-image-cell">
                        @if($reportImages[0] !== '')
                            <img class="ai-cover-image" src="{{ $reportImages[0] }}" alt="Illustration examen">
                        @endif
                    </td>
                    <td>
                        <div class="ai-label">Session d'examen {{ $filters['annee'] }}</div>
                        <div class="ai-cover-title">Rapport statistique <span>{{ $filters['type_examen'] === 'ALL' ? 'BEPC / CEPE' : $filters['type_examen'] }}</span></div>
                        <div class="ai-cover-text">
                            Synthèse visuelle des centres, salles, candidats, langues et besoins spécifiques.
                            Les graphiques mettent en évidence les zones de charge et les priorités d'organisation.
                        </div>
                        <table class="ai-metrics-table">
                            <tr>
                                <td>
                                    <div class="ai-metric-label">Candidats</div>
                                    <div class="ai-metric-value">{{ number_format($recapGeneral['total_candidats'], 0, ',', ' ') }}</div>
                                    <div class="ai-metric-note">inscrits</div>
                                </td>
                                <td>
                                    <div class="ai-metric-label">Salles</div>
                                    <div class="ai-metric-value">{{ number_format($recapGeneral['total_salles'], 0, ',', ' ') }}</div>
                                    <div class="ai-metric-note">mobilisées</div>
                                </td>
                                <td>
                                    <div class="ai-metric-label">Besoins spécifiques</div>
                                    <div class="ai-metric-value">{{ number_format($totalHandicapGlobal, 0, ',', ' ') }}</div>
                                    <div class="ai-metric-note">déclarés</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <table class="visual-table">
            <tr>
                <td>
                    @if($reportImages[1] !== '')
                        <img class="visual-image" src="{{ $reportImages[1] }}" alt="Candidats en salle">
                    @endif
                    <div class="visual-body">
                        <div class="visual-title">Candidats inscrits</div>
                        <div class="visual-kicker">{{ number_format($recapGeneral['total_candidats'], 0, ',', ' ') }} participants</div>
                        <div class="visual-text">Volume national consolidé par région et par centre d'examen.</div>
                    </div>
                </td>
                <td>
                    @if($reportImages[2] !== '')
                        <img class="visual-image" src="{{ $reportImages[2] }}" alt="Centre d'examen">
                    @endif
                    <div class="visual-body">
                        <div class="visual-title">Centres d'écrit</div>
                        <div class="visual-kicker">{{ number_format($recapGeneral['total_centres_ecrit'], 0, ',', ' ') }} centres</div>
                        <div class="visual-text">Lecture opérationnelle de la couverture territoriale.</div>
                    </div>
                </td>
                <td>
                    @if($reportImages[0] !== '')
                        <img class="visual-image" src="{{ $reportImages[0] }}" alt="Analyse statistique">
                    @endif
                    <div class="visual-body">
                        <div class="visual-title">Analyse IA</div>
                        <div class="visual-kicker">{{ $topDren['dren'] ?? '-' }} en tête</div>
                        <div class="visual-text">Détection rapide des pôles de forte charge pour guider les renforts.</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="ai-section">
            <div class="ai-label">Graphique IA</div>
            <div class="ai-section-title">Top DREN par volume de candidats</div>
            @forelse($topDrenForChart as $dren)
                @php $width = min(100, ((int) ($dren['total_candidats'] ?? 0) * 100 / $topDrenMaxCandidates)); @endphp
                <div class="ai-bar-row">
                    <div class="ai-bar-head">{{ $dren['dren'] }} - {{ number_format((int) ($dren['total_candidats'] ?? 0), 0, ',', ' ') }} candidats</div>
                    <div class="ai-bar-track"><div class="ai-bar-fill" style="width: {{ $width }}%"></div></div>
                </div>
            @empty
                <div class="ai-analysis-text">Aucune donnée DREN disponible pour le graphique.</div>
            @endforelse
        </div>

        <div class="ai-section">
            <div class="ai-label">Synthèse assistée par IA</div>
            <table class="ai-analysis-table">
                <tr>
                    <td>
                        <div class="ai-analysis-title">Lecture nationale</div>
                        <div class="ai-analysis-text">
                            La session {{ $filters['annee'] }} regroupe {{ number_format($recapGeneral['total_candidats'], 0, ',', ' ') }}
                            candidats dans {{ number_format($recapGeneral['total_salles'], 0, ',', ' ') }} salles.
                        </div>
                    </td>
                    <td style="border-left-color:#10b981;">
                        <div class="ai-analysis-title">Priorité territoriale</div>
                        <div class="ai-analysis-text">
                            {{ $topDren['dren'] ?? '-' }} concentre le plus grand volume avec
                            {{ number_format((int) ($topDren['total_candidats'] ?? 0), 0, ',', ' ') }} candidats.
                        </div>
                    </td>
                    <td style="border-left-color:#f59e0b;">
                        <div class="ai-analysis-title">Inclusion scolaire</div>
                        <div class="ai-analysis-text">
                            {{ number_format($totalHandicapGlobal, 0, ',', ' ') }} candidats à besoins spécifiques
                            sont intégrés au suivi statistique.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- HEADER -->
        <div class="header">
            <div class="header-title">📊 Rapport Statistique</div>
            <div class="header-subtitle">{{ $filters['type_examen'] === 'ALL' ? 'BEPC / CEPE' : $filters['type_examen'] }} - Année {{ $filters['annee'] }}</div>
            <div class="header-date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>

        <!-- RECAP GENERAL -->
        <h2>📋 Récapitulatif Général</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Centres de Correction</div>
                <div class="stat-value">{{ number_format($recapGeneral['total_centres_correction'], 0, ',', ' ') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Centres d'Écrit</div>
                <div class="stat-value">{{ number_format($recapGeneral['total_centres_ecrit'], 0, ',', ' ') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Nombre de Salles</div>
                <div class="stat-value">{{ number_format($recapGeneral['total_salles'], 0, ',', ' ') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Candidats Inscrits</div>
                <div class="stat-value">{{ number_format($recapGeneral['total_candidats'], 0, ',', ' ') }}</div>
            </div>
        </div>

        <!-- DREN TABLE -->
        <h2>🗺️ Répartition par Région (DREN)</h2>
        <table>
            <thead>
                <tr>
                    <th>Région (DREN)</th>
                    <th class="right">Centres Correction</th>
                    <th class="right">Centres Écrit</th>
                    <th class="right">Salles</th>
                    <th class="right">Candidats</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recapByDren as $dren)
                <tr>
                    <td><strong>{{ $dren['dren'] }}</strong></td>
                    <td class="right">{{ number_format($dren['total_correction'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_ecrit'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_salles'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_candidats'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- DREN CHART -->
        <div class="chart-container">
            <h3>📊 Graphe : Répartition des Candidats par Région</h3>
            <svg width="100%" height="300" viewBox="0 0 800 300" xmlns="http://www.w3.org/2000/svg">
                <!-- Background -->
                <rect width="800" height="300" fill="white"/>
                <!-- Grid lines -->
                <line x1="80" y1="20" x2="80" y2="250" stroke="#1e40af" stroke-width="2"/>
                <line x1="80" y1="250" x2="780" y2="250" stroke="#1e40af" stroke-width="2"/>
                
                @php
                    $maxCandidates = collect($recapByDren)->pluck('total_candidats')->max() ?: 1;
                    $barWidth = (700 - collect($recapByDren)->count() * 10) / max(collect($recapByDren)->count(), 1);
                    $barSpacing = 10;
                    $chartHeight = 230;
                    $x = 100;
                @endphp

                @foreach($recapByDren as $index => $dren)
                    @php
                        $barHeightValue = ($dren['total_candidats'] / $maxCandidates) * $chartHeight;
                        $barY = 250 - $barHeightValue;
                    @endphp
                    <!-- Bar -->
                    <rect x="{{ $x }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeightValue }}" fill="url(#gradient-{{ $index }})" stroke="#1e40af" stroke-width="1"/>
                    <!-- Value label -->
                    <text x="{{ $x + $barWidth / 2 }}" y="{{ $barY - 5 }}" text-anchor="middle" font-size="10" font-weight="bold" fill="#1e40af">
                        {{ number_format($dren['total_candidats'], 0) }}
                    </text>
                    <!-- Region label -->
                    <text x="{{ $x + $barWidth / 2 }}" y="270" text-anchor="middle" font-size="9" fill="#333">
                        {{ substr($dren['dren'], 0, 8) }}
                    </text>
                    @php $x += $barWidth + $barSpacing; @endphp
                @endforeach

                <!-- Gradients -->
                @foreach($recapByDren as $index => $dren)
                    <defs>
                        <linearGradient id="gradient-{{ $index }}" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#1e40af;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                @endforeach

                <!-- Y-axis labels -->
                <text x="70" y="255" text-anchor="end" font-size="9" fill="#666">0</text>
                <text x="70" y="160" text-anchor="end" font-size="9" fill="#666">{{ number_format($maxCandidates / 2, 0) }}</text>
                <text x="70" y="25" text-anchor="end" font-size="9" fill="#666">{{ number_format($maxCandidates, 0) }}</text>
            </svg>
        </div>

        <!-- LANGUES COMPARISON -->
        @if($showLangueComparison)
        <h2>🌐 Comparaison des Langues Vivantes (BEPC)</h2>
        <table>
            <thead>
                <tr>
                    <th>Langue Vivante</th>
                    <th class="right">Candidats</th>
                    <th class="right">Salles</th>
                    <th class="right">% Candidats</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCandidatsLangues = collect($languesComparison)->sum('total_candidats');
                @endphp
                @foreach($languesComparison as $langue)
                    @php $percent = ($totalCandidatsLangues > 0) ? ($langue['total_candidats'] / $totalCandidatsLangues * 100) : 0; @endphp
                <tr>
                    <td><strong>{{ $langue['langue'] }}</strong></td>
                    <td class="right">{{ number_format($langue['total_candidats'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($langue['total_salles'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($percent, 1, ',', ' ') }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- LANGUES PIE CHART -->
        <div class="chart-container">
            <h3>📈 Graphe : Répartition des Langues Vivantes (Camembert)</h3>
            <svg width="100%" height="320" viewBox="0 0 500 320" xmlns="http://www.w3.org/2000/svg">
                <rect width="500" height="320" fill="white"/>
                @php
                    $centerX = 150;
                    $centerY = 150;
                    $radius = 100;
                    $currentAngle = -90;
                    $colors = ['#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe', '#7c3aed', '#ec4899', '#f97316'];
                    $totalLangues = max(collect($languesComparison)->sum('total_candidats'), 1);
                @endphp

                @foreach($languesComparison as $index => $langue)
                    @php
                        $sliceSize = ($langue['total_candidats'] / $totalLangues) * 360;
                        $startAngle = $currentAngle;
                        $endAngle = $currentAngle + $sliceSize;
                        
                        $x1 = $centerX + $radius * cos(deg2rad($startAngle));
                        $y1 = $centerY + $radius * sin(deg2rad($startAngle));
                        $x2 = $centerX + $radius * cos(deg2rad($endAngle));
                        $y2 = $centerY + $radius * sin(deg2rad($endAngle));
                        
                        $largeArc = ($sliceSize > 180) ? 1 : 0;
                        
                        $labelAngle = $startAngle + $sliceSize / 2;
                        $labelX = $centerX + ($radius * 0.65) * cos(deg2rad($labelAngle));
                        $labelY = $centerY + ($radius * 0.65) * sin(deg2rad($labelAngle));
                        $percent = ($totalLangues > 0) ? ($langue['total_candidats'] / $totalLangues * 100) : 0;
                    @endphp
                    <!-- Slice -->
                    <path d="M {{ $centerX }},{{ $centerY }} L {{ $x1 }},{{ $y1 }} A {{ $radius }},{{ $radius }} 0 {{ $largeArc }},1 {{ $x2 }},{{ $y2 }} Z" 
                          fill="{{ $colors[$index % count($colors)] }}" stroke="white" stroke-width="2"/>
                    <!-- Label -->
                    <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="middle" font-size="11" font-weight="bold" fill="white">
                        {{ number_format($percent, 0) }}%
                    </text>
                    @php $currentAngle = $endAngle; @endphp
                @endforeach

                <!-- Legend -->
                @php $legendY = 20; @endphp
                @foreach($languesComparison as $index => $langue)
                    <rect x="280" y="{{ $legendY + ($index * 25) }}" width="15" height="15" fill="{{ $colors[$index % count($colors)] }}"/>
                    <text x="305" y="{{ $legendY + 12 + ($index * 25) }}" font-size="11" fill="#333">
                        {{ $langue['langue'] }} ({{ number_format($langue['total_candidats'], 0) }})
                    </text>
                @endforeach
            </svg>
        </div>
        @endif

        <!-- HANDICAP SECTION -->
        <h2>♿ Candidats à Besoins Spécifiques</h2>

        <h3>Répartition par Type d'Handicap</h3>
        <table>
            <thead>
                <tr>
                    <th>Type d'Handicap</th>
                    <th class="right">Nombre</th>
                    <th class="right">%</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalHandicap = collect($handicapByType)->sum();
                @endphp
                @foreach($handicapByType as $type => $count)
                    @php $percent = ($totalHandicap > 0) ? ($count / $totalHandicap * 100) : 0; @endphp
                <tr>
                    <td>{{ $type ?: 'Non spécifié' }}</td>
                    <td class="right">{{ number_format($count, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($percent, 1, ',', ' ') }}%</td>
                </tr>
                @endforeach
                <tr style="background: #e0e7ff; font-weight: bold;">
                    <td>TOTAL</td>
                    <td class="right">{{ number_format($totalHandicap, 0, ',', ' ') }}</td>
                    <td class="right">100%</td>
                </tr>
            </tbody>
        </table>

        <h3>Répartition par Région</h3>
        <table>
            <thead>
                <tr>
                    <th>Région (DREN)</th>
                    <th class="right">Candidats Handicapés</th>
                    <th class="right">% par Région</th>
                </tr>
            </thead>
            <tbody>
                @foreach($handicapByDren as $dren => $count)
                    @php
                        $drenTotal = collect($recapByDren)->where('dren', $dren)->first()['total_candidats'] ?? 1;
                        $percentDren = ($drenTotal > 0) ? ($count / $drenTotal * 100) : 0;
                    @endphp
                <tr>
                    <td><strong>{{ $dren }}</strong></td>
                    <td class="right">{{ number_format($count, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($percentDren, 1, ',', ' ') }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- HANDICAP CHARTS -->
        <div class="chart-container">
            <h3>📊 Graphe : Candidats Handicapés par Région (Barres)</h3>
            <svg width="100%" height="280" viewBox="0 0 800 280" xmlns="http://www.w3.org/2000/svg">
                <rect width="800" height="280" fill="white"/>
                <line x1="80" y1="20" x2="80" y2="230" stroke="#ec4899" stroke-width="2"/>
                <line x1="80" y1="230" x2="780" y2="230" stroke="#ec4899" stroke-width="2"/>
                
                @php
                    $maxHandicap = collect($handicapByDren)->max() ?: 1;
                    $barWidth = (700 - collect($handicapByDren)->count() * 10) / max(collect($handicapByDren)->count(), 1);
                    $barSpacing = 10;
                    $chartHeight = 210;
                    $x = 100;
                @endphp

                @foreach($handicapByDren as $dren => $count)
                    @php
                        $barHeightValue = ($count / $maxHandicap) * $chartHeight;
                        $barY = 230 - $barHeightValue;
                    @endphp
                    <rect x="{{ $x }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeightValue }}" fill="#ec4899" stroke="#be1249" stroke-width="1" opacity="0.8"/>
                    <text x="{{ $x + $barWidth / 2 }}" y="{{ $barY - 5 }}" text-anchor="middle" font-size="10" font-weight="bold" fill="#ec4899">
                        {{ number_format($count, 0) }}
                    </text>
                    <text x="{{ $x + $barWidth / 2 }}" y="250" text-anchor="middle" font-size="9" fill="#333">
                        {{ substr($dren, 0, 8) }}
                    </text>
                    @php $x += $barWidth + $barSpacing; @endphp
                @endforeach

                <text x="70" y="235" text-anchor="end" font-size="9" fill="#666">0</text>
                <text x="70" y="135" text-anchor="end" font-size="9" fill="#666">{{ number_format($maxHandicap / 2, 0) }}</text>
                <text x="70" y="25" text-anchor="end" font-size="9" fill="#666">{{ number_format($maxHandicap, 0) }}</text>
            </svg>
        </div>

        @if(count($handicapByType) > 0)
        <div class="chart-container">
            <h3>🍰 Graphe : Types d'Handicap (Camembert)</h3>
            <svg width="100%" height="320" viewBox="0 0 500 320" xmlns="http://www.w3.org/2000/svg">
                <rect width="500" height="320" fill="white"/>
                @php
                    $centerX = 150;
                    $centerY = 150;
                    $radius = 100;
                    $currentAngle = -90;
                    $handicapColors = ['#ec4899', '#f97316', '#f59e0b', '#eab308', '#10b981', '#06b6d4'];
                    $totalHandicap = collect($handicapByType)->sum() ?: 1;
                @endphp

                @foreach($handicapByType as $type => $count)
                    @php
                        $sliceSize = ($count / $totalHandicap) * 360;
                        $startAngle = $currentAngle;
                        $endAngle = $currentAngle + $sliceSize;
                        
                        $x1 = $centerX + $radius * cos(deg2rad($startAngle));
                        $y1 = $centerY + $radius * sin(deg2rad($startAngle));
                        $x2 = $centerX + $radius * cos(deg2rad($endAngle));
                        $y2 = $centerY + $radius * sin(deg2rad($endAngle));
                        
                        $largeArc = ($sliceSize > 180) ? 1 : 0;
                        
                        $labelAngle = $startAngle + $sliceSize / 2;
                        $labelX = $centerX + ($radius * 0.65) * cos(deg2rad($labelAngle));
                        $labelY = $centerY + ($radius * 0.65) * sin(deg2rad($labelAngle));
                        $percent = ($totalHandicap > 0) ? ($count / $totalHandicap * 100) : 0;
                    @endphp
                    <path d="M {{ $centerX }},{{ $centerY }} L {{ $x1 }},{{ $y1 }} A {{ $radius }},{{ $radius }} 0 {{ $largeArc }},1 {{ $x2 }},{{ $y2 }} Z" 
                          fill="{{ $handicapColors[$loop->index % count($handicapColors)] }}" stroke="white" stroke-width="2"/>
                    @if($percent > 5)
                    <text x="{{ $labelX }}" y="{{ $labelY }}" text-anchor="middle" font-size="10" font-weight="bold" fill="white">
                        {{ number_format($percent, 0) }}%
                    </text>
                    @endif
                    @php $currentAngle = $endAngle; @endphp
                @endforeach

                <!-- Legend -->
                @php $legendY = 20; $idx = 0; @endphp
                @foreach($handicapByType as $type => $count)
                    <rect x="280" y="{{ $legendY + ($idx * 25) }}" width="15" height="15" fill="{{ $handicapColors[$idx % count($handicapColors)] }}"/>
                    <text x="305" y="{{ $legendY + 12 + ($idx * 25) }}" font-size="10" fill="#333">
                        {{ ($type ?: 'N.S.') }} ({{ number_format($count, 0) }})
                    </text>
                    @php $idx++; @endphp
                @endforeach
            </svg>
        </div>
        @endif

        <!-- FOOTER & SIGNATURE -->
        <div class="footer">
            <div style="margin-bottom: 1cm;">
                <strong>Document de Synthèse Statistique</strong><br>
                Ministère de l'Éducation Nationale<br>
                {{ now()->format('d \\m\\o\\i\\s Y') }}
            </div>
            <div class="signature">
                Rapport produit par <strong>Andry Michael RAMAROSON</strong>
            </div>
        </div>
    </div>
</body>
</html>
