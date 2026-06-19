<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProcurementFolderController;
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ProductNameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferRequestController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleCatalogController;
use App\Http\Controllers\VehicleMakeController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Access control administration (M2). Per-action permissions are
    // enforced in the controllers' constructors.
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except('show');

    // Master data (Phase 2). Per-action permissions enforced in controllers.
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('shops', ShopController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::get('vehicle-catalog', [VehicleCatalogController::class, 'index'])->name('vehicle-catalog.index');
    Route::redirect('/vehicle-makes', '/vehicle-catalog?view=makes');
    Route::redirect('/vehicle-models', '/vehicle-catalog?view=models');
    Route::resource('vehicle-makes', VehicleMakeController::class)->except(['show', 'index']);
    Route::resource('vehicle-models', VehicleModelController::class)->except(['show', 'index']);
    Route::redirect('/categories', '/product-catalog?view=categories');
    Route::resource('categories', CategoryController::class)->except(['show', 'index']);
    Route::get('product-catalog', [ProductCatalogController::class, 'index'])->name('product-catalog.index');
    Route::redirect('/product-names', '/product-catalog?view=names');
    Route::redirect('/units', '/product-catalog?view=units');
    Route::resource('product-names', ProductNameController::class)->except(['show', 'index']);
    Route::resource('units', UnitController::class)->except(['show', 'index']);
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::resource('products', ProductController::class);

    // Approval engine (M11)
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
    Route::post('approvals/{approval}/act', [ApprovalController::class, 'act'])->name('approvals.act');

    // Inventory engine (M12)
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('inventory/valuation', [InventoryController::class, 'valuation'])->name('inventory.valuation');
    Route::get('inventory/balance', [InventoryController::class, 'balanceLookup'])->name('inventory.balance');
    Route::get('inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('stock-adjustments/{stock_adjustment}/submit', [StockAdjustmentController::class, 'submit'])->name('stock-adjustments.submit');

    // Procurement (M13)
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::resource('folders', ProcurementFolderController::class);
        Route::post('folders/{folder}/items', [ProcurementFolderController::class, 'storeItem'])->name('folders.items.store');
        Route::delete('folders/{folder}/items/{item}', [ProcurementFolderController::class, 'destroyItem'])->name('folders.items.destroy');
        Route::post('folders/{folder}/cost-analysis', [ProcurementFolderController::class, 'runCostAnalysis'])->name('folders.cost-analysis');
        Route::post('folders/{folder}/submit', [ProcurementFolderController::class, 'submit'])->name('folders.submit');
        Route::post('folders/{folder}/generate-po', [ProcurementFolderController::class, 'generatePo'])->name('folders.generate-po');
        Route::post('folders/{folder}/in-transit', [ProcurementFolderController::class, 'markInTransit'])->name('folders.in-transit');
        Route::post('folders/{folder}/close', [ProcurementFolderController::class, 'close'])->name('folders.close');
    });
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'show']);
    Route::get('purchase-orders/{purchase_order}/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
    Route::post('purchase-orders/{purchase_order}/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    Route::get('goods-receipts/{goods_receipt_note}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');

    // Distribution & transfers (M14)
    Route::resource('transfer-requests', TransferRequestController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('transfer-requests/{transfer_request}/submit', [TransferRequestController::class, 'submit'])->name('transfer-requests.submit');
    Route::post('transfer-requests/{transfer_request}/dispatch', [TransferRequestController::class, 'dispatch'])->name('transfer-requests.dispatch');
    Route::get('stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
    Route::get('stock-transfers/{stock_transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');
    Route::get('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receiveForm'])->name('stock-transfers.receive');
    Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive.store');

    // Sales / POS (M15)
    Route::get('sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::get('sales/pos/search', [SaleController::class, 'searchProducts'])->name('sales.search');
    Route::post('sales/hold', [SaleController::class, 'hold'])->name('sales.hold');
    Route::post('sales/checkout', [SaleController::class, 'checkout'])->name('sales.checkout');
    Route::post('sales/{sale}/complete', [SaleController::class, 'complete'])->name('sales.complete');
    Route::post('sales/{sale}/reverse', [SaleController::class, 'reverse'])->name('sales.reverse');
    Route::post('sales/{sale}/abandon', [SaleController::class, 'abandon'])->name('sales.abandon');
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('receipts/{sale}', [ReceiptController::class, 'show'])->name('receipts.show');

    // Returns (M16)
    Route::get('customer-returns', [CustomerReturnController::class, 'index'])->name('customer-returns.index');
    Route::get('customer-returns/create', [CustomerReturnController::class, 'create'])->name('customer-returns.create');
    Route::post('customer-returns', [CustomerReturnController::class, 'store'])->name('customer-returns.store');
    Route::get('customer-returns/sales/{sale}/items', [CustomerReturnController::class, 'saleItems'])->name('customer-returns.sale-items');
    Route::get('customer-returns/{customer_return}', [CustomerReturnController::class, 'show'])->name('customer-returns.show');
    Route::post('customer-returns/{customer_return}/submit', [CustomerReturnController::class, 'submit'])->name('customer-returns.submit');
    Route::delete('customer-returns/{customer_return}', [CustomerReturnController::class, 'destroy'])->name('customer-returns.destroy');

    Route::get('supplier-returns', [SupplierReturnController::class, 'index'])->name('supplier-returns.index');
    Route::get('supplier-returns/create', [SupplierReturnController::class, 'create'])->name('supplier-returns.create');
    Route::post('supplier-returns', [SupplierReturnController::class, 'store'])->name('supplier-returns.store');
    Route::get('supplier-returns/{supplier_return}', [SupplierReturnController::class, 'show'])->name('supplier-returns.show');
    Route::post('supplier-returns/{supplier_return}/submit', [SupplierReturnController::class, 'submit'])->name('supplier-returns.submit');
    Route::delete('supplier-returns/{supplier_return}', [SupplierReturnController::class, 'destroy'])->name('supplier-returns.destroy');

    // Reporting (M17)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('reports/procurement', [ReportController::class, 'procurement'])->name('reports.procurement');
    Route::get('reports/transfers', [ReportController::class, 'transfers'])->name('reports.transfers');
    Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

    // Audit log (M18)
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{audit_log}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});

require __DIR__.'/auth.php';
