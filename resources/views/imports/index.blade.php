<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imports Référentiels</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
    @endif
</head>
<body class="bg-slate-100 text-slate-900">
<div class="mx-auto max-w-[1700px] p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-5 md:p-6">
                    <h1 class="text-2xl font-bold tracking-tight">Imports Référentiels</h1>
                    <p class="mt-1 text-sm text-slate-500">Import CSV des DREN, CISCO, centres de correction et centres d'écrit</p>
                </div>

                <div class="p-5 md:p-6">
                    @if(session('status'))
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                        Formats attendus: fichier CSV avec en-têtes exacts. Délimiteur accepté: <code>;</code> ou <code>,</code>. Les caractères accentués sont acceptés (UTF-8/Windows-1252).
                    </div>

                    <div class="space-y-4">
                        <form method="POST" action="{{ route('imports.drens') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <h2 class="mb-1 text-lg font-semibold">Import DREN</h2>
                            <p class="mb-3 text-sm text-slate-500">Colonnes: <code>nom</code></p>
                            <input type="file" name="drens_file" accept=".csv,.txt" required class="mb-3 block w-full text-sm">
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Importer DREN</button>
                        </form>

                        <form method="POST" action="{{ route('imports.ciscos') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <h2 class="mb-1 text-lg font-semibold">Import CISCO</h2>
                            <p class="mb-3 text-sm text-slate-500">Colonnes: <code>dren</code>, <code>nom</code></p>
                            <input type="file" name="ciscos_file" accept=".csv,.txt" required class="mb-3 block w-full text-sm">
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Importer CISCO</button>
                        </form>

                        <form method="POST" action="{{ route('imports.centres.correction') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <h2 class="mb-1 text-lg font-semibold">Import Centres de correction</h2>
                            <p class="mb-3 text-sm text-slate-500">Choisissez d'abord le type d'examen à importer.</p>
                            <select name="type_examen" required class="mb-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                            <p class="mb-3 text-sm text-slate-500">Colonnes: <code>dren</code>, <code>cisco</code>, <code>nom</code> (ou <code>type_examen</code> dans le CSV si besoin)</p>
                            <input type="file" name="centres_correction_file" accept=".csv,.txt" required class="mb-3 block w-full text-sm">
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Importer Centres de correction</button>
                        </form>

                        <form method="POST" action="{{ route('imports.centres.ecrit') }}" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4">
                            @csrf
                            <h2 class="mb-1 text-lg font-semibold">Import Centres d'écrit</h2>
                            <p class="mb-3 text-sm text-slate-500">Choisissez d'abord le type d'examen à importer.</p>
                            <select name="type_examen" required class="mb-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                            <p class="mb-3 text-sm text-slate-500">Colonnes: <code>dren</code>, <code>cisco</code>, <code>centre_correction</code>, <code>nom</code> (ou <code>type_examen</code> dans le CSV si besoin)</p>
                            <input type="file" name="centres_ecrit_file" accept=".csv,.txt" required class="mb-3 block w-full text-sm">
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Importer Centres d'écrit</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
