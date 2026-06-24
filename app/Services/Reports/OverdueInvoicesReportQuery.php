<?php

namespace App\Services\Reports;

use App\Models\CustomerInvoice;
use Illuminate\Support\Collection;

class OverdueInvoicesReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = CustomerInvoice::query()
            ->whereIn('status', ['sent', 'partially_paid'])
            ->where('due_at', '<', now()->startOfDay())
            ->with('account:id,name')
            ->orderBy('due_at')
            ->get()
            ->map(function (CustomerInvoice $invoice) {
                $invoice->balance = $invoice->balanceDue();
                $invoice->days_overdue = (int) $invoice->due_at?->diffInDays(now());

                return $invoice;
            })
            ->filter(fn ($i) => $i->balance > 0);

        return [
            'summary' => ['count' => $rows->count(), 'total' => (float) $rows->sum('balance')],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            $this->run($filters)['rows']->map(fn (CustomerInvoice $i) => [
                'Invoice' => $i->invoice_number,
                'Account' => $i->account?->name,
                'Due Date' => $i->due_at?->format('Y-m-d'),
                'Days Overdue' => $i->days_overdue,
                'Balance' => $i->balance,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Invoice', 'Account', 'Due Date', 'Days Overdue', 'Balance'];
    }
}
