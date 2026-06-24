<?php

use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerInvoiceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuotationSeriesController;
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
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockTransferController;
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

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

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

    // Quotation series (M13)
    Route::get('quotation-series/suppliers/{supplier}/defaults', [QuotationSeriesController::class, 'supplierDefaults'])->name('quotation-series.suppliers.defaults');
    Route::resource('quotation-series', QuotationSeriesController::class);
    Route::get('quotation-series/{quotation_series}/products/search', [QuotationSeriesController::class, 'searchProducts'])->name('quotation-series.products.search');
    Route::post('quotation-series/{quotation_series}/items/bulk', [QuotationSeriesController::class, 'bulkAddItems'])->name('quotation-series.items.bulk');
    Route::patch('quotation-series/{quotation_series}/items/prices', [QuotationSeriesController::class, 'updatePrices'])->name('quotation-series.items.prices');
    Route::delete('quotation-series/{quotation_series}/items/{item}', [QuotationSeriesController::class, 'destroyItem'])->name('quotation-series.items.destroy');
    Route::patch('quotation-series/{quotation_series}/items/{item}', [QuotationSeriesController::class, 'updateItem'])->name('quotation-series.items.update');
    Route::post('quotation-series/{quotation_series}/proceed', [QuotationSeriesController::class, 'proceedToOrder'])->name('quotation-series.proceed');
    Route::post('quotation-series/{quotation_series}/calculate', [QuotationSeriesController::class, 'calculate'])->name('quotation-series.calculate');
    Route::post('quotation-series/{quotation_series}/confirm', [QuotationSeriesController::class, 'confirmOrder'])->name('quotation-series.confirm');
    Route::get('quotation-series/{quotation_series}/export/{format}', [QuotationSeriesController::class, 'export'])->name('quotation-series.export');
    Route::post('quotation-series/{quotation_series}/generate-po', [QuotationSeriesController::class, 'generatePo'])->name('quotation-series.generate-po');
    Route::post('quotation-series/{quotation_series}/in-transit', [QuotationSeriesController::class, 'markInTransit'])->name('quotation-series.in-transit');
    Route::post('quotation-series/{quotation_series}/close', [QuotationSeriesController::class, 'close'])->name('quotation-series.close');

    // Legacy procurement folder URLs (pre-quotation-series rename)
    Route::redirect('procurement/folders', '/quotation-series', 301);
    Route::redirect('procurement/folders/create', '/quotation-series/create', 301);
    Route::get('procurement/suppliers/{supplier}/defaults', fn (\App\Models\Supplier $supplier) => redirect()->route('quotation-series.suppliers.defaults', $supplier));
    Route::get('procurement/folders/{path}', function (string $path) {
        return redirect('/quotation-series/'.$path, 301);
    })->where('path', '.+');

    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'show']);
    Route::post('purchase-orders/{purchase_order}/close-short', [PurchaseOrderController::class, 'closeShort'])->name('purchase-orders.close-short');
    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('purchase-orders/{purchase_order}/goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
    Route::post('purchase-orders/{purchase_order}/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    Route::get('goods-receipts/{goods_receipt_note}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    Route::post('goods-receipts/{goods_receipt_note}/void', [GoodsReceiptController::class, 'void'])->name('goods-receipts.void');

    Route::get('supplier-payments', [\App\Http\Controllers\SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
    Route::get('supplier-payments/create', [\App\Http\Controllers\SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
    Route::post('supplier-payments', [\App\Http\Controllers\SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
    Route::get('supplier-payments/{supplier_payment}', [\App\Http\Controllers\SupplierPaymentController::class, 'show'])->name('supplier-payments.show');
    Route::post('supplier-payments/{supplier_payment}/void', [\App\Http\Controllers\SupplierPaymentController::class, 'void'])->name('supplier-payments.void');

    // Finance / General Ledger (F1)
    Route::get('chart-of-accounts', [\App\Http\Controllers\ChartOfAccountController::class, 'index'])->name('chart-of-accounts.index');
    Route::get('chart-of-accounts/create', [\App\Http\Controllers\ChartOfAccountController::class, 'create'])->name('chart-of-accounts.create');
    Route::post('chart-of-accounts', [\App\Http\Controllers\ChartOfAccountController::class, 'store'])->name('chart-of-accounts.store');
    Route::get('chart-of-accounts/{chart_of_account}', [\App\Http\Controllers\ChartOfAccountController::class, 'show'])->name('chart-of-accounts.show');
    Route::get('chart-of-accounts/{chart_of_account}/edit', [\App\Http\Controllers\ChartOfAccountController::class, 'edit'])->name('chart-of-accounts.edit');
    Route::put('chart-of-accounts/{chart_of_account}', [\App\Http\Controllers\ChartOfAccountController::class, 'update'])->name('chart-of-accounts.update');

    Route::get('journal-entries', [\App\Http\Controllers\JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('journal-entries/create', [\App\Http\Controllers\JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('journal-entries', [\App\Http\Controllers\JournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('journal-entries/{journal_entry}', [\App\Http\Controllers\JournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::post('journal-entries/{journal_entry}/submit', [\App\Http\Controllers\JournalEntryController::class, 'submit'])->name('journal-entries.submit');
    Route::post('journal-entries/{journal_entry}/void', [\App\Http\Controllers\JournalEntryController::class, 'void'])->name('journal-entries.void');

    Route::get('trial-balance', [\App\Http\Controllers\TrialBalanceController::class, 'index'])->name('trial-balance.index');

    Route::get('financial-statements', [\App\Http\Controllers\FinancialStatementController::class, 'index'])->name('financial-statements.index');

    Route::get('tax-remittances', [\App\Http\Controllers\TaxRemittanceController::class, 'index'])->name('tax-remittances.index');
    Route::get('tax-remittances/{tax_remittance}', [\App\Http\Controllers\TaxRemittanceController::class, 'show'])->name('tax-remittances.show');
    Route::post('tax-remittances/{tax_remittance}/file', [\App\Http\Controllers\TaxRemittanceController::class, 'file'])->name('tax-remittances.file');
    Route::post('tax-remittances/{tax_remittance}/pay', [\App\Http\Controllers\TaxRemittanceController::class, 'pay'])->name('tax-remittances.pay');

    Route::get('bank-reconciliations', [\App\Http\Controllers\BankReconciliationController::class, 'index'])->name('bank-reconciliations.index');
    Route::get('bank-reconciliations/create', [\App\Http\Controllers\BankReconciliationController::class, 'create'])->name('bank-reconciliations.create');
    Route::post('bank-reconciliations', [\App\Http\Controllers\BankReconciliationController::class, 'store'])->name('bank-reconciliations.store');
    Route::get('bank-reconciliations/{bank_reconciliation}', [\App\Http\Controllers\BankReconciliationController::class, 'show'])->name('bank-reconciliations.show');
    Route::post('bank-reconciliations/{bank_reconciliation}/complete', [\App\Http\Controllers\BankReconciliationController::class, 'complete'])->name('bank-reconciliations.complete');

    Route::get('financial-periods', [\App\Http\Controllers\FinancialPeriodController::class, 'index'])->name('financial-periods.index');
    Route::post('financial-periods/close', [\App\Http\Controllers\FinancialPeriodController::class, 'close'])->name('financial-periods.close');
    Route::post('financial-periods/reopen', [\App\Http\Controllers\FinancialPeriodController::class, 'reopen'])->name('financial-periods.reopen');

    // Transfer requests (shop managers request stock)
    Route::get('transfer-requests/availability', [TransferRequestController::class, 'availability'])->name('transfer-requests.availability');
    Route::resource('transfer-requests', TransferRequestController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('transfer-requests/{transfer_request}/submit', [TransferRequestController::class, 'submit'])->name('transfer-requests.submit');
    Route::post('transfer-requests/{transfer_request}/accept', [TransferRequestController::class, 'accept'])->name('transfer-requests.accept');
    Route::post('transfer-requests/{transfer_request}/reject', [TransferRequestController::class, 'reject'])->name('transfer-requests.reject');
    Route::post('transfer-requests/{transfer_request}/create-stock-transfer', [TransferRequestController::class, 'createStockTransfer'])->name('transfer-requests.create-stock-transfer');

    // Stock transfers (operational moves — admin approval)
    Route::get('stock-transfers/availability', [StockTransferController::class, 'availability'])->name('stock-transfers.availability');
    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('stock-transfers/{stock_transfer}/submit', [StockTransferController::class, 'submit'])->name('stock-transfers.submit');
    Route::post('stock-transfers/{stock_transfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('stock-transfers.dispatch');
    Route::get('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receiveForm'])->name('stock-transfers.receive');
    Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive'])->name('stock-transfers.receive.store');

    // Legacy URLs
    Route::redirect('transfers', '/stock-transfers');
    Route::redirect('transfers/create', '/stock-transfers/create');

    // Sales / POS (M15)
    Route::get('sales/pos', [SaleController::class, 'pos'])->name('sales.pos');
    Route::get('sales/order', [SaleController::class, 'order'])->name('sales.order');
    Route::get('sales/desk', [SaleController::class, 'desk'])->name('sales.desk');
    Route::get('sales/desk/{sale}', [SaleController::class, 'deskCheckout'])->name('sales.desk.checkout');
    Route::get('sales/pos/search', [SaleController::class, 'searchProducts'])->name('sales.search');
    Route::post('sales/hold', [SaleController::class, 'hold'])->name('sales.hold');
    Route::post('sales/checkout', [SaleController::class, 'checkout'])->name('sales.checkout');
    Route::post('sales/{sale}/complete', [SaleController::class, 'complete'])->name('sales.complete');
    Route::post('sales/{sale}/reverse', [SaleController::class, 'reverse'])->name('sales.reverse');
    Route::post('sales/{sale}/abandon', [SaleController::class, 'abandon'])->name('sales.abandon');
    Route::post('sales/{sale}/issue-on-account', [SaleController::class, 'issueOnAccount'])->name('sales.issue-on-account');
    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('receipts/{sale}', [ReceiptController::class, 'show'])->name('receipts.show');

    // Customer accounts & fleet billing
    Route::resource('customer-accounts', CustomerAccountController::class);
    Route::get('customer-invoices', [CustomerInvoiceController::class, 'index'])->name('customer-invoices.index');
    Route::get('customer-invoices/create', [CustomerInvoiceController::class, 'create'])->name('customer-invoices.create');
    Route::post('customer-invoices', [CustomerInvoiceController::class, 'store'])->name('customer-invoices.store');
    Route::get('customer-invoices/{customer_invoice}', [CustomerInvoiceController::class, 'show'])->name('customer-invoices.show');
    Route::post('customer-invoices/{customer_invoice}/payments', [CustomerInvoiceController::class, 'recordPayment'])->name('customer-invoices.record-payment');

    // Returns (M16)
    Route::get('customer-returns', [CustomerReturnController::class, 'index'])->name('customer-returns.index');
    Route::get('customer-returns/create', [CustomerReturnController::class, 'create'])->name('customer-returns.create');
    Route::post('customer-returns', [CustomerReturnController::class, 'store'])->name('customer-returns.store');
    Route::get('customer-returns/sales/search', [CustomerReturnController::class, 'searchSales'])->name('customer-returns.search-sales');
    Route::get('customer-returns/sales/{sale}/items', [CustomerReturnController::class, 'saleItems'])->name('customer-returns.sale-items');
    Route::get('customer-returns/{customer_return}', [CustomerReturnController::class, 'show'])->name('customer-returns.show');
    Route::post('customer-returns/{customer_return}/submit', [CustomerReturnController::class, 'submit'])->name('customer-returns.submit');
    Route::delete('customer-returns/{customer_return}', [CustomerReturnController::class, 'destroy'])->name('customer-returns.destroy');

    Route::get('supplier-returns/availability', [SupplierReturnController::class, 'availability'])->name('supplier-returns.availability');
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

    // HR & Payroll (M21–M22)
    Route::resource('employees', EmployeeController::class);
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/periods', [PayrollController::class, 'storePeriod'])->name('payroll.periods.store');
    Route::get('payroll/periods/{period}', [PayrollController::class, 'showPeriod'])->name('payroll.periods.show');
    Route::post('payroll/periods/{period}/generate', [PayrollController::class, 'generate'])->name('payroll.periods.generate');
    Route::post('payroll/runs/{run}/lock', [PayrollController::class, 'lock'])->name('payroll.runs.lock');
    Route::post('payroll/runs/{run}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.runs.mark-paid');
    Route::get('payroll/runs/{run}/export/{format}', [PayrollController::class, 'export'])->name('payroll.runs.export');
    Route::get('payroll/payslips/{line}', [PayrollController::class, 'payslip'])->name('payroll.payslip');
});

require __DIR__.'/auth.php';
