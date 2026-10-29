@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp
<div class="ui-grid c4">
    <div class="ui-kpi hero"><div class="l">Montant estimé</div><div class="v">{{ $n($totals['amount']) }}<small>Ar</small></div><div class="n">selon le décret et les barèmes saisis</div></div>
    <div class="ui-kpi blue"><div class="l">Personnes mobilisées</div><div class="v">{{ $n($totals['persons']) }}</div><div class="n">postes (une personne par activité)</div></div>
    <div class="ui-kpi violet"><div class="l">Jours-agents</div><div class="v">{{ $n($totals['person_days']) }}</div><div class="n">postes × jours</div></div>
    <div class="ui-kpi slate"><div class="l">Activités</div><div class="v">{{ $totals['activities'] }}</div><div class="n">du décret, pour cette sélection</div></div>
</div>
@if(($totals['missing_rates'] ?? 0) > 0)
    <div class="ui-note warn">{{ $totals['missing_rates'] }} code(s) de taux n'ont pas de taux saisi : montant partiel. <a href="{{ route('vacation2026.besoins.baremes') }}">Saisir les barèmes</a></div>
@endif
