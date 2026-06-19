# DayByDay Automotive — Build Roadmap

This document outlines **every module** to be built, in the order we will build
them, so the system comes together with dependencies satisfied at each step.

Each module is delivered the same way (your preferred sequential workflow):

> **Model(s) → Form Requests → Controller(s) → Views (Blade) → Routes (`web.php`) → Nav link & permissions**

Supporting pieces (migrations) already exist; per module we add Eloquent
models, validation, controllers, Blade views, and routes, plus seeders/policies
where noted.

---

## 0. Conventions & Stack

**Architecture**
- Server-rendered **Blade** views, organized per module under `resources/views/<module>/`.
- Controllers in `app/Http/Controllers/<Module>/`.
- Form Requests in `app/Http/Requests/<Module>/`.
- Domain logic that touches inventory lives in **Service classes** (`app/Services/`), never directly in controllers, so the ledger stays the single source of truth.
- Authorization via **spatie/laravel-permission** (roles + permissions) and Laravel **Policies**.
- Route model binding + resource controllers where it fits.

**UI stack (decision needed — recommendation):**
- Tailwind CSS + Alpine.js for interactivity (Vite already configured).
- **Livewire** for the POS/Sales screen and other highly-interactive screens (cart, live stock lookup). Plain Blade elsewhere.

**Model naming gotcha**
- The `returns` table cannot map to a class named `Return` (reserved word in PHP).
  We will use model **`ReturnRecord`** (or `SalesReturn`) with `protected $table = 'returns'`.

**Cross-cutting traits/services reused everywhere**
- `Auditable` (writes to `audit_logs`)
- `Approvable` (links to the generic approval engine)
- `InventoryService` (writes `stock_ledger` + updates `stock_balances` atomically)

---

## Build Phases (high-level order)

| Phase | Theme | Modules |
|-------|-------|---------|
| 1 | Foundation | M1 App Shell & Layout · M2 Authentication & Access Control |
| 2 | Master Data (CRUD) | M3 Warehouses · M4 Shops · M5 Suppliers · M6 Vehicle Makes & Models · M7 Categories · M8 Product Names · M9 Units · M10 Products |
| 3 | Core Services | M11 Approval Engine · M12 Inventory & Stock Adjustments |
| 4 | Operations | M13 Procurement & Quotation · M14 Distribution & Transfers · M15 Sales (POS) · M16 Returns |
| 5 | Insight & System | M17 Reporting & Analytics · M18 Audit & Activity Log · M19 Notification Center · M20 Dashboard |

Rationale: master data must exist before products; products before any stock
operation; the **approval engine and inventory service** are dependencies for
procurement/transfers/adjustments/sales, so they come before operations;
reporting/audit/notifications/dashboard consume everything else, so they come last.

---

# PHASE 1 — FOUNDATION

## M1. Application Shell & Layout
**Purpose:** Base layout, navigation, and authenticated app skeleton.
- **Models:** — (none)
- **Controllers:** `DashboardController` (placeholder index)
- **Views:** `layouts/app.blade.php`, `layouts/guest.blade.php`, partials (`partials/sidebar`, `partials/navbar`, `partials/flash`), `dashboard/index` (placeholder)
- **Routes:** `/` → dashboard (auth), home redirect
- **Notes:** Install/confirm UI stack here (Tailwind/Alpine, optional Livewire). Set up auth scaffolding (Laravel Breeze or hand-rolled login).
- **Depends on:** —

## M2. Authentication & Access Control
**Purpose:** Login/logout, users, roles, permissions, login history. (Spec §1)
- **Models:** `User` (add `HasRoles`, relations, soft deletes), `UserLogin`; spatie `Role` & `Permission` (config only)
- **Services:** `RolePermissionSeeder` logic; login-history listener
- **Form Requests:** `StoreUserRequest`, `UpdateUserRequest`, `StoreRoleRequest`
- **Controllers:** `Auth/LoginController` (or Breeze), `UserController` (resource), `RoleController` (resource), `ProfileController`
- **Views:** `auth/login`, `users/{index,create,edit,show}`, `roles/{index,create,edit}`, `profile/edit`
- **Routes:** auth routes; `users.*`, `roles.*` (admin only)
- **Seeders:** `RoleSeeder` (Administrator, Shop Manager + permissions), `AdminUserSeeder`
- **Events/Listeners:** `Login`/`Logout` → record `user_logins`, update `last_login_at`
- **Roles:** Administrator only (user/role management)
- **Depends on:** M1

---

# PHASE 2 — MASTER DATA

> All master-data modules share the same CRUD shape: index (search + paginate),
> create, store, edit, update, archive (soft delete) / toggle `is_active`.
> Administrator-managed; Shop Manager read-only where relevant.

## M3. Warehouses
- **Models:** `Warehouse` (hasMany users; morphMany stock via location)
- **Requests:** `StoreWarehouseRequest`, `UpdateWarehouseRequest`
- **Controller:** `WarehouseController` (resource)
- **Views:** `warehouses/{index,create,edit,show}`
- **Routes:** `warehouses.*`
- **Depends on:** M2

## M4. Shops
- **Models:** `Shop` (hasMany users, sales; morphMany stock)
- **Requests:** `StoreShopRequest`, `UpdateShopRequest`
- **Controller:** `ShopController` (resource)
- **Views:** `shops/{index,create,edit,show}`
- **Routes:** `shops.*`
- **Depends on:** M2

## M5. Suppliers (Spec §6)
- **Models:** `Supplier` (hasMany products, procurementFolders, purchaseOrders, returns)
- **Requests:** `StoreSupplierRequest`, `UpdateSupplierRequest`
- **Controller:** `SupplierController` (resource) + `show` with procurement history tab
- **Views:** `suppliers/{index,create,edit,show}`
- **Routes:** `suppliers.*`
- **Depends on:** M2

## M6. Vehicle Makes & Models (Spec §4)
- **Models:** `VehicleMake` (hasMany models), `VehicleModel` (belongsTo make; belongsToMany products)
- **Requests:** `StoreVehicleMakeRequest`, `StoreVehicleModelRequest` (+ update)
- **Controllers:** `VehicleMakeController`, `VehicleModelController` (nested or filtered by make)
- **Views:** `vehicle-makes/{index,create,edit}`, `vehicle-models/{index,create,edit}`
- **Routes:** `vehicle-makes.*`, `vehicle-models.*`
- **Depends on:** M2

## M7. Categories
- **Models:** `Category` (self-referencing parent/children; hasMany products)
- **Requests:** `StoreCategoryRequest`, `UpdateCategoryRequest`
- **Controller:** `CategoryController` (resource)
- **Views:** `categories/{index,create,edit}` (tree/nested display)
- **Routes:** `categories.*`
- **Depends on:** M2

## M8. Product Names (Spec §5)
- **Models:** `ProductName` (hasMany products)
- **Requests:** `StoreProductNameRequest`, `UpdateProductNameRequest`
- **Controller:** `ProductNameController` (resource)
- **Views:** `product-names/{index,create,edit}`
- **Routes:** `product-names.*`
- **Depends on:** M2

## M9. Units (Spec §5)
- **Models:** `Unit` (hasMany products)
- **Requests:** `StoreUnitRequest`, `UpdateUnitRequest`
- **Controller:** `UnitController` (resource)
- **Views:** `units/{index,create,edit}`
- **Routes:** `units.*`
- **Depends on:** M2

## M10. Products (Spec §3)
- **Models:** `Product` (belongsTo productName, vehicleMake, vehicleModel, category, unit; belongsToMany vehicleModels via `product_vehicle_model`; morphMany stock; hasMany ledger entries)
- **Requests:** `StoreProductRequest`, `UpdateProductRequest`
- **Controller:** `ProductController` (resource) + `show` (stock balances, movement, procurement history tabs), product search endpoint for POS
- **Views:** `products/{index,create,edit,show}`
- **Routes:** `products.*`, `products.search` (AJAX)
- **Notes:** Barcode + part number uniqueness; primary make/model + multi-model fitment selector.
- **Depends on:** M5–M9

---

# PHASE 3 — CORE SERVICES

## M11. Approval Engine (Spec — Additional Modules)
**Purpose:** One reusable approval workflow for procurement, transfers, returns,
adjustments, and large discounts.
- **Models:** `Approval` (morphTo approvable; belongsTo requester/currentApprover), `ApprovalAction`
- **Trait:** `Approvable` (model concern: `requestApproval()`, `approve()`, `reject()`, `returnForRevision()`, `isApproved()`)
- **Service:** `ApprovalService` (state transitions + writes `approval_actions`, fires notifications)
- **Requests:** `ApprovalActionRequest`
- **Controller:** `ApprovalController` (inbox: pending for current user; act on approval)
- **Views:** `approvals/{index,show}` + reusable `partials/approval-timeline`
- **Routes:** `approvals.*`, `approvals.act`
- **Depends on:** M2

## M12. Inventory Management & Stock Adjustments (Spec §8)
**Purpose:** The core inventory engine — ledger + balances + adjustments.
- **Models:** `StockLedger`, `StockBalance`, `StockAdjustment` (Approvable, Auditable), `StockAdjustmentItem`
- **Service:** `InventoryService`
  - `record(product, location, type, qty, unitCost, reference)` → append ledger row, compute `balance_after`, upsert `stock_balances` (atomic, DB transaction)
  - `reserve()` / `release()` for `quantity_reserved`
  - `valuation(location)`, `available(product, location)`
- **Requests:** `StoreStockAdjustmentRequest`
- **Controllers:** `InventoryController` (balances, movement history, valuation views), `StockAdjustmentController` (resource + submit-for-approval)
- **Views:** `inventory/{index,show,movements,valuation}`, `stock-adjustments/{index,create,show}`
- **Routes:** `inventory.*`, `stock-adjustments.*`
- **Notes:** Adjustments require approval (Approval Engine) before posting to the ledger. **Define when `quantity_reserved` is set** (held sales? approved transfer requests?) — confirm here.
- **Depends on:** M10, M11

---

# PHASE 4 — OPERATIONS

## M13. Procurement & Quotation (Spec §7)
**Purpose:** Folder → cost analysis → approval → PO → goods-in-transit → receipt → close.
- **Models:** `ProcurementFolder` (Approvable, Auditable), `ProcurementItem`, `PurchaseOrder`, `PurchaseOrderItem`, `GoodsReceiptNote`, `GoodsReceiptNoteItem`
- **Services:** `CostAnalysisService` (freight/CBM/tax → landing cost, cost-per-unit, margin, recommended price), `GoodsReceiptService` (validates qty, posts `purchase_receipt` to InventoryService, updates product cost)
- **Requests:** `StoreProcurementFolderRequest`, `ProcurementItemRequest`, `StorePurchaseOrderRequest`, `StoreGoodsReceiptRequest`
- **Controllers:** `ProcurementFolderController`, `ProcurementItemController`, `PurchaseOrderController`, `GoodsReceiptController`
- **Views:** `procurement/folders/{index,create,edit,show}`, `procurement/cost-analysis`, `purchase-orders/{index,show}`, `goods-receipts/{create,show}`
- **Routes:** `procurement.*`, `purchase-orders.*`, `goods-receipts.*`
- **Roles:** Administrator
- **Depends on:** M10, M11, M12

## M14. Distribution & Transfers (Spec §9)
**Purpose:** Warehouse→shop and inter-shop stock movement with approvals.
- **Models:** `TransferRequest` (Approvable, Auditable), `TransferRequestItem`, `StockTransfer`, `StockTransferItem`
- **Service:** `TransferService` (dispatch → `transfer_out` ledger at source; receive → `transfer_in` ledger at destination; handles damaged qty)
- **Requests:** `StoreTransferRequestRequest`, `DispatchTransferRequest`, `ReceiveTransferRequest`
- **Controllers:** `TransferRequestController`, `StockTransferController` (dispatch/receive actions)
- **Views:** `transfers/requests/{index,create,show}`, `transfers/dispatch`, `transfers/receive`
- **Routes:** `transfer-requests.*`, `stock-transfers.*`
- **Roles:** Shop Manager (request, receive), Administrator (approve, dispatch)
- **Depends on:** M10, M11, M12

## M15. Sales / POS (Spec §10)
**Purpose:** Retail checkout: search → cart → checkout → payment → receipt → inventory update.
- **Models:** `Sale` (Auditable), `SaleItem`, `Payment`
- **Service:** `SaleService` (totals/discount/tax; on complete → `sale` ledger entry, reduce stock; hold/resume; reversal)
- **Interactivity:** Livewire POS component (cart, live product search, multi-tender payment)
- **Requests:** `StoreSaleRequest`, `PaymentRequest`
- **Controllers:** `SaleController` (POS screen, complete, hold, resume, reverse, receipt), `ReceiptController` (print/reprint/email)
- **Views:** `sales/pos` (Livewire), `sales/{index,show}`, `sales/receipt`
- **Routes:** `sales.*`, `sales.hold`, `sales.resume`, `sales.reverse`, `receipts.*`
- **Roles:** Shop Manager (own shop), Administrator (oversight, reversal, large-discount approval via Approval Engine)
- **Depends on:** M10, M12 (+ M11 for discount approval)

## M16. Returns (Spec §11)
**Purpose:** Customer returns (linked to sale) and supplier returns.
- **Models:** `ReturnRecord` (table `returns`, Approvable, Auditable), `ReturnItem`
- **Service:** `ReturnService` (customer return → restock good items via `customer_return` ledger, refund record; supplier return → `supplier_return` ledger)
- **Requests:** `StoreCustomerReturnRequest`, `StoreSupplierReturnRequest`
- **Controllers:** `CustomerReturnController`, `SupplierReturnController`
- **Views:** `returns/customer/{index,create,show}`, `returns/supplier/{index,create,show}`
- **Routes:** `customer-returns.*`, `supplier-returns.*`
- **Depends on:** M12, M15 (customer returns reference sales)

---

# PHASE 5 — INSIGHT & SYSTEM

## M17. Reporting & Analytics (Spec §12)
**Purpose:** Sales, inventory, procurement, transfer, and financial reports.
- **Models:** — (read-only queries / query objects)
- **Services:** `Reports/*` query classes; export (CSV/PDF)
- **Controllers:** `ReportController` (sales, inventory, procurement, transfers, financial)
- **Views:** `reports/{sales,inventory,procurement,transfers,financial}` + filters
- **Routes:** `reports.*`, `reports.export`
- **Roles:** Administrator (all), Shop Manager (own shop only)
- **Depends on:** M13–M16

## M18. Audit & Activity Log (Spec §13)
**Purpose:** Traceability of critical actions.
- **Models:** `AuditLog`
- **Trait/Observer:** `Auditable` trait + model observers writing old/new values
- **Controller:** `AuditLogController` (filterable index, show)
- **Views:** `audit-logs/{index,show}`
- **Routes:** `audit-logs.*`
- **Roles:** Administrator
- **Notes:** Trait is applied incrementally as each module is built; this module adds the **viewer UI**.
- **Depends on:** M2 (consumes data from all modules)

## M19. Notification Center (Spec — Additional Modules)
**Purpose:** Notify users (low stock, approvals, dispatches, arrivals, folder completion).
- **Models:** uses Laravel `notifications` table
- **Notifications:** `LowStockNotification`, `ApprovalRequestedNotification`, `TransferDispatchedNotification`, `GoodsArrivedNotification`, `ProcurementClosedNotification`
- **Controller:** `NotificationController` (list, mark read)
- **Views:** `notifications/index` + navbar dropdown partial
- **Routes:** `notifications.*`
- **Depends on:** M11–M16

## M20. Dashboard (Spec §2)
**Purpose:** Wire the placeholder dashboard to real KPIs.
- **Service:** `DashboardService` (daily/monthly sales, top sellers, low stock, below-reorder, pending transfers/approvals, open folders, inventory valuation, revenue trends, recent activity)
- **Controller:** `DashboardController` (final)
- **Views:** `dashboard/index` (cards + charts), role-aware (admin = global, shop manager = own shop)
- **Routes:** `dashboard` (already defined in M1)
- **Depends on:** all prior modules

---

## Per-Module Definition of Done

For each module we will consider it complete when:
- [ ] Eloquent model(s) with relationships, casts, `$fillable`, scopes
- [ ] Form Request validation
- [ ] Controller actions (resourceful where applicable)
- [ ] Blade views wired and styled
- [ ] Routes registered in `web.php` with auth + permission middleware
- [ ] Navigation entry + role/permission gating
- [ ] Seeder/factory where useful for testing
- [ ] `Auditable` applied to critical actions (from M18 onward, retro-applied)
- [ ] Manual smoke test passes

---

## Suggested Starting Point

We begin with **M2 Authentication & Access Control** (models first), since every
other module depends on users, roles, and permissions — unless you'd prefer to
start with the **Product module** group (M3–M10 master data) and add a minimal
auth shell first. Confirm and we'll start building model-by-model.
