@extends('layouts.app')

@section('title', 'Statistiques par Option de Langue')

@section('content')
<div class="max-w-[1600px] mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">
                <span>Examens</span>
                <span class="text-slate-300">/</span>
                <span class="text-fuchsia-600">Statistiques par Langue</span>
            </nav>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Statistiques par Option de Langue</h1>
            <p class="mt-1 text-slate-500 font-medium">Nombre de PE, GE et soubiques par option de langue et par région.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-6 items-end">
            <div class="space-y-2">
                <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Type d'examen</label>
                <select name="type_examen" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20">
                    <option value="ALL" {{ $filters['type_examen'] === 'ALL' ? 'selected' : '' }}>Tous</option>
                    <option value="BEPC" {{ $filters['type_examen'] === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                    <option value="CEPE" {{ $filters['type_examen'] === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Année</label>
                <select name="annee" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20">
                    <option value="">Toutes les années</option>
                    @foreach ($annees as $annee)
                        <option value="{{ $annee }}" {{ $filters['annee'] === $annee ? 'selected' : '' }}>
                            {{ $annee }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">DREN</label>
                <select name="dren" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20">
                    <option value="">Tous les DREN</option>
                    @foreach ($drens as $dren)
                        <option value="{{ $dren }}" {{ $filters['dren'] === $dren ? 'selected' : '' }}>
                            {{ $dren }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">CISCO</label>
                <select name="cisco" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20">
                    <option value="">Tous les CISCO</option>
                    @foreach ($ciscos as $cisco)
                        <option value="{{ $cisco }}" {{ $filters['cisco'] === $cisco ? 'selected' : '' }}>
                            {{ $cisco }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Option de Langue</label>
                <select name="langue" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20">
                    @foreach ($allLanguages as $langue)
                        <option value="{{ $langue }}" {{ $selectedLanguage === $langue ? 'selected' : '' }}>
                            {{ $langue }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <button type="submit" class="w-full rounded-xl bg-fuchsia-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-fuchsia-500/25 transition-all hover:bg-fuchsia-700 hover:shadow-fuchsia-500/40 focus:ring-2 focus:ring-fuchsia-500/50">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Table -->
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-gradient-to-r from-fuchsia-50 to-white px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-fuchsia-100 text-fuchsia-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </span>
                {{ $selectedLanguage }}
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">DREN</th>
                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">CISCO</th>
                        <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500">Salle</th>
                        <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500">Grande Enveloppe</th>
                        <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500">Soubique</th>
                        <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-widest text-slate-500">Total Enveloppe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($stats as $stat)
                        <tr class="hover:bg-slate-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $stat['dren'] }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $stat['cisco'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                    {{ $stat['salle'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                    {{ $stat['ge_repartition'] ?: '0' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                    {{ $stat['soubique'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-slate-900">{{ number_format($stat['total_enveloppe']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Aucune donnée pour les filtres sélectionnés.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-black text-slate-900 uppercase tracking-wider">TOTAL</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white text-sm font-black">
                                {{ $totals['salle'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-amber-500 text-white text-sm font-black">
                                {{ $totals['ge_count'] }} GE
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-500 text-white text-sm font-black">
                                {{ $totals['soubique'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-lg font-black text-slate-900">{{ number_format($totals['total_enveloppe']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    .table-row-hover {
        transition: all 0.2s ease;
    }
    .table-row-hover:hover {
        background-color: rgba(99, 102, 241, 0.03);
        transform: translateX(2px);
    }
</style>
@endsection
