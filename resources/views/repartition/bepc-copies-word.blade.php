<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accuse de reception centre BEPC</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; }
        .page { page-break-after: always; margin-bottom: 24px; }
        .page:last-child { page-break-after: auto; }
        .header { text-align: center; margin-bottom: 18px; line-height: 1.4; }
        .title { text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; margin: 14px 0 18px; }
        .meta { margin-bottom: 10px; }
        .line { margin: 8px 0; }
        .dots { display: inline-block; min-width: 220px; border-bottom: 1px dotted #111827; height: 16px; vertical-align: middle; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; margin-bottom: 18px; }
        th, td { border: 1px solid #94a3b8; padding: 6px; }
        th { background: #e2e8f0; text-align: left; }
        .recap { margin-top: 10px; padding: 10px 12px; border: 1px solid #cbd5e1; background: #f8fafc; }
        .center { text-align: center; }
        .right { text-align: right; }
        .signatures { width: 100%; margin-top: 28px; }
        .signatures td { width: 50%; vertical-align: top; border: none; }
        .signature-box { padding-top: 18px; }
    </style>
</head>
<body>
@forelse($rows as $index => $row)
    <div class="page">
        <div class="header">
            <div><strong>REPOBLIKAN'I MADAGASIKARA</strong></div>
            <div>Fitiavana - Tanindrazana - Fandrosoana</div>
            <div><strong>MINISTERE DE L'EDUCATION NATIONALE</strong></div>
            <div>Service de l'Organisation des Examens</div>
        </div>

        <div class="title">Accuse de reception centre</div>

        <div class="meta">
            <div class="line">Je soussigné(e) : <span class="dots"></span></div>
            <div class="line">DREN - CHEF - Autres : <span class="dots"></span></div>
            <div class="line">En service a : <span class="dots"></span></div>
            <div class="line">Annee : <strong>{{ $filters['annee'] !== '' ? $filters['annee'] : '............' }}</strong></div>
            <div class="line">DREN : <strong>{{ $row['dren'] }}</strong> | CISCO : <strong>{{ $row['cisco'] }}</strong> | Type : <strong>{{ $row['row_type'] === 'centre_isole' ? 'Centre isole' : 'CISCO' }}</strong> | Code postal : <strong>{{ $row['code_postal'] !== '' ? $row['code_postal'] : '.............' }}</strong></div>
            <div class="line">Mode d'arrondi : <strong>{{ ($filters['rounding_mode'] ?? 'up') === 'down' ? 'Arrondi moins' : 'Arrondi plus' }}</strong></div>
            <div class="line">Centres sans langue : <strong>{{ number_format($row['centres_sans_langue'], 0, ',', ' ') }}</strong> / {{ number_format($row['total_centres'], 0, ',', ' ') }} ({{ number_format($row['centres_sans_langue_percent'], 1, ',', ' ') }} %)</div>
            <div class="line">Fusion petit lot : <strong>{{ ($filters['merge_small_soubique'] ?? false) ? 'Oui' : 'Non' }}</strong> | Capacite : <strong>{{ number_format($filters['merge_small_soubique_capacity'] ?? 6000, 0, ',', ' ') }}</strong></div>
        </div>

        <div class="line">Le colis ci apres :</div>

        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 8%;">Numero</th>
                    <th style="width: 47%;">Designation</th>
                    <th class="right" style="width: 20%;">Nombre</th>
                    <th style="width: 25%;">Observation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td>Feuilles doubles</td>
                    <td class="right">{{ number_format($row['feuilles_double'], 0, ',', ' ') }}</td>
                    <td>....................</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td>Feuilles simples</td>
                    <td class="right">{{ number_format($row['feuilles_simple'], 0, ',', ' ') }}</td>
                    <td>....................</td>
                </tr>
                <tr>
                    <td class="center">3</td>
                    <td>Total soubique</td>
                    <td class="right">{{ number_format($row['soubique_total'], 0, ',', ' ') }}</td>
                    <td>
                        @if(($row['soubique_mixte'] ?? 0) > 0)
                            Mixte: {{ number_format($row['soubique_mixte'], 0, ',', ' ') }}
                        @else
                            Double: {{ number_format($row['soubique_feuilles_double'], 0, ',', ' ') }} | Simple: {{ number_format($row['soubique_feuilles_simple'], 0, ',', ' ') }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="recap">
            <div class="line"><strong>Recapitulatif arrondi</strong></div>
            <div class="line">Feuilles doubles arrondies : <strong>{{ number_format($row['feuilles_double_arrondies'], 0, ',', ' ') }}</strong></div>
            <div class="line">Feuilles simples arrondies : <strong>{{ number_format($row['feuilles_simple_arrondies'], 0, ',', ' ') }}</strong></div>
            <div class="line">Surplus langue absente : <strong>{{ number_format($row['missing_langue_surplus_sheets'], 0, ',', ' ') }}</strong></div>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div><strong>Le convoyeur</strong></div>
                    <div class="signature-box">Nom et signature : <span class="dots"></span></div>
                </td>
                <td>
                    <div style="text-align: right;"><strong>Le receptionnaire</strong></div>
                    <div class="signature-box" style="text-align: right;">Nom et signature : <span class="dots"></span></div>
                </td>
            </tr>
        </table>
    </div>
@empty
    <p>Aucune donnee BEPC disponible pour ces filtres.</p>
@endforelse
</body>
</html>
