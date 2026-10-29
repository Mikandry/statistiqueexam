<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation soubique sujets</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
</head>
<body class="bg-slate-50 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 lg:p-6">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-amber-600">Simulation</p>
                        <h1 class="text-2xl font-black text-slate-900">Nombre de soubiques sujets</h1>
                        <p class="mt-1 text-sm text-slate-500">Une même matière reste sur une seule soubique dès que son volume le permet. Elle n'est découpée que si elle dépasse à elle seule la capacité autorisée.</p>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                        <div class="rounded-xl bg-amber-50 px-4 py-3">GE max / soubique: <strong>{{ $settings->subject_soubique_ge_capacity }}</strong></div>
                        <div class="rounded-xl bg-amber-50 px-4 py-3">Matières max / soubique: <strong>{{ $settings->subject_soubique_subject_capacity }}</strong></div>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <a href="{{ route('repartition.simulation.soubique.pv', request()->query()) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800">Ouvrir le PV</a>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" class="grid gap-3 md:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Année</label>
                        <select name="annee" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="">Toutes</option>
                            @foreach($annees as $annee)
                                <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select name="type_examen" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                            <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                            <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">DREN</label>
                        <select name="dren" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="">Toutes</option>
                            @foreach($drens as $dren)
                                <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">CISCO</label>
                        <select name="cisco" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="">Tous</option>
                            @foreach($ciscos as $cisco)
                                <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>{{ $cisco }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filtrer</button>
                    </div>
                </form>
            </section>

            <section class="grid gap-3 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Centres</div><div class="mt-2 text-2xl font-black">{{ number_format($globalStats['total_centres'], 0, ',', ' ') }}</div></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Candidats</div><div class="mt-2 text-2xl font-black">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</div></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Salles</div><div class="mt-2 text-2xl font-black">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</div></div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm"><div class="text-xs font-bold uppercase tracking-wide text-amber-700">Soubiques sujets</div><div class="mt-2 text-2xl font-black text-amber-800">{{ number_format($globalStats['total_soubiques_sujets'], 0, ',', ' ') }}</div></div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">DREN</th>
                            <th class="px-4 py-3 text-left font-semibold">CISCO</th>
                            <th class="px-4 py-3 text-left font-semibold">Centre</th>
                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                            <th class="px-4 py-3 text-right font-semibold">Candidats</th>
                            <th class="px-4 py-3 text-right font-semibold">Salles</th>
                            <th class="px-4 py-3 text-right font-semibold">PE matières</th>
                            <th class="px-4 py-3 text-right font-semibold">PE avec +5</th>
                            <th class="px-4 py-3 text-right font-semibold">GE centre</th>
                            <th class="px-4 py-3 text-right font-semibold">Soubique sujets</th>
                            <th class="px-4 py-3 text-left font-semibold">Composition des soubiques</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $row)
                            <tr class="align-top">
                                <td class="px-4 py-3">{{ $row['dren'] }}</td>
                                <td class="px-4 py-3">{{ $row['cisco'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row['centre_ecrit'] }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['centre_correction'] }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $row['type_examen'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['total_salles'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['total_pe_count'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['total_pe_with_margin'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['centre_ge_count'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-black text-amber-700">{{ number_format($row['soubique_sujets'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <div class="space-y-2">
                                        @foreach($row['soubique_details'] as $detail)
                                            <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                                <div class="font-semibold">Soubique {{ $detail['index'] }} | GE {{ $detail['ge_start'] }}-{{ $detail['ge_end'] }}</div>
                                                <div class="mt-1 flex flex-wrap gap-1">
                                                    @foreach($detail['subjects'] as $subject)
                                                        <span class="rounded-full bg-white px-2 py-1 font-semibold">{{ $subject['label'] }}@if(!empty($subject['is_split'])) - GE {{ $subject['ge_range'] }}@endif</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center text-slate-500">Aucune donnée pour ces filtres.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
