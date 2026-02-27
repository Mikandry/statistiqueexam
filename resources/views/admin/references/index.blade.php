<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Référentiels</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-5 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg backdrop-blur-sm transition-all duration-200 hover:shadow-xl">
                <div class="border-b border-slate-200/80 bg-gradient-to-r from-white to-slate-50/50 px-6 py-5 md:px-8 md:py-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Administration Référentiels</h1>
                            <p class="text-sm font-medium text-slate-500">Ajout et modification des DREN, CISCO, centres de correction et centres d'écrit</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.statistics.index') }}">Statistiques</a>
                            <a class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all duration-200 hover:border-slate-300 hover:bg-slate-50 hover:shadow" href="{{ route('admin.users.index') }}">Utilisateurs</a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    @if(session('status'))
                        <div class="mb-6 rounded-lg border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-lg border border-rose-200/80 bg-gradient-to-r from-rose-50 to-white px-4 py-3 text-sm font-medium text-rose-700 shadow-sm">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Ajouter DREN</h2>
                        <form method="POST" action="{{ route('admin.references.drens.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                            @csrf
                            <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="nom" placeholder="Nom DREN" required>
                            <div class="md:col-span-3 flex items-end">
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Ajouter DREN</button>
                            </div>
                        </form>
                    </div>

                    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Ajouter CISCO</h2>
                        <form method="POST" action="{{ route('admin.references.ciscos.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                            @csrf
                            <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="dren_id" required>
                                <option value="">DREN</option>
                                @foreach($drens as $dren)
                                    <option value="{{ $dren->id }}">{{ $dren->nom }}</option>
                                @endforeach
                            </select>
                            <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="nom" placeholder="Nom CISCO" required>
                            <div class="md:col-span-2 flex items-end">
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Ajouter CISCO</button>
                            </div>
                        </form>
                    </div>

                    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Ajouter Centre de correction</h2>
                        <form method="POST" action="{{ route('admin.references.centres-correction.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                            @csrf
                            <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="cisco_id" required>
                                <option value="">CISCO</option>
                                @foreach($ciscos as $cisco)
                                    <option value="{{ $cisco->id }}">{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                @endforeach
                            </select>
                            <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="nom" placeholder="Nom centre correction" required>
                            <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="type_examen" required>
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                            <div class="flex items-end">
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Ajouter Centre correction</button>
                            </div>
                        </form>
                    </div>

                    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Ajouter Centre d'écrit</h2>
                        <form method="POST" action="{{ route('admin.references.centres-ecrit.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                            @csrf
                            <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="centre_correction_id" required>
                                <option value="">Centre correction</option>
                                @foreach($centresCorrection as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                @endforeach
                            </select>
                            <input class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="nom" placeholder="Nom centre écrit" required>
                            <select class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm" name="type_examen" required>
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                            <div class="flex items-end">
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Ajouter Centre écrit</button>
                            </div>
                        </form>
                    </div>

                    <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-md">
                        <h2 class="mb-3 text-lg font-semibold text-slate-800">Modifier référentiels existants</h2>

                        <div class="mb-5 overflow-x-auto">
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">DREN</h3>
                            <table class="min-w-full border-collapse text-sm">
                                <thead><tr class="bg-slate-100"><th class="border border-slate-200 px-3 py-2 text-left">Nom</th><th class="border border-slate-200 px-3 py-2 text-left">Action</th></tr></thead>
                                <tbody>
                                @foreach($drens as $dren)
                                    <tr>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form method="POST" action="{{ route('admin.references.drens.update', $dren) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="nom" value="{{ $dren->nom }}" required>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-5 overflow-x-auto">
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">CISCO</h3>
                            <table class="min-w-full border-collapse text-sm">
                                <thead><tr class="bg-slate-100"><th class="border border-slate-200 px-3 py-2 text-left">DREN</th><th class="border border-slate-200 px-3 py-2 text-left">Nom</th><th class="border border-slate-200 px-3 py-2 text-left">Action</th></tr></thead>
                                <tbody>
                                @foreach($ciscos as $cisco)
                                    <tr>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form method="POST" action="{{ route('admin.references.ciscos.update', $cisco) }}" class="flex flex-wrap gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="dren_id" required>
                                                    @foreach($drens as $dren)
                                                        <option value="{{ $dren->id }}" {{ (int)$cisco->dren_id === (int)$dren->id ? 'selected' : '' }}>{{ $dren->nom }}</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="nom" value="{{ $cisco->nom }}" required></td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-5 overflow-x-auto">
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres de correction</h3>
                            <table class="min-w-full border-collapse text-sm">
                                <thead><tr class="bg-slate-100"><th class="border border-slate-200 px-3 py-2 text-left">CISCO</th><th class="border border-slate-200 px-3 py-2 text-left">Nom</th><th class="border border-slate-200 px-3 py-2 text-left">Type</th><th class="border border-slate-200 px-3 py-2 text-left">Action</th></tr></thead>
                                <tbody>
                                @foreach($centresCorrection as $cc)
                                    <tr>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form method="POST" action="{{ route('admin.references.centres-correction.update', $cc) }}">
                                                @csrf
                                                @method('PUT')
                                                <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="cisco_id" required>
                                                    @foreach($ciscos as $cisco)
                                                        <option value="{{ $cisco->id }}" {{ (int)$cc->cisco_id === (int)$cisco->id ? 'selected' : '' }}>{{ $cisco->dren->nom ?? '-' }} / {{ $cisco->nom }}</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="nom" value="{{ $cc->nom }}" required></td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="type_examen" required>
                                                    <option value="BEPC" {{ $cc->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                    <option value="CEPE" {{ $cc->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="overflow-x-auto">
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Centres d'écrit</h3>
                            <table class="min-w-full border-collapse text-sm">
                                <thead><tr class="bg-slate-100"><th class="border border-slate-200 px-3 py-2 text-left">Centre correction</th><th class="border border-slate-200 px-3 py-2 text-left">Nom</th><th class="border border-slate-200 px-3 py-2 text-left">Type</th><th class="border border-slate-200 px-3 py-2 text-left">Action</th></tr></thead>
                                <tbody>
                                @foreach($centresEcrit as $ce)
                                    <tr>
                                        <td class="border border-slate-200 px-3 py-2">
                                            <form method="POST" action="{{ route('admin.references.centres-ecrit.update', $ce) }}">
                                                @csrf
                                                @method('PUT')
                                                <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="centre_correction_id" required>
                                                    @foreach($centresCorrection as $cc)
                                                        <option value="{{ $cc->id }}" {{ (int)$ce->centre_correction_id === (int)$cc->id ? 'selected' : '' }}>{{ $cc->cisco->dren->nom ?? '-' }} / {{ $cc->cisco->nom ?? '-' }} / {{ $cc->nom }} ({{ $cc->type_examen }})</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2"><input class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="nom" value="{{ $ce->nom }}" required></td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <select class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" name="type_examen" required>
                                                    <option value="BEPC" {{ $ce->type_examen === 'BEPC' ? 'selected' : '' }}>BEPC</option>
                                                    <option value="CEPE" {{ $ce->type_examen === 'CEPE' ? 'selected' : '' }}>CEPE</option>
                                                </select>
                                        </td>
                                        <td class="border border-slate-200 px-3 py-2">
                                                <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700" type="submit">Modifier</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
