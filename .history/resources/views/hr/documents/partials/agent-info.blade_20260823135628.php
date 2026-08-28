<div class="agent-info rounded-3xl border border-slate-200/60 bg-gradient-to-br from-white to-slate-50/80 p-7 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:shadow-2xl hover:border-slate-300/80">

    @php
        $visibleFields = $settings?->fieldsFor($document ?? '') ?? ['nom', 'im', 'corps_grade'];
        $labels = \App\Models\HrDocumentSetting::availableFields();
    @endphp

    <table class="w-full text-sm">

        @foreach($visibleFields as $field)

            @switch($field)

                @case('nom')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td width="30%" class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $labels['nom'] ?? 'Nom et Prénoms' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        <strong class="text-slate-900">{{ $agent->full_name }}</strong>
                    </td>
                </tr>
                @break

                @case('im')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        {{ $labels['im'] ?? 'IM' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $agent->matricule ?: '—' }}</span>
                    </td>
                </tr>
                @break

                @case('corps_grade')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $labels['corps_grade'] ?? 'Corps et Grade' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-slate-700">{{ $agent->corps ?: '—' }}</span>
                            <span class="text-slate-300 font-light">/</span>
                            <span class="text-slate-700">{{ $agent->grade ?: '—' }}</span>
                        </span>
                    </td>
                </tr>
                @break

                @case('budget_chapitre')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $labels['budget_chapitre'] ?? 'Budget / Chapitre' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        <span class="text-slate-700">{{ $agent->budget ?: '—' }}</span>
        <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
            <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Service :
            </td>
            <td class="py-3.5 font-medium text-slate-800">
                {{ $agent->service ?: '—' }}
            </td>
        </tr>

        <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
            <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Fonction :
            </td>
            <td class="py-3.5 font-medium text-slate-800">
                <span class="inline-block rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    {{ $agent->fonction ?: '—' }}
                </span>
            </td>
        </tr>

    </table>

</div>