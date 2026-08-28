<div class="agent-info">

    @php
        $visibleFields = $settings?->fieldsFor($document ?? '') ?? ['nom', 'im', 'corps_grade'];
        $labels = \App\Models\HrDocumentSetting::availableFields();
    @endphp

    <table>

        @foreach($visibleFields as $field)

            @switch($field)

                @case('nom')
                <tr>
                    <td width="25%" class="label">{{ $labels['nom'] ?? 'Nom et Prénoms' }}</td>
                    <td><strong>{{ $agent->full_name }}</strong></td>
                </tr>
                @break

                @case('im')
                <tr>
                    <td class="label">{{ $labels['im'] ?? 'IM' }}</td>
                    <td>{{ $agent->matricule ?: '—' }}</td>
                </tr>
                @break

                @case('corps_grade')
                <tr>
                    <td class="label">{{ $labels['corps_grade'] ?? 'Corps et Grade' }}</td>
                    <td>{{ $agent->corps ?: '—' }} / {{ $agent->grade ?: '—' }}</td>
                </tr>
                @break

                @case('budget_chapitre')
                <tr>
                    <td class="label">{{ $labels['budget_chapitre'] ?? 'Budget / Chapitre' }}</td>
                    <td>
                        {{ $agent->budget ?: '—' }}
                        &nbsp;&nbsp;&nbsp;
                        <strong>Chapitre :</strong>
                        {{ $agent->chapitre ?: '—' }}
                    </td>
                </tr>
                @break

                @case('date_recrutement')
                <tr>
                    <td class="label">{{ $labels['date_recrutement'] ?? 'Date d’entrée dans l’Administration' }}</td>
                    <td>{{ $agent->date_recrutement?->translatedFormat('d F Y') ?: '—' }}</td>
                </tr>
                @break

                @case('date_naissance')
                <tr>
                    <td class="label">{{ $labels['date_naissance'] ?? 'Date de naissance' }}</td>
                    <td>{{ $agent->date_naissance?->format('d/m/Y') ?: '—' }}</td>
                </tr>
                @break

                @case('corps')
                <tr>
                    <td class="label">{{ $labels['corps'] ?? 'Corps' }}</td>
                    <td>{{ $agent->corps ?: '—' }}</td>
                </tr>
                @break

                @case('grade')
                <tr>
                    <td class="label">{{ $labels['grade'] ?? 'Grade' }}</td>
                    <td>{{ $agent->grade ?: '—' }}</td>
                </tr>
                @break

                @case('indice')
                <tr>
                    <td class="label">{{ $labels['indice'] ?? 'Indice' }}</td>
                    <td>{{ $agent->indice ?: '—' }}</td>
                </tr>
                @break

                @case('categorie')
                <tr>
                    <td class="label">{{ $labels['categorie'] ?? 'Catégorie' }}</td>
                    <td>{{ $agent->categorie ?: '—' }}</td>
                </tr>
                @break

                @case('echelon')
                <tr>
                    <td class="label">{{ $labels['echelon'] ?? 'Échelon' }}</td>
                    <td>{{ $agent->echelon ?: '—' }}</td>
                </tr>
                @break

                @case('budget')
                <tr>
                    <td class="label">{{ $labels['budget'] ?? 'Budget' }}</td>
                    <td>{{ $agent->budget ?: '—' }}</td>
                </tr>
                @break

                @case('chapitre')
                <tr>
                    <td class="label">{{ $labels['chapitre'] ?? 'Chapitre' }}</td>
                    <td>{{ $agent->chapitre ?: '—' }}</td>
                </tr>
                @break

                @case('date_prise_service')
                <tr>
                    <td class="label">{{ $labels['date_prise_service'] ?? 'Date de prise de service' }}</td>
                    <td>{{ $agent->date_prise_service?->format('d/m/Y') ?: '—' }}</td>
                </tr>
                @break

                @case('direction')
                <tr>
                    <td class="label">{{ $labels['direction'] ?? 'Direction' }}</td>
                    <td>{{ $agent->direction ?: '—' }}</td>
                </tr>
                @break

                @case('service')
                <tr>
                    <td class="label">{{ $labels['service'] ?? 'Service' }}</td>
                    <td>{{ $agent->service ?: '—' }}</td>
                </tr>
                @break

                @case('service_direction')
                <tr>
                    <td class="label">{{ $labels['service_direction'] ?? 'Direction / Service' }}</td>
                    <td>
                        {{ $agent->direction ?: '' }}
                        @if($agent->direction && $agent->service) — @endif
                        {{ $agent->service ?: '' }}
                        @if(!$agent->direction && !$agent->service) — @endif
                    </td>
                </tr>
                @break

                @case('bureau')
                <tr>
                    <td class="label">{{ $labels['bureau'] ?? 'Bureau' }}</td>
                    <td>{{ $agent->bureau ?: '—' }}</td>
                </tr>
                @break

                @case('fonction')
                <tr>
                    <td class="label">{{ $labels['fonction'] ?? 'Fonction' }}</td>
                    <td>{{ $agent->fonction ?: '—' }}</td>
                </tr>
                @break

                @case('situation_administrative')
                <tr>
                    <td class="label">{{ $labels['situation_administrative'] ?? 'Situation administrative' }}</td>
                    <td>{{ $agent->situation_administrative ?: '—' }}</td>
                </tr>
                @break

                @default
                    {{-- Champ inconnu : on ne l'affiche pas --}}

            @endswitch

        @endforeach

    </table>

</div>