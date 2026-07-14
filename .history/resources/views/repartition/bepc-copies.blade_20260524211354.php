<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistique BEPC</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.75rem; }
        .compact-input { height: 38px; }
        /* Smooth transitions for modern feel */
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>

    @unless($pdfMode ?? false)
        @include('partials.head-assets')
    @endunless
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">

<div class="mx-auto max-w-[1700px] p-4 lg:p-6">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        
        @unless($pdfMode ?? false)
            @include('partials.sidebar')
        @endunless

        <main class="min-w-0 flex-1 space-y-4">
            
            <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Logistique BEPC</p>
                    </div>
                    <h1 class="text-xl font-black text-slate-900">Répartition des feuilles des copies</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:gap-3">
                    @php
                        $miniStats = [
                            ['label' => 'CISCO', 'val' => $globalStats['total_ciscos'], 'bg' => 'bg-slate-100', 'txt' => 'text-slate-700'],
                            ['label' => 'Lignes', 'val' => $globalStats['total_dispatch_rows'], 'bg' => 'bg-slate-100', 'txt' => 'text-slate-700'],
                            ['label' => 'Ctr sans langue', 'val' => $globalStats['total_centres_sans_langue'], 'bg' => 'bg-orange-50', 'txt' => 'text-orange-700'],
                            ['label' => 'Candidats', 'val' => $globalStats['total_candidats'], 'bg' => 'bg-slate-100', 'txt' => 'text-slate-700'],
                            ['label' => 'F. Double', 'val' => $globalStats['total_feuilles_double'], 'bg' => 'bg-cyan-50', 'txt' => 'text-cyan-700'],
                            ['label' => 'F. D Arr.', 'val' => $globalStats['total_feuilles_double_arrondies'], 'bg' => 'bg-sky-50', 'txt' => 'text-sky-700'],
                            ['label' => 'F. Simple', 'val' => $globalStats['total_feuilles_simple'], 'bg' => 'bg-cyan-50', 'txt' => 'text-cyan-700'],
                            ['label' => 'F. S Arr.', 'val' => $globalStats['total_feuilles_simple_arrondies'], 'bg' => 'bg-emerald-50', 'txt' => 'text-emerald-700'],
                            ['label' => 'Surplus', 'val' => $globalStats['total_missing_langue_surplus_sheets'], 'bg' => 'bg-fuchsia-50', 'txt' => 'text-fuchsia-700'],
                            ['label' => 'Saisis', 'val' => $globalStats['codes_postaux_renseignes'], 'bg' => 'bg-emerald-50', 'txt' => 'text-emerald-700'],
                            ['label' => 'Marge', 'val' => number_format($marginPercent, 1) . '%', 'bg' => 'bg-amber-50', 'txt' => 'text-amber-700'],
                        ];
                    @endphp

                    @foreach($miniStats as $stat)
                        <div class="{{ $stat['bg'] }} px-3 py-2 rounded-xl border border-black/5 flex flex-col min-w-[90px]">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-tighter">{{ $stat['label'] }}</span>
                            <span class="text-sm font-black {{ $stat['txt'] }}">{{ is_numeric($stat['val']) ? number_format($stat['val'], 0, ',', ' ') : $stat['val'] }}</span>
                        </div>
                    @endforeach
                </div>

                @unless($pdfMode ?? false)
                    <div class="flex gap-2 border-l border-slate-100 pl-4 hidden lg:flex">
                        <a title="Visualisation PDF" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 transition-all" 
                            href="{{ route('repartition.logistique.bepc-copies.pdf', request()->query()) }}">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-bold whitespace-nowrap">Visualisation</span>
                            </a>
                        <a title="Export en Word" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100 transition-colors" 
                            href="{{ route('repartition.logistique.bepc-copies.word', request()->query()) }}">
                                
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>

                                <span class="text-xs font-bold whitespace-nowrap">Export en Word</span>
                        </a>
                    </div>
                @endunless
            </section>

            @unless($pdfMode ?? false)
                <section class="bg-white px-5 py-3 rounded-2xl border border-slate-200 shadow-sm">
                    @php
                        $selectedIsolatedCentres = collect($availableIsolatableCentres ?? [])
                            ->filter(fn ($centre) => in_array($centre['id'], $filters['isolated_centres'] ?? [], true))
                            ->values();
                    @endphp
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-[300px]">
                            <div class="flex-1">
                                <select name="annee" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold focus:ring-cyan-500/20">
                                    @foreach($annees as $annee)
                                        <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-[2]">
                                <select name="dren" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold focus:ring-cyan-500/20">
                                    <option value="">Toutes les DREN</option>
                                    @foreach($drens as $dren)
                                        <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-[2]">
                                <select name="cisco" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold focus:ring-cyan-500/20">
                                    <option value="">Toutes les CISCO</option>
                                    @foreach(($ciscos ?? []) as $cisco)
                                        <option value="{{ $cisco['id'] }}" {{ (string) $filters['cisco'] === (string) $cisco['id'] ? 'selected' : '' }}>{{ $cisco['nom'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <input type="number" step="0.01" name="margin_percent" value="{{ old('margin_percent', $marginPercent) }}" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold text-center" placeholder="Marge %">
                            </div>
                            <div class="w-40">
                                <select name="rounding_mode" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold focus:ring-cyan-500/20">
                                    <option value="up" {{ $filters['rounding_mode'] === 'up' ? 'selected' : '' }}>Arrondi plus</option>
                                    <option value="down" {{ $filters['rounding_mode'] === 'down' ? 'selected' : '' }}>Arrondi moins</option>
                                </select>
                            </div>
                            <div class="w-36">
                                <input type="number" min="1000" step="1000" name="missing_langue_surplus_step" value="{{ $filters['missing_langue_surplus_step'] }}" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold text-center" placeholder="Surplus">
                            </div>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                                <input type="checkbox" name="add_missing_langue_surplus" value="1" {{ $filters['add_missing_langue_surplus'] ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500/20">
                                Surplus si langue absente
                            </label>
                            <div class="w-36">
                                <input type="number" min="1000" step="1000" name="merge_small_soubique_capacity" value="{{ $filters['merge_small_soubique_capacity'] }}" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold text-center" placeholder="Cap. fusion">
                            </div>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                                <input type="checkbox" name="merge_small_soubique" value="1" {{ $filters['merge_small_soubique'] ? 'checked' : '' }} class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500/20">
                                Fusion petit lot
                            </label>
                        </div>

                        <div class="min-w-[260px] flex-1">
                            @foreach($filters['isolated_centres'] ?? [] as $selectedCentreId)
                                <input type="hidden" name="isolated_centres[]" value="{{ $selectedCentreId }}">
                            @endforeach

                            <select name="isolated_centres[]" class="compact-input w-full rounded-lg border-slate-200 bg-slate-50 text-xs font-bold focus:ring-cyan-500/20">
                                <option value="">Ajouter un centre isole</option>
                                @foreach(($availableIsolatableCentres ?? []) as $centre)
                                    <option value="{{ $centre['id'] }}">
                                        {{ $centre['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] font-medium text-slate-500">Centres isoles: ajoute un centre via la liste, puis filtre. Il sortira de la CISCO et sera calcule sur une ligne separee.</p>
                            @if($selectedIsolatedCentres->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach($selectedIsolatedCentres as $centre)
                                        @php
                                            $remainingCentres = array_values(array_filter(
                                                $filters['isolated_centres'] ?? [],
                                                fn ($id) => (int) $id !== (int) $centre['id']
                                            ));
                                            $removeUrl = route('repartition.logistique.bepc-copies', array_merge(request()->query(), ['isolated_centres' => $remainingCentres]));
                                        @endphp
                                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-[11px] font-bold text-rose-700 border border-rose-100">
                                            {{ $centre['label'] }}
                                            <a href="{{ $removeUrl }}" class="text-rose-500 hover:text-rose-700">Retirer</a>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" class="compact-input px-5 rounded-lg bg-slate-900 text-xs font-bold text-white hover:bg-slate-800 transition-colors">
                                Filtrer
                            </button>
                            <a href="{{ route('repartition.logistique.bepc-copies.excel', request()->query()) }}" class="compact-input px-4 flex items-center rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                XLSX
                            </a>
                        </div>
                    </form>
                </section>
            @endunless

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px] border-collapse">
                        <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-5 py-3 font-bold text-slate-500 uppercase tracking-tighter">DREN</th>
                                    <th class="px-5 py-3 font-bold text-slate-500 uppercase tracking-tighter">CISCO</th>
                                    <th class="px-5 py-3 font-bold text-slate-500 uppercase tracking-tighter">Type</th>
                                    <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter">Ctr sans langue</th>
                                    <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter">% sans langue</th>
                                    <th class="px-5 py-3 font-bold text-slate-500 uppercase tracking-tighter w-[250px]">Code Postal</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter">Candidats</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-cyan-600">F. Double</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-sky-600">F. Double Arr.</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-emerald-600">F. Simple</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-green-600">F. Simple Arr.</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-fuchsia-600">Surplus</th>
                                <th class="px-5 py-3 text-right font-bold text-slate-500 uppercase tracking-tighter text-amber-600">Soubique</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rows as $row)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $row['dren'] }}</td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $row['cisco'] }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-bold {{ $row['row_type'] === 'centre_isole' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $row['row_type'] === 'centre_isole' ? 'Centre isole' : 'CISCO' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-orange-700">{{ number_format($row['centres_sans_langue'], 0, ',', ' ') }} / {{ number_format($row['total_centres'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-orange-700">{{ number_format($row['centres_sans_langue_percent'], 1, ',', ' ') }} %</td>
                                    <td class="px-5 py-3.5">
                                        @if($pdfMode ?? false)
                                            <span class="font-mono">{{ $row['code_postal'] ?: '-' }}</span>
                                        @else
                                            <form method="POST" action="{{ route('repartition.logistique.bepc-copies.postal-code') }}" class="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                                @csrf
                                                <input type="hidden" name="annee" value="{{ $filters['annee'] }}">
                                                <input type="hidden" name="cisco_id" value="{{ $row['cisco_id'] }}">
                                                <input name="code_postal" value="{{ $row['code_postal'] }}" class="w-24 rounded border-slate-200 bg-white px-2 py-1 text-xs font-mono focus:border-cyan-500" placeholder="Code">
                                                <button type="submit" class="p-1 text-cyan-600 hover:text-cyan-700">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold">{{ number_format($row['total_candidats'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-cyan-700">{{ number_format($row['feuilles_double'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-sky-700">{{ number_format($row['feuilles_double_arrondies'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-emerald-700">{{ number_format($row['feuilles_simple'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-green-700">{{ number_format($row['feuilles_simple_arrondies'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-fuchsia-700">
                                        {{ number_format($row['missing_langue_surplus_sheets'], 0, ',', ' ') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-amber-700">
                                        <div>
                                            <span class="bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 font-bold">{{ number_format($row['soubique_total'], 0, ',', ' ') }}</span>
                                        </div>
                                        <div class="mt-1 text-[11px] font-semibold text-amber-600">
                                            @if(($row['soubique_mixte'] ?? 0) > 0)
                                                {{ number_format($row['soubique_mixte'], 0, ',', ' ') }} SBK Mixte
                                            @else
                                                {{ number_format($row['soubique_feuilles_double'], 0, ',', ' ') }} SBK Double,
                                                {{ number_format($row['soubique_feuilles_simple'], 0, ',', ' ') }} Simple
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="13" class="px-5 py-12 text-center text-slate-400 font-medium">Aucun résultat trouvé.</td></tr>
                            @endforelse
                        </tbody>
                        @if($rows->count() > 0)
                            <tfoot class="bg-slate-900 text-white">
                                <tr class="divide-x divide-white/5">
                                    <td class="px-5 py-4 font-black uppercase text-[10px] tracking-widest text-slate-400" colspan="2">Total National</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-slate-400">-</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-orange-300">{{ number_format($globalStats['total_centres_sans_langue'], 0, ',', ' ') }} / {{ number_format($globalStats['total_centres'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-orange-300">{{ $globalStats['total_centres'] > 0 ? number_format(($globalStats['total_centres_sans_langue'] / $globalStats['total_centres']) * 100, 1, ',', ' ') : '0,0' }} %</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-slate-400">-</td>
                                    <td class="px-5 py-4 text-right font-black text-sm">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-cyan-300">{{ number_format($globalStats['total_feuilles_double'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-sky-300">{{ number_format($globalStats['total_feuilles_double_arrondies'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-emerald-300">{{ number_format($globalStats['total_feuilles_simple'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-green-300">{{ number_format($globalStats['total_feuilles_simple_arrondies'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-fuchsia-300">{{ number_format($globalStats['total_missing_langue_surplus_sheets'], 0, ',', ' ') }}</td>
                                    <td class="px-5 py-4 text-right font-black text-sm text-amber-300">
                                        {{ number_format($globalStats['total_soubiques'], 0, ',', ' ') }}
                                        @if(($globalStats['total_soubiques_mixte'] ?? 0) > 0)
                                            <div class="mt-1 text-[11px] font-semibold text-amber-200">{{ number_format($globalStats['total_soubiques_mixte'], 0, ',', ' ') }} Mixte</div>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
