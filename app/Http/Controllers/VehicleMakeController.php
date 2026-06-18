<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleMakeRequest;
use App\Http\Requests\UpdateVehicleMakeRequest;
use App\Models\VehicleMake;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleMakeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

        return redirect()->route('vehicle-catalog.index', ['view' => 'makes'])
            ->with('status', 'Vehicle make created successfully.');
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

        return redirect()->route('vehicle-catalog.index', ['view' => 'makes'])
            ->with('status', 'Vehicle make updated successfully.');
    }

    public function destroy(VehicleMake $vehicleMake): RedirectResponse
    {
        if ($vehicleMake->models()->exists()) {
            return back()->with('error', 'Cannot delete a make that still has models. Remove or reassign models first.');
        }

        $vehicleMake->delete();

        return redirect()->route('vehicle-catalog.index', ['view' => 'makes'])
            ->with('status', 'Vehicle make deleted successfully.');
    }
}
