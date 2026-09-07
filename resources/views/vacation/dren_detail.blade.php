@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">DREN : {{ $hierarchy['nom_dren'] }} — Estimation Vacation 2026</h1>
        <a href="{{ route('vacation.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour Dashboard
        </a>
    </div>

    <!-- CARTE RECAPITULATIVE DREN -->
    <div class="card border-left-primary shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="font-weight-bold text-primary">Total Estimation DREN : {{ number_format($hierarchy['montant_total_dren'], 0, ',', ' ') }} Ar</h5>
                    <p class="mb-0">Inclut la vacation fixe DREN + vacations fixes CISCOs + centres d'épreuve/correction/EPS.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <span class="badge badge-primary p-2">Vacation Fixe DREN : {{ number_format($hierarchy['vacation_fixe']['montant_fixe'], 0, ',', ' ') }} Ar</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU VACATION FIXE DREN -->
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white">
            <h6 class="m-0 font-weight-bold">1. Vacations Fixes Administratives DREN (Non liées au nombre de candidats)</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Activité / Role</th>
                        <th>Phase</th>
                        <th>Agents</th>
                        <th>Jours</th>
                        <th>Indemnité/Jour</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hierarchy['vacation_fixe']['activites_fixes'] as $act)
                    <tr>
                        <td>{{ $act['activite'] }}</td>
                        <td><span class="badge badge-secondary">{{ $act['phase'] }}</span></td>
                        <td>{{ $act['nombre_agents'] }}</td>
                        <td>{{ $act['nombre_jours'] }}</td>
                        <td>{{ number_format($act['indemnite_unitaire'], 0, ',', ' ') }} Ar</td>
                        <td class="font-weight-bold">{{ number_format($act['montant_estime'], 0, ',', ' ') }} Ar</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LISTE DES CISCOS SOUS LA DREN -->
    <h4 class="h5 mb-3 text-gray-800 font-weight-bold">2. Ventilation par CISCO</h4>

    @foreach($hierarchy['ciscos'] as $ciscoData)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                CISCO : {{ $ciscoData['nom_cisco'] }}
            </h6>
            <span class="badge badge-success h6 mb-0">
                Total CISCO : {{ number_format($ciscoData['montant_total_cisco'], 0, ',', ' ') }} Ar
            </span>
        </div>
        <div class="card-body">
            
            <!-- VACATION FIXE CISCO -->
            <div class="alert alert-info py-2">
                <strong>Vacation Fixe CISCO (Forfait) :</strong> {{ number_format($ciscoData['vacation_fixe']['montant_fixe'], 0, ',', ' ') }} Ar
                | <strong>Mise sous-pli & Suivi :</strong> {{ number_format($ciscoData['vacation_variable_cisco']['montant_estime'], 0, ',', ' ') }} Ar
            </div>

            <!-- TABLEAU VENTILATION PAR TYPES DE CENTRES -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Catégorie / Type</th>
                            <th>Nom du Centre / Site</th>
                            <th>Candidats</th>
                            <th>Salles</th>
                            <th>Agents Estimés</th>
                            <th>Montant Estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CENTRES ECRIT -->
                        @foreach($ciscoData['centres_ecrit'] as $c)
                        <tr>
                            <td><span class="badge badge-primary">Centre Écrit Seul</span></td>
                            <td>{{ $c['nom_centre'] }}</td>
                            <td>{{ $c['candidats'] }}</td>
                            <td>{{ $c['salles'] }}</td>
                            <td>{{ $c['total_agents_estimes'] }}</td>
                            <td class="font-weight-bold">{{ number_format($c['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach

                        <!-- CENTRES CORRECTION -->
                        @foreach($ciscoData['centres_correction'] as $c)
                        <tr>
                            <td><span class="badge badge-warning">Centre Correction Seul</span></td>
                            <td>{{ $c['nom_centre'] }}</td>
                            <td>{{ $c['candidats'] }}</td>
                            <td>-</td>
                            <td>{{ $c['total_agents_estimes'] }}</td>
                            <td class="font-weight-bold">{{ number_format($c['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach

                        <!-- CENTRES JUMELES -->
                        @foreach($ciscoData['centres_jumele'] as $c)
                        <tr>
                            <td><span class="badge badge-success">Centre Jumelé (Écrit + Corr)</span></td>
                            <td>{{ $c['nom_centre'] }}</td>
                            <td>{{ $c['candidats'] }}</td>
                            <td>{{ $c['salles'] }}</td>
                            <td>{{ $c['total_agents_estimes'] }}</td>
                            <td class="font-weight-bold">{{ number_format($c['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach

                        <!-- SITES EPS -->
                        @foreach($ciscoData['centres_eps'] as $eps)
                        <tr class="table-info">
                            <td><span class="badge badge-dark">Épreuves EPS / GYM</span></td>
                            <td>Site EPS - {{ $eps['nom_centre'] }}</td>
                            <td>{{ $eps['candidats'] }}</td>
                            <td>-</td>
                            <td>{{ $eps['agents'] }}</td>
                            <td class="font-weight-bold">{{ number_format($eps['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    @endforeach
</div>
@endsection