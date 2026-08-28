<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><style>
@page { margin: 1.4cm 1.1cm; } body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; } .institution { text-align:center; line-height:1.35; font-weight:bold; } h1 { text-align:center; font-size:15px; margin:16px 0; } .centre { page-break-before: auto; page-break-inside: avoid; margin-top:12px; } .centre-title { font-weight:bold; font-size:11px; margin:8px 0 5px; } table { width:100%; border-collapse:collapse; page-break-inside:auto; } tr { page-break-inside:avoid; page-break-after:auto; } thead { display:table-header-group; } th,td { border:1px solid #374151; padding:4px; vertical-align:middle; } th { background:#e5e7eb; text-align:center; } td:nth-child(1),td:nth-child(2) { text-align:center; width:7%; } .closing { page-break-inside:avoid; margin-top:22px; } .signature { margin-top:30px; text-align:center; float:right; width:42%; } .clear { clear:both; }
</style></head>
<body>
<div class="institution">@foreach($batch->institution_lines ?? [] as $line)<div>{{ $line }}</div>@endforeach</div>
<h1>LISTE DES CANDIDATS DÉFINITIVEMENT ADMIS AU {{ $batch->exam_type }}<br>{{ $batch->year }}</h1>
@foreach($groups as $centre => $candidates)
<section class="centre"><div class="centre-title">CENTRE : {{ $centre }}</div><table><thead><tr><th>N° d'ordre général</th><th>N° d'ordre du centre</th><th>Nom et prénoms</th><th>Date de naissance</th><th>Localité de service</th><th>DREN</th><th>Centre</th></tr></thead><tbody>@foreach($candidates as $candidate)<tr><td>{{ $candidate->general_order }}</td><td>{{ $candidate->centre_order }}</td><td>{{ $candidate->name }}</td><td>{{ $candidate->birth_date?->format('d/m/Y') }}</td><td>{{ $candidate->service_location }}</td><td>{{ $candidate->dren }}</td><td>{{ $candidate->centre }}</td></tr>@endforeach</tbody></table></section>
@endforeach
<div class="closing"><p>La présente liste est arrêtée à <strong>{{ $totalInWords }}</strong> ({{ $batch->total_candidates }}) candidats définitivement admis.</p><div class="signature">{{ $batch->signer_place }}@if($batch->signature_date), le {{ $batch->signature_date->format('d/m/Y') }}@endif<br><br>{{ $batch->signer_function }}<br><br>{{ $batch->signer_name }}<br><br>Signature</div><div class="clear"></div></div>
</body></html>
