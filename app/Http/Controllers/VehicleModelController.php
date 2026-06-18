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
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

        return redirect()->route('vehicle-catalog.index', array_filter([
            'view' => 'models',
            'vehicle_make_id' => $request->vehicle_make_id,
        ]))->with('status', 'Vehicle model created successfully.');
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

        return redirect()->route('vehicle-catalog.index', array_filter([
            'view' => 'models',
            'vehicle_make_id' => $request->vehicle_make_id,
        ]))->with('status', 'Vehicle model updated successfully.');
    }

    public function destroy(VehicleModel $vehicleModel): RedirectResponse
    {
        $vehicleModel->delete();

        return redirect()->route('vehicle-catalog.index', array_filter([
            'view' => 'models',
            'vehicle_make_id' => $vehicleModel->vehicle_make_id,
        ]))->with('status', 'Vehicle model deleted successfully.');
    }
}
