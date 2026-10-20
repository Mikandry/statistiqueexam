@php
    $tabs = [
        'vacation2026.besoins.dashboard' => 'Tableau de bord',
        'vacation2026.besoins.activites' => 'Personnes par activité',
        'vacation2026.besoins.dren' => 'Par DREN',
        'vacation2026.besoins.cisco' => 'Par CISCO',
        'vacation2026.besoins.centres' => 'Par centre',
        'vacation2026.besoins.baremes' => 'Barèmes',
        'vacation2026.besoins.parametres' => 'Paramètres',
    ];
@endphp
<div class="mb-4 flex flex-wrap items-center gap-2">
    @foreach($tabs as $route => $label)
        <a href="{{ route($route) }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ request()->routeIs($route.'*') ? 'bg-slate-900 text-white' : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
    <a href="{{ route('vacation2026.besoins.export', request()->query()) }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Exporter Excel</a>
</div>
@if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if(!empty($warnings))
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
        <ul class="list-disc pl-5">@foreach($warnings as $w)<li>{{ $w }}</li>@endforeach</ul>
    </div>
@endif
