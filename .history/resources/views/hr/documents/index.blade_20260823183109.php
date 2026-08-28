@extends('layouts.app')

@section('title', 'Attestations et documents — ' . $agent->full_name)
@section('subtitle', 'Génération des documents administratifs')

@section('content')

<div class="space-y-8 rounded-3xl bg-gradient-to-br from-slate-50 via-white to-teal-50/40 p-6 shadow-lg shadow-slate-200/50">

    {{-- EN-TÊTE --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-md backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-3xl shadow-lg shadow-teal-200/50">
                📄
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Documents administratifs
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Génération d'attestations pour
                    <strong class="text-slate-700">{{ $agent->full_name }}</strong>
                    <span class="mx-1 text-slate-300">•</span>
                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $agent->matricule ?: 'Sans matricule' }}</span>
                </p>
            </div>
        </div>
        <a href="{{ route('hr.agents.show', $agent->id) }}"
           class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow-md active:scale-95">
            ← Retour au dossier
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">✅</span>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">⚠️</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- GRILLE DES DOCUMENTS --}}
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        @foreach($documents as $slug => $doc)

            <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-md transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-slate-300/40">

                {{-- Bandeau supérieur coloré --}}
                <div class="h-2.5 w-full bg-gradient-to-r {{ $doc['color'] }}"></div>

                <div class="flex flex-1 flex-col p-6">

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-2xl shadow-sm ring-1 ring-slate-100">
                            {{ $doc['icon'] }}
                        </div>

                        @if(($doc['requires_event'] ?? false) && ($events[$slug] ?? collect())->isEmpty())
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-amber-700 ring-1 ring-amber-200">
                                En attente
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-lg font-black leading-snug text-slate-900">
                        {{ $doc['title'] }}
                    </h2>

                    <p class="mt-1.5 flex-1 text-sm leading-relaxed text-slate-500">
                        {{ $doc['description'] }}
                    </p>

                    {{-- Sélection d'événement si nécessaire --}}
                    @if($doc['requires_event'] ?? false)

                        @php
                        $docEvents = $events[$slug] ?? collect()    

                        @if($docEvents->isEmpty())

                            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-3 text-xs font-medium text-slate-500">
                                Aucune demande validée pour ce document.
                            </div>

                        @else

                            <label class="mt-4 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Événement concerné
                            </label>

                            <select
                                id="event-{{ $slug }}"
                                data-document="{{ $slug }}"
                                data-agent="{{ $agent->id }}"
                                class="event-select mt-1.5 w-full rounded-xl border-slate-300 bg-slate-50/50 px-3 py-2 text-sm shadow-sm transition-all focus:border-teal-400 focus:ring-2 focus:ring-teal-200 focus:outline-none"
                            >
                                @foreach($docEvents as $event)
                                    <option value="{{ $event->id }}">
                                        {{ $event->date_debut?->format('d/m/Y') }}
                                        @if($event->date_fin)
                                            → {{ $event->date_fin->format('d/m/Y') }}
                                        @endif
                                        @if($event->title)
                                            — {{ $event->title }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                        @endif

                    @endif

                    {{-- Actions --}}
                    <div class="mt-5 flex flex-wrap gap-2">

                        @php
                            $firstEventId = ($events[$slug] ?? collect())->first()?->id ?? 0;
                            $previewUrl = $doc['requires_event'] ?? false
                                ? route('hr.documents.preview', [$agent->id, $slug, $firstEventId])
                                : route('hr.documents.preview', [$agent->id, $slug]);
                            $wordUrl = $doc['requires_event'] ?? false
                                ? route('hr.documents.word', [$agent->id, $slug, $firstEventId])
                                : route('hr.documents.word', [$agent->id, $slug]);
                            $pdfUrl = $doc['requires_event'] ?? false
                                ? route('hr.documents.pdf', [$agent->id, $slug, $firstEventId])
                                : route('hr.documents.pdf', [$agent->id, $slug]);
                        @endphp

                        @if(($doc['requires_event'] ?? false) && empty($events[$slug] ?? collect()))

                            <div class="flex gap-2 opacity-40 pointer-events-none select-none">
                                <span class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-500">👁 Aperçu</span>
                                <span class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white">Word</span>
                                <span class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white">PDF</span>
                            </div>

                        @else

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $previewUrl }}" target="_blank"
                                   data-action="preview"
                                   data-document="{{ $slug }}"
                                   data-agent="{{ $agent->id }}"
                                   class="event-action rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow active:scale-95">
                                    👁 Aperçu
                                </a>
                                <a href="{{ $wordUrl }}"
                                   data-action="word"
                                   data-document="{{ $slug }}"
                                   data-agent="{{ $agent->id }}"
                                   class="event-action rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow active:scale-95">
                                    📝 Word
                                </a>
                                <a href="{{ $pdfUrl }}"
                                   data-action="pdf"
                                   data-document="{{ $slug }}"
                                   data-agent="{{ $agent->id }}"
                                   class="event-action rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-rose-700 hover:shadow active:scale-95">
                                    📄 PDF
                                </a>
                            </div>

                        @endif

                    </div>

                </div>

            </article>

        @endforeach

    </section>

    {{-- INFORMATIONS SUR LES CHAMPS CONFIGURÉS --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-md backdrop-blur-sm">

        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-teal-50 text-xl ring-1 ring-teal-100">
                ⚙️
            </div>
            <div class="flex-1">
                <h2 class="font-black text-slate-900">
                    Champs visibles par document
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Les champs affichés dans chaque attestation sont configurables depuis les paramètres RH.
                </p>
            </div>
            <a href="{{ route('admin.hr.settings') }}"
               class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:shadow active:scale-95">
                ⚙️ Paramètres des documents
            </a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($documents as $slug => $doc)
                <div class="rounded-xl border border-slate-200/70 bg-slate-50/60 p-4 transition-colors hover:border-teal-200 hover:bg-teal-50/40">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">
                        {{ $doc['icon'] }} {{ $doc['title'] }}
                    </p>
                    @php
                    $fieldList = $settings->fieldsFor($slug)
                    @endphp
                    @if(empty($fieldList))
                        <p class="mt-2 text-xs italic text-slate-400">Aucun champ personnalisé</p>
                    @else
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($fieldList as $field)
                                <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200">
                                    {{ $settings->availableFields()[$field] ?? $field }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </section>

</div>

@endsection

@push('scripts')
<script>
    (() => {
        // Lorsque l'utilisateur change l'événement sélectionné, mettre à jour les URLs
        document.querySelectorAll('.event-select').forEach(select => {
            select.addEventListener('change', () => {
                const slug = select.dataset.document;
                const agentId = select.dataset.agent;
                const eventId = select.value;

                document.querySelectorAll(`[data-document="${slug}"]`).forEach(el => {
                    if (!el.classList.contains('event-action')) return;
                    const action = el.dataset.action;
                    const url = `/rh/personnel/${agentId}/documents/${slug}/${action}/${eventId}`;
                    el.setAttribute('href', url);
                });
            });
        });

        // Afficher la sélection des champs visibles par document
        const settingsForm = document.getElementById('fields-config-form');
        if (settingsForm) {
            const checkboxes = settingsForm.querySelectorAll('input[type="checkbox"][data-field]');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const doc = cb.dataset.document;
                    const field = cb.dataset.field;
                    const checked = cb.checked;
                    const url = `/rh/personnel/${agentId}/documents/${slug}/preview/${eventId}`;
                });
            });
        }
    })();
</script>
@endpush