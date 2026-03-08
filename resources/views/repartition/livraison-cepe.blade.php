<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livraison CEPE par CISCO | SOE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .table-container { border-radius: 1rem; overflow: hidden; border: 1px solid #e2e8f0; }
        .sticky-header th { position: sticky; top: 0; z-index: 20; background: #f8fafc; }
        .input-card:focus-within { border-color: #6366f1; ring: 4px; ring-color: rgba(99, 102, 241, 0.05); }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="h-full antialiased text-slate-900">

<div class="mx-auto max-w-[1800px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-6">
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 md:p-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="space-y-1">
                        <nav class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">
                            <span>Logistique</span>
                            <i class="fas fa-chevron-right text-[8px]"></i>
                            <span class="text-indigo-600">Besoins CEPE</span>
                        </nav>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Livraison par CISCO</h1>
                        <p class="text-sm font-medium text-slate-500">Calcul automatique des fournitures selon les effectifs et coefficients techniques.</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('repartition.livraison.cepe.excel', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700 hover:-translate-y-0.5">
                            <i class="fas fa-file-excel"></i>
                            Exporter XLSX
                        </a>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-5">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Candidats</p>
                        <p class="text-xl font-extrabold text-slate-900">{{ number_format($global['total_candidats'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Total Salles</p>
                        <p class="text-xl font-extrabold text-slate-900">{{ number_format($global['total_salles'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">GE Total</p>
                        <p class="text-xl font-extrabold text-indigo-600">{{ number_format($global['total_ge'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">PE Total</p>
                        <p class="text-xl font-extrabold text-slate-900">{{ number_format($global['total_pe'], 0, ',', ' ') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-900 bg-slate-900 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">RAM Papier</p>
                        <p class="text-xl font-extrabold text-white">{{ number_format($global['total_ram'], 0, ',', ' ') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <form method="GET">
                    <div class="p-6 md:p-8 space-y-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4 items-end">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">Année Académique</label>
                                <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="">Toutes</option>
                                    @foreach($annees as $annee)
                                        <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">DREN / Région</label>
                                <select name="dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold focus:bg-white outline-none">
                                    <option value="">Toutes</option>
                                    @foreach($drens as $dren)
                                        <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">GE / Soubique</label>
                                <input name="ge_par_soubique" type="number" min="1" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold outline-none focus:ring-4 focus:ring-indigo-500/10" value="{{ $params['ge_par_soubique'] }}">
                            </div>
                            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                                Recalculer les besoins
                            </button>
                        </div>

                        <details class="group rounded-2xl border border-slate-100 bg-slate-50/50" open>
                            <summary class="flex cursor-pointer items-center justify-between px-6 py-4 text-sm font-extrabold text-slate-600 transition-colors hover:text-indigo-600">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-sliders-h"></i>
                                    <span>PARAMÈTRES AVANCÉS DES FOURNITURES</span>
                                </div>
                                <i class="fas fa-chevron-down text-[10px] transition-transform group-open:rotate-180"></i>
                            </summary>
                            <div class="grid grid-cols-1 gap-6 border-t border-slate-100 p-6 md:grid-cols-2 xl:grid-cols-5">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Enveloppes / Cire</label>
                                    <input name="enveloppes_par_barre_cire" type="number" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $params['enveloppes_par_barre_cire'] }}">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Pages / RAM</label>
                                    <input name="pages_par_ram" type="number" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $params['pages_par_ram'] }}">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Marqueur fixe / CISCO</label>
                                    <input name="marqueur_fixe_par_cisco" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $params['marqueur_fixe_par_cisco'] }}">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Marqueur / Soubique</label>
                                    <input name="marqueur_par_soubique" type="number" step="0.1" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $params['marqueur_par_soubique'] }}">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Pages Français</label>
                                    <input name="pages_francais" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $pagesBySubject['francais'] }}">
                                </div>
                                @foreach(['connaissances_usuelles' => 'CU', 'geographie' => 'Géo', 'malagasy' => 'Malg', 'operation' => 'Opér', 'probleme' => 'Probl', 'tffmom' => 'TFF'] as $key => $label)
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Pages {{ $label }}</label>
                                    <input name="pages_{{ $key }}" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold outline-none" value="{{ $pagesBySubject[$key] }}">
                                </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                </form>

                <div class="px-8 pb-8">
                    <div class="rounded-2xl border-l-4 border-indigo-500 bg-indigo-50/50 p-4 text-xs font-medium text-indigo-700 leading-relaxed">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Méthode de calcul :</strong> PE = Salles • GE Problème = Règle 3 PE/GE • GE Autres = ({{ (int) ($otherSubjectsCount ?? 0) }} matières) × GE(6 PE/GE) • Soubique = ⌈GE Total / Ratio⌉ • Cire = ⌈(PE + GE + Soubique) / Ratio Cire⌉.
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 sticky-header">
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Candidats</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400 bg-indigo-50/30">GE Total</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">PE</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Soubique</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Ficelle</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Cire</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">RAM Papier</th>
                                <th class="px-4 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Marqueur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rows as $row)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900">{{ $row['dren'] }}</td>
                                    <td class="px-6 py-4 font-medium text-slate-600">{{ $row['cisco'] }}</td>
                                    <td class="px-4 py-4 text-right font-black text-slate-900">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-right font-black text-indigo-600 bg-indigo-50/20">{{ number_format($row['ge'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-right font-bold text-slate-600">{{ number_format($row['pe'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 border border-amber-100">
                                            {{ number_format($row['soubique'], 0, ',', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right font-bold text-slate-600">{{ number_format($row['ficelle'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-right font-bold text-slate-600">{{ number_format($row['cire'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700 border border-emerald-100">
                                            {{ number_format($row['papier_ram'], 0, ',', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right font-bold text-slate-600">{{ number_format($row['marqueur'], 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-box-open text-4xl text-slate-200 mb-4"></i>
                                            <p class="text-slate-400 font-bold tracking-wide">Aucune donnée CEPE disponible pour ces filtres.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($rows) > 0)
                            <tfoot class="bg-slate-900 text-white">
                                <tr class="font-black">
                                    <td class="px-6 py-5 uppercase tracking-widest text-[10px] text-slate-400" colspan="2">TOTAL GÉNÉRAL</td>
                                    <td class="px-4 py-5 text-right">{{ number_format($global['total_candidats'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right text-indigo-400">{{ number_format($global['total_ge'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right">{{ number_format($global['total_pe'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right text-amber-400">{{ number_format($global['total_soubique'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right">{{ number_format($global['total_ficelle'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right">{{ number_format($global['total_cire'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right text-emerald-400">{{ number_format($global['total_ram'], 0, ',', ' ') }}</td>
                                    <td class="px-4 py-5 text-right">{{ number_format($global['total_marqueur'], 0, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Logic for auto-submitting on filter change can be added here
</script>

</body>
</html>
