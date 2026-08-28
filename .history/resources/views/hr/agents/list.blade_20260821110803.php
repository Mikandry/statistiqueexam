@extends('layouts.app')

@section('title', 'Personnel')
@section('subtitle', 'Gestion administrative des agents')

@section('content')

<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">

        <div>
            <h1 class="text-xl font-black">
                Personnel
            </h1>

            <p class="text-sm text-slate-500">
                Liste des agents de la direction
            </p>
        </div>

        <a
            href="{{ route('hr.dashboard') }}"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold"
        >
            ← Tableau de bord
        </a>

    </div>


    <form method="GET"
          class="flex flex-wrap gap-2 rounded-lg border bg-white p-4">

        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Nom, matricule, service ou fonction"
            class="min-w-[280px] rounded-lg border-slate-300 text-sm"
        >

        <button
            class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-bold text-white"
        >
            Rechercher
        </button>

    </form>


    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="bg-slate-50 text-xs font-black uppercase text-slate-500">

                    <tr>
                        <th class="px-4 py-3 text-left">Agent</th>
                        <th class="px-4 py-3 text-left">Matricule</th>
                        <th class="px-4 py-3 text-left">Fonction</th>
                        <th class="px-4 py-3 text-left">Service</th>
                        <th class="px-4 py-3 text-left">Compte</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100">

                @forelse($agents as $agent)

                    <tr>

                        <td class="px-4 py-3 font-bold">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="text-blue-700 hover:underline"
                            >
                                {{ $agent->full_name }}
                            </a>

                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->matricule ?: '—' }}
                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->fonction ?: '—' }}
                        </td>


                        <td class="px-4 py-3">
                            {{ $agent->service ?: '—' }}
                        </td>


                        <td class="px-4 py-3">

                            @if($agent->user)

                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">
                                    Compte associé
                                </span>

                            @else

                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">
                                    Aucun compte
                                </span>

                            @endif

                        </td>


                        <td class="px-4 py-3">

                            <a
                                href="{{ route('hr.agents.show', $agent->id) }}"
                                class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white"
                            >
                                Ouvrir
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="px-4 py-10 text-center text-slate-500"
                        >
                            Aucun agent trouvé.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{ $agents->links() }}

</div>

@endsection