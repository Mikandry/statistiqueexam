<form method="GET" class="ui-card">
    <div class="ui-filter">
        <label class="ui-field"><span>Examen</span>
            <select name="exam" class="ui-select" onchange="this.form.submit()">
                <option value="">CEPE + BEPC</option>
                @foreach($filterData['exams'] as $e)<option value="{{ $e }}" @selected(($filters['exam'] ?? '') === $e)>{{ $e }}</option>@endforeach
            </select></label>
        <label class="ui-field"><span>Province</span>
            <select name="province_id" class="ui-select" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach($filterData['provinces'] as $p)<option value="{{ $p->id }}" @selected(($filters['province_id'] ?? null) == $p->id)>{{ $p->nom }}</option>@endforeach
            </select></label>
        <label class="ui-field"><span>DREN</span>
            <select name="dren_id" class="ui-select" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach($filterData['drens'] as $d)
                    @if(empty($filters['province_id']) || (int) $d->province_id === (int) $filters['province_id'])
                        <option value="{{ $d->id }}" @selected(($filters['dren_id'] ?? null) == $d->id)>{{ $d->nom }}</option>
                    @endif
                @endforeach
            </select></label>
        <label class="ui-field"><span>CISCO</span>
            <select name="cisco_id" class="ui-select" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach($filterData['ciscos'] as $c)
                    @if(empty($filters['dren_id']) || (int) $c->dren_id === (int) $filters['dren_id'])
                        <option value="{{ $c->id }}" @selected(($filters['cisco_id'] ?? null) == $c->id)>{{ $c->nom }}</option>
                    @endif
                @endforeach
            </select></label>
        <label class="ui-field"><span>Niveau</span>
            <select name="level" class="ui-select" onchange="this.form.submit()">
                <option value="">Tous</option>
                @foreach($filterData['levels'] as $k => $l)<option value="{{ $k }}" @selected(($filters['level'] ?? '') === $k)>{{ $l }}</option>@endforeach
            </select></label>
        <label class="ui-field"><span>Phase</span>
            <select name="phase" class="ui-select" onchange="this.form.submit()">
                <option value="">Toutes</option>
                @foreach($filterData['phases'] as $k => $l)<option value="{{ $k }}" @selected(($filters['phase'] ?? '') === $k)>{{ $l }}</option>@endforeach
            </select></label>
        @if(isset($filters['type']))
        <label class="ui-field"><span>Type de centre</span>
            <select name="type" class="ui-select" onchange="this.form.submit()">
                <option value="">Tous</option>
                @foreach($filterData['centreTypes'] ?? [] as $t)<option value="{{ $t }}" @selected($filters['type'] === $t)>{{ $t }}</option>@endforeach
            </select></label>
        @endif
        <a class="ui-btn ghost" href="{{ url()->current() }}">Réinitialiser</a>
    </div>
</form>
