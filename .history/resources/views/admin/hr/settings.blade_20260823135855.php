@extends('layouts.app')

@section('title', 'Paramètres RH')
@section('subtitle', 'En-têtes, numérotation et champs des documents administratifs')

@section('content')
<div class="space-y-6 rounded-2xl bg-gradient-to-br from-slate-50 via-white to-teal-50/50 p-1">

    @if(session('status'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">✅</span>
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50/80 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm backdrop-blur-sm">
            <span class="text-xl">⚠️</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- EN-TÊTE OFFICIEL & NUMÉROTATION                            --}}
    {{-- ============================================================= --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white/95 shadow-md transition-all hover:shadow-lg">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50/80 to-teal-50/40 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">🏛️ En-tête officiel</h2>
            <p class="mt-1 text-sm text-slate-500">Lignes affichées dans l'ordre : ministère, SG, DG, direction puis service.</p>
        </div>

        <form method="POST" action="{{ route('admin.hr.settings.update') }}" class="grid gap-4 p-6 md:grid-cols-2">
            @csrf @method('PUT')

            @foreach([
                'ministere' => 'Ministère',
                'secretariat_general' => 'Secrétariat général',
                'direction_generale' => 'Direction générale',
                'direction' => 'Direction',
                'service' => 'Service',
                'signataire' => 'Nom du signataire',
