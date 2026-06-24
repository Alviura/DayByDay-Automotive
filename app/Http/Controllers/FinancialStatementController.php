<?php

namespace App\Http\Controllers;

use App\Services\FinancialStatementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialStatementController extends Controller
{
    public function __construct(private FinancialStatementService $statements)
    {
        $this->middleware('permission:finance.view');
    }

    public function index(Request $request): View
    {
        $tab = $request->tab === 'balance-sheet' ? 'balance-sheet' : 'profit-loss';
        $from = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfYear();
        $to = $request->date_to ? Carbon::parse($request->date_to) : now();
        $asOf = $request->as_of ? Carbon::parse($request->as_of) : now();

        $profitAndLoss = $this->statements->profitAndLoss($from, $to);
        $balanceSheet = $this->statements->balanceSheet($asOf);
        $isBalanced = abs($balanceSheet['total_assets'] - $balanceSheet['total_liabilities_equity']) < 0.01;

        return view('finance.statements.index', compact(
            'tab',
            'from',
            'to',
            'asOf',
            'profitAndLoss',
            'balanceSheet',
            'isBalanced'
        ));
    }
}
