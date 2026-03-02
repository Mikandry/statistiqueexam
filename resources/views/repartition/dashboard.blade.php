<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service de l'Organisation des Examens</title>
     @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-white to-slate-50/50 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Tableau de bord</h1>
                            <p class="text-sm font-medium text-slate-500">Récapitulatif hiérarchique et statistiques globales des examens</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.export.excel', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Export Excel</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.export.dispatching.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Prévisualisation Dispatching</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.export.dispatching', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Export Dispatching</a>
                            @if(auth()->user()?->isAdmin())
                                <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.vacations', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Vacations</a>
                            @endif
                            <a class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-blue-700 hover:shadow-md focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{ route('repartition.livre.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Prévisualiser le livre</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('repartition.livre.pdf', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Version PDF</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="space-y-1.5">
                            <label for="annee" class="block text-sm font-semibold text-slate-700">Année scolaire</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="type_examen" class="block text-sm font-semibold text-slate-700">Type d'examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="dren" class="block text-sm font-semibold text-slate-700">DREN</label>
                            <select id="dren" name="dren" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm transition-colors duration-200 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-slate-800 hover:shadow-lg focus:ring-2 focus:ring-slate-900 focus:ring-offset-2" type="submit">Actualiser</button>
                        </div>
                    </form>

                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="group rounded-xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50 p-5 shadow-md transition-all duration-200 hover:shadow-lg">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total candidats</div>
                            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="group rounded-xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50 p-5 shadow-md transition-all duration-200 hover:shadow-lg">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total salles</div>
                            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="group rounded-xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50 p-5 shadow-md transition-all duration-200 hover:shadow-lg">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total centres d'écrit</div>
                            <div class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($globalStats['total_centres_ecrit'], 0, ',', ' ') }}</div>
                            <div class="mt-2 text-xs font-medium text-slate-500">DREN: {{ $globalStats['total_drens'] }} | CISCO: {{ $globalStats['total_ciscos'] }}</div>
                        </div>
                        <div class="group rounded-xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50 p-5 shadow-md transition-all duration-200 hover:shadow-lg">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Centres saisis / non saisis</div>
                            <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($centresSaisieStats['saisis'], 0, ',', ' ') }} / {{ number_format($centresSaisieStats['non_saisis'], 0, ',', ' ') }}</div>
                            <div class="mt-2 text-xs font-medium text-slate-500">Total centres attendus: {{ number_format($centresSaisieStats['total'], 0, ',', ' ') }}</div>
                        </div>
                    </div>

                    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-500">Récapitulatif par langue / option</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100/80">
                                    <th class="border border-slate-200 px-4 py-3 text-left font-semibold">Langue / Option</th>
                                    <th class="border border-slate-200 px-4 py-3 text-left font-semibold">Total candidats</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($totalsByLangue as $label => $value)
                                    <tr class="transition-colors duration-150 hover:bg-slate-50/80">
                                        <td class="border border-slate-200 px-4 py-3 break-words">{{ $label }}</td>
                                        <td class="border border-slate-200 px-4 py-3 font-medium">{{ number_format($value, 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="border border-slate-200 px-4 py-3" colspan="2">Aucune donnée.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-500">Diagramme comparatif langues / options</h2>
                        <canvas id="langueOptionChart" height="220"></canvas>
                    </div>

                    <div class="mb-6 overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Diagramme DREN (candidats)</h2>
                        @php $maxValue = max((int) $chartData->max('value'), 1); @endphp
                        <div class="space-y-3">
                            @forelse($chartData as $item)
                                <div class="grid grid-cols-1 items-center gap-2 md:grid-cols-[220px_1fr_80px]">
                                    <div class="break-words text-sm font-medium text-slate-700">{{ $item['label'] }}</div>
                                    <div class="h-4 overflow-hidden rounded-full bg-blue-100">
                                        <span class="block h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300" style="width: {{ round(($item['value'] / $maxValue) * 100, 2) }}%;"></span>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-700">{{ number_format($item['value'], 0, ',', ' ') }}</div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">Aucune donnée.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Récap hiérarchique DREN / CISCO / Centres</h2>
                        @forelse($recapByDren as $dren)
                            <details class="group mb-3 rounded-lg border border-slate-200 bg-gradient-to-r from-slate-50 to-white p-4 transition-all duration-200 hover:border-slate-300" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between font-semibold text-slate-800">
                                    <span class="break-words">{{ $dren['nom'] }}</span>
                                    <span class="ml-2 text-sm font-medium text-slate-600">candidats: {{ number_format($dren['total_candidats'], 0, ',', ' ') }} | salles: {{ number_format($dren['total_salles'], 0, ',', ' ') }}</span>
                                </summary>
                                <div class="mt-3 space-y-2 pl-4">
                                    @foreach($dren['ciscos'] as $cisco)
                                        <details class="rounded-lg border border-slate-200 bg-white p-3 transition-all duration-200 hover:border-slate-300">
                                            <summary class="flex cursor-pointer list-none items-center justify-between font-medium text-slate-700">
                                                <span class="break-words">CISCO: {{ $cisco['nom'] }}</span>
                                                <span class="ml-2 text-sm text-slate-600">candidats: {{ number_format($cisco['total_candidats'], 0, ',', ' ') }} | salles: {{ number_format($cisco['total_salles'], 0, ',', ' ') }}</span>
                                            </summary>
                                            <div class="mt-2 space-y-2 pl-3">
                                                @foreach($cisco['centres_correction'] as $cc)
                                                    <details class="rounded-lg border border-slate-200 bg-slate-50/80 p-3 transition-all duration-200 hover:border-slate-300">
                                                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-slate-600">
                                                            <span class="break-words">Centre correction: {{ $cc['nom'] }}</span>
                                                            <span class="ml-2 text-xs">candidats: {{ number_format($cc['total_candidats'], 0, ',', ' ') }} | salles: {{ number_format($cc['total_salles'], 0, ',', ' ') }}</span>
                                                        </summary>
                                                        <div class="mt-2 overflow-x-auto">
                                                            <table class="min-w-full border-collapse text-sm">
                                                                <thead>
                                                                <tr class="bg-slate-100">
                                                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Centre d'écrit</th>
                                                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Total candidats</th>
                                                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold">Total salles</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                @foreach($cc['centres_ecrit'] as $ce)
                                                                    <tr class="transition-colors duration-150 hover:bg-slate-100/80">
                                                                        <td class="border border-slate-200 px-3 py-2 break-words">{{ $ce['nom'] }}</td>
                                                                        <td class="border border-slate-200 px-3 py-2">{{ number_format($ce['total_candidats'], 0, ',', ' ') }}</td>
                                                                        <td class="border border-slate-200 px-3 py-2">{{ number_format($ce['total_salles'], 0, ',', ' ') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </details>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </details>
                        @empty
                            <div class="text-sm text-slate-500">Aucune donnée disponible pour ce filtre.</div>
                        @endforelse

                        @if($recapByDren->hasPages())
                            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 pt-4 text-sm">
                                <div class="text-slate-500">
                                    Page {{ $recapByDren->currentPage() }} / {{ $recapByDren->lastPage() }}
                                </div>
                                <div class="flex gap-2">
                                    @if($recapByDren->onFirstPage())
                                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-400">Précédent</span>
                                    @else
                                        <a class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ $recapByDren->previousPageUrl() }}">Précédent</a>
                                    @endif

                                    @if($recapByDren->hasMorePages())
                                        <a class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ $recapByDren->nextPageUrl() }}">Suivant</a>
                                    @else
                                        <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-400">Suivant</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    (function () {
        const chartData = @json($langueOptionChart ?? []);
        const canvas = document.getElementById('langueOptionChart');
        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        function drawChart() {
            const width = Math.max(canvas.clientWidth || 600, 320);
            const height = 220;
            const ratio = window.devicePixelRatio || 1;
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.clearRect(0, 0, width, height);

            if (!Array.isArray(chartData) || chartData.length === 0) {
                ctx.fillStyle = '#475569';
                ctx.font = '13px Arial';
                ctx.fillText('Aucune donnée disponible.', 16, 24);
                return;
            }

            const pad = { top: 14, right: 16, bottom: 60, left: 42 };
            const chartW = width - pad.left - pad.right;
            const chartH = height - pad.top - pad.bottom;
            const max = Math.max(...chartData.map((x) => Number(x.value || 0)), 1);
            const gap = 10;
            const barW = Math.max(18, (chartW - gap * (chartData.length - 1)) / chartData.length);
            const colors = ['#2563eb', '#059669', '#d97706', '#9333ea', '#e11d48', '#0f766e', '#475569'];

            ctx.strokeStyle = '#cbd5e1';
            ctx.beginPath();
            ctx.moveTo(pad.left, pad.top);
            ctx.lineTo(pad.left, pad.top + chartH);
            ctx.lineTo(pad.left + chartW, pad.top + chartH);
            ctx.stroke();

            chartData.forEach((item, i) => {
                const v = Number(item.value || 0);
                const x = pad.left + i * (barW + gap);
                const h = Math.round((v / max) * (chartH - 6));
                const y = pad.top + chartH - h;
                ctx.fillStyle = colors[i % colors.length];
                ctx.fillRect(x, y, barW, h);

                ctx.fillStyle = '#0f172a';
                ctx.font = '11px Arial';
                ctx.fillText(String(v), x, Math.max(y - 5, 10));

                const label = String(item.label || '');
                const shortLabel = label.length > 22 ? label.slice(0, 20) + '..' : label;
                ctx.save();
                ctx.translate(x + (barW / 2), pad.top + chartH + 10);
                ctx.rotate(-0.55);
                ctx.fillStyle = '#334155';
                ctx.fillText(shortLabel, 0, 0);
                ctx.restore();
            });
        }

        drawChart();
        window.addEventListener('resize', drawChart);
    })();
</script>
</body>
</html>
