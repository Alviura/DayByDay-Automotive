<?php

namespace App\Services\Reports;

use App\Models\CustomerInvoice;
use Illuminate\Support\Collection;

class ArAgingReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $open = CustomerInvoice::query()
            ->whereIn('status', ['sent', 'partially_paid'])
            ->with('account:id,name')
            ->get()
            ->map(function (CustomerInvoice $invoice) {
                $balance = $invoice->balanceDue();
                $days = $invoice->due_at ? now()->startOfDay()->diffInDays($invoice->due_at, false) : 0;
                $bucket = match (true) {
                    $days >= 0 => 'current',
                    abs($days) <= 30 => '1_30',
                    abs($days) <= 60 => '31_60',
                    abs($days) <= 90 => '61_90',
                    default => '90_plus',
                };
                $invoice->balance = $balance;
                $invoice->aging_bucket = $bucket;
                $invoice->days_overdue = $days < 0 ? abs($days) : 0;

                return $invoice;
            })
            ->filter(fn ($i) => $i->balance > 0);

        $buckets = [
            'current' => $open->where('aging_bucket', 'current')->sum('balance'),
            '1_30' => $open->where('aging_bucket', '1_30')->sum('balance'),
            '31_60' => $open->where('aging_bucket', '31_60')->sum('balance'),
            '61_90' => $open->where('aging_bucket', '61_90')->sum('balance'),
            '90_plus' => $open->where('aging_bucket', '90_plus')->sum('balance'),
        ];

        return [
            'summary' => array_merge(['total_outstanding' => $open->sum('balance'), 'invoices' => $open->count()], $buckets),
            'rows' => $open->sortByDesc('days_overdue')->values(),
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
                'Bucket' => $i->aging_bucket,
            ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Invoice', 'Account', 'Due Date', 'Days Overdue', 'Balance', 'Bucket'];
    }
}
