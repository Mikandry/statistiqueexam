@extends('layouts.app')
@section('title', 'Historique · '.$material->name)
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); $r = $reconciliation; @endphp
<div class="ui-page">
    <div class="ui-head"><div>
        <div class="ui-crumb"><a href="{{ route('inventory.index') }}">&larr; Comptabilité des matières</a></div>
        <h1 class="ui-title">{{ $material->name }}</h1>
        <p class="ui-sub">Code {{ $material->code }} · {{ $material->category }} · unité : {{ $material->unit }}@if($material->supplier) · fournisseur : {{ $material->supplier->name }}@endif</p></div></div>

    <div class="ui-grid c5 keep2">
        <div class="ui-kpi slate"><div class="l">Quantité initiale</div><div class="v">{{ $n($r['initial']) }}</div></div>
        <div class="ui-kpi green"><div class="l">Entrées et retours</div><div class="v">{{ $n($r['in']) }}</div></div>
        <div class="ui-kpi amber"><div class="l">Sorties</div><div class="v">{{ $n($r['out']) }}</div></div>
        <div class="ui-kpi blue"><div class="l">Stock reconstitué</div><div class="v">{{ $n($r['expected']) }}</div></div>
        <div class="ui-kpi {{ $r['gap'] === 0 ? 'accent' : 'red' }}"><div class="l">Stock constaté</div><div class="v">{{ $n($r['actual']) }}</div><div class="n">{{ $r['gap'] === 0 ? 'cohérent avec les mouvements' : 'écart de '.$n($r['gap']).' à expliquer' }}</div></div>
    </div>

    <div class="ui-card">
        <div class="ui-card-h"><h2>Mouvements</h2><small>du plus ancien au plus récent</small></div>
        <div class="ui-table-wrap"><table class="ui-table">
            <thead><tr><th>Date</th><th>Type</th><th>Pièce</th><th>Demandeur / service</th><th class="num">Demandé</th><th class="num">Accordé</th><th class="num">Avant</th><th class="num">Après</th><th>Enregistré par</th></tr></thead>
            <tbody>
            @forelse($movements as $m)
                <tr><td>{{ $m->movement_date->format('d/m/Y') }}</td>
                    <td><span class="ui-badge {{ in_array($m->movement_type, \App\Models\InventoryStockMovement::IN_TYPES, true) ? 'green' : 'amber' }}">{{ \App\Models\InventoryStockMovement::TYPES[$m->movement_type] ?? $m->movement_type }}</span></td>
                    <td>{{ $m->voucher_number }}@if($m->supporting_document)<span class="sub">{{ $m->supporting_document }}</span>@endif</td>
                    <td>{{ $m->requester_name }}<span class="sub">{{ $m->requesting_service }}{{ $m->function ? ' · '.$m->function : '' }}</span></td>
                    <td class="num">{{ $n($m->requested_quantity) }}</td><td class="num strong">{{ $n($m->granted_quantity) }}</td>
                    <td class="num">{{ $n($m->stock_before) }}</td><td class="num">{{ $n($m->stock_after) }}</td>
                    <td>{{ $m->validator?->name }}</td></tr>
            @empty
                <tr><td colspan="9" class="ui-empty">Aucun mouvement enregistré pour ce matériel.</td></tr>
            @endforelse
            </tbody></table></div>
    </div>
</div>
@endsection
