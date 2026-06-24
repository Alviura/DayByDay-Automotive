<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\ScopesSales;
use Illuminate\Support\Collection;

class PaymentMethodMixReportQuery extends AbstractReportQuery
{
    use ScopesSales;

    public function run(ReportFilters $filters): array
    {
        $rows = \App\Models\Payment::query()
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->when($filters->shopId, fn ($q) => $q->where('sales.shop_id', $filters->shopId))
            ->selectRaw('payments.method, COUNT(*) as payments, SUM(payments.amount) as total')
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $payment = new \App\Models\Payment(['method' => $row->method]);
                $row->method_label = $payment->methodLabel();

                return $row;
            });

        return [
            'summary' => ['total' => (float) $rows->sum('total'), 'methods' => $rows->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn ($r) => [
                'Method' => $r->method_label,
                'Payments' => $r->payments,
                'Amount' => $r->total,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Method', 'Payments', 'Amount'];
    }
}
