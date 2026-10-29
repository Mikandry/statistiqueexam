@extends('layouts.app')
@section('title', 'Coordonnées de la carte')
@section('content')
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Coordonnées et paramètres de la carte</h1>
        <p class="ui-sub">Latitude (ex. -18,8792) et longitude (ex. 47,5079), en degrés décimaux. Une case vide efface la position. Les valeurs hors de Madagascar sont refusées.</p></div>
        <div class="ui-actions"><a class="ui-btn" href="{{ route('carte.index') }}">Voir la carte</a></div></div>
    @if(session('success'))<div class="ui-note ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="ui-note err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="ui-card"><div class="ui-card-h">Import / export CSV</div><div class="ui-card-b pad">
        <p class="ui-sm ui-muted">Téléchargez le modèle (tous les centres et points de largage), remplissez latitude et longitude dans Excel, puis importez-le.</p>
        <form method="POST" action="{{ route('carte.import') }}" enctype="multipart/form-data" class="ui-row">
            @csrf <a class="ui-btn" href="{{ route('carte.modele') }}">Télécharger le modèle CSV</a>
            <input type="file" name="fichier" accept=".csv,.txt" required class="ui-input"><button class="ui-btn accent">Importer</button>
        </form></div></div>

    <form method="POST" action="{{ route('carte.parametres') }}" class="ui-card">@csrf @method('PUT')
        <div class="ui-card-h">Paramètres de la carte</div><div class="ui-card-b pad"><div class="ui-grid c2">
            <label class="ui-check"><input type="checkbox" name="carte_tuiles_en_ligne" value="1" @checked($params['carte_tuiles_en_ligne']==='1')> Utiliser les données en ligne quand la connexion existe (fond OpenStreetMap et régions)</label>
            <label class="ui-field"><span>URL GeoJSON des régions (en ligne)</span><input name="carte_geojson_url" class="ui-input" value="{{ old('carte_geojson_url',$params['carte_geojson_url']) }}"></label>
            <label class="ui-field"><span>Point de départ du dispatching</span><input name="carte_depart_nom" class="ui-input" value="{{ old('carte_depart_nom',$params['carte_depart_nom']) }}"></label>
            <div class="ui-grid c2">
                <label class="ui-field"><span>Latitude départ</span><input name="carte_depart_lat" class="ui-input" value="{{ old('carte_depart_lat',$params['carte_depart_lat']) }}"></label>
                <label class="ui-field"><span>Longitude départ</span><input name="carte_depart_lng" class="ui-input" value="{{ old('carte_depart_lng',$params['carte_depart_lng']) }}"></label>
            </div></div>
            <p class="ui-sm ui-muted">Hors connexion, la carte utilise les fichiers locaux (public/geo : GeoJSON et shapefile).</p>
            <button class="ui-btn accent">Enregistrer les paramètres</button></div>
    </form>

    <form method="POST" action="{{ route('carte.coordonnees.save') }}" class="ui-page">@csrf @method('PUT')
        @php
            $coordRow = function ($kind, $m, $extra = '') {
                return '<tr><td>'.e($m->nom).$extra.'</td>'
                    .'<td><input class="ui-input" name="c['.$kind.']['.$m->id.'][lat]" value="'.e($m->latitude).'" inputmode="decimal"></td>'
                    .'<td><input class="ui-input" name="c['.$kind.']['.$m->id.'][lng]" value="'.e($m->longitude).'" inputmode="decimal"></td></tr>';
            };
        @endphp
        <div class="ui-card"><div class="ui-card-h">Centres de correction ({{ $corrections->count() }})</div><div class="ui-table-wrap"><table class="ui-table">
            <thead><tr><th>Centre</th><th>Latitude</th><th>Longitude</th></tr></thead><tbody>
            @foreach($corrections as $m){!! $coordRow('correction',$m,' <span class="ui-muted ui-sm">'.e($m->cisco?->nom).'</span>') !!}@endforeach
        </tbody></table></div></div>
        <div class="ui-card"><div class="ui-card-h">Centres d'écrit ({{ $ecrits->count() }})</div>
            <p class="ui-sm ui-muted" style="padding:0 16px">Laissez vide un centre d'écrit situé sur le même site que son centre de correction : la position de ce dernier est utilisée.</p>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Centre</th><th>Latitude</th><th>Longitude</th></tr></thead><tbody>
            @foreach($ecrits as $m){!! $coordRow('ecrit',$m,' <span class="ui-muted ui-sm">'.e($m->type_examen).($m->same_site_as_correction?' · même site':'').'</span>') !!}@endforeach
        </tbody></table></div></div>
        <div class="ui-card"><div class="ui-card-h">Centres de transcription ({{ $transcriptions->count() }})</div><div class="ui-table-wrap"><table class="ui-table">
            <thead><tr><th>Centre</th><th>Latitude</th><th>Longitude</th></tr></thead><tbody>
            @foreach($transcriptions as $m){!! $coordRow('transcription',$m) !!}@endforeach
        </tbody></table></div></div>
        <div class="ui-card"><div class="ui-card-h">Points de largage du dispatching</div>
            <p class="ui-sm ui-muted" style="padding:0 16px">Sans position saisie, le point est placé au centre de gravité des centres qu'il dessert. L'ordre du trajet est celui enregistré dans Dispatching.</p>
            <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Axe</th><th>Point</th><th>Latitude</th><th>Longitude</th></tr></thead><tbody>
            @php $i = 0; @endphp
            @foreach($dispatching['axes'] as $a)@foreach($a['points'] as $p)
                <tr><td>{{ $a['axe'] }}<input type="hidden" name="p[{{ $i }}][axe]" value="{{ $a['axe'] }}"></td>
                    <td>{{ $p['point'] }}<input type="hidden" name="p[{{ $i }}][point]" value="{{ $p['point'] }}"></td>
                    <td><input class="ui-input" name="p[{{ $i }}][lat]" value="{{ $p['source']==='saisi' ? $p['lat'] : '' }}" placeholder="{{ $p['source']==='centres' ? $p['lat'] : '' }}"></td>
                    <td><input class="ui-input" name="p[{{ $i }}][lng]" value="{{ $p['source']==='saisi' ? $p['lng'] : '' }}" placeholder="{{ $p['source']==='centres' ? $p['lng'] : '' }}"></td></tr>
                @php $i++; @endphp
            @endforeach @endforeach
            @if($i===0)<tr><td colspan="4" class="ui-muted">Aucun point de largage enregistré.</td></tr>@endif
        </tbody></table></div></div>
        <div class="ui-row"><button class="ui-btn accent">Enregistrer les coordonnées</button></div>
    </form>
</div>
@endsection
