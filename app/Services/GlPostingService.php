<?php

namespace App\Services;

use App\Models\CustomerInvoice;
use App\Models\CustomerInvoicePayment;
use App\Models\GoodsReceiptNote;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use App\Models\StockTransfer;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GlPostingService
{
    public function __construct(private AccountingService $accounting) {}

    public function enabled(): bool
    {
        return (bool) config('finance.auto_posting', true);
    }

    public function postRetailSale(Sale $sale, ?User $user = null): ?JournalEntry
    {
        $sale->loadMissing(['payments', 'shop']);

        $lines = [];

        foreach ($sale->payments->where('direction', 'receipt') as $payment) {
            $cashAccount = $this->accounting->findCashAccount($sale->shop_id, $payment->method);
            $lines[] = [
                'account_id' => $cashAccount->id,
                'debit' => (float) $payment->amount,
                'shop_id' => $sale->shop_id,
                'payment_method' => $payment->method,
                'description' => 'POS receipt',
            ];
        }

        $lines = array_merge($lines, $this->revenueLines($sale));
        $lines = array_merge($lines, $this->cogsLines($sale, 'sale'));

        return $this->safePost('sale.retail.completed', $sale, $lines, $sale->sold_at, 'Retail sale '.$sale->receipt_number, $user);
    }

    public function postCreditSale(Sale $sale, ?User $user = null): ?JournalEntry
    {
        $sale->loadMissing('customerAccount');

        $lines = [[
            'account_code' => $this->ac('ar_fleet'),
            'debit' => (float) $sale->total,
            'customer_account_id' => $sale->customer_account_id,
            'shop_id' => $sale->shop_id,
            'description' => 'Credit sale — AR',
        ]];

        $lines = array_merge($lines, $this->revenueLines($sale));
        $lines = array_merge($lines, $this->cogsLines($sale, 'sale'));

        return $this->safePost('sale.credit.completed', $sale, $lines, $sale->sold_at, 'Credit sale '.$sale->receipt_number, $user);
    }

    public function postSaleReversal(Sale $sale, ?User $user = null): void
    {
        if (! $this->enabled()) {
            return;
        }

        $event = $sale->isCredit() ? 'sale.credit.completed' : 'sale.retail.completed';
        $this->accounting->reverseByEvent($event, $sale, 'sale.reversed', $user);
    }

    public function postGrnReceived(GoodsReceiptNote $grn, ?User $user = null): ?JournalEntry
    {
        $grn->loadMissing(['items', 'purchaseOrder.supplier']);
        $value = (float) $grn->totalValue();

        if ($value <= 0) {
            return null;
        }

        $supplierId = $grn->purchaseOrder?->supplier_id;

        $lines = [
            [
                'account_code' => $this->ac('inventory'),
                'debit' => $value,
                'description' => 'Goods receipt',
            ],
            [
                'account_code' => $this->ac('grni'),
                'credit' => $value,
                'supplier_id' => $supplierId,
                'description' => 'GRNI accrual',
            ],
        ];

        return $this->safePost('grn.received', $grn, $lines, $grn->received_at, 'GRN '.$grn->grn_number, $user);
    }

    public function postGrnVoided(GoodsReceiptNote $grn, ?User $user = null): ?JournalEntry
    {
        if (! $this->enabled()) {
            return null;
        }

        return $this->accounting->reverseByEvent('grn.received', $grn, 'grn.voided', $user);
    }

    public function postCustomerReturn(ReturnRecord $return, ?User $user = null): ?JournalEntry
    {
        $return->loadMissing(['sale.customerAccount', 'shop']);
        $refund = (float) $return->refund_amount;

        if ($refund <= 0) {
            return null;
        }

        $lines = [[
            'account_code' => $this->ac('sales_returns'),
            'debit' => $refund,
            'description' => 'Customer return',
        ]];

        if ($return->sale?->isCredit()) {
            $lines[] = [
                'account_code' => $this->ac('ar_fleet'),
                'credit' => $refund,
                'customer_account_id' => $return->sale->customer_account_id,
                'shop_id' => $return->shop_id,
            ];
        } else {
            $lines[] = [
                'account_code' => $this->ac('bank'),
                'credit' => $refund,
                'shop_id' => $return->shop_id,
                'description' => 'Refund to customer',
            ];
        }

        $restockValue = $this->ledgerCostTotal($return, 'customer_return', positiveQty: true);
        if ($restockValue > 0) {
            $lines[] = [
                'account_code' => $this->ac('inventory'),
                'debit' => $restockValue,
                'shop_id' => $return->shop_id,
            ];
            $lines[] = [
                'account_code' => $this->ac('cogs'),
                'credit' => $restockValue,
            ];
        }

        return $this->safePost('return.customer.completed', $return, $lines, now(), 'Customer return '.$return->return_number, $user);
    }

    public function postSupplierReturn(ReturnRecord $return, ?User $user = null): ?JournalEntry
    {
        $return->loadMissing('supplier');
        $value = $this->ledgerCostTotal($return, 'supplier_return', positiveQty: false);

        if ($value <= 0) {
            return null;
        }

        $lines = [
            [
                'account_code' => $this->ac('grni'),
                'debit' => $value,
                'supplier_id' => $return->supplier_id,
                'description' => 'Supplier return — reduce GRNI',
            ],
            [
                'account_code' => $this->ac('inventory'),
                'credit' => $value,
                'description' => 'Supplier return — inventory out',
            ],
        ];

        return $this->safePost('return.supplier.completed', $return, $lines, now(), 'Supplier return '.$return->return_number, $user);
    }

    public function postTransferCompleted(StockTransfer $transfer, ?User $user = null): ?JournalEntry
    {
        if (! config('finance.post_transfers', true)) {
            return null;
        }

        $transfer->loadMissing(['source', 'destination']);
        $value = $this->ledgerTransferInValue($transfer);

        if ($value <= 0) {
            return null;
        }

        $lines = [
            [
                'account_code' => $this->ac('inventory'),
                'debit' => $value,
                'shop_id' => $this->locationShopId($transfer->destination),
                'description' => 'Transfer in — '.($transfer->destination?->name ?? 'destination'),
            ],
            [
                'account_code' => $this->ac('inventory'),
                'credit' => $value,
                'shop_id' => $this->locationShopId($transfer->source),
                'description' => 'Transfer out — '.($transfer->source?->name ?? 'source'),
            ],
        ];

        return $this->safePost(
            'transfer.completed',
            $transfer,
            $lines,
            $transfer->received_at,
            'Stock transfer '.$transfer->transfer_number,
            $user
        );
    }

    public function postInvoicePayment(CustomerInvoicePayment $payment, ?User $user = null): ?JournalEntry
    {
        $payment->loadMissing('invoice.account');
        $shopId = $payment->shop_id;

        if (! $shopId) {
            throw new \InvalidArgumentException('Invoice payment requires a shop for GL cash posting.');
        }

        $cashAccount = $this->accounting->findCashAccount($shopId, $payment->method);

        $lines = [
            [
                'account_id' => $cashAccount->id,
                'debit' => (float) $payment->amount,
                'shop_id' => $shopId,
                'payment_method' => $payment->method,
            ],
            [
                'account_code' => $this->ac('ar_fleet'),
                'credit' => (float) $payment->amount,
                'customer_account_id' => $payment->invoice->customer_account_id,
                'shop_id' => $shopId,
            ],
        ];

        return $this->safePost(
            'customer_invoice.payment',
            $payment,
            $lines,
            $payment->paid_at,
            'Invoice payment '.$payment->invoice->invoice_number,
            $user
        );
    }

    public function postSupplierPayment(SupplierPayment $payment, ?User $user = null): ?JournalEntry
    {
        $payment->loadMissing('supplier');

        $creditAccount = $this->accounting->findAccountByCode($this->ac('bank'));

        $lines = [
            [
                'account_code' => $this->ac('grni'),
                'debit' => (float) $payment->amount,
                'supplier_id' => $payment->supplier_id,
                'description' => 'Clear GRNI / AP',
            ],
            [
                'account_id' => $creditAccount->id,
                'credit' => (float) $payment->amount,
                'description' => 'Supplier payment',
            ],
        ];

        return $this->safePost(
            'supplier_payment.posted',
            $payment,
            $lines,
            $payment->paid_at,
            'Supplier payment '.$payment->payment_number,
            $user
        );
    }

    public function postStockAdjustment(StockAdjustment $adjustment, ?User $user = null): ?JournalEntry
    {
        $adjustment->loadMissing('items');
        $lines = [];

        foreach ($adjustment->items as $item) {
            $diff = (float) $item->difference;
            if (abs($diff) < 0.001) {
                continue;
            }

            $value = round(abs($diff) * (float) ($item->unit_cost ?? $item->product->cost_price ?? 0), 2);
            if ($value <= 0) {
                continue;
            }

            if ($diff > 0) {
                $lines[] = ['account_code' => $this->ac('inventory'), 'debit' => $value];
                $lines[] = ['account_code' => $this->ac('inventory_shrinkage'), 'credit' => $value];
            } else {
                $lines[] = ['account_code' => $this->ac('inventory_shrinkage'), 'debit' => $value];
                $lines[] = ['account_code' => $this->ac('inventory'), 'credit' => $value];
            }
        }

        if ($lines === []) {
            return null;
        }

        return $this->safePost(
            'adjustment.posted',
            $adjustment,
            $lines,
            now(),
            'Stock adjustment '.$adjustment->adjustment_number,
            $user
        );
    }

    public function postPayrollLocked(PayrollRun $run, ?User $user = null): ?JournalEntry
    {
        $run->loadMissing('lines');

        $paye = (float) $run->lines->sum('paye');
        $nssfEmployee = (float) $run->lines->sum('nssf_employee');
        $nssfEmployer = (float) $run->lines->sum('nssf_employer');
        $shif = (float) $run->lines->sum('shif');
        $housingEmployee = (float) $run->lines->sum('housing_levy_employee');
        $housingEmployer = (float) $run->lines->sum('housing_levy_employer');
        $net = (float) $run->lines->sum('net_pay');
        $gross = (float) $run->total_gross;

        $employerStatutory = round($nssfEmployer + $housingEmployer, 2);
        $expenseTotal = round($gross + $employerStatutory, 2);

        $lines = [[
            'account_code' => $this->ac('salaries_wages'),
            'debit' => $expenseTotal,
        ]];

        foreach ([
            [$this->ac('paye_payable'), $paye],
            [$this->ac('nssf_employee_payable'), $nssfEmployee],
            [$this->ac('nssf_employer_payable'), $nssfEmployer],
            [$this->ac('shif_payable'), $shif],
            [$this->ac('housing_levy_payable'), round($housingEmployee + $housingEmployer, 2)],
            [$this->ac('wages_payable'), $net],
        ] as [$code, $amount]) {
            if ($amount > 0) {
                $lines[] = ['account_code' => $code, 'credit' => $amount];
            }
        }

        return $this->safePost('payroll.locked', $run, $lines, now(), 'Payroll accrual '.$run->run_number, $user);
    }

    public function postPayrollPaid(PayrollRun $run, ?User $user = null): ?JournalEntry
    {
        $run->loadMissing('lines');

        $paye = (float) $run->lines->sum('paye');
        $nssfEmployee = (float) $run->lines->sum('nssf_employee');
        $nssfEmployer = (float) $run->lines->sum('nssf_employer');
        $shif = (float) $run->lines->sum('shif');
        $housing = (float) $run->lines->sum('housing_levy_employee') + (float) $run->lines->sum('housing_levy_employer');
        $net = (float) $run->lines->sum('net_pay');

        $lines = [];
        foreach ([
            [$this->ac('wages_payable'), $net],
            [$this->ac('paye_payable'), $paye],
            [$this->ac('nssf_employee_payable'), $nssfEmployee],
            [$this->ac('nssf_employer_payable'), $nssfEmployer],
            [$this->ac('shif_payable'), $shif],
            [$this->ac('housing_levy_payable'), $housing],
        ] as [$code, $amount]) {
            if ($amount > 0) {
                $lines[] = ['account_code' => $code, 'debit' => $amount];
            }
        }

        $total = round(collect($lines)->sum('debit'), 2);
        if ($total > 0) {
            $lines[] = ['account_code' => $this->ac('bank'), 'credit' => $total];
        }

        return $this->safePost('payroll.paid', $run, $lines, now(), 'Payroll settlement '.$run->run_number, $user);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function revenueLines(Sale $sale): array
    {
        $lines = [];
        $subtotal = (float) $sale->subtotal;
        $tax = (float) $sale->tax_total;

        if ($subtotal > 0) {
            $lines[] = [
                'account_code' => $this->ac('sales_revenue'),
                'credit' => $subtotal,
                'shop_id' => $sale->shop_id,
            ];
        }

        if ($tax > 0) {
            $lines[] = [
                'account_code' => $this->ac('vat_payable'),
                'credit' => $tax,
                'shop_id' => $sale->shop_id,
            ];
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cogsLines(Sale $sale, string $ledgerType): array
    {
        $cogs = $this->ledgerCostTotal($sale, $ledgerType, positiveQty: false);

        if ($cogs <= 0) {
            return [];
        }

        return [
            [
                'account_code' => $this->ac('cogs'),
                'debit' => $cogs,
                'shop_id' => $sale->shop_id,
            ],
            [
                'account_code' => $this->ac('inventory'),
                'credit' => $cogs,
                'shop_id' => $sale->shop_id,
            ],
        ];
    }

    private function ledgerCostTotal(Model $reference, string $transactionType, bool $positiveQty): float
    {
        return (float) StockLedger::query()
            ->where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->where('transaction_type', $transactionType)
            ->when($positiveQty, fn ($q) => $q->where('quantity', '>', 0), fn ($q) => $q->where('quantity', '<', 0))
            ->get()
            ->sum(fn (StockLedger $row) => abs((float) $row->quantity) * (float) $row->unit_cost);
    }

    private function ledgerTransferInValue(StockTransfer $transfer): float
    {
        return (float) StockLedger::query()
            ->where('reference_type', $transfer->getMorphClass())
            ->where('reference_id', $transfer->getKey())
            ->where('transaction_type', 'transfer_in')
            ->where('quantity', '>', 0)
            ->get()
            ->sum(fn (StockLedger $row) => (float) $row->quantity * (float) $row->unit_cost);
    }

    private function locationShopId(?object $location): ?int
    {
        return $location instanceof Shop ? $location->id : null;
    }

    private function ac(string $key): string
    {
        $code = config("finance.accounts.{$key}");

        if (! $code) {
            throw new \InvalidArgumentException("Missing finance.accounts.{$key} configuration.");
        }

        return $code;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function safePost(
        string $eventType,
        Model $reference,
        array $lines,
        ?\Carbon\Carbon $date = null,
        ?string $description = null,
        ?User $user = null
    ): ?JournalEntry {
        if (! $this->enabled() || $lines === []) {
            return null;
        }

        try {
            return $this->accounting->post($eventType, $reference, $lines, $date, $description, $user);
        } catch (\Throwable $e) {
            Log::error('GL posting failed', [
                'event' => $eventType,
                'reference' => $reference->getMorphClass().':'.$reference->getKey(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
