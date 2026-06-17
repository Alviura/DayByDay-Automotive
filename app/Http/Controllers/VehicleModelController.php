<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleModelRequest;
use App\Http\Requests\UpdateVehicleModelRequest;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleModelController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $vehicleModels = VehicleModel::query()
            ->with('make')
            ->search($request->search)
            ->when($request->vehicle_make_id, fn ($q) => $q->where('vehicle_make_id', $request->vehicle_make_id))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'make', fn ($q) => $q->orderBy(
                VehicleMake::select('name')->whereColumn('vehicle_makes.id', 'vehicle_models.vehicle_make_id')
            ))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['name', 'make', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => VehicleModel::count(),
            'active' => VehicleModel::where('is_active', true)->count(),
            'inactive' => VehicleModel::where('is_active', false)->count(),
            'makes' => VehicleMake::count(),
        ];

        $makes = VehicleMake::orderBy('name')->pluck('name', 'id');

        return view('vehicle-models.index', compact('vehicleModels', 'stats', 'makes'));
    }

    public function create(Request $request): View
    {
        $makes = VehicleMake::orderBy('name')->get();
        $selectedMakeId = $request->query('vehicle_make_id');

        return view('vehicle-models.create', compact('makes', 'selectedMakeId'));
    }

    public function store(StoreVehicleModelRequest $request): RedirectResponse
    {
        VehicleModel::create([
            'vehicle_make_id' => $request->vehicle_make_id,
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicle-models.index')->with('status', 'Vehicle model created successfully.');
    }

    public function edit(VehicleModel $vehicleModel): View
    {
        $vehicleModel->load('make');
        $makes = VehicleMake::orderBy('name')->get();

        return view('vehicle-models.edit', compact('vehicleModel', 'makes'));
    }

    public function update(UpdateVehicleModelRequest $request, VehicleModel $vehicleModel): RedirectResponse
    {
        $vehicleModel->update([
            'vehicle_make_id' => $request->vehicle_make_id,
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('vehicle-models.index')->with('status', 'Vehicle model updated successfully.');
    }

    public function destroy(VehicleModel $vehicleModel): RedirectResponse
    {
        $vehicleModel->delete();

        return redirect()->route('vehicle-models.index')->with('status', 'Vehicle model deleted successfully.');
    }
}
