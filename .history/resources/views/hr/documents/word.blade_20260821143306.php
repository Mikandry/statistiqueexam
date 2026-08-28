<div style="text-align:center;font-weight:bold;line-height:1.5">
    @foreach(collect([$settings?->ministere, $settings?->secretariat_general, $settings?->direction_generale, $settings?->direction, $settings?->service])->filter() as $line)
        <p style="margin:0">{{ $line }}</p>
    @endforeach
</div>
<p style="text-align:left">{{ $settings?->next_reference_number ?: 1 }}/{{ $settings?->reference_year ?: $today->year }}{{ $settings?->reference_prefix ?: 'N°' }} </p>
<h1 style="text-align:center">{{ mb_strtoupper($title) }}</h1>
<p><b>Je soussigné(e), {{ $settings?->signataire ?: 'le responsable habilité' }}, {{ $settings?->signataire_qualite ?: 'responsable du service' }}, atteste que :</b></p>
<p><b>{{ $agent->full_name }}</b>, matricule <b>{{ $agent->matricule ?: 'non renseigné' }}</b>, corps {{ $agent->corps ?: 'non renseigné' }}, grade {{ $agent->grade ?: 'non renseigné' }}, indice {{ $agent->indice ?: 'non renseigné' }}, entré(e) dans l’administration le {{ $agent->date_recrutement?->format('d/m/Y') ?: 'non renseignée' }}.</p>
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