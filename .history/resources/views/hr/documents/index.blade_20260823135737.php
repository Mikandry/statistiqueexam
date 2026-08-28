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

