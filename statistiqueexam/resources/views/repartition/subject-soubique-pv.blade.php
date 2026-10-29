<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PV soubiques sujets</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
</head>
<body class="bg-slate-50 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 lg:p-6">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-amber-600">PV</p>
                        <h1 class="text-2xl font-black text-slate-900">Procès-verbal des soubiques sujets</h1>
                        <p class="mt-1 text-sm text-slate-500">Répartition des matières par soubique et par centre, avec regroupement d'une matière sur une seule soubique dès que possible.</p>
                    </div>
                    <a href="{{ route('repartition.simulation.soubique', request()->query()) }}" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">Retour simulation</a>
                </div>
            </section>

            @forelse($rows as $row)
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ $row['centre_ecrit'] }}</h2>
                                <p class="text-sm text-slate-500">{{ $row['dren'] }} / {{ $row['cisco'] }} / {{ $row['centre_correction'] }} / {{ $row['type_examen'] }}</p>
                            </div>
                            <div class="text-sm text-slate-600">
                                GE max / soubique: <strong>{{ $settings->subject_soubique_ge_capacity }}</strong> |
                                PE total: <strong>{{ $row['total_pe_count'] }}</strong> |
                                PE avec +5: <strong>{{ $row['total_pe_with_margin'] }}</strong> |
                                GE centre: <strong>{{ $row['centre_ge_count'] }}</strong> |
                                Total soubiques: <strong>{{ $row['soubique_sujets'] }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Soubique</th>
                                <th class="px-4 py-3 text-left font-semibold">Tranche GE</th>
                                <th class="px-4 py-3 text-left font-semibold">Matières</th>
                                <th class="px-4 py-3 text-center font-semibold">Case à cocher</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @foreach($row['soubique_details'] as $detail)
                                <tr class="align-top">
                                    <td class="px-4 py-3 font-bold text-slate-900">Soubique {{ $detail['index'] }}</td>
                                    <td class="px-4 py-3">GE {{ $detail['ge_start'] }}-{{ $detail['ge_end'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-2">
                                            @foreach($detail['subjects'] as $subject)
                                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-700">
                                                    <span class="font-semibold">{{ $subject['order_index'] }}. {{ $subject['label'] }}</span>
                                                    <span class="ml-2 text-slate-500">PE: {{ $subject['pe_count'] }} | PE(+5): {{ $subject['pe_total_with_margin'] }} | GE total: {{ $subject['ge_count'] }} | Tranche: {{ $subject['ge_range'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded border border-slate-400"></span>
                                        <div class="mt-2 text-xs text-slate-500">{{ $detail['check_label'] }}</div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <section class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 shadow-sm">
                    Aucun centre pour ces filtres.
                </section>
            @endforelse
        </main>
    </div>
</div>
</body>
</html>
