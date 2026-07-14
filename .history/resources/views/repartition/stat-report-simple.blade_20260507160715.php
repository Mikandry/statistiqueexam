<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Statistique Simple - {{ $filters['annee'] }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 2cm;
        }
        h1, h2, h3 {
            color: #1e40af;
            page-break-after: avoid;
        }
        h1 { font-size: 18pt; text-align: center; margin-bottom: 1cm; }
        h2 { font-size: 16pt; margin-top: 1cm; margin-bottom: 0.5cm; }
        h3 { font-size: 14pt; margin-top: 0.8cm; margin-bottom: 0.3cm; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.5cm 0;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #000;
            padding: 4pt;
            text-align: left;
        }
        th {
            background-color: #e5e7eb;
            font-weight: bold;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }
        .chart-placeholder {
            border: 1px dashed #ccc;
            padding: 1cm;
            text-align: center;
            margin: 0.5cm 0;
            page-break-inside: avoid;
        }
        @media print {
            body { margin: 1cm; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <h1>Rapport Statistique {{ $filters['type_examen'] === 'ALL' ? 'BEPC/CEPE' : $filters['type_examen'] }} - Année {{ $filters['annee'] }}</h1>

    <h2>Récapitulatif Général</h2>
    <table class="no-break">
        <tr>
            <th>Indicateur</th>
            <th class="right">Valeur</th>
        </tr>
        <tr>
            <td>Nombre de centres de correction</td>
            <td class="right">{{ number_format($recapGeneral['total_centres_correction'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Nombre de centres d'écrit</td>
            <td class="right">{{ number_format($recapGeneral['total_centres_ecrit'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Nombre total de salles</td>
            <td class="right">{{ number_format($recapGeneral['total_salles'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Nombre total de candidats</td>
            <td class="right">{{ number_format($recapGeneral['total_candidats'], 0, ',', ' ') }}</td>
        </tr>
    </table>

    <h2>Répartition par Région (DREN)</h2>
    <table class="no-break">
        <tr>
            <th>Région</th>
            <th class="right">Centres Correction</th>
            <th class="right">Centres Écrit</th>
            <th class="right">Salles</th>
            <th class="right">Candidats</th>
        </tr>
        @foreach($recapByDren as $dren)
        <tr>
            <td>{{ $dren['dren'] }}</td>
            <td class="right">{{ number_format($dren['total_correction'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($dren['total_ecrit'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($dren['total_salles'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($dren['total_candidats'], 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="chart-placeholder">
        <h3>Graphe : Répartition des candidats par région</h3>
        <p>[Graphique en barres - Candidats par DREN]</p>
    </div>

    @if($showLangueComparison)
    <h2>Comparaison des Langues Vivantes (BEPC)</h2>
    <table class="no-break">
        <tr>
            <th>Langue</th>
            <th class="right">Candidats</th>
            <th class="right">Salles</th>
        </tr>
        @foreach($languesComparison as $langue)
        <tr>
            <td>{{ $langue['langue'] }}</td>
            <td class="right">{{ number_format($langue['total_candidats'], 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($langue['total_salles'], 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="chart-placeholder">
        <h3>Graphe : Répartition par langue vivante</h3>
        <p>[Graphique circulaire - Pourcentages par langue]</p>
    </div>
    @endif

    <h2>Candidats à Besoins Spécifiques</h2>

    <h3>Par Type d'Handicap</h3>
    <table class="no-break">
        <tr>
            <th>Type d'Handicap</th>
            <th class="right">Nombre</th>
        </tr>
        @foreach($handicapByType as $type => $count)
        <tr>
            <td>{{ $type }}</td>
            <td class="right">{{ number_format($count, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>

    <h3>Par Région</h3>
    <table class="no-break">
        <tr>
            <th>Région</th>
            <th class="right">Nombre</th>
        </tr>
        @foreach($handicapByDren as $dren => $count)
        <tr>
            <td>{{ $dren }}</td>
            <td class="right">{{ number_format($count, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="chart-placeholder">
        <h3>Graphe : Candidats handicapés par région</h3>
        <p>[Graphique en barres - Handicapés par DREN]</p>
    </div>

    <div class="chart-placeholder">
        <h3>Graphe : Types d'handicap</h3>
        <p>[Graphique circulaire - Répartition par type d'handicap]</p>
    </div>

    <h2>Analyse Graphique Complète</h2>

    <div class="chart-placeholder">
        <h3>Évolution des effectifs par année</h3>
        <p>[Graphique linéaire - Comparaison années précédentes]</p>
    </div>

    <div class="chart-placeholder">
        <h3>Répartition géographique des centres</h3>
        <p>[Carte régionale - Densité des centres par DREN]</p>
    </div>

    <div class="chart-placeholder">
        <h3>Analyse des capacités d'accueil</h3>
        <p>[Graphique combiné - Salles vs Candidats par région]</p>
    </div>

    @if($showLangueComparison)
    <div class="chart-placeholder">
        <h3>Évolution des langues vivantes</h3>
        <p>[Graphique en barres groupées - Langues par année]</p>
    </div>
    @endif

    <div class="chart-placeholder">
        <h3>Indicateurs de performance</h3>
        <p>[Tableau de bord - KPIs principaux]</p>
    </div>

    <div style="margin-top: 2cm; text-align: center; font-style: italic;">
        Rapport généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>