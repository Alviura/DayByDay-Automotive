<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierOverviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private SupplierOverviewService $overview)
    {
        $this->middleware('permission:suppliers.view')->only(['index', 'show']);
        $this->middleware('permission:suppliers.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->search($request->search)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->code, fn ($q) => $q->where('code', $request->code))
            ->when($request->country, fn ($q) => $q->where('country', $request->country))
            ->when($request->sort === 'code', fn ($q) => $q->orderBy('code'))
            ->when($request->sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when($request->sort === 'rating', fn ($q) => $q->orderByDesc('rating'))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when(! in_array($request->sort, ['code', 'name', 'rating', 'oldest'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
            'rated' => Supplier::whereNotNull('rating')->count(),
        ];

        $codes = Supplier::whereNotNull('code')->orderBy('code')->pluck('code', 'code');
        $countries = Supplier::whereNotNull('country')->where('country', '!=', '')->orderBy('country')->pluck('country', 'country');

        return view('suppliers.index', compact('suppliers', 'stats', 'codes', 'countries'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($this->supplierAttributes($request));

        return redirect()->route('suppliers.index')->with('status', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', array_merge(
            compact('supplier'),
            $this->overview->context($supplier)
        ));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->supplierAttributes($request));

        return redirect()->route('suppliers.index')->with('status', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier deleted successfully.');
    }

    private function supplierAttributes(StoreSupplierRequest|UpdateSupplierRequest $request): array
    {
        return [
            'name' => $request->name,
            'code' => $request->code,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'country' => $request->country,
            'currency' => strtoupper($request->currency),
            'purchase_type' => $request->purchase_type,
            'lead_time_days' => $request->lead_time_days,
            'rating' => $request->filled('rating') ? $request->rating : null,
            'address' => $request->address,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
