<?php

namespace App\Http\Controllers;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.view')->only(['index']);
    }

    public function index(Request $request): View
    {
        $view = $request->query('view') === 'models' ? 'models' : 'makes';

        if ($view === 'models') {
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
                'related' => VehicleMake::count(),
            ];

            $makes = VehicleMake::orderBy('name')->pluck('name', 'id');

            return view('vehicle-catalog.index', compact('view', 'vehicleModels', 'stats', 'makes'));
        }

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
            'related' => VehicleModel::count(),
        ];

        return view('vehicle-catalog.index', compact('view', 'vehicleMakes', 'stats'));
    }
}
