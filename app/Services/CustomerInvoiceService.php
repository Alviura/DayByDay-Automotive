<?php

namespace App\Services;

use App\Models\CustomerAccount;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoicePayment;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceService
{
    public function generate(
        CustomerAccount $account,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?User $user = null,
        ?string $notes = null
    ): CustomerInvoice {
        $user ??= auth()->user();

        return DB::transaction(function () use ($account, $periodStart, $periodEnd, $user, $notes) {
            $sales = $account->unpaidCreditSales()
                ->whereBetween('sold_at', [
                    $periodStart->copy()->startOfDay(),
                    $periodEnd->copy()->endOfDay(),
                ])
                ->orderBy('sold_at')
                ->lockForUpdate()
                ->get();

            if ($sales->isEmpty()) {
                throw new \InvalidArgumentException('No unpaid credit sales found for this account in the selected period.');
            }

            $subtotal = $sales->sum('subtotal');
            $taxTotal = $sales->sum('tax_total');
            $total = $sales->sum('total');

            $invoice = CustomerInvoice::create([
                'invoice_number' => CustomerInvoice::generateInvoiceNumber(),
                'customer_account_id' => $account->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'amount_paid' => 0,
                'status' => 'sent',
                'issued_at' => now(),
                'due_at' => $periodEnd->copy()->addDays(30)->toDateString(),
                'notes' => $notes,
                'created_by' => $user?->id,
            ]);

            Sale::whereIn('id', $sales->pluck('id'))->update([
                'customer_invoice_id' => $invoice->id,
            ]);

            return $invoice->fresh(['account', 'sales.items.product', 'creator']);
        });
    }

    public function recordPayment(CustomerInvoice $invoice, array $payments, ?User $user = null): CustomerInvoice
    {
        $user ??= auth()->user();

        if (in_array($invoice->status, ['paid'], true)) {
            throw new \InvalidArgumentException('This invoice is already fully paid.');
        }

        $amountPaid = collect($payments)->sum(fn ($p) => (float) $p['amount']);

        if ($amountPaid <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($invoice, $payments, $user, $amountPaid) {
            foreach ($payments as $payment) {
                if ((float) $payment['amount'] <= 0) {
                    continue;
                }

                CustomerInvoicePayment::create([
                    'customer_invoice_id' => $invoice->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'paid_at' => now(),
                    'received_by' => $user?->id,
                ]);
            }

            $newAmountPaid = (float) $invoice->amount_paid + $amountPaid;
            $balance = max(0, (float) $invoice->total - $newAmountPaid);

            $status = match (true) {
                $balance <= 0 => 'paid',
                $newAmountPaid > 0 => 'partially_paid',
                default => $invoice->status,
            };

            $invoice->update([
                'amount_paid' => min($newAmountPaid, (float) $invoice->total),
                'status' => $status,
            ]);

            if ($status === 'paid') {
                $this->markLinkedSalesPaid($invoice);
            }

            return $invoice->fresh(['account', 'sales', 'payments.receiver']);
        });
    }

    private function markLinkedSalesPaid(CustomerInvoice $invoice): void
    {
        $invoice->sales()
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->update([
                'payment_status' => 'paid',
                'amount_paid' => DB::raw('total'),
                'change_due' => 0,
            ]);
    }
}
