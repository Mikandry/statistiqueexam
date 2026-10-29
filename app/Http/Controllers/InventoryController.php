<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryMaterial;
use App\Models\InventoryStockMovement;
use App\Models\InventorySupplier;
use App\Models\InventorySupplyOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryMaterial::class);

        $filters = $request->only(['search', 'category', 'status', 'date_from', 'date_to']);
        $materialsQuery = InventoryMaterial::query()
            ->with('supplier')
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, function ($query, $status): void {
                if ($status === 'rupture') {
                    $query->where('available_quantity', '<=', 0);
                } elseif ($status === 'alerte') {
                    $query->whereColumn('available_quantity', '<=', 'minimum_threshold')->where('available_quantity', '>', 0);
                } elseif ($status === 'disponible') {
                    $query->whereColumn('available_quantity', '>', 'minimum_threshold');
                }
            });

        $materialsAll = (clone $materialsQuery)->get();
        $materials = $materialsQuery->orderBy('name')->paginate(15)->withQueryString();

        $movements = InventoryStockMovement::query()
            ->with(['material', 'validator'])
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->whereHas('material', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('requesting_service', 'like', "%{$search}%");
            })
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('movement_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('movement_date', '<=', $date))
            ->latest('movement_date')
            ->paginate(15, ['*'], 'mouvements')
            ->withQueryString();

        $orders = InventorySupplyOrder::query()->with('material')->latest('order_date')->take(20)->get();
        $suppliers = InventorySupplier::query()->orderBy('name')->get();

        return view('inventory.index', [
            'materials' => $materials,
            'materialsAll' => $materialsAll,
            'movements' => $movements,
            'orders' => $orders,
            'suppliers' => $suppliers,
            'filters' => $filters,
            'stats' => $this->buildStats(),
            'charts' => $this->buildCharts(),
            'categories' => InventoryMaterial::query()->select('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function storeMaterial(Request $request)
    {
        $this->authorize('create', InventoryMaterial::class);

        $data = $this->validateMaterial($request);
        $data['available_quantity'] = $data['initial_quantity']; // le stock de départ est la quantité initiale
        $material = InventoryMaterial::query()->create($data);
        AuditLog::record($request, 'inventory_material_created', ['material_id' => $material->id]);

        return back()->with('success', 'Matériel enregistré.');
    }

    public function updateMaterial(Request $request, InventoryMaterial $material)
    {
        $this->authorize('update', $material);

        $data = $this->validateMaterial($request, $material);
        // Le stock ne se corrige que par un mouvement tracé : jamais en modifiant directement la quantité.
        $stockChangeIgnored = (int) $data['available_quantity'] !== (int) $material->available_quantity
            || (int) $data['initial_quantity'] !== (int) $material->initial_quantity;
        unset($data['available_quantity'], $data['initial_quantity']);

        $material->update($data);
        AuditLog::record($request, 'inventory_material_updated', ['material_id' => $material->id]);

        return back()->with('success', $stockChangeIgnored
            ? 'Matériel mis à jour. Les quantités n\'ont pas été modifiées : enregistrez un mouvement (entrée, sortie ou retour) pour corriger le stock.'
            : 'Matériel mis à jour.');
    }

    public function destroyMaterial(Request $request, InventoryMaterial $material)
    {
        $this->authorize('delete', $material);

        $material->delete();
        AuditLog::record($request, 'inventory_material_deleted', ['material_id' => $material->id]);

        return back()->with('success', 'Matériel supprimé.');
    }

    public function storeMovement(Request $request)
    {
        $this->authorize('create', InventoryMaterial::class);

        $data = $request->validate([
            'material_id' => ['required', 'exists:inventory_materials,id'],
            'movement_date' => ['required', 'date'],
            'movement_type' => ['required', 'in:'.implode(',', array_keys(InventoryStockMovement::TYPES))],
            'voucher_number' => ['nullable', 'string', 'max:120'],
            'supporting_document' => ['nullable', 'string', 'max:255'],
            'request_token' => ['nullable', 'string', 'max:64'],
            'requester_name' => ['nullable', 'string', 'max:180'],
            'requesting_service' => ['nullable', 'string', 'max:180'],
            'function' => ['nullable', 'string', 'max:180'],
            'requested_quantity' => ['required', 'integer', 'min:0'],
            'granted_quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'signature' => ['nullable', 'string', 'max:180'],
            'observation' => ['nullable', 'string', 'max:5000'],
        ]);

        // Double envoi du même formulaire : le mouvement existe déjà, rien n'est enregistré une seconde fois.
        if (! empty($data['request_token']) && InventoryStockMovement::query()->where('request_token', $data['request_token'])->exists()) {
            return back()->with('success', 'Ce mouvement était déjà enregistré : aucun doublon créé.');
        }

        if ($data['movement_type'] === InventoryStockMovement::TYPE_OUT && (int) $data['granted_quantity'] > (int) $data['requested_quantity']) {
            throw ValidationException::withMessages(['granted_quantity' => 'La quantité accordée dépasse la quantité demandée.']);
        }

        DB::transaction(function () use ($request, $data): void {
            $material = InventoryMaterial::query()->lockForUpdate()->findOrFail($data['material_id']);

            // Un même numéro de pièce ne peut pas servir deux fois pour le même matériel et le même type.
            if (! empty($data['voucher_number']) && InventoryStockMovement::query()
                ->where('material_id', $material->id)
                ->where('movement_type', $data['movement_type'])
                ->where('voucher_number', $data['voucher_number'])
                ->exists()) {
                throw ValidationException::withMessages(['voucher_number' => 'Ce numéro de pièce est déjà utilisé pour ce matériel et ce type de mouvement.']);
            }

            $stockBefore = (int) $material->available_quantity;
            $granted = (int) $data['granted_quantity'];
            $increases = in_array($data['movement_type'], InventoryStockMovement::IN_TYPES, true);
            if (! $increases && $granted > $stockBefore) {
                throw ValidationException::withMessages([
                    'granted_quantity' => 'La quantité accordée dépasse le stock disponible.',
                ]);
            }

            $stockAfter = $increases ? $stockBefore + $granted : $stockBefore - $granted;

            $movement = InventoryStockMovement::query()->create(array_merge($data, [
                'validated_by' => $request->user()->id,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]));

            $material->update(['available_quantity' => $stockAfter]);
            AuditLog::record($request, 'inventory_stock_movement_created', ['movement_id' => $movement->id]);
        });

        return back()->with('success', 'Mouvement de stock enregistré.');
    }

    /** Historique complet d'un matériel, avec rapprochement du stock. */
    public function history(InventoryMaterial $material)
    {
        $this->authorize('viewAny', InventoryMaterial::class);

        return view('inventory.history', [
            'material' => $material->load('supplier'),
            'movements' => $material->movements()->with('validator')->orderBy('movement_date')->orderBy('id')->get(),
            'reconciliation' => $material->reconciliation(),
        ]);
    }

    /** Rapprochement de tous les matériels : stock constaté contre stock reconstitué par les mouvements. */
    public function reconciliation()
    {
        $this->authorize('viewAny', InventoryMaterial::class);

        $rows = InventoryMaterial::query()->orderBy('name')->get()
            ->map(fn (InventoryMaterial $m) => ['material' => $m, 'rec' => $m->reconciliation()]);

        return view('inventory.reconciliation', [
            'rows' => $rows,
            'gaps' => $rows->filter(fn ($r) => $r['rec']['gap'] !== 0)->count(),
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $this->authorize('create', InventoryMaterial::class);

        InventorySupplier::query()->create($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:180'],
            'supplied_products' => ['nullable', 'string', 'max:5000'],
        ]));

        AuditLog::record($request, 'inventory_supplier_created');

        return back()->with('success', 'Fournisseur ajouté.');
    }

    public function storeOrder(Request $request)
    {
        $this->authorize('create', InventoryMaterial::class);

        $data = $request->validate([
            'material_id' => ['required', 'exists:inventory_materials,id'],
            'order_date' => ['required', 'date'],
            'quantity_to_order' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'max:80'],
            'observation' => ['nullable', 'string', 'max:5000'],
        ]);

        $material = InventoryMaterial::query()->findOrFail($data['material_id']);
        $order = InventorySupplyOrder::query()->create(array_merge($data, [
            'remaining_quantity' => $material->available_quantity,
        ]));

        AuditLog::record($request, 'inventory_supply_order_created', ['order_id' => $order->id]);

        return back()->with('success', 'Demande d’approvisionnement créée.');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', InventoryMaterial::class);

        $materials = InventoryMaterial::query()->with('supplier')->orderBy('name')->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            'Code', 'Matériel', 'Catégorie', 'Unité', 'Initial', 'Disponible',
            'Seuil', 'Prix unitaire', 'Valeur', 'Fournisseur', 'Etat',
        ], null, 'A1');

        foreach ($materials as $index => $material) {
            $sheet->fromArray([
                $material->code,
                $material->name,
                $material->category,
                $material->unit,
                $material->initial_quantity,
                $material->available_quantity,
                $material->minimum_threshold,
                $material->unit_price,
                $material->total_value,
                $material->supplier?->name,
                $material->condition,
            ], null, 'A'.($index + 2));
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'comptabilite-matieres.xlsx');
    }

    public function exportPdf()
    {
        $this->authorize('viewAny', InventoryMaterial::class);

        return Pdf::loadView('inventory.pdf', [
            'materials' => InventoryMaterial::query()->with('supplier')->orderBy('name')->get(),
            'stats' => $this->buildStats(),
        ])->setPaper('a4', 'landscape')->download('comptabilite-matieres.pdf');
    }

    private function validateMaterial(Request $request, ?InventoryMaterial $material = null): array
    {
        $id = $material?->id ?? 'NULL';

        return $request->validate([
            'supplier_id' => ['nullable', 'exists:inventory_suppliers,id'],
            'code' => ['required', 'string', 'max:80', 'unique:inventory_materials,code,'.$id],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:60'],
            'initial_quantity' => ['required', 'integer', 'min:0'],
            'available_quantity' => ['required', 'integer', 'min:0'],
            'minimum_threshold' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'acquired_at' => ['nullable', 'date'],
            'condition' => ['required', 'string', 'max:120'],
            'observations' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function buildStats(): array
    {
        $materials = InventoryMaterial::query()->get();

        return [
            'total_materials' => $materials->count(),
            'available' => $materials->where('available_quantity', '>', 0)->count(),
            'low' => $materials->filter(fn ($item) => $item->available_quantity > 0 && $item->needsSupply())->count(),
            'out' => $materials->where('available_quantity', '<=', 0)->count(),
            'total_out' => InventoryStockMovement::query()->where('movement_type', 'sortie')->sum('granted_quantity'),
            'total_in' => InventoryStockMovement::query()->whereIn('movement_type', InventoryStockMovement::IN_TYPES)->sum('granted_quantity'),
            'stock_value' => $materials->sum('total_value'),
            'alerts' => $materials->filter->needsSupply()->count(),
        ];
    }

    private function buildCharts(): array
    {
        $monthExpr = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', movement_date)" : "DATE_FORMAT(movement_date, '%Y-%m')";
        $movementsByMonth = InventoryStockMovement::query()
            ->selectRaw("{$monthExpr} as month, SUM(CASE WHEN movement_type IN ('entree','retour') THEN granted_quantity ELSE -granted_quantity END) as quantity")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('quantity', 'month');

        $consumed = InventoryStockMovement::query()
            ->selectRaw('material_id, SUM(granted_quantity) as total')
            ->where('movement_type', 'sortie')
            ->groupBy('material_id')
            ->orderByDesc('total')
            ->take(8)
            ->with('material')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->material?->name ?? 'Matériel' => (int) $item->total]);

        $categories = InventoryMaterial::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('total', 'category');

        $services = InventoryStockMovement::query()
            ->selectRaw('requesting_service, SUM(granted_quantity) as total')
            ->where('movement_type', 'sortie')
            ->groupBy('requesting_service')
            ->orderByDesc('total')
            ->take(8)
            ->pluck('total', 'requesting_service');

        return [
            'stock' => $movementsByMonth,
            'consumed' => $consumed,
            'categories' => $categories,
            'services' => $services,
        ];
    }
}
