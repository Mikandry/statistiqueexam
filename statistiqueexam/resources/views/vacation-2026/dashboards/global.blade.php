@extends('layouts.app')

@section('title', 'Tableau de bord Global - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="space-y-4">
        @include('vacation-2026.dashboards._filters')
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Tableau de bord Global</h1>
                        <p class="mt-1 text-sm text-slate-600">Vacation 2026 - Vue d'ensemble complète de toutes les activités</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">SOE</div>
                        <div class="text-sm">
                            <div class="font-semibold text-slate-900">SOE</div>
                            <div class="text-xs text-slate-500">Service de l'Organisation des Examens</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('vacation-2026.dashboards._phase-summary')

        <!-- Main KPIs -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">CEPE</p>
                <p class="mt-2 text-2xl font-semibold text-blue-900">{{ $cepe['planned'] }}</p>
                <p class="mt-1 text-xs text-blue-600">Positions à pourvoir</p>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-xs uppercase tracking-wider text-green-700">BEPC</p>
                <p class="mt-2 text-2xl font-semibold text-green-900">{{ $bepc['planned'] }}</p>
                <p class="mt-1 text-xs text-green-600">Positions à pourvoir</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">EPS/GYM</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ $eps['planned'] }}</p>
                <p class="mt-1 text-xs text-purple-600">Positions à pourvoir</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs uppercase tracking-wider text-amber-700">Candidats</p>
                <p class="mt-2 text-2xl font-semibold text-amber-900">{{ number_format($total_candidates, 0, ',', ' ') }}</p>
                <p class="mt-1 text-xs text-amber-600">Total nationwide</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-xs uppercase tracking-wider text-indigo-700">Centres</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ $total_centres }}</p>
                <p class="mt-1 text-xs text-indigo-600">D'examen</p>
            </div>
            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4">
                <p class="text-xs uppercase tracking-wider text-cyan-700">Salles</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-900">{{ $total_salles }}</p>
                <p class="mt-1 text-xs text-cyan-600">D'examen</p>
            </div>
        </div>

        <!-- Overall Stats -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Personnel Total</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Prévu</span>
                        <span class="text-2xl font-semibold text-slate-900">{{ $total_planned }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Affecté</span>
                        <span class="text-2xl font-semibold text-emerald-600">{{ $total_assigned }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="text-2xl font-semibold text-orange-600">{{ $remaining }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Répartition par Examen</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-600">CEPE</span>
                        <span class="font-semibold">{{ $cepe['assigned'] }}/{{ $cepe['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">BEPC</span>
                        <span class="font-semibold">{{ $bepc['assigned'] }}/{{ $bepc['planned'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">EPS/GYM</span>
                        <span class="font-semibold">{{ $eps['assigned'] }}/{{ $eps['planned'] }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Taux de Réalisation</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">Global</span>
                        <span class="text-2xl font-semibold">{{ $completion_percentage }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-slate-900 h-3 rounded-full transition-all" style="width: {{ $completion_percentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- Exam Breakdown -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Personnel par Examen</h3>
                <canvas id="examChart" height="250"></canvas>
            </div>

            <!-- Status Breakdown -->
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Statut des Affectations</h3>
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Résumé par Type d'Examen</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Examen</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Prévu</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Affecté</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Restant</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Taux (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">CEPE</td>
                            <td class="px-4 py-3 text-center">{{ $cepe['planned'] }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $cepe['assigned'] }}</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ $cepe['remaining'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $cepe['completion_percentage'] }}%</td>
                        </tr>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">BEPC</td>
                            <td class="px-4 py-3 text-center">{{ $bepc['planned'] }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $bepc['assigned'] }}</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ $bepc['remaining'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $bepc['completion_percentage'] }}%</td>
                        </tr>
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">EPS/GYM</td>
                            <td class="px-4 py-3 text-center">{{ $eps['planned'] }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $eps['assigned'] }}</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ $eps['remaining'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $eps['completion_percentage'] }}%</td>
                        </tr>
                        <tr class="bg-slate-50 font-semibold border-t border-slate-200">
                            <td class="px-4 py-3 text-slate-900">TOTAL</td>
                            <td class="px-4 py-3 text-center text-slate-900">{{ $total_planned }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600">{{ $total_assigned }}</td>
                            <td class="px-4 py-3 text-center text-orange-600">{{ $remaining }}</td>
                            <td class="px-4 py-3 text-center text-slate-900">{{ $completion_percentage }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    // Exam Chart
    const examCtx = document.getElementById('examChart').getContext('2d');
    new Chart(examCtx, {
        type: 'bar',
        data: {
            labels: ['CEPE', 'BEPC', 'EPS/GYM'],
            datasets: [
                {
                    label: 'Prévu',
                    data: [{{ $cepe['planned'] }}, {{ $bepc['planned'] }}, {{ $eps['planned'] }}],
                    backgroundColor: 'rgba(30, 144, 255, 0.5)',
                    borderColor: 'rgba(30, 144, 255, 1)',
                    borderWidth: 2
                },
                {
                    label: 'Affecté',
                    data: [{{ $cepe['assigned'] }}, {{ $bepc['assigned'] }}, {{ $eps['assigned'] }}],
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($assignments_by_status as $status => $count)
                    '{{ $status }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($assignments_by_status as $status => $count)
                        {{ $count }},
                    @endforeach
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.6)',
                    'rgba(34, 197, 94, 0.6)',
                    'rgba(249, 115, 22, 0.6)',
                    'rgba(239, 68, 68, 0.6)',
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(34, 197, 94, 1)',
                    'rgba(249, 115, 22, 1)',
                    'rgba(239, 68, 68, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
    </div>
@endsection
