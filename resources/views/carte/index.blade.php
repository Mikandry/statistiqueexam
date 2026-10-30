@extends('layouts.app')
@section('title', 'Carte des centres')
@section('content')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<style>
    #carte{height:68vh;min-height:420px;border-radius:12px;border:1px solid #e2e8f0;background:#eef2f7;z-index:0}
    .carte-pop h3{margin:0 0 4px;font-size:14px}.carte-pop table{border-collapse:collapse;width:100%;font-size:12px}
    .carte-pop td{padding:2px 0}.carte-pop td:last-child{text-align:right;font-weight:600;padding-left:12px}
    .carte-leg span{display:inline-flex;align-items:center;gap:6px;margin-right:14px;font-size:12px}
    .carte-leg i{width:12px;height:12px;border-radius:50%;display:inline-block;border:2px solid #fff;box-shadow:0 0 0 1px #64748b}
</style>
<div class="ui-page">
    <div class="ui-head"><div><h1 class="ui-title">Carte des centres</h1>
        <p class="ui-sub">Régions de Madagascar, centres d'écrit / de correction / transcription et trajet du dispatching par axe.</p></div>
        <div class="ui-actions">
            @if($canEdit)<a class="ui-btn accent" href="{{ route('carte.coordonnees') }}">Coordonnées et paramètres</a>@endif
        </div>
    </div>

    <form method="GET" class="ui-card"><div class="ui-card-b pad"><div class="ui-grid c4">
        <label class="ui-field"><span>Examen</span><select name="exam" class="ui-input" onchange="this.form.submit()">
            <option value="">Tous</option>@foreach(['CEPE','BEPC'] as $e)<option value="{{ $e }}" @selected($filters['exam']===$e)>{{ $e }}</option>@endforeach</select></label>
        <label class="ui-field"><span>DREN</span><select name="dren_id" class="ui-input" onchange="this.form.submit()">
            <option value="">Toutes</option>@foreach($drens as $d)<option value="{{ $d->id }}" @selected((int)$filters['dren_id']===$d->id)>{{ $d->nom }}</option>@endforeach</select></label>
        <label class="ui-field"><span>Type de centre</span><select name="type" class="ui-input" onchange="this.form.submit()">
            <option value="">Tous</option>
            @foreach(['ecrit'=>"Écrit seul",'correction'=>'Correction seule','ecrit_correction'=>'Écrit + correction','sous_centre'=>'Sous-centre','transcription'=>'Transcription','eps'=>'EPS'] as $k=>$l)
                <option value="{{ $k }}" @selected($filters['type']===$k)>{{ $l }}</option>@endforeach</select></label>
        <label class="ui-field"><span>Affichage</span><select id="mode" class="ui-input">
            <option value="centres">Centres</option><option value="dispatch">Centres + trajet du dispatching</option></select></label>
    </div></div></form>

    <div class="ui-grid c4">
        <div class="ui-kpi"><b>{{ number_format($payload['totals']['located'],0,',',' ') }}</b><span>centres sur la carte</span></div>
        <div class="ui-kpi"><b>{{ count($payload['without_coordinates']) }}</b><span>sans coordonnées</span></div>
        <div class="ui-kpi"><b>{{ number_format($payload['totals']['candidates'],0,',',' ') }}</b><span>candidats localisés</span></div>
        <div class="ui-kpi"><b>{{ number_format($payload['totals']['salles'],0,',',' ') }}</b><span>salles localisées</span></div>
    </div>

    <div class="ui-card"><div class="ui-card-b pad">
        <div class="ui-row" style="margin-bottom:8px">
            <div class="carte-leg">
                <span><i style="background:#2563eb"></i>Écrit</span><span><i style="background:#dc2626"></i>Correction</span>
                <span><i style="background:#7c3aed"></i>Écrit + correction</span><span><i style="background:#0891b2"></i>Sous-centre</span>
                <span><i style="background:#16a34a"></i>Transcription</span><span><i style="background:#ea580c"></i>EPS</span>
            </div>
            <span class="ui-spacer"></span><span id="geo-source" class="ui-muted ui-sm"></span>
        </div>
        <div id="carte"></div>
        <div id="axes" class="ui-muted ui-sm" style="margin-top:8px"></div>
    </div></div>

    <div class="ui-card"><div class="ui-card-h">Candidats par région et par axe</div>
        <p class="ui-sm ui-muted" style="padding:0 16px">Un centre est compté dans la région qui contient ses coordonnées. Les centres sans coordonnées ne sont pas comptés.</p>
        <div class="ui-table-wrap"><table class="ui-table"><thead><tr id="region-head"></tr></thead><tbody id="region-rows"><tr><td class="ui-muted">Chargement…</td></tr></tbody></table></div>
        <p class="ui-sm" id="region-note" style="padding:0 16px"></p></div>

    @if(count($payload['without_coordinates']))
    <details class="ui-details"><summary>{{ count($payload['without_coordinates']) }} centre(s) sans coordonnées</summary>
        <div class="ui-table-wrap"><table class="ui-table"><thead><tr><th>Centre</th><th>CISCO</th><th>Rôle</th></tr></thead><tbody>
        @foreach($payload['without_coordinates'] as $c)<tr><td>{{ $c['nom'] }}</td><td>{{ $c['cisco'] }}</td><td>{{ $c['role_label'] }}</td></tr>@endforeach
        </tbody></table></div>
        @if($canEdit)<p class="ui-sm"><a href="{{ route('carte.coordonnees') }}">Saisir les coordonnées</a></p>@endif
    </details>
    @endif
</div>
@endsection
@push('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script>
(function () {
    const D = @json($payload);
    const COL = {ecrit:'#2563eb',correction:'#dc2626',ecrit_correction:'#7c3aed',sous_centre:'#0891b2',transcription:'#16a34a',eps:'#ea580c'};
    const AXE_COL = ['#0f766e','#b45309','#be123c','#4338ca','#15803d','#a21caf','#0369a1','#854d0e'];
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const nf = n => Number(n || 0).toLocaleString('fr-FR');
    const map = L.map('carte', {zoomControl: true}).setView([-19.0, 46.7], 6);
    L.control.scale({imperial:false}).addTo(map);

    // Fond : tuiles OSM seulement si autorisé ET en ligne ; sinon les régions suffisent (hors-ligne).
    if (D.geo.online && navigator.onLine) {
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 17, attribution: '© OpenStreetMap'}).addTo(map);
    }

    // --- Totaux par région : chaque centre localisé est rattaché à la région qui le contient ---
    function ring(pt, r) { let inside = false; for (let i = 0, j = r.length - 1; i < r.length; j = i++) {
        const xi = r[i][0], yi = r[i][1], xj = r[j][0], yj = r[j][1];
        if (((yi > pt[1]) !== (yj > pt[1])) && (pt[0] < (xj - xi) * (pt[1] - yi) / (yj - yi) + xi)) inside = !inside; } return inside; }
    function polys(g) { return g.type === 'Polygon' ? [g.coordinates] : g.type === 'MultiPolygon' ? g.coordinates : []; }
    function contains(g, pt) { return polys(g).some(p => ring(pt, p[0]) && !p.slice(1).some(h => ring(pt, h))); }
    function nearDist(g, pt) { let m = 1e9; polys(g).forEach(p => p[0].forEach(v => { const d = Math.hypot(v[0]-pt[0], v[1]-pt[1]); if (d < m) m = d; })); return m; }
    function regionIndex(features, lat, lng) {
        const pt = [lng, lat]; let k = features.findIndex(f => contains(f.geometry, pt));
        if (k >= 0) return k;
        let best = -1, bd = 0.25; // côte simplifiée : on tolère un point juste en mer
        features.forEach((f, i) => { const d = nearDist(f.geometry, pt); if (d < bd) { bd = d; best = i; } });
        return best;
    }
    const OWN = ['ecrit', 'ecrit_correction', 'sous_centre']; // seuls les centres d'écrit portent des candidats propres
    function aggregate(features) {
        const R = features.map(f => ({nom: f.properties.nom || f.properties.shapeName || f.properties.region || '—', candidates: 0, salles: 0, centres: 0, axes: {}}));
        let outside = 0;
        D.centres.filter(c => OWN.includes(c.role)).forEach(c => {
            const k = regionIndex(features, c.lat, c.lng); if (k < 0) { outside++; return; }
            R[k].candidates += c.candidates; R[k].salles += c.salles; R[k].centres++;
        });
        D.dispatching.axes.forEach(a => a.points.forEach(p => p.centres.forEach(c => {
            if (c.lat === null) return; const k = regionIndex(features, c.lat, c.lng); if (k < 0) return;
            R[k].axes[a.axe] = (R[k].axes[a.axe] || 0) + c.candidates;
        })));
        return {R, outside};
    }
    function regionPopup(r) {
        let h = '<div class="carte-pop"><h3>' + esc(r.nom) + '</h3><table>' + row('Candidats (centres d\'écrit localisés)', nf(r.candidates))
            + row('Centres d\'écrit', nf(r.centres)) + row('Salles', nf(r.salles)) + '</table>';
        const ax = Object.entries(r.axes).sort((a, b) => a[0].localeCompare(b[0], 'fr', {numeric: true}));
        h += ax.length ? '<div style="margin-top:6px;font-weight:600">Par axe de dispatching</div><table>' + ax.map(([a, n]) => row(esc(a), nf(n))).join('') + '</table>'
            : '<div class="ui-muted" style="font-size:11px;margin-top:6px">Aucun axe de dispatching dans cette région.</div>';
        return h + '</div>';
    }
    function drawRegions(gj, label) {
        const feats = gj.features || [];
        const {R, outside} = aggregate(feats);
        const max = Math.max(1, ...R.map(r => r.candidates));
        L.geoJSON(gj, {
            style: (f) => { const r = R[feats.indexOf(f)]; const t = r.candidates / max;
                return {color:'#475569', weight:1.2, fillColor: t > 0 ? '#2563eb' : '#cbd5e1', fillOpacity: t > 0 ? 0.10 + 0.45 * t : 0.25}; },
            onEachFeature: (f, l) => { const r = R[feats.indexOf(f)];
                l.bindTooltip('<b>' + esc(r.nom) + '</b><br>' + nf(r.candidates) + ' candidats', {sticky: true});
                l.bindPopup(regionPopup(r), {maxWidth: 320});
                l.on('mouseover', () => l.setStyle({weight: 2.6})); l.on('mouseout', () => l.setStyle({weight: 1.2})); }
        }).addTo(map).bringToBack();
        document.getElementById('geo-source').textContent = 'Régions : ' + label;
        const body = document.getElementById('region-rows');
        if (body) {
            const axes = D.dispatching.axes.map(a => a.axe);
            document.getElementById('region-head').innerHTML = '<th>Région</th><th>Centres d\'écrit</th><th>Salles</th><th>Candidats</th>' + axes.map(a => '<th>' + esc(a) + '</th>').join('');
            body.innerHTML = R.slice().sort((a, b) => b.candidates - a.candidates).map(r => '<tr><td>' + esc(r.nom) + '</td><td>' + nf(r.centres) + '</td><td>' + nf(r.salles) + '</td><td><b>' + nf(r.candidates) + '</b></td>'
                + axes.map(a => '<td>' + nf(r.axes[a] || 0) + '</td>').join('') + '</tr>').join('')
                + '<tr><td><b>Total</b></td><td><b>' + nf(R.reduce((s, r) => s + r.centres, 0)) + '</b></td><td><b>' + nf(R.reduce((s, r) => s + r.salles, 0)) + '</b></td><td><b>' + nf(R.reduce((s, r) => s + r.candidates, 0)) + '</b></td>'
                + axes.map(a => '<td><b>' + nf(R.reduce((s, r) => s + (r.axes[a] || 0), 0)) + '</b></td>').join('') + '</tr>';
            if (outside) document.getElementById('region-note').textContent = outside + ' centre(s) hors de toute région (coordonnées à vérifier) non comptés.';
        }
    }
    async function getJson(url, ms) {
        const c = new AbortController(); const t = setTimeout(() => c.abort(), ms);
        try { const r = await fetch(url, {signal: c.signal}); if (!r.ok) throw new Error(r.status); return await r.json(); } finally { clearTimeout(t); }
    }
    (async function loadRegions() {
        if (D.geo.online && navigator.onLine && D.geo.online_url) {
            try { drawRegions(await getJson(D.geo.online_url, 6000), 'source en ligne'); return; } catch (e) {}
        }
        try { drawRegions(await getJson(D.geo.local_url, 15000), 'fichier local (hors-ligne)'); return; } catch (e) {}
        try { drawRegions(await getJson(D.geo.local_fallback_url, 15000), 'fichier local de secours'); } catch (e) {
            document.getElementById('geo-source').textContent = 'Régions indisponibles';
        }
    })();

    function row(l, v) { return '<tr><td>' + l + '</td><td>' + v + '</td></tr>'; }
    function popup(c) {
        let h = '<div class="carte-pop"><h3>' + esc(c.nom) + '</h3><div style="margin-bottom:6px"><b>' + esc(c.role_label) + '</b>';
        if (c.parent_nom) h += ' — rattaché à ' + esc(c.parent_nom);
        h += '<br>' + esc(c.cisco) + (c.dren ? ' · ' + esc(c.dren) : '') + (c.province ? ' · ' + esc(c.province) : '') + '</div><table>';
        if (c.kind === 'CENTRE') {
            h += row('Salles', nf(c.salles)) + row('Candidats', nf(c.candidates));
            if (c.corrected) h += row('Candidats corrigés', nf(c.corrected));
        } else { h += row('Candidats', nf(c.candidates)); }
        if (c.staff) {
            h += '<tr><td colspan="2" style="padding-top:6px;color:#64748b">Agents requis (décret)</td></tr>'
              + row('Avant la session', nf(c.staff.avant)) + row('Pendant la session', nf(c.staff.pendant)) + row('Après la session', nf(c.staff.apres));
            if (c.staff.pendant_apres) h += row('Pendant + après', nf(c.staff.pendant_apres));
            if (c.staff.speciale) h += row('Missions spéciales', nf(c.staff.speciale));
            h += row('Total des postes', nf(c.staff.total)) + row('Affectés / validés', nf(c.staff.assigned) + ' / ' + nf(c.staff.validated));
        }
        return h + '</table></div>';
    }
    const pts = [];
    D.centres.forEach(c => {
        const m = L.circleMarker([c.lat, c.lng], {radius: 7, color:'#fff', weight: 2, fillColor: COL[c.role] || '#334155', fillOpacity: .95});
        m.bindPopup(popup(c), {maxWidth: 320}).bindTooltip(esc(c.nom));
        m.addTo(map); pts.push([c.lat, c.lng]);
    });
    const dispatchLayer = L.layerGroup();
    (function dispatch() {
        const d = D.dispatching; const legend = [];
        const dep = (d.depart && d.depart.lat !== null && d.depart.lng !== null) ? [d.depart.lat, d.depart.lng] : null;
        if (dep) L.marker(dep).bindPopup('<b>Départ</b><br>' + esc(d.depart.nom)).addTo(dispatchLayer);
        d.axes.forEach((a, i) => {
            const col = AXE_COL[i % AXE_COL.length];
            const line = dep ? [dep] : [];
            let missing = 0;
            a.points.forEach(p => {
                if (p.lat === null) { missing++; return; }
                line.push([p.lat, p.lng]);
                L.marker([p.lat, p.lng], {icon: L.divIcon({className:'', html:'<div style="background:'+col+';color:#fff;border-radius:50%;width:22px;height:22px;line-height:22px;text-align:center;font-size:11px;font-weight:700;border:2px solid #fff;box-shadow:0 0 0 1px '+col+'">'+p.order+'</div>', iconSize:[22,22], iconAnchor:[11,11]})})
                  .bindPopup('<div class="carte-pop"><h3>' + esc(a.axe) + ' · point ' + p.order + '</h3><b>' + esc(p.point) + '</b><table>'
                    + row('Centres desservis', nf(p.centres.length)) + row('Candidats', nf(p.candidates)) + row('Salles', nf(p.salles))
                    + '</table><div class="ui-muted" style="font-size:11px;margin-top:4px">Position : ' + (p.source === 'saisi' ? 'saisie' : 'centre de gravité des centres desservis') + '</div></div>')
                  .addTo(dispatchLayer);
            });
            if (line.length > 1) L.polyline(line, {color: col, weight: 3, opacity: .85, dashArray: '6 6'}).bindTooltip(esc(a.axe)).addTo(dispatchLayer);
            legend.push('<span style="color:'+col+'">■</span> ' + esc(a.axe) + ' (' + a.points.length + ' point(s), ' + nf(a.candidates) + ' candidats' + (missing ? ', ' + missing + ' sans position' : '') + (a.ordered_by_user ? '' : ', ordre alphabétique') + ')');
        });
        document.getElementById('axes').innerHTML = legend.length ? '<b>Axes :</b> ' + legend.join(' &nbsp; ') : 'Aucun axe de dispatching enregistré.';
    })();
    const mode = document.getElementById('mode');
    function applyMode() { if (mode.value === 'dispatch') dispatchLayer.addTo(map); else map.removeLayer(dispatchLayer); }
    mode.addEventListener('change', applyMode); applyMode();
    if (pts.length) map.fitBounds(pts, {padding: [40, 40], maxZoom: 10});
})();
</script>
@endpush
