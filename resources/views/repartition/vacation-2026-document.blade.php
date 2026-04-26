<!DOCTYPE html>
<html lang="fr" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strtoupper($document) }} - Vacation 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #111827;
            margin: 12mm 10mm 12mm 6mm;
        }
        p, div, td, th {
            margin-top: 0;
            margin-bottom: 0;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .justify {
            text-align: justify;
        }
        .top-banner {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
        }
        .top-banner td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .country-block {
            width: 42%;
        }
        .decision-block {
            width: 58%;
        }
        .banner-stack {
            text-align: center;
        }
        .banner-image {
            width: 26mm;
            height: 26mm;
            object-fit: contain;
            display: inline-block;
        }
        .country-motto {
            margin-top: 1.5mm;
            font-size: 9.75pt;
            text-align: center;
            font-style: italic;
        }
        .ministry {
            margin-top: 3mm;
            font-size: 11.25pt;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .ministry-line {
            margin-top: 0.5mm;
            font-size: 10.5pt;
            font-weight: 700;
            text-align: center;
        }
        .decision-reference {
            margin-top: 6mm;
            font-size: 12.5pt;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .decision-subject {
            margin-top: 2.5mm;
            text-align: justify;
        }
        .doc-title {
            margin-bottom: 3mm;
            font-size: 13.5pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .article-text,
        .decision-subject,
        .page-stop,
        .final-summary {
            text-align: justify;
            white-space: pre-line;
        }
        .considerant-list {
            margin-top: 0;
        }
        .considerant-line {
            text-align: left;
            white-space: normal;
        }
        .decide {
            margin-top: 5mm;
            margin-bottom: 2mm;
            font-weight: 700;
            text-transform: uppercase;
        }
        .article-title {
            font-weight: 700;
        }
        .decision-pages {
            width: 100%;
        }
        .table-page {
            width: 100%;
            margin-top: 4mm;
            page-break-inside: avoid;
        }
        .table-page.page-break {
            page-break-before: always;
            break-before: page;
        }
        .table-page table,
        .plain-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table-page th,
        .table-page td,
        .plain-table th,
        .plain-table td {
            border: 1px solid #111827;
            padding: 4px 5px;
            vertical-align: top;
            font-size: 11pt;
        }
        .table-page thead th,
        .plain-table thead th {
            font-weight: 700;
            text-align: center;
        }
        .decision-col-order {
            width: 10%;
        }
        .decision-col-page {
            width: 8%;
        }
        .decision-col-name {
            width: 38%;
        }
        .decision-col-im {
            width: 16%;
        }
        .decision-col-localite {
            width: 28%;
        }
        .page-stop {
            margin-top: 3mm;
            font-weight: 700;
            page-break-inside: avoid;
        }
        .page-number {
            margin-top: 1mm;
            text-align: center;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .final-summary {
            margin-top: 4mm;
            font-weight: 700;
        }
        .signature {
            margin-top: 10mm;
            text-align: right;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 10mm 12mm 6mm;
            }
            body {
                margin: 0;
            }
            tr, td, th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    @php
        $activityLabel = $selectedActivity?->examen
            ? trim($selectedActivity->examen.' - '.$selectedActivity->libelle)
            : 'TOUTES ACTIVITES';
    @endphp

    @if($document === 'decision')
        @php
            $spellFormatter = class_exists(\NumberFormatter::class)
                ? new \NumberFormatter('fr_FR', \NumberFormatter::SPELLOUT)
                : null;
            $toWords = static function (int $number) use ($spellFormatter): string {
                if ($number <= 0) {
                    return 'ZERO';
                }

                $words = $spellFormatter ? $spellFormatter->format($number) : (string) $number;
                $words = is_string($words) ? trim(str_replace('-', ' ', $words)) : (string) $number;

                return mb_strtoupper($words);
            };

            $decisionRows = [];
            $globalOrder = 1;
            foreach ($rows as $row) {
                $decisionRows[] = [
                    'type' => 'agent',
                    'order' => $globalOrder,
                    'nom' => $row['nom'],
                    'im' => $row['im'],
                    'localite' => $row['localite'],
                ];
                $globalOrder++;
            }

            $totalAgents = collect($decisionRows)->where('type', 'agent')->count();
            $articlePremier = filled($setting?->decision_article_1)
                ? $setting->decision_article_1
                : "Les agents fonctionnaires participants aux travaux de {$activityLabel} sont nommes ainsi qu'il suit :";
            $decisionHeaders = ["N° D'ORDRE", 'N°', 'NOM ET PRENOMS', 'IM', 'LOCALITE DE SERVICE'];
            $considerantLines = collect(preg_split('/\r\n|\r|\n/', (string) ($setting?->considerant ?? '')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values();
            $estimateVisualLines = static function (?string $text, int $charsPerLine): int {
                $text = trim((string) $text);
                if ($text === '') {
                    return 0;
                }

                $rawLines = preg_split('/\r\n|\r|\n/', $text) ?: [];
                $count = 0;
                foreach ($rawLines as $rawLine) {
                    $line = trim((string) $rawLine);
                    $count += max(1, (int) ceil(mb_strlen($line) / $charsPerLine));
                }

                return $count;
            };
            $considerantVisualLines = $considerantLines->sum(fn ($line) => $estimateVisualLines($line, 100));
            $articleVisualLines = $estimateVisualLines($articlePremier, 110);
            $subjectVisualLines = $estimateVisualLines("portant nomination des agents fonctionnaires participants aux travaux de {$activityLabel}.", 70);
            $introVisualLines = 11 + $considerantVisualLines + $articleVisualLines + $subjectVisualLines;
            $firstPageRows = max(10, 28 - max(0, $introVisualLines - 20));
            $otherPageRows = 18;
            $decisionPages = collect();
            $remainingRows = collect($decisionRows)->values();

            if ($remainingRows->isNotEmpty()) {
                $decisionPages->push($remainingRows->take($firstPageRows)->values());
                $remainingRows = $remainingRows->slice($firstPageRows)->values();

                while ($remainingRows->isNotEmpty()) {
                    $decisionPages->push($remainingRows->take($otherPageRows)->values());
                    $remainingRows = $remainingRows->slice($otherPageRows)->values();
                }
            }
        @endphp

        <div id="decision-intro">
            <table class="top-banner">
                <tr>
                    <td class="country-block">
                        <div class="banner-stack">
                            <img src="{{ asset('madagascar-seal.png') }}" alt="Sceau de Madagascar" class="banner-image">
                            <div class="country-motto">Fitiavana - Tanindrazana - Fandrosoana</div>
                            <div class="ministry">MINISTERE DE L'EDUCATION NATIONALE</div>
                            <div class="ministry-line">--------------------</div>
                        </div>
                    </td>
                    <td class="decision-block">
                        <div class="decision-reference">DECISION N°________________ - MEN</div>
                        <div class="decision-subject">
                            portant nomination des agents fonctionnaires participants aux travaux de {{ $activityLabel }}.
                        </div>
                    </td>
                </tr>
            </table>

            <div class="doc-title">{{ $documentTitle ?? 'DECISION' }}</div>

            @if($considerantLines->isNotEmpty())
                <div class="considerant-list">
                    @foreach($considerantLines as $considerantLine)
                        <div class="considerant-line">{{ $considerantLine }}</div>
                    @endforeach
                </div>
            @endif

            <div class="decide">DECIDE :</div>

            <div class="article-title">Article premier :</div>
            <div class="article-text">{{ $articlePremier }}</div>
        </div>

        <div id="decision-pages" class="decision-pages">
            @foreach($decisionPages as $pageIndex => $pageRows)
                @php
                    $pageCount = $pageRows->count();
                    $pageWords = mb_strtolower($toWords($pageCount));
                @endphp
                <div class="table-page{{ $pageIndex > 0 ? ' page-break' : '' }}">
                    <table class="plain-table">
                        <thead>
                            <tr>
                                <th class="decision-col-order">{{ $decisionHeaders[0] }}</th>
                                <th class="decision-col-page">{{ $decisionHeaders[1] }}</th>
                                <th class="decision-col-name">{{ $decisionHeaders[2] }}</th>
                                <th class="decision-col-im">{{ $decisionHeaders[3] }}</th>
                                <th class="decision-col-localite">{{ $decisionHeaders[4] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pageRows as $rowIndex => $row)
                                <tr>
                                    <td class="center">{{ $row['order'] }}</td>
                                    <td class="center">{{ $rowIndex + 1 }}</td>
                                    <td class="justify">{{ $row['nom'] }}</td>
                                    <td class="center">{{ $row['im'] }}</td>
                                    <td class="justify">{{ $row['localite'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="page-stop">
                        Arrête la présente page au nombre de {{ $pageWords }} ({{ $pageCount }}) agent{{ $pageCount > 1 ? 's' : '' }}.
                    </div>

                    <div class="page-number">{{ $pageIndex + 1 }}</div>
                </div>
            @endforeach
        </div>

        <div id="decision-tail">
            @if(filled($setting?->decision_article_2))
                <div class="article-title" style="margin-top: 4mm;">Article 2 :</div>
                <div class="article-text">{{ $setting->decision_article_2 }}</div>
            @endif

            <div class="final-summary">
                ARRETE LA PRESENTE DECISION AU NOMBRE DE {{ $toWords($totalAgents) }} ({{ $totalAgents }}) AGENT{{ $totalAgents > 1 ? 'S' : '' }}.
            </div>

            <div class="signature">
                Antananarivo, le {{ now()->format('d/m/Y') }}<br>
                {{ $setting?->signature }}
            </div>
        </div>
    @else
        @php
            $considerantLines = collect(preg_split('/\r\n|\r|\n/', (string) ($setting?->considerant ?? '')))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values();
            $referenceLabel = $document === 'note-service' ? 'NOTE DE SERVICE N°________________ - MEN' : (($documentTitle ?? strtoupper($document)).' N°________________ - MEN');
        @endphp

        @if(in_array($document, ['note-service', 'decision'], true))
            <table class="top-banner">
                <tr>
                    <td class="country-block">
                        <div class="country-top">
                            <div class="country-name">REPOBLIKAN'I MADAGASIKARA</div>
                            <div class="country-motto">Fitiavana - Tanindrazana - Fandrosoana</div>
                        </div>
                        <div class="flag-row">
                            <div class="flag-white"></div>
                            <div class="flag-red"></div>
                            <div class="flag-green"></div>
                        </div>
                        <div class="ministry">MINISTERE DE L'EDUCATION</div>
                        <div class="ministry-line">--------------------</div>
                    </td>
                    <td class="decision-block">
                        <div class="decision-reference">{{ $referenceLabel }}</div>
                        <div class="decision-subject">
                            {{ $document === 'note-service' ? "relative aux travaux de {$activityLabel}." : "portant nomination des agents fonctionnaires participants aux travaux de {$activityLabel}." }}
                        </div>
                    </td>
                </tr>
            </table>
        @endif

        <div class="doc-title">{{ $documentTitle ?? strtoupper($document) }} - VACATION 2026</div>

        @if($considerantLines->isNotEmpty())
            <div class="considerant-list">
                @foreach($considerantLines as $considerantLine)
                    <div class="considerant-line">{{ $considerantLine }}</div>
                @endforeach
            </div>
        @endif

        <div style="margin-top: 4mm;">
            <table class="plain-table">
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        <tr>
                            @if($document === 'decompte')
                                <td class="center">{{ $index + 1 }}</td>
                                <td>{{ $row['activite'] }}</td>
                                <td>{{ $row['nom'] }}</td>
                                <td class="center">{{ $row['im'] }}</td>
                                <td>{{ $row['localite'] }}</td>
                                <td class="right">{{ $row['jours'] }}</td>
                                <td class="right">{{ $row['taux'] !== null ? number_format((float) $row['taux'], 2, ',', ' ') : '' }}</td>
                                <td class="right">{{ $row['montant'] !== null ? number_format((float) $row['montant'], 2, ',', ' ') : '' }}</td>
                            @elseif($document === 'presence')
                                <td class="center">{{ $index + 1 }}</td>
                                <td>{{ $row['activite'] }}</td>
                                <td>{{ $row['nom'] }}</td>
                                <td class="center">{{ $row['im'] }}</td>
                                <td>{{ $row['localite'] }}</td>
                                <td>{{ $row['cin'] }}</td>
                                <td></td>
                            @else
                                <td>{{ $row['examen'] }}</td>
                                <td>{{ $row['activite'] }}</td>
                                <td>{{ $row['nom'] }}</td>
                                <td class="center">{{ $row['im'] }}</td>
                                <td>{{ $row['localite'] }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) }}" class="center">Aucune donnée affectée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="signature">
            Antananarivo, le {{ now()->format('d/m/Y') }}<br>
            {{ $setting?->signature }}
        </div>
    @endif

    <script>
        (() => {
            const shouldPrint = window.location.search.includes('autoprint=1');

            if (shouldPrint) {
                window.print();
            }
        })();
    </script>
</body>
</html>
