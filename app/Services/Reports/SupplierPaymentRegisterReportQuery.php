<?php

namespace App\Services\Reports;

use App\Models\SupplierPayment;
use Illuminate\Support\Collection;

class SupplierPaymentRegisterReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $query = SupplierPayment::query()
            ->whereBetween('paid_at', [$filters->from, $filters->to])
            ->when($filters->supplierId, fn ($q) => $q->where('supplier_id', $filters->supplierId));

        $rows = (clone $query)
            ->with(['supplier:id,name', 'purchaseOrder:id,po_number'])
            ->latest('paid_at')
            ->limit(50)
            ->get();

        return [
            'summary' => [
                'payments' => (clone $query)->where('status', '!=', 'voided')->count(),
                'total' => (float) (clone $query)->where('status', '!=', 'voided')->sum('amount'),
            ],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            SupplierPayment::query()
                ->whereBetween('paid_at', [$filters->from, $filters->to])
                ->when($filters->supplierId, fn ($q) => $q->where('supplier_id', $filters->supplierId))
                ->with(['supplier:id,name', 'purchaseOrder:id,po_number'])
                ->orderBy('paid_at')
                ->get()
                ->map(fn (SupplierPayment $p) => [
                    'Reference' => $p->payment_number,
                    'Supplier' => $p->supplier?->name,
                    'PO' => $p->purchaseOrder?->po_number,
                    'Amount' => $p->amount,
                    'Method' => $p->method,
                    'Status' => $p->status,
                    'Paid At' => $p->paid_at?->format('Y-m-d'),
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Reference', 'Supplier', 'PO', 'Amount', 'Method', 'Status', 'Paid At'];
    }
}
