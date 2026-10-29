@extends('layouts.app')

@section('title', 'Comptabilité des Matières')
@section('subtitle', 'Gestion du magasin, mouvements de stock et demandes d’approvisionnement')

@section('content')
<div class="space-y-6" data-inventory-page>
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Total matériels', $stats['total_materials'], 'bg-slate-900 text-white'],
            ['Disponibles', $stats['available'], 'bg-emerald-600 text-white'],
            ['Presque épuisés', $stats['low'], 'bg-orange-500 text-white'],
            ['Rupture', $stats['out'], 'bg-red-600 text-white'],
            ['Total sorties', number_format($stats['total_out'], 0, ',', ' '), 'bg-white text-slate-900'],
            ['Total entrées', number_format($stats['total_in'], 0, ',', ' '), 'bg-white text-slate-900'],
            ['Valeur stock', number_format($stats['stock_value'], 0, ',', ' ').' Ar', 'bg-white text-slate-900'],
            ['Alertes', $stats['alerts'], 'bg-white text-slate-900'],
        ] as [$label, $value, $class])
            <article class="rounded-lg border border-slate-200 p-4 shadow-sm {{ $class }}">
                <p class="text-xs font-bold uppercase opacity-70">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="stockChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="consumedChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="categoryChart" height="180"></canvas></div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"><canvas id="serviceChart" height="180"></canvas></div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" class="grid gap-3 md:grid-cols-5">
            <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Recherche instantanée" class="rounded-lg border-slate-300 text-sm">
            <select name="category" class="rounded-lg border-slate-300 text-sm">
                <option value="">Catégorie</option>
                @foreach($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach
            </select>
            <select name="status" class="rounded-lg border-slate-300 text-sm">
                <option value="">Statut stock</option>
                <option value="disponible" @selected(($filters['status'] ?? '') === 'disponible')>Stock suffisant</option>
                <option value="alerte" @selected(($filters['status'] ?? '') === 'alerte')>Approvisionnement nécessaire</option>
                <option value="rupture" @selected(($filters['status'] ?? '') === 'rupture')>Rupture</option>
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border-slate-300 text-sm">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border-slate-300 text-sm">
            <div class="flex flex-wrap gap-2 md:col-span-5">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold">Réinitialiser</a>
                <a href="{{ route('inventory.export.excel', request()->query()) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Exporter Excel</a>
                <a href="{{ route('inventory.export.pdf', request()->query()) }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white">Exporter PDF</a>
                <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold">Imprimer</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-3">
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm xl:col-span-2">
            <h2 class="text-base font-black">Ajouter un matériel</h2>
            <form method="POST" action="{{ route('inventory.materials.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">
                @csrf
                <input name="code" placeholder="Code matériel" class="rounded-lg border-slate-300 text-sm" required>
                <input name="name" placeholder="Nom du matériel" class="rounded-lg border-slate-300 text-sm" required>
                <input name="category" placeholder="Catégorie" class="rounded-lg border-slate-300 text-sm" required>
                <input name="unit" placeholder="Unité" value="unité" class="rounded-lg border-slate-300 text-sm" required>
                <input type="number" min="0" name="initial_quantity" placeholder="Quantité initiale" class="rounded-lg border-slate-300 text-sm" required>
                <input type="number" min="0" name="available_quantity" placeholder="Quantité disponible" class="rounded-lg border-slate-300 text-sm" required>
                <input type="number" min="0" name="minimum_threshold" placeholder="Seuil minimum" class="rounded-lg border-slate-300 text-sm" required>
                <input type="number" min="0" step="0.01" name="unit_price" placeholder="Prix unitaire" class="rounded-lg border-slate-300 text-sm" required>
                <select name="supplier_id" class="rounded-lg border-slate-300 text-sm"><option value="">Fournisseur</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select>
                <input type="date" name="acquired_at" class="rounded-lg border-slate-300 text-sm">
                <input name="condition" value="Bon" placeholder="Etat" class="rounded-lg border-slate-300 text-sm" required>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white">Ajouter</button>
                <textarea name="description" placeholder="Description" class="rounded-lg border-slate-300 text-sm md:col-span-2"></textarea>
                <textarea name="observations" placeholder="Observations" class="rounded-lg border-slate-300 text-sm md:col-span-2"></textarea>
            </form>
        </article>

        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-black">Fournisseur</h2>
            <form method="POST" action="{{ route('inventory.suppliers.store') }}" class="mt-4 space-y-3">
                @csrf
                <input name="name" placeholder="Nom" class="w-full rounded-lg border-slate-300 text-sm" required>
                <input name="address" placeholder="Adresse" class="w-full rounded-lg border-slate-300 text-sm">
                <input name="phone" placeholder="Téléphone" class="w-full rounded-lg border-slate-300 text-sm">
                <input type="email" name="email" placeholder="Email" class="w-full rounded-lg border-slate-300 text-sm">
                <textarea name="supplied_products" placeholder="Produits fournis" class="w-full rounded-lg border-slate-300 text-sm"></textarea>
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Enregistrer</button>
            </form>
        </article>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4"><h2 class="font-black">Gestion des matériels</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">
                    <tr><th class="px-3 py-3">Code</th><th class="px-3 py-3">Matériel</th><th class="px-3 py-3">Catégorie</th><th class="px-3 py-3">Disponible</th><th class="px-3 py-3">Seuil</th><th class="px-3 py-3">Valeur</th><th class="px-3 py-3">Fournisseur</th><th class="px-3 py-3">Alerte</th><th class="px-3 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($materials as $material)
                        <tr>
                            <td class="px-3 py-3 font-bold">{{ $material->code }}</td>
                            <td class="px-3 py-3">{{ $material->name }}<br><span class="text-xs text-slate-500">{{ $material->condition }}</span></td>
                            <td class="px-3 py-3">{{ $material->category }}</td>
                            <td class="px-3 py-3">{{ $material->available_quantity }} {{ $material->unit }}</td>
                            <td class="px-3 py-3">{{ $material->minimum_threshold }}</td>
                            <td class="px-3 py-3">{{ number_format($material->total_value, 0, ',', ' ') }} Ar</td>
                            <td class="px-3 py-3">{{ $material->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-3">
                                @if($material->needsSupply())
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">Approvisionnement nécessaire</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">Stock suffisant</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <details class="mb-2">
                                    <summary class="cursor-pointer rounded bg-slate-900 px-3 py-1.5 text-xs font-bold text-white">Modifier</summary>
                                    <form method="POST" action="{{ route('inventory.materials.update', $material) }}" class="mt-3 grid w-80 gap-2">
                                        @csrf @method('PUT')
                                        <input name="code" value="{{ $material->code }}" class="rounded border-slate-300 text-xs" required>
                                        <input name="name" value="{{ $material->name }}" class="rounded border-slate-300 text-xs" required>
                                        <input name="category" value="{{ $material->category }}" class="rounded border-slate-300 text-xs" required>
                                        <input name="unit" value="{{ $material->unit }}" class="rounded border-slate-300 text-xs" required>
                                        <input type="number" name="initial_quantity" value="{{ $material->initial_quantity }}" class="rounded border-slate-300 text-xs" required>
                                        <input type="number" name="available_quantity" value="{{ $material->available_quantity }}" class="rounded border-slate-300 text-xs" required>
                                        <input type="number" name="minimum_threshold" value="{{ $material->minimum_threshold }}" class="rounded border-slate-300 text-xs" required>
                                        <input type="number" step="0.01" name="unit_price" value="{{ $material->unit_price }}" class="rounded border-slate-300 text-xs" required>
                                        <input name="condition" value="{{ $material->condition }}" class="rounded border-slate-300 text-xs" required>
                                        <textarea name="description" class="rounded border-slate-300 text-xs">{{ $material->description }}</textarea>
                                        <textarea name="observations" class="rounded border-slate-300 text-xs">{{ $material->observations }}</textarea>
                                        <button class="rounded bg-blue-600 px-3 py-1.5 text-xs font-bold text-white">Enregistrer</button>
                                    </form>
                                </details>
                                @can('delete', $material)
                                    <form method="POST" action="{{ route('inventory.materials.destroy', $material) }}" onsubmit="return confirm('Supprimer ce matériel ?')">@csrf @method('DELETE')<button class="rounded bg-red-600 px-3 py-1.5 text-xs font-bold text-white">Supprimer</button></form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center font-bold text-slate-500">Aucun matériel enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $materials->links() }}</div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="font-black">Mouvement de stock</h2>
            <form method="POST" action="{{ route('inventory.movements.store') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf
                <input type="date" name="movement_date" value="{{ date('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm" required>
                <input name="voucher_number" placeholder="Numéro de sortie" class="rounded-lg border-slate-300 text-sm">
                <input name="requester_name" placeholder="Nom du demandeur" class="rounded-lg border-slate-300 text-sm">
                <input name="requesting_service" placeholder="Service demandeur" class="rounded-lg border-slate-300 text-sm">
                <input name="function" placeholder="Fonction" class="rounded-lg border-slate-300 text-sm">
                <select name="material_id" class="rounded-lg border-slate-300 text-sm" required>
                    <option value="">Matériel demandé</option>
                    @foreach($materialsAll as $material)<option value="{{ $material->id }}">{{ $material->name }} · stock {{ $material->available_quantity }}</option>@endforeach
                </select>
                <select name="movement_type" class="rounded-lg border-slate-300 text-sm"><option value="sortie">Sortie</option><option value="entree">Entrée</option></select>
                <input type="number" min="0" name="requested_quantity" placeholder="Quantité demandée" class="rounded-lg border-slate-300 text-sm" required>
                <input type="number" min="0" name="granted_quantity" placeholder="Quantité accordée" class="rounded-lg border-slate-300 text-sm" required>
                <input name="signature" placeholder="Signature" class="rounded-lg border-slate-300 text-sm">
                <textarea name="reason" placeholder="Motif" class="rounded-lg border-slate-300 text-sm"></textarea>
                <textarea name="observation" placeholder="Observation" class="rounded-lg border-slate-300 text-sm"></textarea>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white md:col-span-2">Valider le mouvement</button>
            </form>
        </article>

        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="font-black">Demande d’approvisionnement</h2>
            <form method="POST" action="{{ route('inventory.orders.store') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf
                <select name="material_id" class="rounded-lg border-slate-300 text-sm" required>
                    <option value="">Matériel</option>
                    @foreach($materialsAll as $material)<option value="{{ $material->id }}">{{ $material->name }} · reste {{ $material->available_quantity }}</option>@endforeach
                </select>
                <input type="number" min="1" name="quantity_to_order" placeholder="Quantité à commander" class="rounded-lg border-slate-300 text-sm" required>
                <input type="date" name="order_date" value="{{ date('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm" required>
                <select name="status" class="rounded-lg border-slate-300 text-sm"><option value="demandee">Demandée</option><option value="validee">Validée</option><option value="livree">Livrée</option></select>
                <textarea name="observation" placeholder="Observation" class="rounded-lg border-slate-300 text-sm md:col-span-2"></textarea>
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white md:col-span-2">Créer la demande</button>
            </form>
            <div class="mt-5 space-y-2">
                @foreach($orders as $order)
                    <div class="rounded-lg border border-slate-200 p-3 text-sm"><b>{{ $order->material?->name }}</b> · reste {{ $order->remaining_quantity }} · commander {{ $order->quantity_to_order }} · {{ $order->status }}</div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4"><h2 class="font-black">Historique complet des mouvements</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">
                    <tr><th class="px-3 py-3">Date</th><th class="px-3 py-3">N° sortie</th><th class="px-3 py-3">Demandeur</th><th class="px-3 py-3">Service</th><th class="px-3 py-3">Matériel</th><th class="px-3 py-3">Demandé</th><th class="px-3 py-3">Accordé</th><th class="px-3 py-3">Avant</th><th class="px-3 py-3">Restant</th><th class="px-3 py-3">Responsable</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($movements as $movement)
                        <tr>
                            <td class="px-3 py-3">{{ $movement->movement_date?->format('d/m/Y') }}</td><td class="px-3 py-3">{{ $movement->voucher_number }}</td><td class="px-3 py-3">{{ $movement->requester_name }}</td><td class="px-3 py-3">{{ $movement->requesting_service }}</td><td class="px-3 py-3">{{ $movement->material?->name }}</td><td class="px-3 py-3">{{ $movement->requested_quantity }}</td><td class="px-3 py-3">{{ $movement->granted_quantity }}</td><td class="px-3 py-3">{{ $movement->stock_before }}</td><td class="px-3 py-3 font-bold">{{ $movement->stock_after }}</td><td class="px-3 py-3">{{ $movement->validator?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">{{ $movements->links() }}</div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const inventoryCharts = @json($charts);
const renderInventoryChart = (id, type, source, label, color) => new Chart(document.getElementById(id), {
    type, data: { labels: Object.keys(source), datasets: [{ label, data: Object.values(source), backgroundColor: color, borderColor: color, tension: .35 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
renderInventoryChart('stockChart', 'line', inventoryCharts.stock, 'Evolution du stock', '#2563eb');
renderInventoryChart('consumedChart', 'bar', inventoryCharts.consumed, 'Plus consommés', '#dc2626');
renderInventoryChart('categoryChart', 'doughnut', inventoryCharts.categories, 'Catégories', ['#0f172a', '#059669', '#f97316', '#2563eb', '#7c3aed']);
renderInventoryChart('serviceChart', 'bar', inventoryCharts.services, 'Services consommateurs', '#0891b2');
</script>
@endpush
