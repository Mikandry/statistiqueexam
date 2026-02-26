<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propositions Vacations</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Propositions Vacations</h1>
                    <p class="mt-1 text-sm text-slate-500">Estimations DREN, CISCO et centres basées sur candidats et salles</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('bepc.repartition.create') }}">Saisie</a>
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.dashboard', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Dashboard</a>
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.export.dispatching', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Export Dispatching</a>
                    <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.livre.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Livre</a>
                </div>
            </div>
        </div>

        <div class="p-5 md:p-6">
            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label for="annee" class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
                    <select id="annee" name="annee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Toutes</option>
                        @foreach($annees as $annee)
                            <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="type_examen" class="mb-1 block text-sm font-medium text-slate-700">Type d'examen</label>
                    <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                        <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                        <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                    </select>
                </div>
                <div>
                    <label for="dren" class="mb-1 block text-sm font-medium text-slate-700">DREN</label>
                    <select id="dren" name="dren" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Toutes</option>
                        @foreach($drens as $dren)
                            <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Actualiser</button>
                </div>
            </form>

            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                Récap calculé par niveau: Central (nombres fixes), DREN, CISCO, EPS, Centres d'examen, Centres de correction et transcription. Les niveaux déconcentrés sont liés aux volumes DREN/CISCO/centres/salles/candidats de votre base.
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Diagramme comparatif langues / options</h2>
                <canvas id="langueOptionChart" height="220"></canvas>
            </div>

            <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-8">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Candidats</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_candidats'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Salles</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_salles'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Centres</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_centres'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">DREN</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_drens'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">CISCO</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_ciscos'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Centres correction</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['total_centres_correction'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Proposition DREN</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['proposition_dren'], 0, ',', ' ') }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Proposition CISCO</div>
                    <div class="mt-1 text-xl font-semibold">{{ number_format($globalStats['proposition_cisco'], 0, ',', ' ') }}</div>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Niveau Central (nombres fixes)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">Examen</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Règle</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'agents</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($centralActivities as $item)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['examen'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['regle'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($item['agents'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Niveau DREN (activités détaillées)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">Volet</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Calcul</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'agents</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($drenActivities as $item)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['bloc'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['regle'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($item['agents'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Niveau CISCO et EPS</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">Volet</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Calcul</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'agents</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ciscoActivities as $item)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['bloc'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['regle'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($item['agents'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres d'examen (CEPE, CAE, CAP)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">Volet</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Calcul</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'agents</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($centreActivities as $item)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['bloc'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['regle'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($item['agents'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Centre de correction et transcription</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">Volet</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Activité</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Calcul</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Nombre d'agents</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($correctionTranscriptionActivities as $item)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['bloc'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['activite'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $item['regle'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($item['agents'], 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Par DREN</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">DREN</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">CISCO</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Centres</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Candidats</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Organisation</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Supervision</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Total proposé</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($drenRows as $row)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['dren'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['ciscos'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['centres'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['propositions']['organisation_generale'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['propositions']['supervision_session'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($row['propositions']['total'], 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-2 text-center text-slate-500" colspan="7">Aucune donnée.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Par CISCO</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">DREN</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">CISCO</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Centres</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Candidats</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Salles</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Total proposé</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ciscoRows as $row)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['dren'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['cisco'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['centres'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($row['proposition'], 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-2 text-center text-slate-500" colspan="6">Aucune donnée.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Par centre</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-200 px-3 py-2 text-left">DREN</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">CISCO</th>
                            <th class="border border-slate-200 px-3 py-2 text-left">Centre écrit</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Candidats</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Salles</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Avant session</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Pendant session</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Après session</th>
                            <th class="border border-slate-200 px-3 py-2 text-right">Total proposé</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($centreRows as $row)
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['dren'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['cisco'] }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $row['centre_ecrit'] }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['candidats'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['salles'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['propositions']['avant_session'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['propositions']['pendant_session'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($row['propositions']['apres_session'], 0, ',', ' ') }}</td>
                                <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($row['propositions']['total'], 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td class="border border-slate-200 px-3 py-2 text-center text-slate-500" colspan="9">Aucune donnée.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
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
