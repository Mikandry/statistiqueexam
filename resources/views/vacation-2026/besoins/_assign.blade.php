@php $n = fn ($v) => number_format((float) $v, 0, ',', ' '); $gap = max(0, $totals['persons'] - $assign['assigned']); $pct = $totals['persons'] > 0 ? min(100, round($assign['assigned'] / $totals['persons'] * 100)) : 0; @endphp
<div class="ui-card">
    <div class="ui-card-h"><h2>Besoin, affectation, validation, paiement</h2><small>Quatre notions distinctes, jamais additionnées</small></div>
    <div class="ui-card-b pad">
        <div class="ui-grid c5 keep2">
            <div><div class="ui-label">Besoin (postes)</div><div style="font-size:1.4rem;font-weight:750">{{ $n($totals['persons']) }}</div><div class="ui-muted ui-sm">calculé depuis le décret</div></div>
            <div><div class="ui-label">Affectés</div><div style="font-size:1.4rem;font-weight:750">{{ $n($assign['assigned']) }}</div><div class="ui-muted ui-sm">agents distincts saisis</div></div>
            <div><div class="ui-label">Validés</div><div style="font-size:1.4rem;font-weight:750">{{ $n($assign['validated']) }}</div><div class="ui-muted ui-sm">affectations validées</div></div>
            <div><div class="ui-label">Reste à affecter</div><div style="font-size:1.4rem;font-weight:750">{{ $n($gap) }}</div>
                <div class="ui-bar {{ $pct < 50 ? 'amber' : '' }}" style="margin-top:6px"><i style="width: {{ $pct }}%"></i></div></div>
            <div><div class="ui-label">Montant validé</div><div style="font-size:1.4rem;font-weight:750">{{ $n($assign['validated_amount']) }} <small class="ui-muted">Ar</small></div><div class="ui-muted ui-sm">Paiement : non suivi dans l'application</div></div>
        </div>
    </div>
</div>
