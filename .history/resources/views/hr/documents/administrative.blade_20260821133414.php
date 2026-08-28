<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">

    <style>

        @page {
            margin: 1.8cm 2cm 2cm 2.2cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            line-height: 1.55;
        }

        .institution {
            text-align: center;
            font-weight: bold;
            line-height: 1.35;
            font-size: 10.5px;
        }

        .stars {
            text-align: center;
            font-weight: bold;
            margin: 2px 0;
        }

        .reference {
            text-align: right;
            margin-top: 20px;
            font-size: 11px;
        }

        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .intro {
            text-align: justify;
            margin-bottom: 20px;
        }

        .agent-info {
            margin: 15px 0;
            line-height: 1.8;
        }

        .agent-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .agent-info td {
            vertical-align: top;
            padding: 2px 5px;
        }

        .label {
            font-weight: bold;
        }

        .body-text {
            text-align: justify;
            margin-top: 18px;
        }

        .event-box {
            margin: 18px 0;
            padding: 12px;
            border: 1px solid #555;
        }

        .signature {
            margin-top: 60px;
            margin-left: 55%;
            text-align: center;
        }

        .signature-line {
            margin-top: 45px;
            border-bottom: 1px solid #111;
            width: 220px;
            display: inline-block;
        }

        .footer {
            margin-top: 35px;
            font-size: 9px;
            text-align: center;
        }

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