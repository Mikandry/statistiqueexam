<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traçabilité automatique des compteurs</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    @include('partials.head-assets')
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1850px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Traçabilité automatique des compteurs</h1>
                            <p class="mt-1 text-sm text-slate-500">Regroupement des DREN, saisie des compteurs par groupe et division automatique des PE par centre.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.livre.controle', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Fiche contrôle</a>
                            <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.livre.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Retour livre</a>
                            <button type="button" id="pvWordBtn" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700" title="Télécharger le PV en Word">PV Word</button>
                            <button type="button" onclick="window.print()" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Imprimer</button>
                        </div>

                        <form id="pvExportForm" method="POST" action="{{ route('repartition.livre.pv.auto.word') }}" style="display:none;">
                            @csrf
                            <input type="hidden" name="annee" value="{{ $filters['annee'] }}">
                            <input type="hidden" name="type_examen" value="{{ $filters['type_examen'] }}">
                            <input type="hidden" name="dren" value="{{ $filters['dren'] }}">
                            <input type="hidden" id="compteurNames" name="compteur_names" value="">
                            <input type="hidden" id="drenAssignments" name="dren_assignments" value="">
                            <input type="hidden" id="groupNames" name="group_names" value="">
                        </form>
                    </div>
                </div>

                <div class="border-b border-slate-200 p-5 md:p-6 print:hidden">
                    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="annee">Année</label>
                            <select id="annee" name="annee" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>{{ $annee }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="type_examen">Type examen</label>
                            <select id="type_examen" name="type_examen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                                <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="dren">DREN</label>
                            <select id="dren" name="dren" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Toutes</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>{{ $dren }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Actualiser</button>
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="autoBalanceBtn" class="w-full rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Equilibrer</button>
                        </div>
                    </form>
                </div>

                <div class="p-5 md:p-6">
                    <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-6">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">DREN: <strong>{{ number_format($stats['total_drens'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Centres: <strong>{{ number_format($stats['total_centres'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Candidats: <strong>{{ number_format($stats['total_candidats'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Salles: <strong>{{ number_format($stats['total_salles'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">PE: <strong>{{ number_format($stats['total_pe'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">GE: <strong>{{ number_format($stats['total_ge'], 0, ',', ' ') }}</strong></div>
                    </div>

                    <section class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4 print:hidden">
                        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold text-indigo-950">Paramètres des groupes</h2>
                                <p class="mt-1 text-xs text-indigo-700">Chaque DREN est affectée à un groupe; chaque groupe possède sa propre liste de compteurs.</p>
                            </div>
                            <label class="text-sm font-semibold text-indigo-950">
                                Nombre de groupes
                                <input id="groupCount" class="ml-2 w-20 rounded border border-indigo-300 bg-white px-2 py-1 text-right text-sm" type="number" min="1" max="12" value="3">
                            </label>
                        </div>

                        <div id="groupInputs" class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-3"></div>

                        <div class="overflow-x-auto rounded-lg border border-indigo-200 bg-white">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-indigo-100 text-left text-indigo-950">
                                    <th class="border border-indigo-200 px-2 py-2">DREN</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">Candidats</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">Salles</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">PE</th>
                                    <th class="border border-indigo-200 px-2 py-2">Groupe</th>
                                </tr>
                                </thead>
                                <tbody id="drenAssignmentBody"></tbody>
                            </table>
                        </div>
                    </section>

                    <section class="mb-6">
                        <h2 class="mb-2 text-base font-bold">Total des DREN regroupées</h2>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border border-slate-200 px-2 py-2">Groupe</th>
                                    <th class="border border-slate-200 px-2 py-2">DREN incluses</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Centres</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Candidats</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Salles</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">PE</th>
                                    <th class="border border-slate-200 px-2 py-2">Compteurs</th>
                                </tr>
                                </thead>
                                <tbody id="groupRecapBody"></tbody>
                            </table>
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-2 text-base font-bold">Division automatique des PE par compteur et par centre</h2>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border border-slate-200 px-2 py-2">Groupe</th>
                                    <th class="border border-slate-200 px-2 py-2">DREN</th>
                                    <th class="border border-slate-200 px-2 py-2">CISCO</th>
                                    <th class="border border-slate-200 px-2 py-2">Centre écrit</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Candidats</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Salles</th>
                                    <th class="border border-slate-200 px-2 py-2">Compteur</th>
                                    <th class="border border-slate-200 px-2 py-2">PE affectés</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Nb PE</th>
                                </tr>
                                </thead>
                                <tbody id="autoPlanBody"></tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    (function () {
        const drenRows = @json($drenRows);
        const centreRows = @json($centreRows);
        const storageKey = 'controle_auto_' + btoa(unescape(encodeURIComponent(JSON.stringify(@json($filters)))));
        const groupCountEl = document.getElementById('groupCount');
        const groupInputs = document.getElementById('groupInputs');
        const drenAssignmentBody = document.getElementById('drenAssignmentBody');
        const groupRecapBody = document.getElementById('groupRecapBody');
        const autoPlanBody = document.getElementById('autoPlanBody');
        const autoBalanceBtn = document.getElementById('autoBalanceBtn');

        let state = readState();

        function readState() {
            try {
                return JSON.parse(localStorage.getItem(storageKey) || '{}');
            } catch (e) {
                return {};
            }
        }

        function saveState() {
            localStorage.setItem(storageKey, JSON.stringify(state));
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function numberFormat(value) {
            return new Intl.NumberFormat('fr-FR').format(Number(value || 0));
        }

        function groupCount() {
            return Math.max(1, Math.min(12, parseInt(groupCountEl.value || '1', 10) || 1));
        }

        function buildBalancedAssignments(count) {
            const loads = Array.from({ length: count }, (_, index) => ({ id: index + 1, pe: 0 }));
            const assignments = {};

            [...drenRows]
                .sort((a, b) => Number(b.total_pe || 0) - Number(a.total_pe || 0))
                .forEach((dren) => {
                    loads.sort((a, b) => a.pe - b.pe || a.id - b.id);
                    assignments[dren.dren] = loads[0].id;
                    loads[0].pe += Number(dren.total_pe || 0);
                });

            return assignments;
        }

        function ensureState() {
            const count = groupCount();
            state.groupNames = state.groupNames || {};
            state.groupCounters = state.groupCounters || {};
            state.assignments = state.assignments || buildBalancedAssignments(count);

            drenRows.forEach((dren) => {
                const current = parseInt(state.assignments[dren.dren] || '0', 10);
                if (current < 1 || current > count) {
                    state.assignments[dren.dren] = 1;
                }
            });

            for (let id = 1; id <= count; id++) {
                state.groupNames[id] = state.groupNames[id] || `Groupe ${id}`;
                state.groupCounters[id] = state.groupCounters[id] || `Compteur ${id}`;
            }
        }

        function countersForGroup(groupId) {
            const raw = String(state.groupCounters[groupId] || '').split('\n').map((name) => name.trim()).filter(Boolean);
            return raw.length ? raw : [`Compteur ${groupId}`];
        }

        function groupName(groupId) {
            return String(state.groupNames[groupId] || `Groupe ${groupId}`).trim() || `Groupe ${groupId}`;
        }

        function renderGroupInputs() {
            const count = groupCount();
            groupInputs.innerHTML = Array.from({ length: count }, (_, index) => {
                const id = index + 1;
                return `
                    <div class="rounded-lg border border-indigo-200 bg-white p-3">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-indigo-900">Nom du groupe</label>
                        <input class="mb-3 w-full rounded border border-indigo-200 px-2 py-1 text-sm" data-group-name="${id}" value="${escapeHtml(groupName(id))}">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-indigo-900">Noms des compteurs</label>
                        <textarea class="h-24 w-full rounded border border-indigo-200 px-2 py-2 text-sm" data-group-counters="${id}" placeholder="Un compteur par ligne">${escapeHtml(state.groupCounters[id] || '')}</textarea>
                    </div>
                `;
            }).join('');

            groupInputs.querySelectorAll('[data-group-name]').forEach((input) => {
                input.addEventListener('input', function () {
                    state.groupNames[this.dataset.groupName] = this.value;
                    saveState();
                    renderComputedTables();
                });
            });

            groupInputs.querySelectorAll('[data-group-counters]').forEach((textarea) => {
                textarea.addEventListener('input', function () {
                    state.groupCounters[this.dataset.groupCounters] = this.value;
                    saveState();
                    renderComputedTables();
                });
            });
        }

        function renderAssignments() {
            const count = groupCount();
            drenAssignmentBody.innerHTML = drenRows.map((dren) => {
                const selected = parseInt(state.assignments[dren.dren] || '1', 10);
                const options = Array.from({ length: count }, (_, index) => {
                    const id = index + 1;
                    return `<option value="${id}" ${selected === id ? 'selected' : ''}>${escapeHtml(groupName(id))}</option>`;
                }).join('');

                return `
                    <tr>
                        <td class="border border-indigo-200 px-2 py-2 font-semibold text-indigo-950">${escapeHtml(dren.dren)}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">${numberFormat(dren.total_candidats)}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">${numberFormat(dren.total_salles)}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">${numberFormat(dren.total_pe)}</td>
                        <td class="border border-indigo-200 px-2 py-2">
                            <select class="w-full rounded border border-indigo-200 bg-white px-2 py-1 text-sm" data-dren-assignment="${escapeHtml(dren.dren)}">${options}</select>
                        </td>
                    </tr>
                `;
            }).join('') || '<tr><td colspan="5" class="border border-indigo-200 px-3 py-5 text-center text-indigo-700">Aucune DREN.</td></tr>';

            drenAssignmentBody.querySelectorAll('[data-dren-assignment]').forEach((select) => {
                select.addEventListener('change', function () {
                    state.assignments[this.dataset.drenAssignment] = parseInt(this.value, 10);
                    saveState();
                    renderComputedTables();
                });
            });
        }

        function computeGroups() {
            const count = groupCount();
            const groups = Array.from({ length: count }, (_, index) => ({
                id: index + 1,
                name: groupName(index + 1),
                counters: countersForGroup(index + 1),
                drens: [],
                centres: [],
                candidats: 0,
                salles: 0,
                pe: 0,
            }));

            drenRows.forEach((dren) => {
                const groupId = parseInt(state.assignments[dren.dren] || '1', 10);
                const group = groups[Math.max(1, Math.min(count, groupId)) - 1];
                group.drens.push(dren.dren);
                group.candidats += Number(dren.total_candidats || 0);
                group.salles += Number(dren.total_salles || 0);
                group.pe += Number(dren.total_pe || 0);
            });

            centreRows.forEach((centre) => {
                const groupId = parseInt(state.assignments[centre.dren] || '1', 10);
                groups[Math.max(1, Math.min(count, groupId)) - 1].centres.push(centre);
            });

            return groups;
        }

        function peRange(start, count) {
            if (count <= 0) return '-';
            const end = start + count - 1;
            return start === end ? `PE${start}` : `PE${start}-PE${end}`;
        }

        function splitCentrePe(centre, counters) {
            const total = Number(centre.pe || 0);
            const base = Math.floor(total / counters.length);
            let extra = total % counters.length;
            let cursor = 1;

            return counters.map((counter) => {
                const count = base + (extra > 0 ? 1 : 0);
                if (extra > 0) extra--;
                const range = peRange(cursor, count);
                cursor += count;
                return { counter, count, range };
            }).filter((line) => line.count > 0);
        }

        function renderComputedTables() {
            const groups = computeGroups();

            groupRecapBody.innerHTML = groups.map((group) => `
                <tr>
                    <td class="border border-slate-200 px-2 py-2 font-semibold">${escapeHtml(group.name)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-xs">${group.drens.length ? group.drens.map(escapeHtml).join(', ') : '-'}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(group.centres.length)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(group.candidats)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(group.salles)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(group.pe)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-xs">${group.counters.map(escapeHtml).join('<br>')}</td>
                </tr>
            `).join('') || '<tr><td colspan="7" class="border border-slate-200 px-3 py-5 text-center text-slate-500">Aucune donnée.</td></tr>';

            const planRows = [];
            groups.forEach((group) => {
                group.centres
                    .sort((a, b) => `${a.dren}|${a.cisco}|${a.centre_ecrit}`.localeCompare(`${b.dren}|${b.cisco}|${b.centre_ecrit}`))
                    .forEach((centre) => {
                        splitCentrePe(centre, group.counters).forEach((line) => {
                            planRows.push({ group, centre, line });
                        });
                    });
            });

            autoPlanBody.innerHTML = planRows.map(({ group, centre, line }) => `
                <tr>
                    <td class="border border-slate-200 px-2 py-2 font-semibold">${escapeHtml(group.name)}</td>
                    <td class="border border-slate-200 px-2 py-2">${escapeHtml(centre.dren)}</td>
                    <td class="border border-slate-200 px-2 py-2">${escapeHtml(centre.cisco)}</td>
                    <td class="border border-slate-200 px-2 py-2">${escapeHtml(centre.centre_ecrit)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(centre.total_candidats)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(centre.total_salles)}</td>
                    <td class="border border-slate-200 px-2 py-2">${escapeHtml(line.counter)}</td>
                    <td class="border border-slate-200 px-2 py-2">${escapeHtml(line.range)}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">${numberFormat(line.count)}</td>
                </tr>
            `).join('') || '<tr><td colspan="9" class="border border-slate-200 px-3 py-5 text-center text-slate-500">Aucune donnée.</td></tr>';
        }

        function renderAll() {
            ensureState();
            saveState();
            renderGroupInputs();
            renderAssignments();
            renderComputedTables();
        }

        groupCountEl.value = state.groupCount || 3;
        groupCountEl.addEventListener('input', function () {
            state.groupCount = groupCount();
            saveState();
            renderAll();
        });

        autoBalanceBtn.addEventListener('click', function () {
            state.assignments = buildBalancedAssignments(groupCount());
            saveState();
            renderAll();
        });

        // Handle PV Word export
        document.getElementById('pvWordBtn').addEventListener('click', function () {
            document.getElementById('compteurNames').value = JSON.stringify(state.groupCounters || {});
            document.getElementById('drenAssignments').value = JSON.stringify(state.assignments || {});
            document.getElementById('groupNames').value = JSON.stringify(state.groupNames || {});
            document.getElementById('pvExportForm').submit();
        });

        renderAll();
    })();
</script>
</body>
</html>
