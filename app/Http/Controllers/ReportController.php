<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\Reports\FinancialReportQuery;
use App\Services\Reports\InventoryReportQuery;
use App\Services\Reports\ProcurementReportQuery;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\SalesReportQuery;
use App\Services\Reports\TransferReportQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private SalesReportQuery $salesReport,
        private InventoryReportQuery $inventoryReport,
        private ProcurementReportQuery $procurementReport,
        private TransferReportQuery $transferReport,
        private FinancialReportQuery $financialReport,
    ) {
        $this->middleware('permission:reports.view');
        $this->middleware('permission:reports.export')->only('export');
    }

    public function index(): View
    {
        return view('reports.index');
    }

    public function sales(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());
        $data = $this->salesReport->run($filters);

        return view('reports.sales', $this->reportViewData('sales', $filters, $data));
    }

    public function inventory(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());
        $data = $this->inventoryReport->run($filters);

        return view('reports.inventory', $this->reportViewData('inventory', $filters, $data));
    }

    public function procurement(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());
        $data = $this->procurementReport->run($filters);

        return view('reports.procurement', $this->reportViewData('procurement', $filters, $data));
    }

    public function transfers(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());
        $data = $this->transferReport->run($filters);

        return view('reports.transfers', $this->reportViewData('transfers', $filters, $data));
    }

    public function financial(Request $request): View
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());
        $data = $this->financialReport->run($filters);

        return view('reports.financial', $this->reportViewData('financial', $filters, $data));
    }

    public function export(Request $request, string $type): StreamedResponse|Response
    {
        $filters = ReportFilters::fromRequest($request, $this->scopedShopId());

        $rows = match ($type) {
            'sales' => $this->salesReport->csvRows($filters),
            'inventory' => $this->inventoryReport->csvRows($filters),
            'procurement' => $this->procurementReport->csvRows($filters),
            'transfers' => $this->transferReport->csvRows($filters),
            'financial' => $this->financialReport->csvRows($filters),
            default => abort(404),
        };

        $filename = "{$type}-report-".$filters->from->format('Ymd').'-'.$filters->to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $this->outputCsv($rows);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function reportViewData(string $type, ReportFilters $filters, array $data): array
    {
        return array_merge($data, [
            'filters' => $filters,
            'reportType' => $type,
            'shops' => Shop::active()->orderBy('name')->get(['id', 'name', 'code']),
            'scopedShopId' => $this->scopedShopId(),
        ]);
    }

    private function scopedShopId(): ?int
    {
        $user = auth()->user();

        if ($user->hasRole('Shop Manager') && $user->shop_id) {
            return (int) $user->shop_id;
        }

        return null;
    }

    private function outputCsv(Collection $rows): void
    {
        $handle = fopen('php://output', 'w');

        if ($rows->isEmpty()) {
            fclose($handle);

            return;
        }

        fputcsv($handle, array_keys($rows->first()));
        foreach ($rows as $row) {
            fputcsv($handle, array_values($row instanceof \Illuminate\Database\Eloquent\Model ? $row->toArray() : (array) $row));
        }

        fclose($handle);
    }
}
