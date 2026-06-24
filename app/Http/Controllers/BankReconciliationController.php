<?php

namespace App\Http\Controllers;

use App\Models\BankReconciliation;
use App\Models\ChartOfAccount;
use App\Services\BankReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    public function __construct(private BankReconciliationService $reconciliations)
    {
        $this->middleware('permission:finance.view')->only(['index', 'show', 'create']);
        $this->middleware('permission:finance.manage')->only(['store', 'complete']);
    }

    public function index(Request $request): View
    {
        $recons = BankReconciliation::query()
            ->with(['account', 'reconciler', 'creator'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('statement_date')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'draft' => BankReconciliation::where('status', 'draft')->count(),
            'reconciled' => BankReconciliation::where('status', 'reconciled')->count(),
            'this_month' => BankReconciliation::where('status', 'reconciled')
                ->whereMonth('reconciled_at', now()->month)
                ->whereYear('reconciled_at', now()->year)
                ->count(),
        ];

        return view('finance.bank-reconciliations.index', compact('recons', 'stats'));
    }

    public function create(Request $request): View
    {
        $accounts = $this->reconciliations->bankAccounts();
        $selectedAccount = $request->account_id
            ? $accounts->firstWhere('id', (int) $request->account_id)
            : $accounts->first();

        $statementDate = $request->statement_date ? Carbon::parse($request->statement_date) : now();
        $bookBalance = $selectedAccount
            ? $this->reconciliations->bookBalance($selectedAccount, $statementDate)
            : 0;

        return view('finance.bank-reconciliations.create', compact('accounts', 'selectedAccount', 'statementDate', 'bookBalance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'statement_date' => ['required', 'date'],
            'statement_balance' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = ChartOfAccount::findOrFail($data['chart_of_account_id']);
        $recon = $this->reconciliations->createDraft(
            $account,
            Carbon::parse($data['statement_date']),
            (float) $data['statement_balance'],
            $request->user(),
            $data['notes'] ?? null
        );

        return redirect()
            ->route('bank-reconciliations.show', $recon)
            ->with('success', 'Bank reconciliation draft created.');
    }

    public function show(BankReconciliation $bank_reconciliation): View
    {
        $recon = $bank_reconciliation->load(['account', 'items.journalLine.journalEntry', 'reconciler', 'creator']);
        $asOf = Carbon::parse($recon->statement_date);
        $bookBalance = $this->reconciliations->bookBalance($recon->account, $asOf);
        $uncleared = $recon->isReconciled()
            ? collect()
            : $this->reconciliations->unclearedLines($recon->account, $asOf);

        $outstandingCredits = round($uncleared->sum('credit'), 2);
        $outstandingDebits = round($uncleared->sum('debit'), 2);
        $adjusted = round($bookBalance - $outstandingCredits + $outstandingDebits, 2);
        $difference = round((float) $recon->statement_balance - $adjusted, 2);

        return view('finance.bank-reconciliations.show', compact(
            'recon',
            'bookBalance',
            'uncleared',
            'outstandingCredits',
            'outstandingDebits',
            'adjusted',
            'difference'
        ));
    }

    public function complete(Request $request, BankReconciliation $bank_reconciliation): RedirectResponse
    {
        $data = $request->validate([
            'journal_line_ids' => ['nullable', 'array'],
            'journal_line_ids.*' => ['integer', 'exists:journal_lines,id'],
        ]);

        $this->reconciliations->reconcile(
            $bank_reconciliation,
            $data['journal_line_ids'] ?? [],
            $request->user()
        );

        return redirect()
            ->route('bank-reconciliations.show', $bank_reconciliation)
            ->with('success', 'Bank account reconciled successfully.');
    }
}
