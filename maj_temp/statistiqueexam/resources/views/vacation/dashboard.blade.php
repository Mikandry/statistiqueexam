<!-- ... existing code ... -->

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tableau de Bord - Vacation 2026 (Décret N°2026-1257)</h1>

    <!-- KPI DASHBOARD -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Candidats / Salles</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCandidats) }} Cand. / {{ number_format($totalSalles) }} Salles</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Agents (Besoin / Affecté / Écart)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($totalAgentsEstimes) }} Est. | 
                        <span class="text-success">{{ number_format($totalAgentsAffectes) }} Aff.</span> | 
                        <span class="text-danger">Écart: {{ number_format($ecartAgents) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Montant Estimé (Décret)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($montantEstimatifTotal, 0, ',', ' ') }} Ar</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Validé vs Payé</div>
                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                        Val: {{ number_format($montantValide, 0, ',', ' ') }} Ar<br>
                        Payé: {{ number_format($montantPaye, 0, ',', ' ') }} Ar
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU DE DÉTAIL ACTIVITÉS -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détail des Estimations par Centre & Activité</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Centre</th>
                            <th>Phase</th>
                            <th>Niveau</th>
                            <th>Activité</th>
                            <th>Nb Agents Est.</th>
                            <th>Nb Jours</th>
                            <th>Indemnité Unitaire</th>
                            <th>Montant Estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailsCentres as $centreData)
                            @foreach($centreData['activites'] as $act)
                                <tr>
                                    <td>{{ $centreData['nom_centre'] }} ({{ $centreData['type_centre'] }})</td>
                                    <td><span class="badge badge-secondary">{{ $act['phase'] }}</span></td>
                                    <td><span class="badge badge-info">{{ $act['niveau'] }}</span></td>
                                    <td>{{ $act['activite'] }}</td>
                                    <td>{{ $act['nombre_agents'] }}</td>
                                    <td>{{ $act['nombre_jours'] }}</td>
                                    <td>{{ number_format($act['indemnite_unitaire'], 0, ',', ' ') }} Ar</td>
                                    <td class="font-weight-bold">{{ number_format($act['montant_estime'], 0, ',', ' ') }} Ar</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ... existing code ... -->