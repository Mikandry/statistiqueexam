<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
        @if(isset($allDrens))
            <select name="dren_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Toutes les DREN</option>
                @foreach($allDrens as $dren)<option value="{{ $dren->id }}" @selected((string)($selectedDrenId ?? '') === (string)$dren->id)>{{ $dren->nom }}</option>@endforeach
            </select>
        @endif
        @if(isset($allCiscos))
            <select name="cisco_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Toutes les CISCO</option>
                @foreach($allCiscos as $cisco)<option value="{{ $cisco->id }}" @selected((string)($selectedCiscoId ?? '') === (string)$cisco->id)>{{ $cisco->nom }}</option>@endforeach
            </select>
        @endif
        @if(isset($allCentres) && (empty($allCentres) || is_object($allCentres[0] ?? null)))
            <select name="centre_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tous les centres</option>
                @foreach($allCentres as $centre)<option value="{{ $centre->id }}" @selected((string)($selectedCentreId ?? '') === (string)$centre->id)>{{ $centre->nom }}</option>@endforeach
            </select>
        @endif
        <select name="exam" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous les examens</option>
            @foreach($exams as $exam)<option value="{{ $exam }}" @selected(($examFilter ?? '') === $exam)>{{ $exam }}</option>@endforeach
        </select>
        <select name="phase" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes les phases</option>
            @foreach($phases as $phase)<option value="{{ $phase }}" @selected(($phaseFilter ?? '') === $phase)>{{ str_replace('_', ' ', $phase) }}</option>@endforeach
        </select>
        <select name="activity_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes les activités</option>
            @foreach($filterActivities as $activity)<option value="{{ $activity->id }}" @selected((string)($activityFilter ?? '') === (string)$activity->id)>{{ $activity->examen }} - {{ $activity->libelle }}</option>@endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrer</button>
    </form>
</div>
