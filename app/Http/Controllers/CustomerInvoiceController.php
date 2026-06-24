<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateCustomerInvoiceRequest;
use App\Http\Requests\RecordInvoicePaymentRequest;
use App\Models\CustomerAccount;
use App\Models\CustomerInvoice;
use App\Services\CustomerInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerInvoiceController extends Controller
{
    public function __construct(private CustomerInvoiceService $invoices)
    {
        $this->middleware('permission:customer_invoices.view')->only(['index', 'show']);
        $this->middleware('permission:customer_invoices.manage')->only(['create', 'store', 'recordPayment']);
    }

    public function index(Request $request): View
    {
        $baseQuery = CustomerInvoice::query();

        $invoices = (clone $baseQuery)
            ->with('account')
            ->when($request->account_id, fn ($q) => $q->where('customer_account_id', $request->account_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('account', fn ($a) => $a->where('name', 'like', '%'.$request->search.'%'));
            }))
            ->when($request->sort === 'balance', fn ($q) => $q->orderByRaw('(total - amount_paid) DESC'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['balance', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $outstanding = (float) CustomerInvoice::whereIn('status', ['sent', 'partially_paid'])
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as balance')
            ->value('balance');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'outstanding' => $outstanding,
            'paid_this_month' => (float) CustomerInvoice::where('status', 'paid')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->sum('total'),
            'sent' => (clone $baseQuery)->where('status', 'sent')->count(),
            'partially_paid' => (clone $baseQuery)->where('status', 'partially_paid')->count(),
            'paid' => (clone $baseQuery)->where('status', 'paid')->count(),
            'overdue' => (clone $baseQuery)->whereIn('status', ['sent', 'partially_paid'])
                ->where('due_at', '<', now()->startOfDay())
                ->count(),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'sent', 'label' => 'Sent', 'icon' => 'fa-paper-plane', 'count' => $stats['sent']],
            ['key' => 'partially_paid', 'label' => 'Partial', 'icon' => 'fa-hourglass-half', 'count' => $stats['partially_paid']],
            ['key' => 'paid', 'label' => 'Paid', 'icon' => 'fa-circle-check', 'count' => $stats['paid']],
        ];

        $accounts = CustomerAccount::active()->orderBy('name')->get(['id', 'name']);

        return view('customer-invoices.index', compact('invoices', 'stats', 'accounts', 'pipeline'));
    }

    public function create(Request $request): View
    {
        $accounts = CustomerAccount::active()->orderBy('name')->get();
        $selectedAccount = $request->account_id ? CustomerAccount::find($request->account_id) : null;

        $periodStart = $request->period_start ?? now()->startOfMonth()->toDateString();
        $periodEnd = $request->period_end ?? now()->endOfMonth()->toDateString();

        $previewSales = collect();
        if ($selectedAccount) {
            $previewSales = $selectedAccount->unpaidCreditSales()
                ->whereBetween('sold_at', [
                    Carbon::parse($periodStart)->startOfDay(),
                    Carbon::parse($periodEnd)->endOfDay(),
                ])
                ->withCount('items')
                ->orderBy('sold_at')
                ->get();
        }

        return view('customer-invoices.create', compact('accounts', 'selectedAccount', 'periodStart', 'periodEnd', 'previewSales'));
    }

    public function store(GenerateCustomerInvoiceRequest $request): RedirectResponse
    {
        try {
            $account = CustomerAccount::findOrFail($request->customer_account_id);
            $invoice = $this->invoices->generate(
                $account,
                Carbon::parse($request->period_start),
                Carbon::parse($request->period_end),
                auth()->user(),
                $request->notes
            );

            return redirect()->route('customer-invoices.show', $invoice)
                ->with('status', 'Invoice '.$invoice->invoice_number.' generated.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(CustomerInvoice $customerInvoice): View
    {
        $customerInvoice->load([
            'account',
            'sales.items.product',
            'sales.shop',
            'payments.receiver',
            'payments.shop',
            'creator',
        ]);

        $paymentMethods = \App\Models\Payment::methods();
        $shops = \App\Models\Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('customer-invoices.show', compact('customerInvoice', 'paymentMethods', 'shops'));
    }

    public function recordPayment(RecordInvoicePaymentRequest $request, CustomerInvoice $customerInvoice): RedirectResponse
    {
        try {
            $this->invoices->recordPayment($customerInvoice, $request->payments);

            return redirect()->route('customer-invoices.show', $customerInvoice)
                ->with('status', 'Payment recorded.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
