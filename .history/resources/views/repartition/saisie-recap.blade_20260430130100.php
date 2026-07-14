<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOE · Récapitulatif de saisie</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')

    <style>
        body {
            font-family: var(--app-font-sans);
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(16, 185, 129, 0.07), transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    </style>
</head>

<body class="h-full antialiased text-slate-900">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 overflow-auto p-4 lg:p-8 custom-scrollbar">
            <div class="mx-auto max-w-[1500px] space-y-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <nav class="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <span>Répartition</span>
                            <i class="fas fa-chevron-right text-[8px]"></i>
                            <span class="text-sky-600">Récap saisie</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">Complétude de la saisie</h1>
                        <p class="mt-1 font-medium italic text-slate-500">Statistiques par DREN, basées sur les centres existants dans la décision de centre.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50" href="{{ route('bepc.repartition.create') }}">
                            <i class="fas fa-plus-circle text-emerald-600"></i> Saisie
                        </a>
                        <a class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800" href="{{ route('repartition.dashboard', request()->query()) }}">
                            <i class="fas fa-chart-pie text-slate-400"></i> Dashboard
                        </a>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur">
                    <form method="GET" action="{{ route('repartition.saisie.recap') }}" class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Année</label>
                            <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold outline-none focus:bg-white focus:ring-4 focus:ring-sky-500/10">
                                <option value="" {{ $filters['annee'] === '' ? 'selected' : '' }}>Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Examen</label>
                            <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold outline-none focus:bg-white focus:ring-4 focus:ring-sky-500/10">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</label>
                            <select name="dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold outline-none focus:bg-white focus:ring-4 focus:ring-sky-500/10">
                                <option value="" {{ $filters['dren'] === '' ? 'selected' : '' }}>Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-sky-600 px-5 text-xs font-black uppercase tracking-widest text-gr shadow-lg shadow-sky-100 hover:bg-sky-700" type="submit">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                    </form>
                </section>

                <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach([
                        ['label' => 'Centres décision', 'value' => $global['total_centres'], 'class' => 'text-slate-700'],
                        ['label' => 'Centres saisis', 'value' => $global['centres_saisis'], 'class' => 'text-emerald-700'],
                        ['label' => 'Centres restants', 'value' => $global['centres_restants'], 'class' => 'text-rose-700'],
                        ['label' => 'DREN complètes', 'value' => $global['drens_completes'].' / '.$global['total_drens'], 'class' => 'text-sky-700'],
                        ['label' => 'CISCO non démarrées', 'value' => $global['ciscos_non_demarre'], 'class' => 'text-amber-700'],
                    ] as $card)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $card['label'] }}</p>
                            <div class="mt-3 text-3xl font-black {{ $card['class'] }}">{{ is_numeric($card['value']) ? number_format((int) $card['value'], 0, ',', ' ') : $card['value'] }}</div>
                        </div>
                    @endforeach
                </section>

                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Récapitulatif par DREN</h2>
                            <p class="text-sm font-medium text-slate-500">Progression globale: {{ number_format($global['progress'], 1, ',', ' ') }} %</p>
                        </div>
                        <div class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-500">
                            Aucun nom de centre affiché
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[980px] border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    <th class="px-6 py-4">DREN</th>
                                    <th class="px-4 py-4 text-right">Centres décision</th>
                                    <th class="px-4 py-4 text-right">Saisis</th>
                                    <th class="px-4 py-4 text-right">Restants</th>
                                    <th class="px-4 py-4 text-right">CISCO</th>
                                    <th class="px-4 py-4 text-right">CISCO complètes</th>
                                    <th class="px-4 py-4 text-right">CISCO non démarrées</th>
                                    <th class="px-6 py-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($rows as $row)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-6 py-4 font-black text-slate-800">{{ $row['dren'] }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-slate-700">{{ number_format($row['total_centres'], 0, ',', ' ') }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-emerald-700">{{ number_format($row['centres_saisis'], 0, ',', ' ') }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-rose-700">{{ number_format($row['centres_restants'], 0, ',', ' ') }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-slate-700">{{ number_format($row['total_ciscos'], 0, ',', ' ') }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-sky-700">{{ number_format($row['ciscos_completes'], 0, ',', ' ') }}</td>
                                        <td class="px-4 py-4 text-right font-bold text-amber-700">{{ number_format($row['ciscos_non_demarre'], 0, ',', ' ') }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $row['complete'] ? 'bg-emerald-500' : 'bg-sky-500' }}" style="width: {{ min(100, $row['progress']) }}%"></div>
                                                </div>
                                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $row['complete'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                    {{ $row['complete'] ? 'Complet' : number_format($row['progress'], 1, ',', ' ').' %' }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center font-bold text-slate-500">
                                            Aucun centre trouvé pour ces filtres.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
