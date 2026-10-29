@extends('layouts.app')

@section('title', 'Résultats des Examens')
@section('subtitle', 'Saisie officielle, publication et analyse nationale par région et CISCO')

@php
    $statusClasses = [
        'publie' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'en_cours' => 'bg-orange-100 text-orange-700 border-orange-200',
        'en_attente' => 'bg-red-100 text-red-700 border-red-200',
    ];
    $statusLabels = ['publie' => 'Publié', 'en_cours' => 'En cours', 'en_attente' => 'En attente'];
@endphp

@section('content')
<div class="space-y-6" data-results-page>
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Total CISCO', $stats['cisco_total'], 'bg-slate-900 text-white'],
            ['Résultats publiés', $stats['published'], 'bg-emerald-600 text-white'],
            ['En attente', $stats['pending'], 'bg-red-600 text-white'],
            ['En cours', $stats['in_progress'], 'bg-orange-500 text-white'],
            ['Publication', $stats['published_percent'].'%', 'bg-blue-600 text-white'],
            ['Candidats', number_format($stats['total_candidates'], 0, ',', ' '), 'bg-white text-slate-900'],
            ['Présents', number_format($stats['present_candidates'], 0, ',', ' '), 'bg-white text-slate-900'],
            ['Admis', number_format($stats['admitted_candidates'], 0, ',', ' '), 'bg-white text-slate-900'],
            ['Réussite nationale', $stats['national_success_rate'].'%', 'bg-white text-slate-900'],
            ['Abandon national', $stats['national_abandonment_rate'].'%', 'bg-white text-slate-900'],
        ] as [$label, $value, $class])
            <article class="rounded-lg border border-slate-200 p-4 shadow-sm {{ $class }}">
                <p class="text-xs font-bold uppercase tracking-wide opacity-70">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="publicationsChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="regionsChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="mapChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="bestChart" height="180"></canvas></div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" class="grid gap-3 md:grid-cols-4 xl:grid-cols-7">
            <input name="year" value="{{ $filters['year'] ?? '' }}" placeholder="Année" class="rounded-lg border-slate-300 text-sm">
            <input name="exam_name" value="{{ $filters['exam_name'] ?? '' }}" placeholder="Examen" class="rounded-lg border-slate-300 text-sm">
            <select name="dren_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">Région</option>
                @foreach($drens as $dren)<option value="{{ $dren->id }}" @selected(($filters['dren_id'] ?? '') == $dren->id)>{{ $dren->nom }}</option>@endforeach
            </select>
            <select name="cisco_id" class="rounded-lg border-slate-300 text-sm">
                <option value="">CISCO</option>
                @foreach($ciscos as $cisco)<option value="{{ $cisco->id }}" @selected(($filters['cisco_id'] ?? '') == $cisco->id)>{{ $cisco->nom }}</option>@endforeach
            </select>
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">Statut</option>
                @foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach
            </select>
            <input type="date" name="published_at" value="{{ $filters['published_at'] ?? '' }}" class="rounded-lg border-slate-300 text-sm">
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Recherche" class="rounded-lg border-slate-300 text-sm">
            <div class="flex flex-wrap gap-2 md:col-span-4 xl:col-span-7">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
                <a href="{{ route('exam-results.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold">Réinitialiser</a>
                <a href="{{ route('exam-results.export.excel', request()->query()) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Exporter Excel</a>
                <a href="{{ route('exam-results.export.pdf', request()->query()) }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white">Exporter PDF</a>
                <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold">Imprimer</button>
            </div>
        </form>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="text-base font-black text-slate-900">Ajouter un résultat</h2>
        <form method="POST" action="{{ route('exam-results.store') }}" class="mt-4 grid gap-3 md:grid-cols-4 xl:grid-cols-8">
            @csrf
            <input name="year" value="{{ old('year', date('Y')) }}" class="rounded-lg border-slate-300 text-sm" required>
            <input name="exam_name" value="{{ old('exam_name', 'BEPC') }}" class="rounded-lg border-slate-300 text-sm" required>
            <select name="cisco_id" class="rounded-lg border-slate-300 text-sm" required>
                <option value="">CISCO</option>
                @foreach($ciscos as $cisco)<option value="{{ $cisco->id }}">{{ $cisco->dren?->nom }} · {{ $cisco->nom }}</option>@endforeach
            </select>
            <input type="number" min="0" name="total_candidates" placeholder="Candidats" class="js-total rounded-lg border-slate-300 text-sm" required>
            <input type="number" min="0" name="absent_candidates" placeholder="Absents" class="js-absent rounded-lg border-slate-300 text-sm">
            <input type="number" min="0" name="admitted_candidates" placeholder="Admis" class="js-admitted rounded-lg border-slate-300 text-sm">
            <input type="number" step="0.01" min="0" max="20" name="admission_threshold" placeholder="Seuil" class="rounded-lg border-slate-300 text-sm">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Ajouter</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Région</th><th class="px-3 py-3">CISCO</th><th class="px-3 py-3">Candidats</th><th class="px-3 py-3">Absents</th><th class="px-3 py-3">Présents</th><th class="px-3 py-3">Admis</th><th class="px-3 py-3">Seuil</th><th class="px-3 py-3">% admis</th><th class="px-3 py-3">Abandon</th><th class="px-3 py-3">Publication</th><th class="px-3 py-3">Statut</th><th class="px-3 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($results as $result)
                        <tr data-calculation-row>
                            <td class="px-3 py-3 font-semibold">{{ $result->dren?->nom }}</td>
                            <td class="px-3 py-3">{{ $result->cisco?->nom }}</td>
                            <td class="px-3 py-3"><input form="update-result-{{ $result->id }}" type="number" name="total_candidates" value="{{ $result->total_candidates }}" class="js-total w-24 rounded border-slate-300 text-sm"></td>
                            <td class="px-3 py-3"><input form="update-result-{{ $result->id }}" type="number" name="absent_candidates" value="{{ $result->absent_candidates }}" class="js-absent w-24 rounded border-slate-300 text-sm"></td>
                            <td class="px-3 py-3 font-bold js-present">{{ $result->present_candidates }}</td>
                            <td class="px-3 py-3"><input form="update-result-{{ $result->id }}" type="number" name="admitted_candidates" value="{{ $result->admitted_candidates }}" class="js-admitted w-24 rounded border-slate-300 text-sm"></td>
                            <td class="px-3 py-3"><input form="update-result-{{ $result->id }}" type="number" step="0.01" name="admission_threshold" value="{{ $result->admission_threshold }}" class="w-20 rounded border-slate-300 text-sm"></td>
                            <td class="px-3 py-3 font-bold js-success">{{ $result->success_rate }}%</td>
                            <td class="px-3 py-3 js-abandon">{{ $result->abandonment_rate }}%</td>
                            <td class="px-3 py-3">{{ $result->published_at?->format('d/m/Y') ?? '—' }}<br><span class="text-xs text-slate-500">{{ $result->published_at?->format('H:i') }}</span></td>
                            <td class="px-3 py-3">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-black {{ $statusClasses[$result->status] ?? $statusClasses['en_attente'] }}">{{ $statusLabels[$result->status] ?? 'En attente' }}</span>
                                @if($result->status === 'en_attente')<p class="mt-2 text-xs font-bold text-red-600">Résultat en attente.</p>@endif
                                @if($result->success_rate < 30 && $result->status === 'publie')<p class="mt-2 rounded bg-red-50 px-2 py-1 text-xs font-bold text-red-700">Alerte: taux inférieur à 30%</p>@endif
                                @if($result->success_rate > 90 && $result->status === 'publie')<p class="mt-2 rounded bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">Excellence: taux supérieur à 90%</p>@endif
                            </td>
                            <td class="px-3 py-3">
                                <form id="update-result-{{ $result->id }}" method="POST" action="{{ route('exam-results.update', $result) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="year" value="{{ $result->year }}">
                                    <input type="hidden" name="exam_name" value="{{ $result->exam_name }}">
                                    <input type="hidden" name="cisco_id" value="{{ $result->cisco_id }}">
                                    <button class="mb-2 rounded bg-slate-900 px-3 py-1.5 text-xs font-bold text-white">Modifier</button>
                                </form>
                                <form method="POST" action="{{ route('exam-results.publish', $result) }}">@csrf<button class="mb-2 rounded bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">Publier</button></form>
                                @can('delete', $result)
                                    <form method="POST" action="{{ route('exam-results.destroy', $result) }}" onsubmit="return confirm('Supprimer ce résultat ?')">@csrf @method('DELETE')<button class="rounded bg-red-600 px-3 py-1.5 text-xs font-bold text-white">Supprimer</button></form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="px-4 py-10 text-center font-bold text-slate-500">Aucun résultat saisi. Tous les résultats sont actuellement en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $results->links() }}</div>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="font-black">Analyse automatique</h2>
            <dl class="mt-3 space-y-2 text-sm">
                <div><dt class="font-bold text-slate-500">Meilleure CISCO</dt><dd>{{ $rankings['best_cisco']?->cisco?->nom ?? '—' }} · {{ $rankings['best_cisco']?->success_rate ?? 0 }}% · {{ $rankings['best_cisco']?->admitted_candidates ?? 0 }}/{{ $rankings['best_cisco']?->present_candidates ?? 0 }}</dd></div>
                <div><dt class="font-bold text-slate-500">Meilleure Région</dt><dd>{{ $rankings['best_region']['name'] ?? '—' }} · {{ $rankings['best_region']['rate'] ?? 0 }}% · {{ $rankings['best_region']['admitted'] ?? 0 }}/{{ $rankings['best_region']['presents'] ?? 0 }}</dd></div>
                <div><dt class="font-bold text-slate-500">Plus faible CISCO</dt><dd>{{ $rankings['worst_cisco']?->cisco?->nom ?? '—' }} · {{ $rankings['worst_cisco']?->success_rate ?? 0 }}%</dd></div>
                <div><dt class="font-bold text-slate-500">Plus faible Région</dt><dd>{{ $rankings['worst_region']['name'] ?? '—' }} · {{ $rankings['worst_region']['rate'] ?? 0 }}%</dd></div>
            </dl>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="font-black">Top 10 CISCO</h2>
            <ol class="mt-3 space-y-2 text-sm">@foreach($rankings['top_ciscos'] as $item)<li class="flex justify-between"><span>{{ $item->cisco?->nom }}</span><b>{{ $item->success_rate }}%</b></li>@endforeach</ol>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="font-black">10 moins bonnes CISCO</h2>
            <ol class="mt-3 space-y-2 text-sm">@foreach($rankings['low_ciscos'] as $item)<li class="flex justify-between"><span>{{ $item->cisco?->nom }}</span><b>{{ $item->success_rate }}%</b></li>@endforeach</ol>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.querySelectorAll('[data-calculation-row], form').forEach((scope) => {
    const update = () => {
        const total = Number(scope.querySelector('.js-total')?.value || 0);
        const absent = Math.min(total, Number(scope.querySelector('.js-absent')?.value || 0));
        const admitted = Number(scope.querySelector('.js-admitted')?.value || 0);
        const present = Math.max(0, total - absent);
        const success = present > 0 ? ((Math.min(admitted, present) / present) * 100).toFixed(2) : '0.00';
        const abandon = total > 0 ? ((absent / total) * 100).toFixed(2) : '0.00';
        if (scope.querySelector('.js-present')) scope.querySelector('.js-present').textContent = present;
        if (scope.querySelector('.js-success')) scope.querySelector('.js-success').textContent = success + '%';
        if (scope.querySelector('.js-abandon')) scope.querySelector('.js-abandon').textContent = abandon + '%';
    };
    scope.querySelectorAll('.js-total,.js-absent,.js-admitted').forEach(input => input.addEventListener('input', update));
    update();
});

const chartData = @json($charts);
const makeChart = (id, type, labels, data, label, color) => new Chart(document.getElementById(id), {
    type, data: { labels, datasets: [{ label, data, backgroundColor: color, borderColor: color, tension: .35 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
makeChart('publicationsChart', 'line', Object.keys(chartData.publications), Object.values(chartData.publications), 'Publications', '#2563eb');
makeChart('regionsChart', 'bar', Object.keys(chartData.regions), Object.values(chartData.regions), 'Admis par région', '#059669');
makeChart('mapChart', 'bar', Object.keys(chartData.success_by_region), Object.values(chartData.success_by_region), 'Taux par région', '#f97316');
makeChart('bestChart', 'bar', Object.keys(chartData.best), Object.values(chartData.best), 'Meilleurs résultats', '#7c3aed');
</script>
@endpush
