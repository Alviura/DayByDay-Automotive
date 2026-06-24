<?php

namespace App\Http\Controllers;

use App\Services\FinancialPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialPeriodController extends Controller
{
    public function __construct(private FinancialPeriodService $periods)
    {
        $this->middleware('permission:finance.view')->only(['index']);
        $this->middleware('permission:finance.manage')->only(['close', 'reopen']);
    }

    public function index(Request $request): View
    {
        $periods = $this->periods->recentPeriods(18);

        $stats = [
            'open' => $periods->where('status', 'open')->count(),
            'closed' => $periods->where('status', 'closed')->count(),
            'current' => now()->format('F Y'),
        ];

        return view('finance.periods.index', compact('periods', 'stats'));
    }

    public function close(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $period = $this->periods->close(
            (int) $data['period_year'],
            (int) $data['period_month'],
            $request->user(),
            $data['notes'] ?? null
        );

        return redirect()
            ->route('financial-periods.index')
            ->with('success', 'Period '.$period->periodLabel().' closed.');
    }

    public function reopen(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $period = $this->periods->reopen((int) $data['period_year'], (int) $data['period_month']);

        return redirect()
            ->route('financial-periods.index')
            ->with('success', 'Period '.$period->periodLabel().' reopened.');
    }
}
