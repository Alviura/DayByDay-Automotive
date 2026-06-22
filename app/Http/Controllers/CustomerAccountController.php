<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerAccountRequest;
use App\Http\Requests\UpdateCustomerAccountRequest;
use App\Models\CustomerAccount;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer_accounts.view')->only(['index', 'show']);
        $this->middleware('permission:customer_accounts.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $baseQuery = CustomerAccount::query();

        $accounts = (clone $baseQuery)
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filter === 'with_balance', function ($q) {
                $q->whereHas('sales', fn ($s) => $s
                    ->where('sale_type', 'credit')
                    ->where('status', 'completed')
                    ->whereIn('payment_status', ['unpaid', 'partial']));
            })
            ->withSum(['sales as outstanding_balance' => fn ($q) => $q
                ->where('sale_type', 'credit')
                ->where('status', 'completed')
                ->whereIn('payment_status', ['unpaid', 'partial'])], 'total')
            ->withCount(['sales as uninvoiced_count' => fn ($q) => $q
                ->where('sale_type', 'credit')
                ->where('status', 'completed')
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNull('customer_invoice_id')])
            ->when($request->sort === 'balance', fn ($q) => $q->orderByDesc('outstanding_balance'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['balance', 'oldest'], true), fn ($q) => $q->orderBy('name'))
            ->paginate(15)
            ->withQueryString();

        $statsQuery = clone $baseQuery;

        $totalOutstanding = (float) Sale::query()
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('total');

        $uninvoicedSales = Sale::query()
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNull('customer_invoice_id')
            ->count();

        $withBalance = CustomerAccount::query()
            ->whereHas('sales', fn ($s) => $s
                ->where('sale_type', 'credit')
                ->where('status', 'completed')
                ->whereIn('payment_status', ['unpaid', 'partial']))
            ->count();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'inactive' => (clone $statsQuery)->where('is_active', false)->count(),
            'with_balance' => $withBalance,
            'total_outstanding' => $totalOutstanding,
            'uninvoiced_sales' => $uninvoicedSales,
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'active', 'label' => 'Active', 'icon' => 'fa-circle-check', 'count' => $stats['active']],
            ['key' => 'with_balance', 'label' => 'Owing', 'icon' => 'fa-coins', 'count' => $stats['with_balance']],
            ['key' => 'inactive', 'label' => 'Inactive', 'icon' => 'fa-pause', 'count' => $stats['inactive']],
        ];

        return view('customer-accounts.index', compact('accounts', 'stats', 'pipeline'));
    }

    public function create(): View
    {
        return view('customer-accounts.create');
    }

    public function store(StoreCustomerAccountRequest $request): RedirectResponse
    {
        CustomerAccount::create([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'billing_terms' => $request->billing_terms ?: 'monthly',
            'credit_limit' => $request->credit_limit,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customer-accounts.index')->with('status', 'Customer account created.');
    }

    public function show(CustomerAccount $customerAccount): View
    {
        $outstanding = $customerAccount->outstandingBalance();

        $recentSales = Sale::query()
            ->where('customer_account_id', $customerAccount->id)
            ->where('sale_type', 'credit')
            ->with(['shop', 'customerInvoice'])
            ->withCount('items')
            ->latest('sold_at')
            ->limit(10)
            ->get();

        $unpaidSales = $customerAccount->unpaidCreditSales()
            ->whereNull('customer_invoice_id')
            ->with(['shop'])
            ->withCount('items')
            ->orderBy('sold_at')
            ->get();

        $recentInvoices = $customerAccount->invoices()
            ->latest('issued_at')
            ->limit(5)
            ->get();

        $totalCreditSales = Sale::query()
            ->where('customer_account_id', $customerAccount->id)
            ->where('sale_type', 'credit')
            ->where('status', 'completed')
            ->count();

        $uninvoicedTotal = (float) $unpaidSales->sum('total');

        $creditUsedPct = $customerAccount->credit_limit > 0
            ? min(100, round($outstanding / (float) $customerAccount->credit_limit * 100))
            : null;

        $returnRefunds = $customerAccount->completedReturnRefunds();

        $recentReturns = ReturnRecord::query()
            ->where('type', 'customer')
            ->whereHas('sale', fn ($q) => $q->where('customer_account_id', $customerAccount->id))
            ->with(['sale', 'shop'])
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get();

        return view('customer-accounts.show', compact(
            'customerAccount',
            'outstanding',
            'recentSales',
            'unpaidSales',
            'recentInvoices',
            'totalCreditSales',
            'uninvoicedTotal',
            'creditUsedPct',
            'returnRefunds',
            'recentReturns',
        ));
    }

    public function edit(CustomerAccount $customerAccount): View
    {
        return view('customer-accounts.edit', compact('customerAccount'));
    }

    public function update(UpdateCustomerAccountRequest $request, CustomerAccount $customerAccount): RedirectResponse
    {
        $customerAccount->update([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'billing_terms' => $request->billing_terms ?: 'monthly',
            'credit_limit' => $request->credit_limit,
            'notes' => $request->notes,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('customer-accounts.show', $customerAccount)->with('status', 'Account updated.');
    }

    public function destroy(CustomerAccount $customerAccount): RedirectResponse
    {
        if ($customerAccount->sales()->exists()) {
            return back()->with('error', 'Cannot delete an account with sales history. Deactivate it instead.');
        }

        $customerAccount->delete();

        return redirect()->route('customer-accounts.index')->with('status', 'Account deleted.');
    }
}
