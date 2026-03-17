<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Service de l'Organisation des Examens</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* Style pour les selects pour plus de modernité */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>

<body class="bg-gradient-to-br from-slate-50 via-slate-100 to-blue-50/30 text-slate-900 min-h-screen">

<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-6 md:flex-row md:items-start">
        <div class="md:shrink-0 md:sticky md:top-8 z-10">
            @include('partials.sidebar')
        </div>

        <main class="min-w-0 flex-1 space-y-6">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Décision de Centre</h1>
                    <p class="text-slate-500 text-sm mt-1">Gestion et consultation des centres de correction et d'écrit par zone.</p>
                </div>

                <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-indigo-200 hover:text-indigo-600 active:scale-95" href="{{ route('repartition.dashboard') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Tableau de bord
                </a>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 p-6 transition-all hover:shadow-md">
                <form method="GET" action="{{ route('decision.centre') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">Type d'Examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                                <option value="">Tous les examens</option>
                                <option value="BEPC" {{ ($typeExamen ?? '') == 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ ($typeExamen ?? '') == 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">DREN</label>
                            <select id="dren" name="dren" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                                <option value="">Toutes les directions</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren->id }}" {{ ($drenId ?? '') == $dren->id ? 'selected' : '' }}>
                                        {{ $dren->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 ml-1">CISCO</label>
                            <select id="cisco" name="cisco" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                                <option value="">Tous les districts</option>
                                @foreach($ciscos as $cisco)
                                    <option value="{{ $cisco->id }}" {{ ($ciscoId ?? '') == $cisco->id ? 'selected' : '' }}>
                                        {{ $cisco->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                        <a href="{{ route('decision.centre') }}" class="px-6 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800 transition-colors">
                            Réinitialiser
                        </a>
                        <button class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all active:translate-y-0">
                            Appliquer les filtres
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-800">Centres importés</h2>
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-tight">
                        {{ count($tableData) }} Lignes
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-slate-50/80">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-widest text-slate-500">DREN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-widest text-slate-500">CISCO</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-widest text-slate-500">Centre Correction</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-widest text-slate-500">Centre Écrit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tableData as $row)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-slate-800 group-hover:text-indigo-700">{{ $row['dren'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-medium">{{ $row['cisco'] }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full bg-amber-400 mr-2"></div>
                                            <span class="text-slate-700">{{ $row['correction'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-700">
                                        {{ $row['ecrit'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-900 text-white border-t-2 border-indigo-500">
                                <td class="px-6 py-4 font-bold text-indigo-300">TOTAL (DREN : {{ $totalDren }})</td>
                                <td class="px-6 py-4 text-left font-bold">{{ $totalCisco }}</td>
                                <td class="px-6 py-4 text-left font-bold text-amber-400">{{ $totalCorrection }}</td>
                                <td class="px-6 py-4 text-left font-bold text-emerald-400">{{ $totalEcrit }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function(){
                // Auto submit on change
                const selects = ["type_examen", "dren"];
                selects.forEach(id => {
                    const el = document.getElementById(id);
                    if(el) {
                        el.addEventListener("change", function(){
                            if(id === "dren" && document.getElementById("cisco")) {
                                document.getElementById("cisco").value = "";
                            }
                            this.form.submit();
                        });
                    }
                });
            });
            </script>

        </main>
    </div>
</div>

</body>
</html>