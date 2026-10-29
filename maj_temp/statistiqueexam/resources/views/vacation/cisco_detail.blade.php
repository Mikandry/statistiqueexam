@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">CISCO : {{ $hierarchy['nom_cisco'] }}</h1>
        <a href="{{ route('vacation.dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left"></i> Retour Dashboard
        </a>
    </div>

    <!-- RECAPITULATIF CISCO -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Vacation Fixe CISCO</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($hierarchy['vacation_fixe']['montant_fixe'], 0, ',', ' ') }} Ar
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sous-Pli & Admin CISCO</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($hierarchy['vacation_variable_cisco']['montant_estime'], 0, ',', ' ') }} Ar
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Général CISCO</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($hierarchy['montant_total_cisco'], 0, ',', ' ') }} Ar
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLEAU DÉTAILLÉ CENTRES D'ÉCRIT, CORRECTION, JUMELÉ ET EPS -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Détail des Centres rattachés au CISCO</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Type Centre</th>
                            <th>Nom du Centre</th>
                            <th>Candidats</th>
                            <th>Salles</th>
                            <th>Agents Estimés</th>
                            <th>Activités</th>
                            <th>Total Estimé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_merge($hierarchy['centres_ecrit'], $hierarchy['centres_correction'], $hierarchy['centres_jumele']) as $c)
                        <tr>
                            <td>
                                @if($c['type_centre'] === 'ECRIT_SEUL')
                                    <span class="badge badge-primary">Écrit Seul</span>
                                @elseif($c['type_centre'] === 'CORRECTION_SEULE')
                                    <span class="badge badge-warning">Correction Seule</span>
                                @else
                                    <span class="badge badge-success">Jumelé</span>
                                @endif
                            </td>
                            <td class="font-weight-bold">{{ $c['nom_centre'] }}</td>
                            <td>{{ $c['candidats'] }}</td>
                            <td>{{ $c['salles'] }}</td>
                            <td>{{ $c['total_agents_estimes'] }}</td>
                            <td>
                                <ul class="pl-3 mb-0 text-xs">
                                    @foreach($c['activites'] as $act)
                                        <li>{{ $act['activite'] }} : {{ $act['nombre_agents'] }} ag. ({{ number_format($act['montant_estime'], 0, ',', ' ') }} Ar)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="font-weight-bold text-primary">{{ number_format($c['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach

                        @foreach($hierarchy['centres_eps'] as $eps)
                        <tr class="table-info">
                            <td><span class="badge badge-dark">EPS / GYM</span></td>
                            <td class="font-weight-bold">Site EPS - {{ $eps['nom_centre'] }}</td>
                            <td>{{ $eps['candidats'] }}</td>
                            <td>-</td>
                            <td>{{ $eps['agents'] }}</td>
                            <td>Épreuves physiques ({{ $eps['jours'] }} jours)</td>
                            <td class="font-weight-bold text-primary">{{ number_format($eps['montant_estime'], 0, ',', ' ') }} Ar</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection