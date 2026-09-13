@extends('layouts.app')

@section('title', 'Tableau de bord EPS/GYM - Vacation 2026')
@section('content')

    @include('vacation-2026.dashboards._navigation', [
        'navBackLabel' => 'Dashboard global',
        'navBackRoute' => route('vacation2026.dashboard.global'),
    ])

    <div class="space-y-4">
        @include('vacation-2026.dashboards._filters')
        <!-- Header -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5 md:p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tableau de bord EPS/GYM</h1>
                        <p class="mt-1 text-sm text-slate-600">Vacation 2026 - Épreuves Physiques et Sportives</p>
                    </div>
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-900 text-sm font-bold text-white">EPS</div>
                        <div class="text-sm">
                            <div class="font-semibold text-slate-900">EPS/GYM</div>
                            <div class="text-xs text-slate-500">Épreuves Physiques et Sportives</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs uppercase tracking-wider text-blue-700">Candidats</p>
                <p class="mt-2 text-2xl font-semibold text-blue-900">{{ number_format($total_candidates, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
                <p class="text-xs uppercase tracking-wider text-purple-700">Centres EPS</p>
                <p class="mt-2 text-2xl font-semibold text-purple-900">{{ $total_centres }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs uppercase tracking-wider text-emerald-700">Interrogateurs</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-900">{{ $interrogators_required }}</p>
                <p class="mt-1 text-xs text-emerald-600">3 par 600 candidats</p>
            </div>
            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4">
                <p class="text-xs uppercase tracking-wider text-cyan-700">Secrétariat</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-900">{{ $secretariat_required }}</p>
                <p class="mt-1 text-xs text-cyan-600">1 par 200 candidats</p>
            </div>
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
                <p class="text-xs uppercase tracking-wider text-orange-700">Médecin</p>
                <p class="mt-2 text-2xl font-semibold text-orange-900">{{ $medical_required }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <p class="text-xs uppercase tracking-wider text-indigo-700">Taux de réalisation</p>
                <p class="mt-2 text-2xl font-semibold text-indigo-900">{{ $completion_percentage }}%</p>
            </div>
        </div>

        <!-- Personnel Summary -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Récapitulatif Personnel</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Chefs de centre</span>
                        <span class="font-semibold">{{ $chef_centre_required }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Surveillants</span>
                        <span class="font-semibold">{{ $surveillants_required }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Interrogateurs</span>
                        <span class="font-semibold">{{ $interrogators_required }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Secrétariat</span>
                        <span class="font-semibold">{{ $secretariat_required }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Médecin</span>
                        <span class="font-semibold">{{ $medical_required }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Agents de stade</span>
                        <span class="font-semibold">{{ $stadium_agents_required }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-3 flex justify-between font-semibold">
                        <span class="text-slate-900">Total</span>
                        <span class="text-slate-900">{{ $total_planned }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Affectations</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Prévu</span>
                        <span class="font-semibold">{{ $total_planned }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Affecté</span>
                        <span class="font-semibold text-emerald-600">{{ $total_assigned }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Restant</span>
                        <span class="font-semibold text-orange-600">{{ $remaining }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 mt-4">
                        <div class="bg-purple-600 h-3 rounded-full" style="width: {{ $completion_percentage }}%"></div>
                    </div>
                    <div class="text-right text-xs text-slate-600 mt-2">{{ $completion_percentage }}% complété</div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-4">Statut par Phase</h3>
                <div class="space-y-2">
                    @forelse($assignments_by_status as $status => $count)
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-600">{{ $status }}</span>
                        <span class="inline-block px-3 py-1 bg-slate-100 rounded text-sm font-semibold">{{ $count }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-slate-600">Aucune affectation</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Centres EPS Details -->
        <div class="rounded-2xl border border-purple-200 bg-purple-50 p-5">
            <h3 class="text-lg font-semibold text-purple-950">Configuration des centres EPS par CISCO</h3>
            <p class="mt-1 text-sm text-purple-800">Ajoutez autant de centres EPS que nécessaire et saisissez leurs candidats. Ces données sont prioritaires sur les anciens centres EPS/GYM.</p>
            <form method="POST" action="{{ $selectedCiscoId ? route('vacation2026.eps-centres.store', $selectedCiscoId) : '#' }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf
                <select name="cisco_id_display" onchange="window.location='{{ route('vacation2026.dashboard.eps') }}?cisco_id='+this.value" class="rounded-lg border border-purple-300 px-3 py-2 text-sm" aria-label="CISCO">
                    <option value="">Choisir un CISCO</option>
                    @foreach($allCiscos as $cisco)<option value="{{ $cisco->id }}" @selected((string)$selectedCiscoId === (string)$cisco->id)>{{ $cisco->nom }} — {{ $cisco->dren?->nom }}</option>@endforeach
                </select>
                <input name="name" required maxlength="255" placeholder="Nom du centre EPS" class="rounded-lg border border-purple-300 px-3 py-2 text-sm" @disabled(! $selectedCiscoId)>
                <div class="flex gap-2"><input name="candidate_count" type="number" min="0" required placeholder="Candidats" class="w-full rounded-lg border border-purple-300 px-3 py-2 text-sm" @disabled(! $selectedCiscoId)><button type="submit" class="rounded-lg bg-purple-800 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50" @disabled(! $selectedCiscoId)>Ajouter</button></div>
            </form>
        </div>

        @if(!empty($centres))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Centres EPS/GYM</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Centre</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Origine</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Candidats</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Chefs</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Surveillants</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Interrogateurs</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Secrétariat</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Médecins</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Agents stade</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Candidats EPS</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-900">Durée</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($centres as $centre)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $centre['centre_name'] }}</td>
                            <td class="px-4 py-3 text-xs">{{ $centre['legacy'] ? 'Ancien centre EPS/GYM' : 'Configuration EPS' }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($centre['candidates'], 0, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-center">{{ $centre['chef_centre_required'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $centre['surveillants_required'] }}</td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $centre['interrogators_required'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $centre['secretariat_required'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $centre['medical_required'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $centre['stadium_agents_required'] }}</td>
                            <td class="px-4 py-3 text-center">@if($centre['legacy'])<form method="POST" action="{{ route('vacation2026.centres.eps-capacity.update', $centre['centre_id']) }}" class="inline-flex items-center gap-1">@csrf @method('PUT')<input class="w-20 rounded border border-slate-300 px-1 py-1 text-center" type="number" min="0" step="1" name="eps_capacity" value="{{ $centre['capacity'] }}"><button class="text-xs text-slate-600 underline" type="submit">OK</button></form>@else<form method="POST" action="{{ route('vacation2026.eps-centres.update', $centre['centre_id']) }}" class="inline-flex items-center gap-1">@csrf @method('PUT')<input type="hidden" name="name" value="{{ $centre['centre_name'] }}"><input class="w-20 rounded border border-slate-300 px-1 py-1 text-center" type="number" min="0" name="candidate_count" value="{{ $centre['candidates'] }}"><button class="text-xs text-slate-600 underline">OK</button></form>@endif</td>
                            <td class="px-4 py-3 text-center">{{ $centre['duration'] }} jours</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(isset($activities) && !empty($activities))
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-slate-900">Activités EPS par examen</h3>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="border-b border-slate-200">
                <th class="px-3 py-2 text-left">Activité</th><th class="px-3 py-2 text-center">Personnel</th><th class="px-3 py-2 text-center">Jours</th><th class="px-3 py-2 text-right">Montant</th>
            </tr></thead><tbody>@foreach($activities as $activity)<tr class="border-b border-slate-100">
                <td class="px-3 py-2">{{ $activity['examen'] }} - {{ $activity['libelle'] }}</td><td class="px-3 py-2 text-center">{{ $activity['required'] }}</td><td class="px-3 py-2 text-center">{{ $activity['days'] }}</td><td class="px-3 py-2 text-right">{{ number_format($activity['amount'], 0, ',', ' ') }}</td>
            </tr>@endforeach</tbody></table></div>
        </div>
        @endif

        @if(!empty($personnel_details))
    <div class="rounded-2xl border border-purple-200 bg-white p-5 shadow-sm">
        <h3 class="mb-1 text-lg font-semibold text-slate-900">
            Détail du personnel EPS requis
        </h3>

        <p class="mb-4 text-sm text-slate-600">
            Effectifs calculés selon les règles du décret, tous centres EPS confondus.
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="px-3 py-2 text-left">Fonction</th>
                        <th class="px-3 py-2 text-left">Règle de calcul</th>
                        <th class="px-3 py-2 text-right">Taux / jour</th>
                        <th class="px-3 py-2 text-center">Nombre requis</th>
                        <th class="px-3 py-2 text-right">Montant estimé</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($personnel_details as $detail)
                        <tr class="border-b border-slate-100">
                            <td class="px-3 py-3 font-medium text-slate-900">
                                {{ $detail['role'] }}
                            </td>

                            <td class="px-3 py-3 text-slate-600">
                                {{ $detail['rule'] }}
                            </td>

                            <td class="px-3 py-3 text-right">
                                @if($detail['rate_id'])
                                    <form
                                        method="POST"
                                        action="{{ route('vacation2026.eps-role-rates.update', $detail['rate_id']) }}"
                                        class="inline-flex items-center gap-1"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <input
                                            name="rate"
                                            type="number"
                                            min="0"
                                            step="1"
                                            value="{{ $detail['rate'] }}"
                                            class="w-24 rounded border border-slate-300 px-1 py-1 text-right"
                                        >

                                        <span>Ar</span>

                                        <button
                                            type="submit"
                                            class="text-xs text-purple-700 underline"
                                        >
                                            OK
                                        </button>
                                    </form>
                                @else
                                    {{ number_format($detail['rate'], 0, ',', ' ') }} Ar
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center text-lg font-semibold text-purple-800">
                                {{ $detail['required'] }}
                            </td>

                            <td class="px-3 py-3 text-right font-semibold">
                                {{ number_format($detail['amount'], 0, ',', ' ') }} Ar
                            </td>
                        </tr>
                    @endforeach

                    <tr class="bg-purple-50 font-semibold">
                        <td class="px-3 py-3" colspan="3">
                            Total EPS
                        </td>

                        <td class="px-3 py-3 text-center text-purple-900">
                            {{ $total_planned }}
                        </td>

                        <td class="px-3 py-3 text-right text-purple-900">
                            {{ number_format($estimated_indemnity, 0, ',', ' ') }} Ar
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

        <!-- Notes on Calculations -->
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wider mb-3">Notes sur les calculs</h3>
            <ul class="space-y-2 text-sm text-blue-800">
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Candidats EPS :</strong> saisie manuelle, par centre EPS/GYM (1 à 3 centres par CISCO)</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Chefs de centre EPS :</strong> 2 par centre (1 chef + 1 adjoint)</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Surveillants :</strong> 2 par centre</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Interrogateurs :</strong> 3 par tranche de 600 candidats</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Secrétariat :</strong> 1 par tranche de 200 candidats</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Durée :</strong> 4 jours normalement, 5 jours si candidats > 3000</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Médecin :</strong> 1 par centre EPS</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-semibold">•</span>
                    <span><strong>Agents de stade :</strong> 2 par centre</span>
                </li>
            </ul>
        </div>
    </div>
@endsection
