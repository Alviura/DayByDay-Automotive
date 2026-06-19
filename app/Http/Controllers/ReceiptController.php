<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales.view');
    }

    public function show(Sale $sale): View|RedirectResponse
    {
        if ($sale->status !== 'completed') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Receipt is only available for completed sales.');
        }

        $sale->load(['shop', 'cashier', 'items.product.unit', 'payments.receiver']);

        return view('sales.receipt', compact('sale'));
    }
}
