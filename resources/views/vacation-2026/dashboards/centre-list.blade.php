@extends('layouts.app')

@section('title', 'Tableau de bord des Centres - Vacation 2026')

@section('content')

@include('vacation-2026.dashboards._navigation')
@include('vacation-2026.dashboards._filters')

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Tableau de bord des Centres
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Organisation des centres d'examen — Vacation 2026
            </p>
        </div>
    </div>


    {{-- ========================================================= --}}
    {{-- 1. CENTRES D'ÉCRIT SEULEMENT --}}
    {{-- ========================================================= --}}

    <section class="space-y-4">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Centres d'écrit seulement
                </h2>
                <p class="text-sm text-slate-500">
                    Centres assurant uniquement les épreuves écrites
                </p>
            </div>

            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                {{ $ecritOnly->count() }} centres
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            @forelse($ecritOnly as $dashboard)

                @include('vacation-2026.dashboards.centre-card', [
                    'dashboard' => $dashboard,
                    'type' => 'ecrit'
                ])

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center xl:col-span-2">
                    <p class="text-sm text-slate-500">
                        Aucun centre d'écrit seulement.
                    </p>
                </div>

            @endforelse

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- 2. CENTRES DE CORRECTION SEULEMENT --}}
    {{-- ========================================================= --}}

    <section class="space-y-4">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Centres de correction seulement
                </h2>
                <p class="text-sm text-slate-500">
                    Centres assurant uniquement la correction
                </p>
            </div>

            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                {{ $correctionOnly->count() }} centres
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            @forelse($correctionOnly as $dashboard)

                @include('vacation-2026.dashboards.centre-card', [
                    'dashboard' => $dashboard,
                    'type' => 'correction'
                ])

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center xl:col-span-2">
                    <p class="text-sm text-slate-500">
                        Aucun centre de correction seulement.
                    </p>
                </div>

            @endforelse

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- 3. CENTRES JUMELÉS --}}
    {{-- ========================================================= --}}

    <section class="space-y-4">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">
                    Centres jumelés
                </h2>
                <p class="text-sm text-slate-500">
                    Centres d'écrit + correction
                </p>
            </div>

            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                {{ $jumeles->count() }} centres
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            @forelse($jumeles as $dashboard)

                @include('vacation-2026.dashboards.centre-card', [
                    'dashboard' => $dashboard,
                    'type' => 'jumele'
                ])

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center xl:col-span-2">
                    <p class="text-sm text-slate-500">
                        Aucun centre jumelé.
                    </p>
                </div>

            @endforelse

        </div>

    </section>

</div>

@endsection