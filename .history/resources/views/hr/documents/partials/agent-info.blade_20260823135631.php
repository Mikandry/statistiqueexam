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
                        <span class="mx-2 text-slate-300">|</span>
                        <span class="font-semibold text-slate-600">Chapitre :</span>
                        <span class="text-slate-700">{{ $agent->chapitre ?: '—' }}</span>
                    </td>
                </tr>
                @break

                @case('date_recrutement')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $labels['date_recrutement'] ?? 'Date d’entrée dans l’Administration' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        {{ $agent->date_recrutement?->translatedFormat('d F Y') ?: '—' }}
                    </td>
                </tr>
                @break

                @case('date_naissance')
                <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                    <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $labels['date_naissance'] ?? 'Date de naissance' }} :
                    </td>
                    <td class="py-3.5 font-medium text-slate-800">
                        {{ $agent->date_naissance?->format('d/m/Y') ?: '—' }}
                    </td>
                </tr>
                @break

                @case('corps')
                    <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                        <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $labels['corps'] ?? 'Corps' }} :
                        </td>
                        <td class="py-3.5 font-medium text-slate-800">{{ $agent->corps ?: '—' }}</td>
                    </tr>
                @break

                @case('grade')
                    <tr class="border-b border-slate-100/70 last:border-0 hover:bg-slate-50/40 transition-colors duration-150">
                        <td class="py-3.5 pr-5 font-semibold text-slate-600 flex items-center gap-2">
