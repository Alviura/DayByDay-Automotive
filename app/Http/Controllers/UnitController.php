<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create([
            'name' => $request->name,
            'abbreviation' => $request->abbreviation,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-catalog.index', ['view' => 'units'])
            ->with('status', 'Unit created successfully.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update([
            'name' => $request->name,
            'abbreviation' => $request->abbreviation,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('product-catalog.index', ['view' => 'units'])
            ->with('status', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('product-catalog.index', ['view' => 'units'])
            ->with('status', 'Unit deleted successfully.');
    }
}
