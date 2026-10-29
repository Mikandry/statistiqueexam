@extends('layouts.app') {{-- Adaptez à votre layout principal --}}

@section('content')
<div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Bienvenue, {{ auth()->user()->name }}
            </h1>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500">
                Veuillez sélectionner un espace de travail pour continuer.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            
            <!-- Espace Statistiques / Répartition (Accessible par défaut) -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Statistiques & Répartition</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Gestion des répartitions BEPC, tirages, rapports statistiques et résultats d'examens.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('bepc.repartition.create') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Accéder à l'espace
                        </a>
                    </div>
                </div>
            </div>

            <!-- Espace Ressources Humaines (Accessible par défaut) -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Ressources Humaines</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Gestion des agents, mon dossier personnel, suivi des événements et actes administratifs.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('hr.dashboard') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            Accéder à l'espace
                        </a>
                    </div>
                </div>
            </div>

            <!-- Espace Vacations (Reservé aux Administrateurs) -->
            @if($canAccessVacations)
                <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300 border border-purple-200 relative">
                    <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        Admin
                    </span>
                    <div class="p-6">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white mb-4">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Gestion des Vacations</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Module d'affectations, suivi des activités, imports et exports des décomptes 2026.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('vacation2026.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                Accéder à l'espace
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection