<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkQuotationItemsRequest;
use App\Http\Requests\StoreQuotationSeriesRequest;
use App\Http\Requests\UpdateQuotationPricesRequest;
use App\Http\Requests\UpdateQuotationSeriesRequest;
use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationSeries;
use App\Models\Supplier;
use App\Services\Procurement\QuotationCalculationService;
use App\Services\Procurement\QuotationSeriesService;
use App\Services\ProcurementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuotationSeriesController extends Controller
{
    public function __construct(
        private QuotationSeriesService $seriesService,
        private QuotationCalculationService $calculator,
        private ProcurementService $procurement,
    ) {
        $this->middleware('permission:procurement.view')->only(['index', 'show', 'export']);
        $this->middleware('permission:procurement.manage')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'searchProducts', 'bulkAddItems', 'destroyItem', 'proceedToOrder',
            'updatePrices', 'calculate', 'confirmOrder',
            'generatePo', 'markInTransit', 'close',
        ]);
    }

    public function index(Request $request): View
    {
        $seriesList = QuotationSeries::query()
            ->with(['supplier', 'creator'])
            ->withCount('items')
            ->when($request->search, fn ($q) => $q->where(function ($query) use ($request) {
                $query->where('series_number', 'like', "%{$request->search}%")
                    ->orWhere('title', 'like', "%{$request->search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$request->search}%"));
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->purchase_type, fn ($q) => $q->where('purchase_type', $request->purchase_type))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort === 'cost', fn ($q) => $q->orderByDesc('total_actual_cost'))
            ->when(! in_array($request->sort, ['oldest', 'cost'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $baseQuery = QuotationSeries::query();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'quotation_draft' => (clone $baseQuery)->where('status', 'quotation_draft')->count(),
            'order_draft' => (clone $baseQuery)->where('status', 'order_draft')->count(),
            'approved' => (clone $baseQuery)->whereIn('status', ['approved', 'po_generated'])->count(),
            'in_transit' => (clone $baseQuery)->where('status', 'in_transit')->count(),
            'received' => (clone $baseQuery)->where('status', 'received')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            'total_cost' => (float) (clone $baseQuery)->sum('total_actual_cost'),
            'total_margin' => (float) (clone $baseQuery)->sum('total_expected_margin'),
            'local' => (clone $baseQuery)->where('purchase_type', 'local')->count(),
            'import' => (clone $baseQuery)->where('purchase_type', 'import')->count(),
            'this_month' => (clone $baseQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $statusBreakdown = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'label' => (new QuotationSeries(['status' => $row->status]))->statusLabel(),
                'count' => (int) $row->count,
            ]);

        $monthlyTrend = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(COALESCE(total_actual_cost, 0)) as value")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'label' => \Illuminate\Support\Carbon::createFromFormat('Y-m', $row->month)->format('M Y'),
                'count' => (int) $row->count,
                'value' => (float) $row->value,
            ]);

        $pipeline = [
            ['key' => 'quotation_draft', 'label' => 'Quotation', 'icon' => 'fa-pen', 'count' => $stats['quotation_draft']],
            ['key' => 'order_draft', 'label' => 'Order', 'icon' => 'fa-calculator', 'count' => $stats['order_draft']],
            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fa-circle-check', 'count' => (clone $baseQuery)->where('status', 'approved')->count()],
            ['key' => 'po_generated', 'label' => 'PO Sent', 'icon' => 'fa-file-invoice', 'count' => (clone $baseQuery)->where('status', 'po_generated')->count()],
            ['key' => 'in_transit', 'label' => 'In Transit', 'icon' => 'fa-truck', 'count' => $stats['in_transit']],
            ['key' => 'received', 'label' => 'Received', 'icon' => 'fa-box-open', 'count' => $stats['received']],
            ['key' => 'closed', 'label' => 'Closed', 'icon' => 'fa-flag-checkered', 'count' => $stats['closed']],
        ];

        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);

        $chartData = [
            'status' => [
                'labels' => $statusBreakdown->pluck('label')->values()->all(),
                'counts' => $statusBreakdown->pluck('count')->values()->all(),
            ],
            'monthly' => [
                'labels' => $monthlyTrend->pluck('label')->values()->all(),
                'counts' => $monthlyTrend->pluck('count')->values()->all(),
                'values' => $monthlyTrend->pluck('value')->values()->all(),
            ],
            'types' => [
                'labels' => ['Local', 'Import'],
                'counts' => [$stats['local'], $stats['import']],
            ],
        ];

        return view('quotation-series.index', compact(
            'seriesList', 'stats', 'suppliers', 'statusBreakdown', 'monthlyTrend', 'pipeline', 'chartData'
        ));
    }

    public function create(): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('quotation-series.create', compact('suppliers'));
    }

    public function store(StoreQuotationSeriesRequest $request): RedirectResponse
    {
        $supplier = Supplier::findOrFail($request->supplier_id);

        $series = $this->seriesService->createFromSupplier(
            $supplier,
            $request->validated(),
            $request->user()
        );

        return redirect()->route('quotation-series.show', $series)
            ->with('status', 'Quotation series created. Add quotation products next.');
    }

    public function show(QuotationSeries $quotationSeries): View
    {
        $quotationSeries->load([
            'supplier', 'creator', 'approver',
            'items.product.productName', 'items.product.vehicleMake', 'items.product.vehicleModel', 'items.product.unit',
            'purchaseOrders.items', 'goodsReceiptNotes.warehouse', 'approval',
        ]);

        return view('quotation-series.show', [
            'series' => $quotationSeries,
        ]);
    }

    public function searchProducts(Request $request, QuotationSeries $quotationSeries): JsonResponse
    {
        if (! $quotationSeries->canBulkAddItems()) {
            return response()->json([]);
        }

        $term = trim((string) $request->query('q', ''));
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $excludeIds = $quotationSeries->items()->pluck('product_id')->all();

        $products = Product::query()
            ->active()
            ->where(function ($query) use ($term) {
                $query->search($term)
                    ->orWhereHas('vehicleMake', fn ($make) => $make->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('vehicleModel', fn ($model) => $model->where('name', 'like', "%{$term}%"));
            })
            ->when($excludeIds !== [], fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->with(['productName:id,name', 'vehicleMake:id,name', 'vehicleModel:id,name', 'unit:id,name,abbreviation'])
            ->orderBy('part_number')
            ->limit(25)
            ->get(['id', 'part_number', 'name', 'product_name_id', 'vehicle_make_id', 'vehicle_model_id', 'unit_id']);

        return response()->json(
            $products->map(fn (Product $product) => [
                'id' => $product->id,
                'part_number' => $product->part_number,
                'name' => $product->productName?->name ?? $product->name,
                'make' => $product->vehicleMake?->name ?? '',
                'vehicle' => $product->vehicleModel?->name ?? '',
                'unit' => $product->unit?->abbreviation ?? $product->unit?->name ?? '',
            ])->values()
        );
    }

    public function edit(QuotationSeries $quotationSeries): View|RedirectResponse
    {
        if (! $quotationSeries->canEditHeader()) {
            return redirect()->route('quotation-series.show', $quotationSeries)
                ->with('error', 'This quotation series cannot be edited.');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('quotation-series.edit', [
            'series' => $quotationSeries,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(UpdateQuotationSeriesRequest $request, QuotationSeries $quotationSeries): RedirectResponse
    {
        if (! $quotationSeries->canEditHeader()) {
            return back()->with('error', 'This quotation series cannot be edited.');
        }

        $supplier = Supplier::findOrFail($request->supplier_id);

        $quotationSeries->update([
            'supplier_id' => $supplier->id,
            'title' => QuotationSeries::generateTitle($supplier, $request->description),
            'description' => $request->description,
            'currency' => strtoupper($request->currency),
            'purchase_type' => $request->purchase_type,
            'import_type' => $request->purchase_type,
            'exchange_rate' => $request->purchase_type === 'import' ? ($request->exchange_rate ?? 1) : 1,
            'cbm_rate' => $request->purchase_type === 'import' ? $request->cbm_rate : null,
            'notes' => $request->notes,
        ]);

        return redirect()->route('quotation-series.show', $quotationSeries)
            ->with('status', 'Quotation series updated.');
    }

    public function destroy(QuotationSeries $quotationSeries): RedirectResponse
    {
        if (! in_array($quotationSeries->status, ['quotation_draft', 'draft'], true)) {
            return back()->with('error', 'Only quotation drafts can be deleted.');
        }

        $quotationSeries->delete();

        return redirect()->route('quotation-series.index')
            ->with('status', 'Quotation series deleted.');
    }

    public function bulkAddItems(BulkQuotationItemsRequest $request, QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->seriesService->bulkAddItems(
                $quotationSeries,
                array_values($request->validated('items'))
            );

            return back()->with('status', 'Products added to quotation.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyItem(QuotationSeries $quotationSeries, QuotationItem $item): RedirectResponse
    {
        if ($item->quotation_series_id !== $quotationSeries->id || ! $quotationSeries->canBulkAddItems()) {
            return back()->with('error', 'Cannot remove this item.');
        }

        $item->delete();

        return back()->with('status', 'Line item removed.');
    }

    public function proceedToOrder(QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->seriesService->proceedToOrder($quotationSeries);

            return redirect()->route('quotation-series.show', $quotationSeries)
                ->with('status', 'Ready for order processing. Enter supplier prices.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePrices(UpdateQuotationPricesRequest $request, QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->seriesService->updatePrices($quotationSeries, $request->validated('items'));

            return back()
                ->with('status', 'Prices saved.')
                ->with('prices_panel_collapsed', true);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function calculate(QuotationSeries $quotationSeries): RedirectResponse
    {
        if (! $quotationSeries->canCalculate()) {
            return back()->with('error', 'This quotation series cannot be calculated.');
        }

        try {
            $this->calculator->calculate($quotationSeries);

            return back()->with('status', 'Order calculations completed.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmOrder(QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->calculator->confirmOrder($quotationSeries, auth()->user());

            return back()->with('status', 'Order confirmed and approved.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(QuotationSeries $quotationSeries, string $format): Response|StreamedResponse|RedirectResponse
    {
        if (! $quotationSeries->canExportQuotation()) {
            return back()->with('error', 'Nothing to export.');
        }

        $rows = $this->seriesService->quotationExportRows($quotationSeries);
        $filename = str($quotationSeries->displayName())->slug('-').'-quotation';

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['N/S', 'Part Number', 'Product Name', 'Make', 'Vehicle', 'Unit', 'Quantity', 'Unit Price']);
                foreach ($rows as $row) {
                    fputcsv($handle, array_values($row));
                }
                fclose($handle);
            }, "{$filename}.csv", ['Content-Type' => 'text/csv']);
        }

        if ($format === 'print') {
            return response()->view('quotation-series.export-print', [
                'series' => $quotationSeries,
                'rows' => $rows,
            ]);
        }

        return back()->with('error', 'Unsupported export format.');
    }

    public function generatePo(QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $po = $this->procurement->generatePurchaseOrder($quotationSeries);

            return redirect()->route('purchase-orders.show', $po)
                ->with('status', 'Purchase order '.$po->po_number.' generated.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markInTransit(QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->procurement->markInTransit($quotationSeries);

            return back()->with('status', 'Quotation series and PO marked in transit.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function close(QuotationSeries $quotationSeries): RedirectResponse
    {
        try {
            $this->procurement->closeSeries($quotationSeries);

            return back()->with('status', 'Quotation series closed.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function supplierDefaults(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'currency' => $supplier->currency ?? 'KES',
            'purchase_type' => $supplier->purchase_type ?? 'local',
            'exchange_rate' => ($supplier->purchase_type ?? 'local') === 'import' ? 1 : 1,
        ]);
    }
}
