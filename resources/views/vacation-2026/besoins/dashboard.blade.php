@extends('layouts.app')
@section('title', 'Vacation 2026 · Besoins et montants')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); $maxLevel = max(1, collect($byLevel)->max('persons') ?: 1); @endphp
<div class="ui-page">
    <div class="ui-head">
        <div>
            <h1 class="ui-title">Vacation 2026</h1>
            <p class="ui-sub">Combien de personnes sont mobilisées, pour quelle activité, et pour quel montant. Les chiffres viennent des centres, salles et candidats déjà saisis ; aucun agent n'est importé ici.</p>
        </div>
    </div>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')

    <div class="ui-grid c6 keep2">
        @foreach([['Candidats CEPE', $n($stats['candidates']['CEPE'])], ['Candidats BEPC', $n($stats['candidates']['BEPC'])], ['Centres CEPE', $n($stats['centres']['CEPE'])], ['Centres BEPC', $n($stats['centres']['BEPC'])], ['DREN · CISCO', $drenCount.' · '.$ciscoCount], ['Centres concernés', $n($centreCount)]] as [$l, $v])
            <div class="ui-kpi slate"><div class="l">{{ $l }}</div><div class="v" style="font-size:1.3rem">{{ $v }}</div></div>
        @endforeach
    </div>

    @include('vacation-2026.besoins._assign')

    <div class="ui-card">
        <div class="ui-card-h"><h2>Par niveau et par examen</h2><small>MEN central → DREN → CISCO → centres</small></div>
        <div class="ui-table-wrap"><table class="ui-table">
            <thead><tr><th>Niveau</th>@foreach($exams as $e)<th class="num">{{ $e }} pers.</th><th class="num">{{ $e }} Ar</th>@endforeach<th class="num">Total pers.</th><th class="num">Total Ar</th></tr></thead>
            <tbody>
            @foreach($levels as $k => $label)
                <tr><td class="strong">{{ $label }}</td>
                    @foreach($exams as $e)<td class="num">{{ $n($matrix[$k][$e]['persons']) }}</td><td class="num">{{ $n($matrix[$k][$e]['amount']) }}</td>@endforeach
                    <td class="num strong">{{ $n($byLevel[$k]['persons'] ?? 0) }}</td><td class="num strong">{{ $n($byLevel[$k]['amount'] ?? 0) }}</td></tr>
            @endforeach
            </tbody>
            <tfoot><tr><td>Madagascar</td>@foreach($exams as $e)<td class="num">{{ $n($byExam[$e]['persons'] ?? 0) }}</td><td class="num">{{ $n($byExam[$e]['amount'] ?? 0) }}</td>@endforeach<td class="num">{{ $n($totals['persons']) }}</td><td class="num">{{ $n($totals['amount']) }}</td></tr></tfoot>
        </table></div>
    </div>

    <div class="ui-split">
        <div class="ui-card">
            <div class="ui-card-h"><h2>Par phase</h2><small>avant, pendant, après la session</small></div>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Phase</th><th class="num">Personnes</th><th class="num">Jours-agents</th><th class="num">Montant Ar</th></tr></thead><tbody>
            @foreach($phases as $k => $label) @if(isset($byPhase[$k]))
                <tr><td>{{ $label }}</td><td class="num">{{ $n($byPhase[$k]['persons']) }}</td><td class="num">{{ $n($byPhase[$k]['person_days']) }}</td><td class="num">{{ $n($byPhase[$k]['amount']) }}</td></tr>
            @endif @endforeach</tbody></table></div>
        </div>
        <div class="ui-card">
            <div class="ui-card-h"><h2>Par catégorie de personnel</h2><small>direction, encadrement, surveillance, correction…</small></div>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Catégorie</th><th class="num">Personnes</th><th class="num">Montant Ar</th></tr></thead><tbody>
            @foreach($byCategory as $c)
                <tr><td>{{ $c['category'] }}</td><td class="num">{{ $n($c['persons']) }}</td><td class="num">{{ $n($c['amount']) }}</td></tr>
            @endforeach</tbody></table></div>
        </div>
    </div>

    <div class="ui-card">
        <div class="ui-card-h"><h2>Personnes mobilisées par niveau</h2></div>
        <div class="ui-card-b pad">
            @foreach($levels as $k => $label)
                @php $p = $byLevel[$k]['persons'] ?? 0; @endphp
                <div class="ui-kv"><span style="flex:0 0 190px">{{ $label }}</span><span class="ui-bar" style="flex:1;align-self:center"><i style="width: {{ round($p / $maxLevel * 100) }}%"></i></span><b style="flex:0 0 80px;text-align:right">{{ $n($p) }}</b></div>
            @endforeach
        </div>
    </div>

    @if($byProvince->isNotEmpty())
    <div class="ui-card">
        <div class="ui-card-h"><h2>Par province</h2><a href="{{ route('vacation2026.besoins.province') }}" class="ui-sm">Détail</a></div>
        <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Province</th><th class="num">DREN</th><th class="num">CISCO</th><th class="num">Centres</th><th class="num">Personnes</th><th class="num">Montant Ar</th></tr></thead><tbody>
        @foreach($byProvince as $p)
            <tr><td class="strong">{{ $p['nom'] }}</td><td class="num">{{ $p['drens'] }}</td><td class="num">{{ $p['ciscos'] }}</td><td class="num">{{ $p['centres'] }}</td><td class="num">{{ $n($p['persons']) }}</td><td class="num">{{ $n($p['amount']) }}</td></tr>
        @endforeach</tbody></table></div>
    </div>
    @endif

    <div class="ui-card">
        <div class="ui-card-h"><h2>DREN les plus coûteuses</h2><a href="{{ route('vacation2026.besoins.dren') }}" class="ui-sm">Toutes les DREN</a></div>
        <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>DREN</th><th class="num">Personnes</th><th class="num">Montant Ar</th></tr></thead><tbody>
        @foreach($topDrens as $d)
            <tr><td><a href="{{ route('vacation2026.besoins.dren.show', $d['id']) }}">{{ $d['nom'] }}</a></td><td class="num">{{ $n($d['persons']) }}</td><td class="num">{{ $n($d['amount']) }}</td></tr>
        @endforeach</tbody></table></div>
    </div>

    @if(!empty($checks))
    <div class="ui-card">
        <div class="ui-card-h"><h2>Contrôle d'intégrité des données</h2><small>cohérence statistiques ↔ centres ↔ salles</small></div>
        <div class="ui-card-b pad">
            @foreach($checks as $label => $value)
                <div class="ui-kv"><span>{{ is_string($label) ? $label : '' }}</span><b>{{ is_scalar($value) ? $value : json_encode($value) }}</b></div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
