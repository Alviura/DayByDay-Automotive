<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Http\Requests\VoidSupplierPaymentRequest;
use App\Models\GoodsReceiptNote;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\SupplierApService;
use App\Services\SupplierPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierPaymentController extends Controller
{
    public function __construct(
        private SupplierPaymentService $payments,
        private SupplierApService $ap
    ) {
        $this->middleware('permission:supplier_payments.view')->only(['index', 'show']);
        $this->middleware('permission:supplier_payments.manage')->only(['create', 'store', 'void']);
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->status ?: 'posted';

        $baseQuery = SupplierPayment::query();

        $payments = (clone $baseQuery)
            ->with(['supplier', 'purchaseOrder', 'goodsReceiptNote', 'payer'])
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($statusFilter === 'voided', fn ($q) => $q->where('status', 'voided'))
            ->when($statusFilter === 'posted', fn ($q) => $q->where('status', 'posted'))
            ->when($request->method, fn ($q) => $q->where('method', $request->method))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('payment_number', 'like', '%'.$request->search.'%')
                    ->orWhere('supplier_invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhere('reference', 'like', '%'.$request->search.'%')
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%'.$request->search.'%'));
            }))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest('paid_at'))
            ->when($request->sort === 'amount', fn ($q) => $q->orderByDesc('amount'))
            ->when(! in_array($request->sort, ['oldest', 'amount'], true), fn ($q) => $q->latest('paid_at'))
            ->paginate(15)
            ->withQueryString();

        $postedTotal = (float) SupplierPayment::where('status', 'posted')->sum('amount');
        $thisMonth = (float) SupplierPayment::where('status', 'posted')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $stats = [
            'total' => SupplierPayment::where('status', 'posted')->count(),
            'posted_total' => $postedTotal,
            'this_month' => $thisMonth,
            'voided' => SupplierPayment::where('status', 'voided')->count(),
            'suppliers_paid' => SupplierPayment::where('status', 'posted')
                ->distinct('supplier_id')
                ->count('supplier_id'),
            'outstanding' => $this->ap->totalOutstanding(),
        ];

        $pipeline = [
            [
                'key' => 'posted',
                'label' => 'Posted',
                'count' => SupplierPayment::where('status', 'posted')->count(),
                'icon' => 'fa-circle-check',
            ],
            [
                'key' => 'voided',
                'label' => 'Voided',
                'count' => SupplierPayment::where('status', 'voided')->count(),
                'icon' => 'fa-ban',
            ],
        ];

        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);

        return view('supplier-payments.index', compact('payments', 'stats', 'suppliers', 'pipeline', 'statusFilter'));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $selectedSupplier = $request->supplier_id ? Supplier::find($request->supplier_id) : null;

        $selectedGrn = $request->goods_receipt_note_id
            ? GoodsReceiptNote::with(['purchaseOrder.supplier', 'items'])->find($request->goods_receipt_note_id)
            : null;

        $selectedPo = $request->purchase_order_id
            ? PurchaseOrder::with('supplier')->find($request->purchase_order_id)
            : null;

        if ($selectedGrn && ! $selectedSupplier) {
            $selectedSupplier = $selectedGrn->purchaseOrder?->supplier;
        }

        if ($selectedPo && ! $selectedSupplier) {
            $selectedSupplier = $selectedPo->supplier;
        }

        $balanceDue = null;
        if ($selectedGrn?->isPosted()) {
            $balanceDue = $this->ap->grnPayableBalance($selectedGrn);
        } elseif ($selectedPo) {
            $balanceDue = $this->ap->poPayableBalance($selectedPo);
        } elseif ($selectedSupplier) {
            $balanceDue = $this->ap->supplierPayableTotal($selectedSupplier);
        }

        $openGrns = collect();
        if ($selectedSupplier) {
            $fifoBalances = $this->ap->fifoGrnBalancesForSupplier($selectedSupplier->id);

            $openGrns = GoodsReceiptNote::query()
                ->posted()
                ->whereHas('purchaseOrder', fn ($q) => $q->where('supplier_id', $selectedSupplier->id))
                ->with(['purchaseOrder', 'items'])
                ->latest('received_at')
                ->get()
                ->map(function (GoodsReceiptNote $grn) use ($fifoBalances) {
                    $balance = (float) ($fifoBalances[$grn->id] ?? 0);

                    return ['grn' => $grn, 'balance' => $balance];
                })
                ->filter(fn (array $row) => $row['balance'] > 0.001)
                ->values();
        }

        $paymentMethods = \App\Models\Payment::methods();

        return view('supplier-payments.create', compact(
            'suppliers',
            'selectedSupplier',
            'selectedGrn',
            'selectedPo',
            'balanceDue',
            'openGrns',
            'paymentMethods'
        ));
    }

    public function store(StoreSupplierPaymentRequest $request): RedirectResponse
    {
        try {
            $supplier = Supplier::findOrFail($request->supplier_id);
            $grn = $request->goods_receipt_note_id
                ? GoodsReceiptNote::find($request->goods_receipt_note_id)
                : null;
            $po = $request->purchase_order_id
                ? PurchaseOrder::find($request->purchase_order_id)
                : null;

            $payment = $this->payments->record(
                $supplier,
                (float) $request->amount,
                $request->method,
                $grn,
                $po,
                $request->supplier_invoice_number,
                $request->reference,
                $request->notes
            );

            return redirect()->route('supplier-payments.show', $payment)
                ->with('status', 'Supplier payment '.$payment->payment_number.' recorded.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(SupplierPayment $supplierPayment): View
    {
        $supplierPayment->load([
            'supplier',
            'purchaseOrder',
            'goodsReceiptNote.warehouse',
            'goodsReceiptNote.purchaseOrder',
            'payer',
            'voidedBy',
        ]);

        $journalEntry = JournalEntry::query()
            ->where('reference_type', $supplierPayment->getMorphClass())
            ->where('reference_id', $supplierPayment->id)
            ->where('event_type', 'supplier_payment.posted')
            ->first();

        $allocationContext = null;
        if ($supplierPayment->goodsReceiptNote) {
            $grn = $supplierPayment->goodsReceiptNote;
            $grnValue = (float) $grn->totalValue();
            $remaining = $this->ap->grnPayableBalance($grn);

            $allocationContext = [
                'grn_value' => $grnValue,
                'remaining' => $remaining,
                'cleared_by_this' => $supplierPayment->isPosted() ? (float) $supplierPayment->amount : 0,
            ];
        }

        $relatedPayments = collect();
        if ($supplierPayment->goods_receipt_note_id) {
            $relatedPayments = SupplierPayment::query()
                ->where('goods_receipt_note_id', $supplierPayment->goods_receipt_note_id)
                ->where('id', '!=', $supplierPayment->id)
                ->with('payer')
                ->latest('paid_at')
                ->get();
        }

        return view('supplier-payments.show', compact(
            'supplierPayment',
            'journalEntry',
            'allocationContext',
            'relatedPayments'
        ));
    }

    public function void(VoidSupplierPaymentRequest $request, SupplierPayment $supplierPayment): RedirectResponse
    {
        try {
            $this->payments->void($supplierPayment, $request->void_reason);

            return redirect()->route('supplier-payments.show', $supplierPayment)
                ->with('status', 'Payment voided.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
