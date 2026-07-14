@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Statistiques par Option de Langue</h1>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="type_examen" class="form-label">Type d'examen</label>
                    <select name="type_examen" id="type_examen" class="form-select">
                        <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                        <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                        <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="annee" class="form-label">Année</label>
                    <select name="annee" id="annee" class="form-select">
                        <option value="">Toutes les années</option>
                        @foreach ($annees as $annee)
                            <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>
                                {{ $annee }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="dren" class="form-label">DREN</label>
                    <select name="dren" id="dren" class="form-select">
                        <option value="">Tous les DREN</option>
                        @foreach ($drens as $dren)
                            <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>
                                {{ $dren }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="cisco" class="form-label">CISCO</label>
                    <select name="cisco" id="cisco" class="form-select">
                        <option value="">Tous les CISCO</option>
                        @foreach ($ciscos as $cisco)
                            <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>
                                {{ $cisco }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="langue" class="form-label">Option de Langue</label>
                    <select name="langue" id="langue" class="form-select">
                        @foreach ($allLanguages as $langue)
                            <option value="{{ $langue }}" {{ $selectedLanguage === $langue ? 'selected' : '' }}>
                                {{ $langue }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats for selected language -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $selectedLanguage }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>DREN</th>
                            <th>CISCO</th>
                            <th class="text-center">PE</th>
                            <th class="text-center">GE</th>
                            <th class="text-center">Soubique</th>
                            <th class="text-end">Total Enveloppe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats as $stat)
                            <tr>
                                <td>{{ $stat['dren'] }}</td>
                                <td>{{ $stat['cisco'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $stat['pe'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $stat['ge'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $stat['soubique'] }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ $stat['total_enveloppe'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucune donnée pour les filtres sélectionnés.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2">TOTAL</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $totals['pe'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $totals['ge'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $totals['soubique'] }}</span>
                            </td>
                            <td class="text-end">{{ $totals['total_enveloppe'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
    }
</style>
@endsection
