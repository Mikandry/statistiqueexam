<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">

    <style>
        /* ============================================================
           MARGES ET FORMAT PAGE (A4)
           ============================================================ */
        @page {
            margin: 1.25cm 1.45cm 1.35cm 1.45cm;
            size: A4;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            color: #1e293b;
            line-height: 1.35;
            background: white;
        }

        /* ============================================================
           EN-TÊTE INSTITUTIONNEL (avec logo simulé)
           ============================================================ */
        .institution {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            line-height: 1.25;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #0f172a;
            position: relative;
        }

        /* Simule le logo à gauche (carré avec texte) */
        .institution::before {
            content: "MEN";
            display: inline-block;
            font-size: 14pt;
            font-weight: 900;
            background: #0f766e;
            color: white;
            width: 50px;
            height: 50px;
            line-height: 50px;
            border-radius: 8px;
            text-align: center;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            letter-spacing: 0;
        }

        .institution .stars {
            font-size: 10pt;
            letter-spacing: 2px;
            color: #0f766e;
            margin: 3px 0;
        }

        .header-rule {
            border-bottom: 3px solid #0f766e;
            width: 70%;
            margin: 7px auto 0;
            border-radius: 2px;
        }

        /* ============================================================
           RÉFÉRENCE
           ============================================================ */
        .reference {
            text-align: lef;
            font-size: 9.5pt;
            font-weight: 600;
            margin-top: 12px;
            color: #334155;
            letter-spacing: 0.5px;
        }

        /* ============================================================
           TITRE PRINCIPAL
           ============================================================ */
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #0f172a;
            margin: 18px 0 14px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid #cbd5e1;
            display: inline-block;
            width: 100%;
        }

        /* ============================================================
           CORPS DU TEXTE
           ============================================================ */
        .body-text {
            text-align: justify;
            margin: 0.75em 0;
            font-size: 10.5pt;
            text-indent: 1em;
        }

        /* ============================================================
           TABLEAU DES INFORMATIONS DE L'AGENT
           ============================================================ */
        .agent-info {
            margin: 10px 0 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 7px 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .agent-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .agent-info td {
            vertical-align: top;
            padding: 3px 7px 3px 0;
        }

        .agent-info .label {
            font-weight: 600;
            color: #334155;
            width: 34%;
            white-space: nowrap;
        }

        .agent-info .label::after {
            content: " :";
        }

        .agent-info td:last-child {
            font-weight: 500;
            color: #0f172a;
        }

        /* ============================================================
           ENCADRÉS (ÉVÉNEMENTS)
           ============================================================ */
        .event-box {
            margin: 10px 0 12px;
            padding: 8px 12px;
            border-left: 4px solid #0f766e;
            background: #f1f5f9;
            border-radius: 4px;
            font-size: 9.5pt;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }

        .event-box p {
            margin: 2px 0;
        }

        .event-box strong {
            color: #0f172a;
        }

        /* ============================================================
           SIGNATURE
           ============================================================ */
        .signature {
            margin-top: 28px;
            text-align: right;
            padding-right: 20px;
        }

        .signature .ville-date {
            font-weight: 500;
            color: #334155;
            margin-bottom: 15px;
            font-size: 10pt;
        }

        .signature .fonction {
            font-weight: 600;
            font-size: 11.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b;
        }

        .signature .nom-signataire {
            font-weight: bold;
            font-size: 13pt;
            margin-top: 6px;
        }

        .signature .signature-line {
            width: 220px;
            border-bottom: 1.5px solid #1e293b;
            margin: 12px auto 0;
            display: inline-block;
        }

        /* ============================================================
           PIED DE PAGE (optionnel)
           ============================================================ */
        .footer {
            margin-top: 18px;
            font-size: 8pt;
            text-align: center;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
        }

        /* ============================================================
           SAUT DE PAGE
           ============================================================ */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

@php
    $headerLines = collect([
        $settings?->ministere,
        $settings?->secretariat_general,
        $settings?->direction_generale,
        $settings?->direction,
        $settings?->service,
    ])->filter();
@endphp


{{-- ========================================================= --}}
{{-- EN-TETE --}}
{{-- ========================================================= --}}

<div class="institution">

    @foreach($headerLines as $index => $line)

        @if($index > 0)
            <div class="stars">**********</div>
        @endif

        <div>
            {{ $line }}
        </div>

    @endforeach

</div>

<div class="header-rule"></div>


{{-- ========================================================= --}}
{{-- REFERENCE --}}
{{-- ========================================================= --}}

<div class="reference">

    {{ $reference }}

</div>


{{-- ========================================================= --}}
{{-- TITRE --}}
{{-- ========================================================= --}}

<h1 class="title">
    {{ $title }}
</h1>


{{-- ========================================================= --}}
{{-- NON INTERRUPTION --}}
{{-- ========================================================= --}}

@if($document === 'non-interruption')

    <p class="body-text">

        Je, soussigné(e),
        <strong>{{ $settings?->signataire ?: 'le responsable habilité' }}</strong>,
        {{ $settings?->signataire_qualite ?: 'responsable habilité' }},
        atteste que :

    </p>

    <div class="agent-info">

        <table>

            <tr>
                <td width="25%" class="label">
                    Nom et Prénoms :
                </td>

                <td>
                    <strong>{{ $agent->full_name }}</strong>
                </td>
            </tr>

            <tr>
                <td class="label">
                    IM :
                </td>

                <td>
                    {{ $agent->matricule ?: '—' }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Corps et Grade :
                </td>

                <td>
                    {{ $agent->corps ?: '—' }}
                    /
                    {{ $agent->grade ?: '—' }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Budget :
                </td>

                <td>
                    {{ $agent->budget ?: '—' }}
                    &nbsp;&nbsp;&nbsp;
                    <strong>Chapitre :</strong>
                    {{ $agent->chapitre ?: '—' }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    Date d’entrée dans l’Administration :
                </td>

                <td>
                    {{ $agent->date_recrutement?->translatedFormat('d F Y') ?: '—' }}
                </td>
            </tr>

            <tr>
                <td class="label">
                    En service à :
                </td>

                <td>
                    {{ $agent->direction ?: '—' }}
                    @if($agent->service)
                        — {{ $agent->service }}
                    @endif
                </td>
            </tr>

        </table>

    </div>

    <p class="body-text">

        A servi sans interruption au sein du Ministère de l’Education Nationale
        depuis la date de son entrée dans l’administration jusqu’à ce jour.

    </p>

    <p class="body-text">

        En foi de quoi, la présente attestation lui est délivrée pour servir
        et valoir ce que de droit.

    </p>

@elseif($document === 'prise-service')

    <p class="body-text">
        Je, soussigné(e), <strong>{{ $settings?->signataire ?: 'le responsable habilité' }}</strong>,
        {{ $settings?->signataire_qualite ?: 'responsable habilité' }}, certifie que l’agent ci-dessous
        a effectivement pris service au sein de l’administration.
    </p>

    @include('hr.documents.partials.agent-info')

    <div class="event-box">
        <p><strong>Date de prise de service :</strong> {{ $agent->date_prise_service?->format('d/m/Y') ?: 'Non renseignée' }}</p>
        <p><strong>Fonction :</strong> {{ $agent->fonction ?: 'Non renseignée' }}</p>
        <p><strong>Direction :</strong> {{ $agent->direction ?: 'Non renseignée' }}</p>
        <p><strong>Service :</strong> {{ $agent->service ?: 'Non renseigné' }}</p>
    </div>

    <p class="body-text">La présente fiche est délivrée pour servir et valoir ce que de droit.</p>


{{-- ========================================================= --}}
{{-- NON-JOUISSANCE DE CONGE                                   --}}
{{-- ========================================================= --}}

@elseif($document === 'non-jouissance')

    @include('hr.documents.partials.agent-info')

    <p class="body-text">
        Après vérification des registres disponibles, il est attesté que
        l’agent désigné ci-dessus n’a pas bénéficié de congé durant
        {{ $period ?? 'l’année en cours' }}.
    </p>

    <p class="body-text">
        La présente attestation est délivrée pour servir et valoir ce que de droit.
    </p>


{{-- ========================================================= --}}
{{-- CONGE                                                       --}}
{{-- ========================================================= --}}

@elseif($document === 'conge')

    <p class="body-text">

        Je, soussigné(e),
        <strong>{{ $settings?->signataire }}</strong>,
        {{ $settings?->signataire_qualite }},
        autorise par la présente :

    </p>

    @include('hr.documents.partials.agent-info')

    @if($event)

        <div class="event-box">

            <p>
                <strong>Nature :</strong>
                Congé
        {{-- ========================================================= --}}
        {{-- MISSION                                                     --}}
        {{-- ========================================================= --}}

        @elseif($document === 'mission')

            <p class="body-text">
                Je, soussigné(e), {{ $settings?->signataire }},
                {{ $settings?->signataire_qualite }}, donne ordre à l’agent désigné
                ci-dessus d’effectuer la mission suivante :
            </p>

            @include('hr.documents.partials.agent-info')

            @if($event)
                <div class="event-box">
                    <p><strong>Objet :</strong> {{ $event->title ?: 'Mission' }}</p>
                    <p>
                        <strong>Période :</strong>
                        du {{ $event->date_debut?->format('d/m/Y') }}
                        @if($event->date_fin)
                            au {{ $event->date_fin->format('d/m/Y') }}
                        @endif
                    </p>
                    @if($event->destination)
                        <p><strong>Destination :</strong> {{ $event->destination }}</p>
                    @endif
                    @if($event->motif)
                        <p><strong>Motif :</strong> {{ $event->motif }}</p>
                    @endif
                </div>
            @endif

            <p class="body-text">
                La présente décision est établie pour servir et valoir ce que de droit.
            </p>


            </p>

            <p>
                <strong>Période :</strong>
                du {{ $event->date_debut?->format('d/m/Y') }}

                @if($event->date_fin)
                    au {{ $event->date_fin->format('d/m/Y') }}
                @endif
            </p>

            @if($event->motif)

                <p>
                    <strong>Motif :</strong>
                    {{ $event->motif }}
                </p>

            @endif

            @if($event->reference)

                <p>
                    <strong>Référence :</strong>
                    {{ $event->reference }}
                </p>

            @endif

        </div>

    @endif

    <p class="body-text">

        La présente fiche est établie pour constater la situation administrative
        de l’agent pendant la période indiquée ci-dessus.

    </p>


{{-- ========================================================= --}}
{{-- ABSENCE --}}
{{-- ========================================================= --}}

@elseif($document === 'absence')

    <p class="body-text">

        Je, soussigné(e),
        <strong>{{ $settings?->signataire }}</strong>,
        {{ $settings?->signataire_qualite }},
        autorise :

    </p>

    @include('hr.documents.partials.agent-info')

    @if($event)

        <div class="event-box">

            <p>
                <strong>Objet de l’autorisation :</strong>
                {{ $event->title ?: 'Autorisation d’absence' }}
            </p>

            <p>
                <strong>Période :</strong>
                du {{ $event->date_debut?->format('d/m/Y') }}

                @if($event->date_fin)
                    au {{ $event->date_fin?->format('d/m/Y') }}
                @endif
            </p>

            @if($event->motif)

                <p>
                    <strong>Motif :</strong>
                    {{ $event->motif }}
                </p>

            @endif

        </div>

    @endif

    <p class="body-text">

        L’intéressé(e) est autorisé(e) à s’absenter pendant la période
        indiquée sous réserve du respect des dispositions administratives
        applicables.

    </p>


{{-- ========================================================= --}}
{{-- FORMATION --}}
{{-- ========================================================= --}}

@elseif($document === 'formation')

    <p class="body-text">

        Je, soussigné(e),
        <strong>{{ $settings?->signataire }}</strong>,
        {{ $settings?->signataire_qualite }},
        sollicite / autorise la participation de l’agent ci-après désigné
        à une action de formation :

    </p>

    @include('hr.documents.partials.agent-info')

    @if($event)

        <div class="event-box">

            <p>
                <strong>Formation :</strong>
                {{ $event->title ?: 'Formation continue' }}
            </p>

            <p>
                <strong>Période :</strong>
                du {{ $event->date_debut?->format('d/m/Y') }}

                @if($event->date_fin)
                    au {{ $event->date_fin?->format('d/m/Y') }}
                @endif
            </p>

            @if($event->motif)

                <p>
                    <strong>Motif / Objet :</strong>
                    {{ $event->motif }}
                </p>

            @endif

            @if($event->autorite)

                <p>
                    <strong>Organisme / Autorité :</strong>
                    {{ $event->autorite }}
                </p>

            @endif

        </div>

    @endif

    <p class="body-text">

        La participation à cette formation est enregistrée dans le dossier
        administratif de l’intéressé(e).

    </p>


{{-- ========================================================= --}}
{{-- AUTRE DEMANDE --}}
{{-- ========================================================= --}}

@elseif($document === 'autre')

    <p class="body-text">

        Je, soussigné(e),
        <strong>{{ $settings?->signataire }}</strong>,
        {{ $settings?->signataire_qualite }},
        certifie que la présente demande concerne :

    </p>

    @include('hr.documents.partials.agent-info')

    @if($event)

        <div class="event-box">

            <p>
                <strong>Objet :</strong>
                {{ $event->title ?: 'Demande administrative' }}
            </p>

            @if($event->motif)

                <p>
                    <strong>Motif :</strong>
                    {{ $event->motif }}
                </p>

            @endif

            <p>
                <strong>Date :</strong>
                {{ $event->date_debut?->format('d/m/Y') }}

                @if($event->date_fin)
                    au {{ $event->date_fin?->format('d/m/Y') }}
                @endif
            </p>

        </div>

    @endif

    <p class="body-text">

        La présente pièce est établie pour servir et valoir ce que de droit.

    </p>


{{-- ========================================================= --}}
{{-- FICHE ADMINISTRATIVE --}}
{{-- ========================================================= --}}

@elseif($document === 'fiche-administrative')

    <div class="agent-info">

        <table>

            <tr>
                <td class="label">Nom et Prénoms</td>
                <td>{{ $agent->full_name }}</td>
            </tr>

            <tr>
                <td class="label">IM / Matricule</td>
                <td>{{ $agent->matricule ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Date de naissance</td>
                <td>{{ $agent->date_naissance?->format('d/m/Y') ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Corps</td>
                <td>{{ $agent->corps ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Grade</td>
                <td>{{ $agent->grade ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Indice</td>
                <td>{{ $agent->indice ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Catégorie</td>
                <td>{{ $agent->categorie ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Échelon</td>
                <td>{{ $agent->echelon ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Budget</td>
                <td>{{ $agent->budget ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Chapitre</td>
                <td>{{ $agent->chapitre ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Date de recrutement</td>
                <td>{{ $agent->date_recrutement?->format('d/m/Y') ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Prise de service</td>
                <td>{{ $agent->date_prise_service?->format('d/m/Y') ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Direction</td>
                <td>{{ $agent->direction ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Service</td>
                <td>{{ $agent->service ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Bureau</td>
                <td>{{ $agent->bureau ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Fonction</td>
                <td>{{ $agent->fonction ?: '—' }}</td>
            </tr>

            <tr>
                <td class="label">Situation administrative</td>
                <td>{{ $agent->situation_administrative ?: '—' }}</td>
            </tr>

        </table>

    </div>

@endif


{{-- ========================================================= --}}
{{-- SIGNATURE --}}
{{-- ========================================================= --}}

<div class="signature">

    {{ $settings?->ville ?: 'Antananarivo' }},
    le {{ $today->format('d/m/Y') }}

    <br><br>

    <strong>
        {{ $settings?->signataire_qualite ?: 'Le responsable habilité' }}
    </strong>

    <br><br>

    {{ $settings?->signataire ?: '' }}

    <div class="signature-line"></div>

</div>

</body>
</html>