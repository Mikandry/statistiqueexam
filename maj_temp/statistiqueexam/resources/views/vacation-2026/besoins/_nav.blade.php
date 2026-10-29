@php
    $tabs = [
        'vacation2026.besoins.dashboard' => 'Vue d\'ensemble',
        'vacation2026.besoins.province' => 'Provinces',
        'vacation2026.besoins.dren' => 'DREN',
        'vacation2026.besoins.cisco' => 'CISCO',
        'vacation2026.besoins.centres' => 'Centres',
        'vacation2026.besoins.activites' => 'Activités',
        'vacation2026.besoins.structure' => 'Transcription',
        'vacation2026.besoins.baremes' => 'Barèmes',
        'vacation2026.besoins.parametres' => 'Paramètres',
    ];
@endphp
<div class="ui-row">
    <nav class="ui-tabs" aria-label="Vacation 2026">
        @foreach($tabs as $route => $label)
            <a href="{{ route($route) }}" class="ui-tab {{ request()->routeIs($route.'*') ? 'is-active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <span class="ui-spacer"></span>
    <a href="{{ route('vacation2026.besoins.export', request()->query()) }}" class="ui-btn accent">Exporter en Excel</a>
</div>
@if(session('success'))<div class="ui-note ok">{{ session('success') }}</div>@endif
@if(isset($errors) && $errors->any())<div class="ui-note err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@if(!empty($warnings))
    <div class="ui-note warn"><b>À vérifier</b><ul>@foreach($warnings as $w)<li>{{ $w }}</li>@endforeach</ul></div>
@endif
