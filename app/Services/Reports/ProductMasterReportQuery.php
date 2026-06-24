<?php

namespace App\Services\Reports;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductMasterReportQuery extends AbstractReportQuery
{
    public function run(ReportFilters $filters): array
    {
        $rows = Product::query()
            ->with(['category', 'productName', 'vehicleMake', 'vehicleModel', 'unit', 'fitmentModels.make'])
            ->orderBy('part_number')
            ->limit(100)
            ->get();

        return [
            'summary' => ['products' => Product::count(), 'active' => Product::where('is_active', true)->count()],
            'rows' => $rows,
        ];
    }

    public function csvRows(ReportFilters $filters): Collection
    {
        return $this->truncateIfNeeded(
            Product::query()
                ->with(['category', 'productName', 'vehicleMake', 'vehicleModel', 'unit'])
                ->orderBy('part_number')
                ->get()
                ->map(fn (Product $p) => [
                    'Part Number' => $p->part_number,
                    'Name' => $p->name,
                    'Product Name' => $p->productName?->name,
                    'Category' => $p->category?->name,
                    'Fitment' => $p->fitmentLabel(),
                    'Unit' => $p->unit?->abbreviation,
                    'Order Unit' => $p->orderUnitLabel(),
                    'Min Price' => $p->min_selling_price,
                    'Max Price' => $p->max_selling_price,
                    'Reorder Level' => $p->reorder_level,
                    'Active' => $p->is_active ? 'Yes' : 'No',
                ])
        );
    }

    public function csvHeaders(): array
    {
        return ['Part Number', 'Name', 'Product Name', 'Category', 'Fitment', 'Unit', 'Order Unit', 'Min Price', 'Max Price', 'Reorder Level', 'Active'];
    }
}
