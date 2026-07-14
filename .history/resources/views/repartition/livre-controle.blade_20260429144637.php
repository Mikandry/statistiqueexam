<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Traçabilité et de Contrôle de Traçabilité</title>
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
                            <h1 class="text-2xl font-bold tracking-tight">Fiche de Traçabilité et de Contrôle de Traçabilité</h1>
                            <p class="mt-1 text-sm text-slate-500">Traçabilité des dépôts de sujets par PE et par GE (saisie manuelle des compteurs).</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('repartition.livre.preview', ['annee' => $filters['annee'], 'type_examen' => $filters['type_examen'], 'dren' => $filters['dren']]) }}">Retour Livre</a>
                            <form id="wordExportForm" method="POST" action="{{ route('repartition.livre.controle.word') }}" class="inline-flex">
                                @csrf
                                <input type="hidden" name="annee" value="{{ $filters['annee'] }}">
                                <input type="hidden" name="type_examen" value="{{ $filters['type_examen'] }}">
                                <input type="hidden" name="dren" value="{{ $filters['dren'] }}">
                                <input type="hidden" name="trace_payload" id="tracePayloadInput" value="">
                                <button type="submit" class="rounded-lg border border-blue-300 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">Export Word</button>
                            </form>
                            <button type="button" onclick="window.print()" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Imprimer</button>
                        </div>
                    </div>
                </div>

                <div class="border-b border-slate-200 p-5 md:p-6">
                    <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-4">
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
                    </form>
                </div>

                <div class="p-5 md:p-6">
                    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">Centres: <strong>{{ number_format($stats['total_centres'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">PE total: <strong>{{ number_format($stats['total_pe'], 0, ',', ' ') }}</strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">GE total: <strong>{{ number_format($stats['total_ge'], 0, ',', ' ') }}</strong></div>
                    </div>

                    <div class="mb-8 rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
                        <h2 class="mb-2 text-base font-bold text-indigo-900">Configuration des Groupes de Compteurs</h2>
                        <p class="mb-3 text-xs text-indigo-700">Configurez les groupes de compteurs. Les régions seront automatiquement distribuées entre les groupes.</p>
                        <div class="mb-3 flex items-center gap-3">
                            <label class="text-sm font-medium text-indigo-900" for="groupMode">Mode de répartition:</label>
                            <select id="groupMode" class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm">
                                <option value="DREN">Par DREN</option>
                                <option value="CISCO">Par CISCO</option>
                                <option value="CENTRE_ECRIT">Par centre</option>
                            </select>
                        </div>
                        <div id="globalCounterDiv" class="mb-3 flex items-center gap-3" style="display: none;">
                            <label class="text-sm font-medium text-indigo-900" for="globalCounter">Nombre de compteurs global:</label>
                            <input id="globalCounter" class="w-20 rounded border border-indigo-300 px-2 py-1 text-right text-xs" type="number" min="0" value="1">
                        </div>
                        <div class="mb-3">
                            <label class="text-sm font-medium text-indigo-900" for="groupCount">Nombre de groupes:</label>
                            <input id="groupCount" class="w-20 rounded border border-indigo-300 px-2 py-1 text-right text-xs" type="number" min="1" value="1">
                        </div>
                        <div id="groupsContainer" class="mb-3 space-y-3">
                            <!-- Groups will be dynamically added here -->
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-indigo-100 text-left">
                                    <th class="border border-indigo-200 px-2 py-2">Groupe</th>
                                    <th class="border border-indigo-200 px-2 py-2">Régions assignées</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">Total PE</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">Nb compteurs</th>
                                    <th class="border border-indigo-200 px-2 py-2 text-right">PE / compteur</th>
                                    <th class="border border-indigo-200 px-2 py-2">Répartition des plages PE</th>
                                </tr>
                                </thead>
                                <tbody id="compteurMatrixBody">
                                <tr>
                                    <td colspan="6" class="border border-indigo-200 px-2 py-4 text-center text-indigo-700">Chargement...</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="mb-2 text-base font-bold">Fiche Contrôle PE (dépôt des sujets par PE)</h2>
                        <p class="mb-3 text-xs text-slate-500">Saisissez manuellement le compteur et les signatures. Les valeurs sont conservées localement dans ce navigateur.</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border border-slate-200 px-2 py-2">DREN</th>
                                    <th class="border border-slate-200 px-2 py-2">CISCO</th>
                                    <th class="border border-slate-200 px-2 py-2">Centre écrit</th>
                                    <th class="border border-slate-200 px-2 py-2">Examen</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">PE</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">GE</th>
                                    <th class="border border-slate-200 px-2 py-2">Compteur</th>
                                    <th class="border border-slate-200 px-2 py-2">Matière/LV</th>
                                    <th class="border border-slate-200 px-2 py-2">Date/Heure</th>
                                    <th class="border border-slate-200 px-2 py-2">Agent</th>
                                    <th class="border border-slate-200 px-2 py-2">Observation</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($peRows as $idx => $row)
                                    @php($baseKey = implode('|', ['PE', $row['centre_idx'], $row['type_examen'], $row['pe_no']]))
                                    <tr>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['dren'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['cisco'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['centre_ecrit'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['type_examen'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $row['pe_no'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $row['ge_no'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:compteur" class="w-28 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="N° compteur"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:matiere" class="w-40 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="Matière / LV"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:datetime" class="w-40 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="JJ/MM/AAAA HH:mm"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:agent" class="w-32 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="Nom agent"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:obs" class="w-48 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="Observation"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="border border-slate-200 px-3 py-5 text-center text-slate-500">Aucune donnée.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-2 text-base font-bold">Fiche Contrôle GE (suivi global par GE)</h2>
                        <p class="mb-3 text-xs text-slate-500">Inscrivez les PE concernés par GE, le nombre de sujets reçus/déposés et les validations.</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm">
                                <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="border border-slate-200 px-2 py-2">DREN</th>
                                    <th class="border border-slate-200 px-2 py-2">CISCO</th>
                                    <th class="border border-slate-200 px-2 py-2">Centre écrit</th>
                                    <th class="border border-slate-200 px-2 py-2">Examen</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">GE</th>
                                    <th class="border border-slate-200 px-2 py-2">PE concernés</th>
                                    <th class="border border-slate-200 px-2 py-2 text-right">Nb PE</th>
                                    <th class="border border-slate-200 px-2 py-2">Compteur GE</th>
                                    <th class="border border-slate-200 px-2 py-2">Date/Heure</th>
                                    <th class="border border-slate-200 px-2 py-2">Validé par</th>
                                    <th class="border border-slate-200 px-2 py-2">Observation</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($geRows as $idx => $row)
                                    @php($baseKey = implode('|', ['GE', $row['centre_idx'], $row['type_examen'], $row['ge_no']]))
                                    <tr>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['dren'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['cisco'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['centre_ecrit'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['type_examen'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $row['ge_no'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2">{{ $row['pe_range'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $row['pe_count'] }}</td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:compteur" class="w-28 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="N° compteur"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:datetime" class="w-40 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="JJ/MM/AAAA HH:mm"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:agent" class="w-32 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="Nom agent"></td>
                                        <td class="border border-slate-200 px-2 py-2"><input data-store-key="{{ $baseKey }}:obs" class="w-48 rounded border border-slate-300 px-2 py-1 text-xs" type="text" placeholder="Observation"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="border border-slate-200 px-3 py-5 text-center text-slate-500">Aucune donnée.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    (function () {
        const storageKey = 'trace_controle_' + btoa(unescape(encodeURIComponent(JSON.stringify(@json($filters)))));
        const peRows = @json($peRows ?? []);
        let store = {};

        try {
            store = JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch (e) {
            store = {};
        }

        document.querySelectorAll('[data-store-key]').forEach((input) => {
            const key = input.getAttribute('data-store-key');
            if (Object.prototype.hasOwnProperty.call(store, key)) {
                input.value = store[key];
            }

            input.addEventListener('input', function () {
                store[key] = this.value;
                localStorage.setItem(storageKey, JSON.stringify(store));
            });
        });

        const groupModeEl = document.getElementById('groupMode');
        const matrixBody = document.getElementById('compteurMatrixBody');
        const globalCounterDiv = document.getElementById('globalCounterDiv');
        const globalCounterEl = document.getElementById('globalCounter');
        const compteurNamesEl = document.getElementById('compteurNames');
        const groupCountEl = document.getElementById('groupCount');
        const groupsContainer = document.getElementById('groupsContainer');
        

        function buildGroups(mode) {
            const grouped = {};
            peRows.forEach((row) => {
                const key = mode === 'CENTRE_ECRIT' 
            ? `${row.dren} | ${row.cisco} | ${row.centre_ecrit}` 
            : (mode === 'CISCO' ? `${row.dren} | ${row.cisco}` : row.dren);
                if (!grouped[key]) {
                    grouped[key] = { totalPe: 0, regions: [key] };
                }
                grouped[key].totalPe += 1;
            });
            return grouped;
        }

        function buildRanges(totalPe, compteurCount, names = []) {
            if (compteurCount <= 0 || totalPe <= 0) return '-';
            const base = Math.floor(totalPe / compteurCount);
            let extra = totalPe % compteurCount;
            let start = 1;
            const ranges = [];

            for (let i = 1; i <= compteurCount; i++) {
                const count = base + (extra > 0 ? 1 : 0);
                if (extra > 0) extra--;
                if (count <= 0) {
                    const name = names[i-1] || `C${i}`;
                    ranges.push(`${name}: -`);
                    continue;
                }
                const end = start + count - 1;
                const name = names[i-1] || `C${i}`;
                ranges.push(`${name}: PE${start}${end > start ? '-PE' + end : ''}`);
                start = end + 1;
            }

            return ranges.join(' | ');
        }

        function getCompteurNames() {
            const text = compteurNamesEl?.value || '';
            return text.split('\n').map(name => name.trim()).filter(name => name !== '');
        }

        function getGroupCount() {
            return Math.max(1, parseInt(groupCountEl?.value || '1', 10) || 1);
        }

        function distributeRegionsToGroups(groups, groupCount) {
            const allRegions = Object.keys(groups).sort((a, b) => a.localeCompare(b));
            const regionsPerGroup = Math.ceil(allRegions.length / groupCount);
            const distributedGroups = [];

            for (let i = 0; i < groupCount; i++) {
                const start = i * regionsPerGroup;
                const end = Math.min(start + regionsPerGroup, allRegions.length);
                const groupRegions = allRegions.slice(start, end);
                distributedGroups.push({
                    id: i + 1,
                    regions: groupRegions,
                    names: []
                });
            }

            return distributedGroups;
        }

        function getGroupData() {
            const groupCount = getGroupCount();
            const groups = buildGroups(groupModeEl?.value || 'DREN');
            const distributedGroups = distributeRegionsToGroups(groups, groupCount);

            // Load saved names for each group
            distributedGroups.forEach(group => {
                const namesText = store[`group${group.id}|names`] || '';
                group.names = namesText.split('\n').map(n => n.trim()).filter(n => n !== '');
            });

            return distributedGroups;
        }

        function renderGroups() {
            const groupCount = getGroupCount();
            const groups = getGroupData();
            
            if (!groupsContainer) return;
            
            groupsContainer.innerHTML = groups.map(group => `
                <div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-3">
                    <h4 class="text-sm font-semibold text-indigo-900 mb-2">Groupe ${group.id}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-indigo-700 mb-1">Régions (une par ligne):</label>
                            <textarea class="w-full rounded border border-indigo-300 px-2 py-1 text-xs" rows="3" data-group-regions="${group.id}" placeholder="DREN1&#10;DREN2 / CISCO1&#10;...">${group.regions.join('\n')}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-indigo-700 mb-1">Noms des compteurs (un par ligne):</label>
                            <textarea class="w-full rounded border border-indigo-300 px-2 py-1 text-xs" rows="3" data-group-names="${group.id}" placeholder="Rakoto&#10;Rasoa&#10;Rabe&#10;...">${group.names.join('\n')}</textarea>
                        </div>
                    </div>
                </div>
            `).join('');

            // Add event listeners
            groupsContainer.querySelectorAll('[data-group-regions]').forEach(textarea => {
                const groupId = textarea.getAttribute('data-group-regions');
                textarea.addEventListener('input', function() {
                    store[`group${groupId}|regions`] = this.value;
                    localStorage.setItem(storageKey, JSON.stringify(store));
                    renderMatrix();
                });
            });
            
            groupsContainer.querySelectorAll('[data-group-names]').forEach(textarea => {
                const groupId = textarea.getAttribute('data-group-names');
                textarea.addEventListener('input', function() {
                    store[`group${groupId}|names`] = this.value;
                    localStorage.setItem(storageKey, JSON.stringify(store));
                    renderMatrix();
                });
            });
        }

        function getNamesForRegion(regionKey, groups) {
            for (const group of groups) {
                if (group.regions.some(region => regionKey.includes(region) || region.includes(regionKey))) {
                    return group.names;
                }
            }
            return getCompteurNames(); // Fallback to global names
        }

        function renderMatrix() {
            const mode = (groupModeEl?.value || 'DREN');
            const groups = buildGroups(mode);
            const keys = Object.keys(groups).sort((a, b) => a.localeCompare(b));
            const groupData = getGroupData();

            if (!matrixBody) return;

            if (keys.length === 0) {
                matrixBody.innerHTML = '<tr><td colspan="5" class="border border-indigo-200 px-2 py-4 text-center text-indigo-700">Aucune donnée.</td></tr>';
                return;
            }

            matrixBody.innerHTML = keys.map((key) => {
                const totalPe = groups[key].totalPe;
                const countStoreKey = `matrix|${mode}|${key}|compteurs`;
                let compteurCount;
                if (mode === 'CENTRE_ECRIT') {
                    const globalCounterStoreKey = 'matrix|global|compteurs';
                    compteurCount = Math.max(0, parseInt(store[globalCounterStoreKey] || store[countStoreKey] || '1', 10) || 1);
                } else {
                    compteurCount = Math.max(0, parseInt(store[countStoreKey] || '1', 10) || 1);
                }
                const peParCompteur = (totalPe / compteurCount).toFixed(2);
                const regionNames = getNamesForRegion(key, groupData);
                const ranges = buildRanges(totalPe, compteurCount, regionNames);

                return `
                    <tr>
                        <td class="border border-indigo-200 px-2 py-2 font-semibold text-indigo-900">${key}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">${totalPe}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">
                            <input data-matrix-key="${countStoreKey}" class="w-20 rounded border border-indigo-300 px-2 py-1 text-right text-xs ${mode === 'CENTRE_ECRIT' ? 'bg-gray-100' : ''}" type="number" min="0" value="${compteurCount}" ${mode === 'CENTRE_ECRIT' ? 'readonly' : ''}>
                        </td>
                        <td class="border border-indigo-200 px-2 py-2 text-right">${peParCompteur}</td>
                        <td class="border border-indigo-200 px-2 py-2 text-xs">${ranges}</td>
                    </tr>
                `;
            }).join('');

            matrixBody.querySelectorAll('[data-matrix-key]').forEach((input) => {
                input.addEventListener('input', function () {
                    const key = this.getAttribute('data-matrix-key');
                    store[key] = this.value;
                    localStorage.setItem(storageKey, JSON.stringify(store));
                    renderMatrix();
                });
            });
        }

        if (groupModeEl) {
            const groupModeStoreKey = 'matrix|mode';
            groupModeEl.value = store[groupModeStoreKey] || 'DREN';
            groupModeEl.addEventListener('change', function () {
                store[groupModeStoreKey] = this.value;
                localStorage.setItem(storageKey, JSON.stringify(store));
                updateGlobalCounterVisibility();
                renderMatrix();
            });
        }

        function updateGlobalCounterVisibility() {
            const mode = groupModeEl?.value || 'DREN';
            if (globalCounterDiv) {
                globalCounterDiv.style.display = mode === 'CENTRE_ECRIT' ? 'flex' : 'none';
            }
            if (globalCounterEl && mode === 'CENTRE_ECRIT') {
                const globalCounterStoreKey = 'matrix|global|compteurs';
                globalCounterEl.value = store[globalCounterStoreKey] || '1';
            }
            renderGroups();
        }

        if (globalCounterEl) {
            globalCounterEl.addEventListener('input', function () {
                const mode = groupModeEl?.value || 'DREN';
                if (mode === 'CENTRE_ECRIT') {
                    const value = this.value;
                    const groups = buildGroups(mode);
                    const keys = Object.keys(groups);
                    keys.forEach(key => {
                        const countStoreKey = `matrix|${mode}|${key}|compteurs`;
                        store[countStoreKey] = value;
                    });
                    const globalCounterStoreKey = 'matrix|global|compteurs';
                    store[globalCounterStoreKey] = value;
                    localStorage.setItem(storageKey, JSON.stringify(store));
                    renderMatrix();
                }
            });
        }

        if (compteurNamesEl) {
            const namesStoreKey = 'compteurNames';
            compteurNamesEl.value = store[namesStoreKey] || '';
            compteurNamesEl.addEventListener('input', function () {
                store[namesStoreKey] = this.value;
                localStorage.setItem(storageKey, JSON.stringify(store));
                renderMatrix();
            });
        }

        if (groupCountEl) {
            const groupCountStoreKey = 'groupCount';
            groupCountEl.value = store[groupCountStoreKey] || '1';
            groupCountEl.addEventListener('input', function () {
                store[groupCountStoreKey] = this.value;
                localStorage.setItem(storageKey, JSON.stringify(store));
                renderGroups();
                renderMatrix();
            });
        }

        const tracePayloadInput = document.getElementById('tracePayloadInput');
        const wordExportForm = document.getElementById('wordExportForm');
        if (wordExportForm && tracePayloadInput) {
            wordExportForm.addEventListener('submit', function () {
                tracePayloadInput.value = JSON.stringify(store);
            });
        }

        renderMatrix();
        updateGlobalCounterVisibility();
    })();
</script>
</body>
</html>
