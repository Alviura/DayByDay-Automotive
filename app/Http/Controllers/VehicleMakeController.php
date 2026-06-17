<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleMakeRequest;
use App\Http\Requests\UpdateVehicleMakeRequest;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleMakeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $vehicleMakes = VehicleMake::query()
            ->withCount('models')
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'models', fn ($q) => $q->orderByDesc('models_count'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'models', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => VehicleMake::count(),
            'active' => VehicleMake::where('is_active', true)->count(),
            'inactive' => VehicleMake::where('is_active', false)->count(),
            'models' => VehicleModel::count(),
        ];

        return view('vehicle-makes.index', compact('vehicleMakes', 'stats'));
    }

    public function create(): View
    {
        return view('vehicle-makes.create');
    }

    public function store(StoreVehicleMakeRequest $request): RedirectResponse
    {
        VehicleMake::create([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicle-makes.index')->with('status', 'Vehicle make created successfully.');
    }

    public function edit(VehicleMake $vehicleMake): View
    {
        return view('vehicle-makes.edit', compact('vehicleMake'));
    }

    public function update(UpdateVehicleMakeRequest $request, VehicleMake $vehicleMake): RedirectResponse
    {
        $vehicleMake->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicle-makes.index')->with('status', 'Vehicle make updated successfully.');
    }

    public function destroy(VehicleMake $vehicleMake): RedirectResponse
    {
        if ($vehicleMake->models()->exists()) {
            return back()->with('error', 'Cannot delete a make that still has models. Remove or reassign models first.');
        }

        $vehicleMake->delete();

        return redirect()->route('vehicle-makes.index')->with('status', 'Vehicle make deleted successfully.');
    }
}
