<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Service de l'Organisation des Examens</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if (file_exists(public_path('build/manifest.json')))
@vite(['resources/css/app.css', 'resources/js/app.js'])
@else
<link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
@endif

<style>
body { font-family:'Plus Jakarta Sans',sans-serif; }
</style>
</head>

<body class="h-full">

<div class="max-w-7xl mx-auto p-6 space-y-6">

<!-- HEADER -->
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Décision de Centre</h1>

    <<a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300" href="{{ route('repartition.dashboard') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                Dashboard
                            </a>
</div>

<!-- FILTER CARD -->
<div class="bg-white rounded-2xl shadow p-6">
    <form method="GET" action="{{ route('decision.centre') }}">
        <div class="grid md:grid-cols-3 gap-6">
            <!-- TYPE EXAMEN -->
            <div>
                <label class="text-sm font-medium text-slate-600">Type d'Examen</label>
                <select id="type_examen" name="type_examen" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Tous</option>
                    <option value="BEPC" {{ ($typeExamen ?? '') == 'BEPC' ? 'selected' : '' }}>BEPC</option>
                    <option value="CEPE" {{ ($typeExamen ?? '') == 'CEPE' ? 'selected' : '' }}>CEPE</option>
                </select>
            </div>

            <!-- DREN -->
            <div>
                <label class="text-sm font-medium text-slate-600">DREN</label>
                <select name="dren" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Tous</option>
                    @foreach($drens as $dren)
                        <option value="{{ $dren->id }}" {{ ($drenId ?? '') == $dren->id ? 'selected' : '' }}>
                            {{ $dren->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- CISCO -->
            <div>
                <label class="text-sm font-medium text-slate-600">CISCO</label>
                <select name="cisco" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Tous</option>
                    @foreach($ciscos as $cisco)
                        <option value="{{ $cisco->id }}" {{ ($ciscoId ?? '') == $cisco->id ? 'selected' : '' }}>
                            {{ $cisco->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6">
            <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
                Filtrer
            </button>
        </div>
    </form>
</div>

<!-- GLOBAL CHART -->
<div class="bg-white rounded-2xl shadow p-6">
    <h2 class="text-lg font-semibold mb-4 text-slate-700">Statistiques Globales</h2>
    <canvas id="centreChart"></canvas>
</div>

<!-- TABLE -->
<div class="overflow-x-auto bg-white rounded-2xl shadow p-6 mt-6">
    <h2 class="text-lg font-semibold mb-4 text-slate-700">Centres par DREN</h2>
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-100">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-slate-700">DREN</th>
                <th class="px-4 py-3 text-center font-medium text-slate-700">CISCO</th>
                <th class="px-4 py-3 text-center font-medium text-slate-700">Centre Correction</th>
                <th class="px-4 py-3 text-center font-medium text-slate-700">Centre Écrit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-sm">
            @foreach($tableData as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-700">{{ $row['dren'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $row['cisco'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $row['correction'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $row['ecrit'] }}</td>
                </tr>
            @endforeach

            <!-- TOTAL ROW -->
            <tr class="bg-slate-100 font-semibold">
                <td class="px-4 py-3 text-slate-800">TOTAL (DREN : {{ $totalDren }})</td>
                <td class="px-4 py-3 text-center text-indigo-600">{{ $totalCisco }}</td>
                <td class="px-4 py-3 text-center text-indigo-600">{{ $totalCorrection }}</td>
                <td class="px-4 py-3 text-center text-indigo-600">{{ $totalEcrit }}</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    // Auto submit on type_examen change
    const typeExamen = document.getElementById("type_examen");
    if(typeExamen){
        typeExamen.addEventListener("change", function(){
            this.form.submit();
        });
    }

    // Global chart
    const ctx = document.getElementById('centreChart');
    if(ctx){
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Nombre Total',
                    data: @json($chartData),
                    backgroundColor: [
                        '#4f46e5', '#0ea5e9', '#f97316', '#10b981'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Statistiques des Centres' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>

</body>
</html>