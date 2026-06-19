<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Http\Requests\ReceiveTransferRequest;
use App\Models\StockTransfer;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private TransferService $transfers)
    {
        $this->middleware('permission:transfers.view')->only(['index', 'show']);
        $this->middleware('permission:transfers.receive')->only(['receiveForm', 'receive']);
    }

    public function index(): View
    {
        $transfers = StockTransfer::query()
            ->with(['source', 'destination', 'transferRequest', 'dispatcher'])
            ->latest()
            ->paginate(15);

        return view('transfers.stock-transfers.index', compact('transfers'));
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load([
            'source', 'destination', 'dispatcher', 'receiver',
            'items.product.unit', 'transferRequest',
        ]);

        return view('transfers.stock-transfers.show', ['transfer' => $stockTransfer]);
    }

    public function receiveForm(StockTransfer $stockTransfer): View|RedirectResponse
    {
        if (! $stockTransfer->canReceive()) {
            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('error', 'This transfer cannot be received.');
        }

        $stockTransfer->load(['items.product.unit', 'source', 'destination', 'transferRequest']);

        return view('transfers.receive', ['transfer' => $stockTransfer]);
    }

    public function receive(ReceiveTransferRequest $request, StockTransfer $stockTransfer): RedirectResponse
    {
        try {
            $this->transfers->receive(
                $stockTransfer,
                $request->items,
                $request->notes
            );

            return redirect()->route('stock-transfers.show', $stockTransfer)
                ->with('status', 'Transfer receipt recorded.');
        } catch (InventoryException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
