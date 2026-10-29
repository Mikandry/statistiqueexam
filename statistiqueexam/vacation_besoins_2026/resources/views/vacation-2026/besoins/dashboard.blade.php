@extends('layouts.app')
@section('title', 'Besoins & montants - Vacation 2026')
@section('content')
@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="space-y-4">
    <div><h1 class="text-3xl font-bold text-slate-900">Vacation 2026 — Besoins et montants</h1>
        <p class="text-sm text-slate-600">Personnes mobilisées et indemnités selon le décret, calculées depuis les centres, salles et candidats saisis. « Poste » = une personne mobilisée sur une activité.</p></div>
    @include('vacation-2026.besoins._nav')
    @include('vacation-2026.besoins._filters')
    @include('vacation-2026.besoins._kpis')

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        @foreach([['Candidats CEPE', $stats['candidates']['CEPE']], ['Candidats BEPC', $stats['candidates']['BEPC']], ['Centres CEPE', $stats['centres']['CEPE']], ['Centres BEPC', $stats['centres']['BEPC']], ['DREN / CISCO', $drenCount.' / '.$ciscoCount], ['Centres concernés', $centreCount]] as [$l, $v])
            <div class="rounded-xl border border-slate-200 bg-white p-3"><p class="text-xs text-slate-500">{{ $l }}</p><p class="text-lg font-semibold">{{ is_numeric($v) ? $n($v) : $v }}</p></div>
        @endforeach
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 font-semibold">Personnes mobilisées et montant par niveau et par examen</h2>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr>
                <th class="p-2">Niveau</th><th class="p-2 text-right">CEPE personnes</th><th class="p-2 text-right">CEPE Ar</th><th class="p-2 text-right">BEPC personnes</th><th class="p-2 text-right">BEPC Ar</th><th class="p-2 text-right">Total personnes</th><th class="p-2 text-right">Total Ar</th></tr></thead>
            <tbody>
            @foreach($levels as $k => $label)
                @php $c = $matrix[$k]['CEPE']; $b = $matrix[$k]['BEPC']; @endphp
                <tr class="border-t"><td class="p-2 font-medium">{{ $label }}</td>
                    <td class="p-2 text-right">{{ $n($c['persons']) }}</td><td class="p-2 text-right">{{ $n($c['amount']) }}</td>
                    <td class="p-2 text-right">{{ $n($b['persons']) }}</td><td class="p-2 text-right">{{ $n($b['amount']) }}</td>
                    <td class="p-2 text-right font-semibold">{{ $n($c['persons'] + $b['persons']) }}</td><td class="p-2 text-right font-semibold">{{ $n($c['amount'] + $b['amount']) }}</td></tr>
            @endforeach
            <tr class="border-t-2 bg-slate-50 font-bold"><td class="p-2">Madagascar</td>
                <td class="p-2 text-right">{{ $n($byExam['CEPE']['persons'] ?? 0) }}</td><td class="p-2 text-right">{{ $n($byExam['CEPE']['amount'] ?? 0) }}</td>
                <td class="p-2 text-right">{{ $n($byExam['BEPC']['persons'] ?? 0) }}</td><td class="p-2 text-right">{{ $n($byExam['BEPC']['amount'] ?? 0) }}</td>
                <td class="p-2 text-right">{{ $n($totals['persons']) }}</td><td class="p-2 text-right">{{ $n($totals['amount']) }}</td></tr>
            </tbody></table></div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><h2 class="mb-2 font-semibold">Personnes mobilisées par niveau</h2><canvas id="chLevel" height="220"></canvas></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><h2 class="mb-2 font-semibold">Montant par DREN (10 premières)</h2><canvas id="chDren" height="220"></canvas></div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="mb-3 font-semibold">Par phase</h2>
        <table class="w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="p-2">Phase</th><th class="p-2 text-right">Personnes</th><th class="p-2 text-right">Jours-agents</th><th class="p-2 text-right">Montant Ar</th></tr></thead><tbody>
        @foreach($phases as $k => $label) @if(isset($byPhase[$k]))
            <tr class="border-t"><td class="p-2">{{ $label }}</td><td class="p-2 text-right">{{ $n($byPhase[$k]['persons']) }}</td><td class="p-2 text-right">{{ $n($byPhase[$k]['person_days']) }}</td><td class="p-2 text-right">{{ $n($byPhase[$k]['amount']) }}</td></tr>
        @endif @endforeach</tbody></table>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="mb-3 flex items-center justify-between"><h2 class="font-semibold">DREN les plus coûteuses</h2><a class="text-sm text-blue-700 underline" href="{{ route('vacation2026.besoins.dren') }}">Toutes les DREN</a></div>
        <table class="w-full text-sm"><thead class="bg-slate-100 text-left text-xs uppercase text-slate-600"><tr><th class="p-2">DREN</th><th class="p-2 text-right">Personnes</th><th class="p-2 text-right">Montant Ar</th></tr></thead><tbody>
        @foreach($topDrens as $d)
            <tr class="border-t"><td class="p-2"><a class="text-blue-700 underline" href="{{ route('vacation2026.besoins.dren.show', $d['id']) }}">{{ $d['nom'] }}</a></td><td class="p-2 text-right">{{ $n($d['persons']) }}</td><td class="p-2 text-right">{{ $n($d['amount']) }}</td></tr>
        @endforeach</tbody></table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    new Chart(document.getElementById('chLevel'), {type: 'bar', data: {labels: @json(array_values($levels)), datasets: [{label: 'Personnes', data: @json(collect($levels)->keys()->map(fn ($k) => $byLevel[$k]['persons'] ?? 0)->all()), backgroundColor: '#0f766e'}]}, options: {plugins: {legend: {display: false}}}});
    new Chart(document.getElementById('chDren'), {type: 'bar', data: {labels: @json($topDrens->pluck('nom')->all()), datasets: [{label: 'Ar', data: @json($topDrens->pluck('amount')->all()), backgroundColor: '#1d4ed8'}]}, options: {indexAxis: 'y', plugins: {legend: {display: false}}}});
})();
</script>
@endsection
