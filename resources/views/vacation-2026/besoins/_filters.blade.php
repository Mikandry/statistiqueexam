<form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
    <div><label class="block text-xs font-semibold text-slate-600">Examen</label>
        <select name="exam" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">CEPE + BEPC</option>
            @foreach($filterData['exams'] as $e)<option value="{{ $e }}" @selected(($filters['exam'] ?? '') === $e)>{{ $e }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs font-semibold text-slate-600">DREN</label>
        <select name="dren_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes</option>
            @foreach($filterData['drens'] as $d)<option value="{{ $d->id }}" @selected(($filters['dren_id'] ?? null) == $d->id)>{{ $d->nom }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs font-semibold text-slate-600">CISCO</label>
        <select name="cisco_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes</option>
            @foreach($filterData['ciscos'] as $c)
                @if(empty($filters['dren_id']) || $c->dren_id == $filters['dren_id'])
                    <option value="{{ $c->id }}" @selected(($filters['cisco_id'] ?? null) == $c->id)>{{ $c->nom }}</option>
                @endif
            @endforeach
        </select></div>
    <div><label class="block text-xs font-semibold text-slate-600">Niveau</label>
        <select name="level" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous</option>
            @foreach($filterData['levels'] as $k => $l)<option value="{{ $k }}" @selected(($filters['level'] ?? '') === $k)>{{ $l }}</option>@endforeach
        </select></div>
    <div><label class="block text-xs font-semibold text-slate-600">Phase</label>
        <select name="phase" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Toutes</option>
            @foreach($filterData['phases'] as $k => $l)<option value="{{ $k }}" @selected(($filters['phase'] ?? '') === $k)>{{ $l }}</option>@endforeach
        </select></div>
    @if(isset($filters['type']))
    <div><label class="block text-xs font-semibold text-slate-600">Type de centre</label>
        <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Tous</option>
            @foreach($filterData['centreTypes'] ?? [] as $t)<option value="{{ $t }}" @selected($filters['type'] === $t)>{{ $t }}</option>@endforeach
        </select></div>
    @endif
    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Filtrer</button>
</form>
