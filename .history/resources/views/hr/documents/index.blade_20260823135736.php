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
