<?php

namespace App\Http\Controllers;

use App\Services\TrialBalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrialBalanceController extends Controller
{
    public function __construct(private TrialBalanceService $trialBalance)
    {
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request): View
    {
        $from = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $to = $request->date_to ? Carbon::parse($request->date_to) : now();

        $rows = $this->trialBalance->forPeriod($from, $to);
        $totals = $this->trialBalance->totals($rows);
        $isBalanced = abs($totals['debit'] - $totals['credit']) < 0.01;
        $accountCount = $rows->count();

        $grouped = $rows->groupBy(fn (array $row) => $row['account']->account_type->value);
        $typeOrder = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('finance.trial-balance.index', compact('rows', 'totals', 'from', 'to', 'isBalanced', 'accountCount', 'grouped', 'typeOrder'));
    }
}
