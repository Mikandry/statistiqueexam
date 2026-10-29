@extends('layouts.app')
@section('title', 'Accueil')
@section('content')
<div class="ui-page">
    <div class="ui-head"><div>
        <h1 class="ui-title">Bienvenue, {{ auth()->user()->name }}</h1>
        <p class="ui-sub">Profil : {{ $roleLabel }}. Seuls les espaces auxquels votre profil donne accès sont affichés.</p></div></div>

    @if(count($indicators))
    <div class="ui-grid c4 keep2">
        @foreach($indicators as $i)
            <div class="ui-kpi accent"><div class="l">{{ $i['label'] }}</div><div class="v">{{ number_format($i['value'], 0, ',', ' ') }}</div></div>
        @endforeach
    </div>
    @endif

    <div class="ui-grid c3">
        @foreach($cards as $c)
            <div class="ui-card ui-card-pad" style="display:flex;flex-direction:column;gap:10px">
                <h2 style="margin:0;font-size:1.05rem">{{ $c['title'] }}</h2>
                <p class="ui-muted" style="margin:0;flex:1">{{ $c['text'] }}</p>
                <div><a class="ui-btn accent" href="{{ route($c['route']) }}">Ouvrir</a></div>
            </div>
        @endforeach
    </div>
</div>
@endsection
