<?php

namespace App\Http\Controllers;

use App\Models\TaxRemittance;
use App\Services\TaxRemittanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxRemittanceController extends Controller
{
    public function __construct(private TaxRemittanceService $remittances)
    {
        $this->middleware('permission:finance.view')->only(['index', 'show']);
        $this->middleware('permission:finance.manage')->only(['file', 'pay']);
    }

    public function index(Request $request): View
    {
        $year = (int) ($request->year ?: now()->year);

        for ($month = 1; $month <= 12; $month++) {
            $this->remittances->ensurePeriod($year, $month, $request->user());
        }

        $remittances = TaxRemittance::query()
            ->where('period_year', $year)
            ->orderByDesc('period_month')
            ->with('creator')
            ->get();

        $stats = [
            'open' => $remittances->where('status', 'open')->count(),
            'filed' => $remittances->where('status', 'filed')->count(),
            'paid' => $remittances->where('status', 'paid')->count(),
            'due' => $remittances->filter(fn (TaxRemittance $r) => $r->status !== 'paid' && $r->due_date && $r->due_date->isPast())->count(),
            'collected_ytd' => round($remittances->sum('tax_collected'), 2),
            'remitted_ytd' => round($remittances->sum('amount_remitted'), 2),
        ];

        return view('finance.tax-remittances.index', compact('remittances', 'year', 'stats'));
    }

    public function show(TaxRemittance $tax_remittance): View
    {
        $remittance = $this->remittances->syncTaxCollected($tax_remittance);

        return view('finance.tax-remittances.show', compact('remittance'));
    }

    public function file(TaxRemittance $tax_remittance): RedirectResponse
    {
        $this->remittances->file($tax_remittance, auth()->user());

        return redirect()
            ->route('tax-remittances.show', $tax_remittance)
            ->with('success', 'VAT return filed for '.$tax_remittance->periodLabel().'.');
    }

    public function pay(Request $request, TaxRemittance $tax_remittance): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $amount = (float) $request->amount;
        $this->remittances->markPaid($tax_remittance, $amount, $request->user());

        return redirect()
            ->route('tax-remittances.show', $tax_remittance)
            ->with('success', 'VAT remittance of KES '.number_format($amount, 2).' recorded.');
    }
}
