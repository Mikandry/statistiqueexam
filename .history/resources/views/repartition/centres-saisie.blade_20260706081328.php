@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h1 class="mb-1">Centres Saisis et Non Saisis</h1>
            <p class="text-muted mb-0">Total: {{ $totalCentres }} | Saisis: {{ $totalSaisis }} | Non saisis: {{ $totalNonSaisis }}</p>
        </div>
        <a href="{{ route('repartition.centres.saisie.pdf', request()->query()) }}" class="btn btn-danger">
            Télécharger en PDF
        </a>
    </div>

    <form method="GET" action="{{ route('repartition.centres.saisie') }}">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="type" class="form-label">Type d'examen</label>
                <select id="type" name="type" class="form-select">
                    <option value="" @selected($selectedType === '')>Tous</option>
                    <option value="BEPC" @selected($selectedType === 'BEPC')>BEPC</option>
                    <option value="CEPE" @selected($selectedType === 'CEPE')>CEPE</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="region" class="form-label">Région</label>
                <select id="region" name="region" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($drens as $dren)
                        <option value="{{ $dren }}" @selected($selectedRegion === $dren)>{{ $dren }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="annee" class="form-label">Année</label>
                <input list="annees-list" id="annee" name="annee" class="form-control" value="{{ $selectedAnnee ?? '' }}" placeholder="{{ date('Y') + 1 }}">
                <datalist id="annees-list">
                    @if(!empty($annees ?? []))
                        @foreach($annees as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    @endif
                </datalist>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-6">
            <h2>Centres Saisis</h2>
            <ul>
                @foreach($centresSaisis as $centre)
                    <li>{{ $centre->nom }} ({{ $centre->region }})</li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <h2>Centres Non Saisis</h2>
            <ul>
                @foreach($centresNonSaisis as $centre)
                    <li>{{ $centre->nom }} ({{ $centre->region }})</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
