<div style="text-align:center;font-weight:bold;line-height:1.5">
    @foreach(collect([$settings?->ministere, $settings?->secretariat_general, $settings?->direction_generale, $settings?->direction, $settings?->service])->filter() as $line)
        <p style="margin:0">{{ $line }}</p>
    @endforeach
</div>
<div class="reference">
    <p class="reference-left">
        {{-- N° {{ $settings?->next_reference_number ?: 1 }}/{{ $settings?->reference_year ?: $today->year }}
        {{ $settings?->reference_prefix ?: '' }} --}}
         <
    </p>
</div>
<h1 style="text-align:center">{{ mb_strtoupper($title) }}</h1>
<p><b>Je soussigné(e), {{ $settings?->signataire_qualite ?: 'responsable du service' }}, atteste que :</b></p>
@php
    $visibleFields = $settings?->fieldsFor($document ?? '') ?? ['nom', 'im', 'corps_grade'];
    $agentParts = [];

    foreach ($visibleFields as $field) {
        switch ($field) {
            case 'nom':
                $agentParts[] = "<b>{$agent->full_name}</b>";
                break;
            case 'im':
                $agentParts[] = 'matricule <b>' . ($agent->matricule ?: 'non renseigné') . '</b>';
                break;
            case 'corps_grade':
                $agentParts[] = 'corps ' . ($agent->corps ?: 'non renseigné') . ', grade ' . ($agent->grade ?: 'non renseigné');
                break;
            case 'indice':
                $agentParts[] = 'indice ' . ($agent->indice ?: 'non renseigné');
                break;
            case 'date_recrutement':
                $agentParts[] = 'entré(e) dans l’administration le ' . ($agent->date_recrutement?->format('d/m/Y') ?: 'non renseignée');
                break;
            case 'budget_chapitre':
                $agentParts[] = 'budget ' . ($agent->budget ?: 'non renseigné') . ', chapitre ' . ($agent->chapitre ?: 'non renseigné');
                break;
            case 'service_direction':
                $agentParts[] = trim(($agent->direction ?: '') . ' ' . ($agent->service ?: ''));
                break;
            case 'fonction':
                $agentParts[] = 'fonction ' . ($agent->fonction ?: 'non renseignée');
                break;
            case 'date_prise_service':
                $agentParts[] = 'prise de service le ' . ($agent->date_prise_service?->format('d/m/Y') ?: 'non renseignée');
                break;
        }
    }
@endphp
<p>{!! implode(', ', $agentParts) !!}.</p>
@if($event)<p><b>Objet :</b> {{ $event->title ?: \App\Models\HrEvent::TYPES[$event->type] }}<br /><b>Période :</b> du {{ $event->date_debut?->format('d/m/Y') }} @if($event->date_fin) au {{ $event->date_fin->format('d/m/Y') }} @endif<br /><b>Nombre de jours :</b> {{ ($event->date_fin ?: $event->date_debut)->diffInDays($event->date_debut) + 1 }}</p>@endif
@if($title === 'ATTESTATION DE NON-JOUISSANCE DE CONGE')
    <p>Après vérification des registres disponibles, l’intéressé(e) n’a pas bénéficié de congé pendant {{ $period ?? 'l’année en cours' }}. La présente attestation est délivrée pour servir et valoir ce que de droit.</p>
@elseif($title === 'FICHE DE PRISE DE SERVICE')
    <p>L’agent a effectivement pris service au sein de l’administration le {{ $agent->date_prise_service?->format('d/m/Y') ?: 'date non renseignée' }}.</p>
    <p>Fonction : {{ $agent->fonction ?: 'non renseignée' }}. Direction : {{ $agent->direction ?: 'non renseignée' }}. Service : {{ $agent->service ?: 'non renseigné' }}.</p>
@else
    <p>La présente attestation est délivrée pour servir et valoir ce que de droit.</p>
@endif
<p style="text-align:right">Fait à ____________________, le {{ $today->format('d/m/Y') }}<br /><br /><b>{{ $settings?->signataire_qualite ?: 'Le responsable habilité' }}</b><br />{{ $settings?->signataire ?: '' }}</p>