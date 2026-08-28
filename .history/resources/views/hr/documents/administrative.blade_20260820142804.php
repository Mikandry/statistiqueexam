<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><style>
@page{margin:2cm 2cm 2cm 2.2cm}body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#111827;line-height:1.5}.institution{text-align:center;font-weight:bold;line-height:1.45}.title{text-align:center;font-size:16px;font-weight:bold;text-transform:uppercase;margin:35px 0}.meta{margin:18px 0}.meta p{margin:5px 0}.text{text-align:justify}.signature{margin-top:55px;margin-left:58%;text-align:center}.line{border-bottom:1px solid #111827;display:inline-block;min-width:180px;height:18px}.box{border:1px solid #374151;padding:12px;margin:18px 0}.label{font-weight:bold}.footer{margin-top:35px;font-size:10px;color:#4b5563}
</style></head>
<body>
@php($headerLines = collect([$settings?->ministere, $settings?->secretariat_general, $settings?->direction_generale, $settings?->direction, $settings?->service])->filter())
<div class="institution">@foreach($headerLines as $line)<div>{{ $line }}</div>@endforeach</div>
<div style="text-align:right;margin-top:18px">{{ $settings?->reference_prefix ?: 'N°' }} {{ $settings?->next_reference_number ?: 1 }}/{{ $settings?->reference_year ?: $today->year }}</div>
<h1 class="title">{{ $title }}</h1>
<div class="meta"><p><span class="label">Agent :</span> {{ $agent->full_name }}</p><p><span class="label">Matricule :</span> {{ $agent->matricule ?: '—' }}</p><p><span class="label">Corps / Grade / Indice :</span> {{ $agent->corps ?: '—' }} / {{ $agent->grade ?: '—' }} / {{ $agent->indice ?: '—' }}</p><p><span class="label">Entrée dans l’administration :</span> {{ $agent->date_recrutement?->format('d/m/Y') ?: '—' }}</p><p><span class="label">Direction / Service :</span> {{ $agent->direction ?: '—' }} / {{ $agent->service ?: '—' }}</p></div>
<p class="text"><strong>Je soussigné(e), {{ $settings?->signataire ?: 'le responsable habilité' }}, {{ $settings?->signataire_qualite ?: 'responsable du service' }}, atteste ce qui suit :</strong></p>
@if($event)
<div class="box"><p><span class="label">Objet :</span> {{ $event->title ?: \App\Models\HrEvent::TYPES[$event->type] }}</p><p><span class="label">Période :</span> du {{ $event->date_debut?->format('d/m/Y') }} @if($event->date_fin) au {{ $event->date_fin->format('d/m/Y') }} @endif</p>@if($event->motif)<p><span class="label">Motif :</span> {{ $event->motif }}</p>@endif @if($event->reference)<p><span class="label">Référence :</span> {{ $event->reference }}</p>@endif @if($event->autorite)<p><span class="label">Autorité :</span> {{ $event->autorite }}</p>@endif</div>
<p class="text"><strong>Nombre de jours concernés :</strong> {{ ($event->date_fin ?: $event->date_debut)->diffInDays($event->date_debut) + 1 }} jour(s).</p>
@endif
@if($title === 'Fiche de congé')<p class="text">La présente fiche constate l’autorisation de congé accordée à l’agent désigné ci-dessus.</p>@elseif($title === 'Attestation de non-jouissance de congé')<p class="text">Après vérification des registres disponibles, il est attesté que l’agent désigné ci-dessus n’a pas bénéficié de congé durant {{ $period ?? 'l’année en cours' }}. La présente attestation est délivrée pour servir et valoir ce que de droit.</p>@elseif($title === 'Autorisation d’absence')<p class="text">L’autorisation d’absence est accordée pour la période indiquée.</p>@elseif($title === 'Ordre de mission')<p class="text">L’agent est chargé d’effectuer la mission décrite ci-dessus pendant la période indiquée.</p>@else<p class="text">La présente autorisation permet à l’agent de participer à l’activité de formation indiquée.</p>@endif
<div class="signature">Fait à <span class="line"></span><br>Le {{ $today->format('d/m/Y') }}<br><br><strong>{{ $settings?->signataire ?: 'Le responsable' }}</strong><br><br><br><span class="line"></span></div>
<div class="footer">Document généré à partir des informations enregistrées dans le module Ressources humaines.</div>
</body></html>
