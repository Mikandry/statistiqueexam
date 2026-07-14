<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Procès-Verbal de Traçabilité Automatique</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        @page WordSectionPortrait {
            size: 595.3pt 841.9pt;
            mso-page-orientation: portrait;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            color: #111827;
            line-height: 1.4;
        }
        h1 { 
            font-size: 16px; 
            font-weight: bold;
            margin: 0 0 12px 0;
            text-align: center;
        }
        h2 { 
            font-size: 13px; 
            font-weight: bold;
            margin: 12px 0 8px 0;
            border-bottom: 2px solid #111827;
            padding-bottom: 4px;
        }
        h3 {
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }
        .meta { 
            margin-bottom: 8px;
            text-align: center;
            font-size: 10px;
        }
        .box { 
            border: 1px solid #cbd5e1; 
            padding: 8px; 
            margin-bottom: 12px;
            background: #f8fafc;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 12px;
        }
        th, td { 
            border: 1px solid #cbd5e1; 
            padding: 5px; 
            vertical-align: top;
            text-align: left;
        }
        th { 
            background: #e2e8f0; 
            font-weight: bold;
        }
        .right { 
            text-align: right; 
        }
        .center {
            text-align: center;
        }
        .group-section {
            page-break-inside: avoid;
            margin-bottom: 16px;
            border: 1px solid #475569;
            padding: 8px;
            background: #ffffff;
        }
        .group-title {
            background: #0f172a;
            color: white;
            padding: 6px 8px;
            font-weight: bold;
            margin: -8px -8px 8px -8px;
        }
        .compteur-block {
            margin-bottom: 10px;
            padding: 6px;
            border-left: 3px solid #0f172a;
            background: #f8fafc;
        }
        .compteur-label {
            font-weight: bold;
            margin-bottom: 4px;
        }
        .centre-line {
            margin-bottom: 3px;
            font-size: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .portrait-section { 
            page: WordSectionPortrait; 
        }
        .small { 
            font-size: 9px; 
        }
    </style>
</head>
<body>
<div class="portrait-section">
    <h1>PROCÈS-VERBAL DE TRAÇABILITÉ AUTOMATIQUE DES COMPTEURS</h1>
    
    <div class="meta">
        <strong>Année:</strong> {{ $filters['annee'] !== '' ? $filters['annee'] : 'Toutes' }} | 
        <strong>Examen:</strong> {{ $filters['type_examen'] }} | 
        <strong>DREN:</strong> {{ $filters['dren'] !== '' ? $filters['dren'] : 'Toutes' }}<br>
        <strong>Date:</strong> {{ now()->format('d/m/Y') }}
    </div>

    <div class="box">
        <strong>Résumé Général:</strong><br>
        Centres de correction: {{ number_format($stats['total_centres'], 0, ',', ' ') }} | 
        Candidats: {{ number_format($stats['total_candidats'], 0, ',', ' ') }} | 
        Salles d'examen: {{ number_format($stats['total_salles'], 0, ',', ' ') }} | 
        PE: {{ number_format($stats['total_pe'], 0, ',', ' ') }} | 
        GE: {{ number_format($stats['total_ge'], 0, ',', ' ') }}
    </div>

    <h2>Répartition par DREN</h2>
    <table>
        <thead>
            <tr>
                <th>DREN</th>
                <th class="right">Candidats</th>
                <th class="right">Salles</th>
                <th class="right">PE</th>
                <th class="right">GE</th>
                <th class="right">Centres</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drenRows as $dren)
                <tr>
                    <td><strong>{{ $dren['dren'] }}</strong></td>
                    <td class="right">{{ number_format($dren['total_candidats'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_salles'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_ge'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($dren['total_centres'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Aucune donnée disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Distribution Automatique des Compteurs par Centre</h2>
    
    @php
        // Grouper les centres par DREN, puis par CISCO
        $centresByDrenCisco = $centreRows->groupBy(function($centre) {
            return $centre['dren'] . '|' . $centre['cisco'];
        })->map(function($group) {
            $first = $group->first();
            return [
                'dren' => $first['dren'],
                'cisco' => $first['cisco'],
                'centres' => $group->sortBy('centre_ecrit')->values()
            ];
        })->sortBy(function($item) {
            return $item['dren'] . '|' . $item['cisco'];
        })->values();

        // Fonction pour diviser les centres entre compteurs de manière équilibrée
        function distributeCompteurs($centres, $numCompteurs = 3) {
            $centresArray = $centres->toArray();
            $count = count($centresArray);
            $compteurs = [];
            
            // Déterminer le nombre réel de compteurs basé sur le nombre de centres
            $actualCompteurs = min($numCompteurs, max(1, ceil($count / 2)));
            
            // Diviser les centres entre les compteurs
            $centresPerCompteur = ceil($count / $actualCompteurs);
            
            for ($i = 0; $i < $actualCompteurs; $i++) {
                $start = $i * $centresPerCompteur;
                $compteurCentres = array_slice($centresArray, $start, $centresPerCompteur);
                
                if (!empty($compteurCentres)) {
                    $firstPe = 1;
                    $totalPe = 0;
                    $labels = [];
                    
                    foreach ($compteurCentres as $centre) {
                        $totalPe += (int)$centre['pe'];
                        $lastPe = $firstPe + ((int)$centre['pe']) - 1;
                        $centreLabel = $centre['centre_ecrit'];
                        if ((int)$centre['pe'] > 0) {
                            $labels[] = $centreLabel . ' PE ' . $firstPe . '-' . $lastPe;
                        } else {
                            $labels[] = $centreLabel . ' (sans PE)';
                        }
                        $firstPe = $lastPe + 1;
                    }
                    
                    $compteurs[] = [
                        'numero' => chr(65 + $i), // A, B, C, etc.
                        'centres' => $compteurCentres,
                        'labels' => $labels,
                        'total_candidats' => array_sum(array_map(fn($c) => (int)$c['total_candidats'], $compteurCentres)),
                        'total_pe' => $totalPe,
                    ];
                }
            }
            
            return $compteurs;
        }
    @endphp

    @forelse($centresByDrenCisco as $ciscoGroup)
        <div class="group-section">
            <div class="group-title">
                {{ $ciscoGroup['dren'] }} - {{ $ciscoGroup['cisco'] }}
            </div>
            
            @php
                $compteurs = distributeCompteurs(collect($ciscoGroup['centres']));
            @endphp

            @foreach($compteurs as $compteur)
                <div class="compteur-block">
                    <div class="compteur-label">
                        {{ $ciscoGroup['cisco'] }}, Compteur {{ $compteur['numero'] }}
                    </div>
                    <div class="centre-line">
                        @foreach($compteur['labels'] as $label)
                            {{ $label }}<br>
                        @endforeach
                        <strong style="margin-top: 4px; display: block;">
                            Total: {{ number_format($compteur['total_candidats'], 0, ',', ' ') }} candidats | {{ number_format($compteur['total_pe'], 0, ',', ' ') }} PE
                        </strong>
                    </div>
                </div>
            @endforeach

            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #cbd5e1;">
                <strong>Résumé CISCO {{ $ciscoGroup['cisco'] }}:</strong> {{ count($compteurs) }} compteur(s)
            </div>
        </div>
    @empty
        <div class="box">
            Aucun centre disponible pour les filtres sélectionnés.
        </div>
    @endforelse

    <div class="page-break"></div>

    <h2>Récapitulatif Complet par Compteur</h2>
    
    @php
        // Reconstruire le recap complet avec tous les compteurs
        $fullRecap = [];
        foreach ($centresByDrenCisco as $ciscoGroup) {
            $compteurs = distributeCompteurs(collect($ciscoGroup['centres']));
            foreach ($compteurs as $compteur) {
                $fullRecap[] = [
                    'dren' => $ciscoGroup['dren'],
                    'cisco' => $ciscoGroup['cisco'],
                    'numero' => $compteur['numero'],
                    'centres' => $compteur['centres'],
                    'labels' => $compteur['labels'],
                    'total_candidats' => $compteur['total_candidats'],
                    'total_pe' => $compteur['total_pe'],
                ];
            }
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th>DREN</th>
                <th>CISCO</th>
                <th>Compteur</th>
                <th>Centres Assignés</th>
                <th class="right">Candidats</th>
                <th class="right">PE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fullRecap as $recap)
                <tr>
                    <td>{{ $recap['dren'] }}</td>
                    <td>{{ $recap['cisco'] }}</td>
                    <td><strong>{{ $recap['numero'] }}</strong></td>
                    <td>
                        @foreach($recap['labels'] as $label)
                            {{ $label }}<br>
                        @endforeach
                    </td>
                    <td class="right">{{ number_format($centre['total_candidats'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($centre['total_salles'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($centre['pe'], 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($centre['ge_count'], 0, ',', ' ') }}</td>
                    <td>
                        @if($centre['pe'] > 0)
                            PE 1 - PE {{ number_format($centre['pe'], 0, ',', ' ') }}
                        @else
                            Aucun
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Aucun centre disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="box" style="margin-top: 16px;">
        <strong>Notes Importantes:</strong>
        <ul style="margin: 4px 0; padding-left: 20px;">
            <li>Ce document constitue la traçabilité automatique des compteurs pour l'année {{ $filters['annee'] !== '' ? $filters['annee'] : 'en cours' }}</li>
            <li>Chaque compteur est associé à un centre d'examen spécifique</li>
            <li>Les PE (Petits Examinateurs) sont numérotés séquentiellement par centre</li>
            <li>La distribution des GE (Grands Examinateurs) est automatique selon le nombre de salles</li>
        </ul>
    </div>

    <div style="margin-top: 20px; text-align: center; font-size: 9px; color: #475569;">
        <p>Document généré automatiquement - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
</body>
</html>
