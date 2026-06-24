<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Services\TrialBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChartOfAccountController extends Controller
{
    public function __construct(private TrialBalanceService $trialBalance)
    {
        $this->middleware('permission:finance.view')->only(['index', 'show']);
        $this->middleware('permission:finance.manage')->only(['create', 'store', 'edit', 'update']);
    }

    public function index(Request $request): View
    {
        $accounts = ChartOfAccount::query()
            ->with('shop')
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->type, fn ($q) => $q->where('account_type', $request->type))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'active' || ! $request->status, fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'all', fn ($q) => $q)
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString();

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = $this->trialBalance->accountBalance($account);
        }

        $withBalanceCount = ChartOfAccount::active()->get()
            ->filter(fn (ChartOfAccount $a) => abs($this->trialBalance->accountBalance($a)) > 0.001)
            ->count();

        $stats = [
            'total' => ChartOfAccount::count(),
            'active' => ChartOfAccount::where('is_active', true)->count(),
            'assets' => ChartOfAccount::where('account_type', AccountType::Asset)->where('is_active', true)->count(),
            'liabilities' => ChartOfAccount::where('account_type', AccountType::Liability)->where('is_active', true)->count(),
            'with_balance' => $withBalanceCount,
        ];

        $pipeline = collect([
            ['key' => '', 'label' => 'All Active', 'icon' => 'fa-list', 'count' => $stats['active']],
        ])->merge(collect(AccountType::cases())->map(fn (AccountType $type) => [
            'key' => $type->value,
            'label' => $type->label(),
            'icon' => $type->icon(),
            'count' => ChartOfAccount::where('account_type', $type)->where('is_active', true)->count(),
        ]));

        return view('finance.chart-of-accounts.index', compact('accounts', 'balances', 'stats', 'pipeline'));
    }

    public function show(ChartOfAccount $chartOfAccount, Request $request): View
    {
        $from = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : now()->startOfMonth();
        $to = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : now();

        $ledger = $this->trialBalance->accountLedger($chartOfAccount, $from, $to);
        $balance = $this->trialBalance->accountBalance($chartOfAccount, $to);
        $openingBalance = $this->trialBalance->accountBalance($chartOfAccount, $from->copy()->subDay());
        $periodDebit = (float) $ledger->sum(fn ($row) => (float) $row['line']->debit);
        $periodCredit = (float) $ledger->sum(fn ($row) => (float) $row['line']->credit);

        return view('finance.chart-of-accounts.show', compact(
            'chartOfAccount', 'ledger', 'balance', 'openingBalance', 'periodDebit', 'periodCredit', 'from', 'to'
        ));
    }

    public function create(): View
    {
        $parents = ChartOfAccount::active()->orderBy('code')->get(['id', 'code', 'name']);

        return view('finance.chart-of-accounts.create', compact('parents'));
    }

    public function store(StoreChartOfAccountRequest $request): RedirectResponse
    {
        $type = AccountType::from($request->account_type);

        ChartOfAccount::create([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'account_type' => $type,
            'normal_balance' => $type->normalBalance(),
            'parent_id' => $request->parent_id,
            'shop_id' => $request->shop_id,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('chart-of-accounts.index')
            ->with('status', 'Account created.');
    }

    public function edit(ChartOfAccount $chartOfAccount): View
    {
        $parents = ChartOfAccount::active()
            ->where('id', '!=', $chartOfAccount->id)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('finance.chart-of-accounts.edit', compact('chartOfAccount', 'parents'));
    }

    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        if ($chartOfAccount->is_system && $request->code !== $chartOfAccount->code) {
            return back()->with('error', 'System account codes cannot be changed.');
        }

        $type = AccountType::from($request->account_type);

        $chartOfAccount->update([
            'code' => strtoupper(trim($request->code)),
            'name' => $request->name,
            'account_type' => $type,
            'normal_balance' => $type->normalBalance(),
            'parent_id' => $request->parent_id,
            'shop_id' => $request->shop_id,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('chart-of-accounts.show', $chartOfAccount)
            ->with('status', 'Account updated.');
    }
}
