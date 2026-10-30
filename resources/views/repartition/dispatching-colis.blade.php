@extends('layouts.app')
@section('title', 'Colis du dispatching')
@section('content')
@php
    $isAdminLike = true;
    $q = array_filter($query, fn ($v) => $v !== null && $v !== '');
@endphp
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Saisie des colis par centre</h1>
        <p class="ui-sub">Saisissez le nombre de colis remis à chaque centre. Le total par point de largage (arrêtage) s'imprime en bas de chaque point dans la fiche de largage et l'accusé de réception.</p></div>
        <div class="ui-actions"><a class="ui-btn" href="{{ route('repartition.export.dispatching.preview', $q) }}">Aperçu dispatching</a></div></div>
    @if(session('success'))<div class="ui-note ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="ui-note err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="GET" class="ui-card"><div class="ui-card-b pad"><div class="ui-grid c4">
        <label class="ui-field"><span>Année</span><select name="annee" class="ui-input" onchange="this.form.submit()"><option value="">Toutes</option>@foreach($annees as $a)<option value="{{ $a }}" @selected($filters['annee']===(string)$a)>{{ $a }}</option>@endforeach</select></label>
        <label class="ui-field"><span>Examen</span><select name="type_examen" class="ui-input" onchange="this.form.submit()">@foreach(['ALL'=>'Tous','BEPC'=>'BEPC','CEPE'=>'CEPE'] as $k=>$l)<option value="{{ $k }}" @selected($filters['type_examen']===$k)>{{ $l }}</option>@endforeach</select></label>
        <label class="ui-field"><span>DREN</span><select name="dren" class="ui-input" onchange="this.form.submit()"><option value="">Toutes</option>@foreach($drens as $d)<option value="{{ $d }}" @selected($filters['dren']===$d)>{{ $d }}</option>@endforeach</select></label>
    </div></div></form>

    <form method="POST" action="{{ route('repartition.dispatching.signataires') }}" class="ui-card">@csrf
        @foreach($q as $k=>$v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <div class="ui-card-h">Noms imprimés en bas des documents</div><div class="ui-card-b pad"><div class="ui-grid c3">
            <label class="ui-field"><span>Le remettant</span><input name="sign[remettant]" class="ui-input" value="{{ $signatories['remettant'] }}"></label>
            <label class="ui-field"><span>Forces de l'ordre (fiche de largage)</span><input name="sign[force_ordre]" class="ui-input" value="{{ $signatories['force_ordre'] }}"></label>
            <label class="ui-field"><span>Réceptionnaire(s) (fiche de largage)</span><input name="sign[receptionnaires]" class="ui-input" value="{{ $signatories['receptionnaires'] }}"></label>
            <label class="ui-field"><span>Sécurité — conservation des colis (accusé)</span><input name="sign[securite]" class="ui-input" value="{{ $signatories['securite'] }}"></label>
            <label class="ui-field"><span>Agents du MEN réceptionnaires (accusé)</span><input name="sign[agents_men]" class="ui-input" value="{{ $signatories['agents_men'] }}"></label>
        </div><p class="ui-sm ui-muted">Laissés vides, ils s'impriment en pointillés à compléter à la main.</p><button class="ui-btn">Enregistrer les noms</button></div>
    </form>

    @php
        $docLink = fn ($doc, $fmt, $axe, $point = null) => route('repartition.dispatching.document.'.$fmt, ['doc' => $doc] + $q + ['axe' => $axe] + ($point !== null ? ['point' => $point] : []));
    @endphp

    <form method="POST" action="{{ route('repartition.dispatching.colis.save') }}" class="ui-page">@csrf @method('PUT')
        @foreach($q as $k=>$v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        @forelse($axes as $axe)
        <div class="ui-card">
            <div class="ui-card-h"><div class="ui-row">
                <span><b>Axe : {{ $axe['axe'] }}</b> — {{ number_format($axe['candidats'],0,',',' ') }} candidats · {{ number_format($axe['salles'],0,',',' ') }} salles · <b>{{ $axe['colis'] === null ? 'colis non saisis' : number_format($axe['colis'],0,',',' ').' colis' }}</b></span>
                <span class="ui-spacer"></span>
                <span class="ui-actions"><span class="ui-sm ui-muted">Fiche de largage de l'axe :</span>
                    <a class="ui-btn" href="{{ $docLink('fiche','pdf',$axe['axe']) }}">PDF</a><a class="ui-btn" href="{{ $docLink('fiche','word',$axe['axe']) }}">Word</a>
                    <span class="ui-sm ui-muted">Accusés de l'axe :</span>
                    <a class="ui-btn" href="{{ $docLink('accuse','pdf',$axe['axe']) }}">PDF</a><a class="ui-btn" href="{{ $docLink('accuse','word',$axe['axe']) }}">Word</a></span>
            </div></div>
            @foreach($axe['points'] as $p)
            <div class="ui-table-wrap"><table class="ui-table">
                <thead><tr><th colspan="5"><div class="ui-row"><span>Point de largage : <b>{{ $p['point'] }}</b></span><span class="ui-spacer"></span>
                    <span class="ui-actions">
                        <a class="ui-btn" href="{{ $docLink('fiche','pdf',$axe['axe'],$p['point']) }}">Fiche PDF</a><a class="ui-btn" href="{{ $docLink('fiche','word',$axe['axe'],$p['point']) }}">Fiche Word</a>
                        <a class="ui-btn" href="{{ $docLink('accuse','pdf',$axe['axe'],$p['point']) }}">Accusé PDF</a><a class="ui-btn" href="{{ $docLink('accuse','word',$axe['axe'],$p['point']) }}">Accusé Word</a></span></div></th></tr>
                    <tr><th style="width:5%">N°</th><th>Centre</th><th class="ui-r">Candidats</th><th class="ui-r">Salles</th><th style="width:14%">Colis</th></tr></thead>
                <tbody>
                @foreach($p['lines'] as $l)
                    <tr><td>{{ $l['n'] }}</td><td>{{ $l['nom'] }} <span class="ui-muted ui-sm">{{ $l['cisco'] }}</span></td>
                        <td class="ui-r">{{ number_format($l['candidats'],0,',',' ') }}</td><td class="ui-r">{{ number_format($l['salles'],0,',',' ') }}</td>
                        <td><input type="number" min="0" max="100000" class="ui-input" name="colis[{{ $l['centre_ecrit_id'] }}]" value="{{ $l['colis'] }}">
                            <input type="hidden" name="annee_row[{{ $l['centre_ecrit_id'] }}]" value="{{ $l['annee'] }}"></td></tr>
                @endforeach
                    <tr><td colspan="2"><b>Arrêtage du point</b></td><td class="ui-r"><b>{{ number_format($p['candidats'],0,',',' ') }}</b></td><td class="ui-r"><b>{{ number_format($p['salles'],0,',',' ') }}</b></td>
                        <td><b>{{ $p['colis'] === null ? '—' : number_format($p['colis'],0,',',' ') }}</b> <span class="ui-muted ui-sm">({{ $p['colis_saisis'] }}/{{ count($p['lines']) }} centres saisis)</span></td></tr>
                </tbody></table></div>
            @endforeach
        </div>
        @empty
            <div class="ui-note warn">Aucun centre avec axe et point de largage pour ces filtres. Renseignez d'abord les axes et points de largage.</div>
        @endforelse
        @if(count($axes))<div class="ui-row"><button class="ui-btn accent">Enregistrer les colis</button>
            <span class="ui-sm ui-muted">Les totaux (arrêtage) sont recalculés après l'enregistrement. Case vide = pas de saisie.</span></div>@endif
    </form>
</div>
@endsection
