<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $units = Unit::query()
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'abbreviation', fn ($q) => $q->orderBy('abbreviation'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'abbreviation', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Unit::count(),
            'active' => Unit::where('is_active', true)->count(),
            'inactive' => Unit::where('is_active', false)->count(),
            'with_abbreviation' => Unit::whereNotNull('abbreviation')->where('abbreviation', '!=', '')->count(),
        ];

        return view('units.index', compact('units', 'stats'));
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

        return redirect()->route('units.index')->with('status', 'Unit created successfully.');
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

        return redirect()->route('units.index')->with('status', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('units.index')->with('status', 'Unit deleted successfully.');
    }
}
