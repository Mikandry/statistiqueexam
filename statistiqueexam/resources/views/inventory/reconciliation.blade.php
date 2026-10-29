@extends('layouts.app')
@section('title', 'Rapprochement du stock')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="ui-page">
    <div class="ui-head"><div>
        <div class="ui-crumb"><a href="{{ route('inventory.index') }}">&larr; Comptabilité des matières</a></div>
        <h1 class="ui-title">Rapprochement du stock</h1>
        <p class="ui-sub">Stock reconstitué = quantité initiale + entrées + retours − sorties. Un écart signale un stock modifié hors mouvement ou un mouvement manquant : il n'est jamais corrigé automatiquement.</p></div></div>

    @if($gaps > 0)<div class="ui-note warn">{{ $gaps }} matériel(s) présentent un écart à expliquer.</div>
    @else<div class="ui-note ok">Tous les stocks sont cohérents avec leurs mouvements.</div>@endif

    <div class="ui-card"><div class="ui-table-wrap"><table class="ui-table">
        <thead><tr><th>Code</th><th>Matériel</th><th class="num">Initial</th><th class="num">Entrées + retours</th><th class="num">Sorties</th><th class="num">Reconstitué</th><th class="num">Constaté</th><th class="num">Écart</th><th></th></tr></thead>
        <tbody>
        @foreach($rows as $row)
            @php $r = $row['rec']; @endphp
            <tr><td class="strong">{{ $row['material']->code }}</td><td>{{ $row['material']->name }}</td>
                <td class="num">{{ $n($r['initial']) }}</td><td class="num">{{ $n($r['in']) }}</td><td class="num">{{ $n($r['out']) }}</td>
                <td class="num">{{ $n($r['expected']) }}</td><td class="num">{{ $n($r['actual']) }}</td>
                <td class="num">@if($r['gap'] === 0)<span class="ui-badge green">0</span>@else<span class="ui-badge red">{{ $r['gap'] > 0 ? '+' : '' }}{{ $n($r['gap']) }}</span>@endif</td>
                <td><a href="{{ route('inventory.materials.history', $row['material']) }}">Historique</a></td></tr>
        @endforeach
        </tbody></table></div></div>
</div>
@endsection
