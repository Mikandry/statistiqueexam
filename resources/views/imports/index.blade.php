<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imports Référentiels | Système d'Organisation</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/tailwind-fallback.css') }}">
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .import-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .import-card:hover { transform: translateY(-4px); border-color: #6366f1; }
        .file-input-wrapper input[type=file]::file-selector-button {
            margin-right: 1rem; border: none; background: #f1f5f9; padding: 0.5rem 1rem;
            border-radius: 0.5rem; color: #475569; font-weight: 700; font-size: 0.75rem; cursor: pointer;
            transition: all 0.2s;
        }
        .file-input-wrapper input[type=file]::file-selector-button:hover { background: #e2e8f0; }
        .reject-list::-webkit-scrollbar { width: 4px; }
        .reject-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="h-full antialiased text-slate-900 bg-[radial-gradient(at_top_right,_var(--tw-gradient-stops))] from-slate-50 via-slate-50 to-indigo-50/30">

<div class="mx-auto max-w-[1700px] p-4 md:p-6 lg:p-8">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        @include('partials.sidebar')

        <main class="min-w-0 flex-1 space-y-6">
            <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 md:p-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500 mb-3">
                            <i class="fas fa-database"></i> Administration
                        </div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Imports Référentiels</h1>
                        <p class="text-slate-500 font-medium mt-1">Gérez la structure géographique et administrative de l'organisation.</p>
                    </div>
                    <div class="hidden xl:block">
                         <div class="flex gap-2">
                             <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm"><i class="fas fa-file-csv"></i></div>
                             <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100"><i class="fas fa-shield-alt"></i></div>
                         </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @if(session('status'))
                        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 p-4 text-emerald-800 border border-emerald-100 animate-slideDown">
                            <i class="fas fa-check-circle text-lg"></i>
                            <span class="font-bold text-sm">{{ session('status') }}</span>
                        </div>
                    @endif

                    @if(session('import_rejects') && is_array(session('import_rejects')) && count(session('import_rejects')) > 0)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-800 shadow-sm">
                            <div class="flex items-center gap-2 mb-3 font-black text-xs uppercase tracking-widest">
                                <i class="fas fa-exclamation-circle text-amber-500"></i> Lignes Rejetées ({{ count(session('import_rejects')) }})
                            </div>
                            <ul class="reject-list max-h-40 list-inside list-disc space-y-1 overflow-y-auto pr-4 text-xs font-medium">
                                @foreach(session('import_rejects') as $reject)
                                    <li>{{ $reject }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="rounded-2xl bg-rose-50 p-4 text-rose-800 border border-rose-100 animate-slideDown">
                            <ul class="text-sm font-bold space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="flex items-center gap-2"><i class="fas fa-times-circle text-rose-400"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-indigo-50/50 p-4">
                        <i class="fas fa-info-circle text-indigo-500 mt-1"></i>
                        <div class="text-xs font-semibold text-indigo-800 leading-relaxed">
                            <p class="font-black uppercase tracking-wider mb-1">Guide de formatage :</p>
                            Fichier CSV (UTF-8). Délimiteurs autorisés : <code>;</code> ou <code>,</code>. 
                            Vérifiez l'exactitude des en-têtes avant l'envoi.
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Import général</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Importer DREN, CISCO, centre de correction et centre d'écrit en même temps. Les doublons ne sont pas ajoutés.
                            Format accepté: avec en-têtes ou directement 4 colonnes dans l'ordre
                            <code>DREN</code>, <code>CISCO</code>, <code>Centre correction</code>, <code>Centre écrit</code>.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('imports.references') }}" enctype="multipart/form-data" class="flex flex-col gap-4 md:flex-row md:items-end">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Type d'examen</label>
                            <select name="type_examen" required class="w-full rounded-xl border border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-bold focus:bg-white outline-none ring-indigo-500/20 focus:ring-4 transition-all">
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" name="references_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500">
                        </div>
                        <button type="submit" class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-black text-white uppercase tracking-widest transition-all hover:bg-indigo-600 hover:shadow-xl hover:shadow-indigo-100">
                            Import général
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="import-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg"><i class="fas fa-map"></i></div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">1. DREN</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Schéma : [nom]</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('imports.drens') }}" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
                        @csrf
                        <div class="file-input-wrapper mb-6">
                            <input type="file" name="drens_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-slate-900 py-3 text-sm font-black text-white uppercase tracking-widest transition-all hover:bg-indigo-600 hover:shadow-xl hover:shadow-indigo-100">
                            Importer DREN
                        </button>
                    </form>
                </div>

                <div class="import-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg"><i class="fas fa-city"></i></div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">2. CISCO</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Schéma : [dren, nom]</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('imports.ciscos') }}" enctype="multipart/form-data" class="flex-1 flex flex-col justify-between">
                        @csrf
                        <div class="file-input-wrapper mb-6">
                            <input type="file" name="ciscos_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-indigo-600 py-3 text-sm font-black text-white uppercase tracking-widest transition-all hover:bg-indigo-700 hover:shadow-xl hover:shadow-indigo-100">
                            Importer CISCO
                        </button>
                    </form>
                </div>

                <div class="import-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg"><i class="fas fa-pen-nib"></i></div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">3. Correction</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Schéma : [dren, cisco, nom]</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('imports.centres.correction') }}" enctype="multipart/form-data" class="flex-1 flex flex-col gap-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Type d'examen</label>
                            <select name="type_examen" required class="w-full rounded-xl border border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-bold focus:bg-white outline-none ring-indigo-500/20 focus:ring-4 transition-all">
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" name="centres_correction_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500">
                        </div>
                        <button type="submit" class="w-full mt-auto rounded-xl bg-emerald-600 py-3 text-sm font-black text-white uppercase tracking-widest transition-all hover:bg-emerald-700 hover:shadow-xl hover:shadow-emerald-100">
                            Importer Correction
                        </button>
                    </form>
                </div>

                <div class="import-card rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg"><i class="fas fa-file-signature"></i></div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">4. Écrit</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Schéma : [dren, cisco, centre_correction, nom]</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('imports.centres.ecrit') }}" enctype="multipart/form-data" class="flex-1 flex flex-col gap-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase ml-1">Type d'examen</label>
                            <select name="type_examen" required class="w-full rounded-xl border border-slate-100 bg-slate-50 px-4 py-2.5 text-xs font-bold focus:bg-white outline-none ring-indigo-500/20 focus:ring-4 transition-all">
                                <option value="BEPC">BEPC</option>
                                <option value="CEPE">CEPE</option>
                            </select>
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" name="centres_ecrit_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500">
                        </div>
                        <button type="submit" class="w-full mt-auto rounded-xl bg-blue-600 py-3 text-sm font-black text-white uppercase tracking-widest transition-all hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-100">
                            Importer Écrit
                        </button>
                    </form>
                </div>

            </div>
        </main>
    </div>
</div>

<style>
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slideDown { animation: slideDown 0.4s ease-out forwards; }
</style>

</body>
</html>
