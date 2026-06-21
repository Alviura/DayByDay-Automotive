# Procurement Quotation & Order Processing — Design Specification

**Project:** DayByDay Automotive  
**Module:** M13 Procurement (redesign of existing `ProcurementFolder`)  
**Status:** Approved for implementation — reference document for build  
**Last updated:** 2026-06-20

---

## 1. Purpose

This document defines the **redesign of the existing procurement folder** into a three-phase workflow:

1. **Create folder** — admin selects supplier; system derives purchase type and currency; admin enters CBM(R) and Conversion(R).
2. **Quotation draft** — bulk-add products (checkbox + quantity); prices blank; exportable document sent to supplier.
3. **Order processing** — admin enters supplier prices; system computes Local or Import line summaries and cumulative totals; folder auto-approves.

This replaces the current generic `CostAnalysisService` flow (margin-as-input → recommended price computed) with the business-accurate model shown in legacy screenshots: **MKT Wholesale Price is read from the product; margin is derived.**

**Out of scope for this redesign:** goods receipt, PO generation mechanics, inventory posting — those remain as-is but consume the new calculated fields.

---

## 2. Terminology

| Term | Meaning in this system |
|------|------------------------|
| **Procurement folder** | The container record (`procurement_folders`). Same table, redesigned behaviour. |
| **Quotation / draft** | Phase B state: line items with quantities but no supplier prices yet. |
| **Order** | Phase C state: supplier prices entered, calculations run, folder approved. |
| **Purchase type** | `local` or `import` — drives which calculator runs. |
| **CBM(R)** | Transport rate in KES **per cubic metre** (folder-level). Used for import transport allocation. |
| **Conversion(R)** | Exchange rate: foreign currency → KES (folder-level). Only meaningful for import. |
| **MKT Wholesale Price** | Pulled from `products.min_selling_price` at calculation time (read-only on the order). |
| **Unit Cost (Arrival)** | Landed cost per unit in KES after purchase price + transport. Becomes PO/receipt cost basis. |

---

## 3. High-Level Workflow

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐     ┌──────────────┐
│ Create Folder   │────▶│ Quotation Draft  │────▶│ Order Processing    │────▶│ PO / Transit │
│ (Phase A)       │     │ (Phase B)        │     │ (Phase C)           │     │ / Receipt    │
└─────────────────┘     └──────────────────┘     └─────────────────────┘     └──────────────┘
  Select supplier         Bulk add products        Enter supplier prices      Existing flow
  Auto: type, currency    Qty only, no prices      Run calculator           unchanged
  User: CBM(R), Conv(R)   Export draft to supplier Auto-approve (admin)
```

### Phase A — Create folder

**User actions:**
- Select **supplier** (required).
- Optionally add a **description** suffix for the display title (e.g. `TUBE SHARK/NISSAN AIR CLEANERS`).
- Enter **CBM(R)** and **Conversion(R)** when applicable.

**System actions:**
- Generate folder **display name**: `{dateOfCreation}-{supplierName}[-{description}]`  
  Example: `26MAR2022 - MUTYSIA TUBE SHARK/NISSAN AIR CLEANERS`
- Generate internal **folder number** (keep existing `PF-YYYY-####` for audit/PO linkage).
- Derive from supplier:
  - **Currency** ← `suppliers.currency`
  - **Purchase type** ← new `suppliers.purchase_type` (`local` | `import`)
- Set status → `quotation_draft` (replaces generic `draft` for new folders).
- For **local**: Conversion(R) defaults to `1`, CBM(R) optional/unused.
- For **import**: Conversion(R) required; CBM(R) required before order processing.

### Phase B — Quotation draft (add products)

**User actions:**
- Open folder → **Add Products** screen.
- Multi-select products via checkbox list; checking a row reveals **quantity** input.
- Submit bulk → all selected lines saved at once.
- Remove individual lines.
- **Export** draft (Copy / CSV / Excel / PDF / Print) — prices column shows `—`.

**System actions:**
- Create `procurement_items` rows: `product_id`, `quantity` only.
- `unit_price` (supplier quote) = `null` until Phase C.
- Display product metadata from relations: part number, name, make, vehicle, unit.
- Status remains `quotation_draft` until user proceeds to order processing.

**Validation:**
- At least one line before proceeding to Phase C.
- No duplicate product on same folder (update qty if re-added).
- Quantity > 0.

### Phase C — Order processing

**User actions:**
- Open quotation draft → **Process Order** (or equivalent tab).
- Enter supplier prices per line:
  - **Local:** Unit Price (Ksh) + Transport (Ksh, per line, manual).
  - **Import:** Unit Price (foreign currency) + packet dimensions (width, length, height) + qty per packet + number of packets (or derive packets from total qty).
- Click **Calculate** / save → system runs calculator.
- Review line summary + cumulative footer.
- **Confirm order** → folder auto-approves.

**System actions:**
- Pull `market_wholesale_price` from `products.min_selling_price` per line.
- Run `LocalOrderCalculator` or `ImportOrderCalculator`.
- Persist all computed columns on `procurement_items`.
- Persist folder-level cumulative totals on `procurement_folders`.
- Set status → `approved` immediately (no approval inbox step for admin-created orders).
- Set `approved_by` = current user, `approved_at` = now().
- Do **not** write back to `products.cost_price` or selling band (see §10).

**Post-approval (unchanged):**
- Generate PO → `po_generated`
- Mark in transit → `in_transit`
- Goods receipt → `received` / `closed`

---

## 4. Status Machine

### New / revised statuses

| Status | Phase | Editable | Description |
|--------|-------|----------|-------------|
| `quotation_draft` | B | Yes (items, header) | Products added; no prices or not yet processed |
| `order_draft` | C | Yes (prices, dimensions) | Prices entered but not yet calculated/confirmed |
| `approved` | C complete | No | Order confirmed; auto-approved by admin |
| `po_generated` | Post | No | PO created |
| `in_transit` | Post | No | Shipment in progress |
| `received` | Post | No | GRN completed |
| `closed` | Post | No | Folder closed |
| `cancelled` | Any | No | Voided |

### Status transitions

```
quotation_draft ──(add items, proceed)──▶ order_draft
order_draft     ──(calculate + confirm)──▶ approved
approved        ──(generate PO)──────────▶ po_generated
po_generated    ──(mark in transit)──────▶ in_transit
in_transit      ──(goods receipt)────────▶ received
received        ──(close)────────────────▶ closed

quotation_draft / order_draft ──(delete)──▶ soft deleted (draft only)
```

### Deprecate / map old statuses

| Old status | New equivalent |
|------------|----------------|
| `draft` | `quotation_draft` |
| `cost_analysis` | `order_draft` |
| `pending_approval` | **Removed** — auto-approve on confirm |

Existing seed/data using old statuses should be migrated in a one-time migration or seeder update.

### Approval engine

- **Do not** call `requestApproval()` on order confirm.
- Remove dependency on `pending_approval` for this module.
- `ProcurementFolder` may retain `Approvable` trait for audit compatibility but auto-approve path bypasses inbox.
- Optional: create a synthetic `Approval` record with status `approved` for audit trail — **defer unless needed**.

---

## 5. Folder Naming & Display

### Display title (user-facing)

Format:
```
{DDMMMYYYY} - {SUPPLIER_NAME}[- {DESCRIPTION}][ - {STATUS_SUFFIX}]
```

Examples from legacy system:
- `26MAR2022 - MUTYSIA TUBE SHARK/NISSAN AIR CLEANERS`
- `14NOVEMBER2021 - ALKHAIR - COMPLETED-CLOSED`

**Implementation:**
- Add `title` or `display_name` column on `procurement_folders` (varchar 255).
- Auto-generate on create: `now()->format('dMY') . ' - ' . $supplier->name`.
- Append optional `description` field if user provides it.
- Append status suffix only when `closed` (e.g. ` - COMPLETED-CLOSED`).

### Internal reference (unchanged)

- Keep `folder_number` as `PF-{YYYY}-{####}` for PO, GRN, audit logs, and reports.

---

## 6. Supplier-Driven Defaults

### New supplier field

Add to `suppliers` table:

| Column | Type | Values | Notes |
|--------|------|--------|-------|
| `purchase_type` | enum | `local`, `import` | Required for auto-fill |

**Migration default:** infer from `country` — `Kenya` → `local`, else `import` — or default `import` for existing rows.

### Auto-fill on folder create

| Folder field | Source |
|--------------|--------|
| `currency` | `suppliers.currency` |
| `purchase_type` | `suppliers.purchase_type` |
| `exchange_rate` (Conversion R) | `1` if local; user-entered if import |
| `cbm_rate` (CBM R) | User-entered; required for import before Phase C |

User can override currency and purchase type on create if needed (admin only).

---

## 7. Calculation Specifications

All money values stored as `decimal(15,2)` unless noted. Percentages as `decimal(7,2)`.

**Rounding:** round to 2 decimal places at each displayed column unless legacy requires 4 decimals for foreign unit price (store `unit_price_foreign` as `decimal(15,4)`).

### 7.1 Shared inputs (per line)

| Input | Source | Phase |
|-------|--------|-------|
| `quantity` | User (Phase B) | B |
| `market_wholesale_price` | `products.min_selling_price` | C (read-only) |
| `unit_price` | User — supplier quote | C |

### 7.2 Local purchase calculator

**Folder-level:** none required beyond supplier defaults.  
**Line-level inputs:** `unit_price` (KES), `transport` (KES, manual per line).

| Output column | Formula |
|---------------|---------|
| `total_purchase_price` | `quantity × unit_price` |
| `transport` | User input (default 0) |
| `unit_cost_arrival` | `unit_price + (transport / quantity)` — if transport is **per-line total**, use `(unit_price × quantity + transport) / quantity`; **confirm with user: transport is per-line total amount, not per unit** |
| `margin_amount` | `market_wholesale_price - unit_cost_arrival` |
| `margin_percent` | `(margin_amount / market_wholesale_price) × 100` when wholesale > 0, else 0 |
| `actual_total_cost` | `unit_cost_arrival × quantity` |
| `expected_sales` | `market_wholesale_price × quantity` |
| `expected_margin` | `margin_amount × quantity` |

**Transport clarification (confirmed):** transport is a **per-line manual entry** (total transport cost for that line, not per unit). Therefore:

```
unit_cost_arrival = ((unit_price × quantity) + transport) / quantity
                  = unit_price + (transport / quantity)

actual_total_cost = (unit_price × quantity) + transport
```

**Verified against screenshot sample (Row 1):**
- Qty 22, Unit 160, Transport 0 → Total 3,520; Arrival 160; Wholesale 200; Margin 40 (20%); Expected sales 4,400; Expected margin 880 ✓

### 7.3 Import purchase calculator

**Folder-level inputs:**
- `exchange_rate` (Conversion R)
- `cbm_rate` (CBM R) — KES per CBM

**Line-level inputs:**
- `unit_price_foreign` — supplier price in folder currency (e.g. USD)
- `width`, `length`, `height` — metres (per packet)
- `quantity_per_packet` — units in one packet/carton
- `number_of_packets` — cartons (may equal `quantity / quantity_per_packet`)

| Output column | Formula |
|---------------|---------|
| `total_cost_foreign` | `quantity × unit_price_foreign` |
| `unit_price_ksh` | `unit_price_foreign × exchange_rate` |
| `cbm_per_packet` | `width × length × height` |
| `total_cbm` | `cbm_per_packet × number_of_packets` |
| `transport_per_unit` | `(total_cbm × cbm_rate) / quantity` |
| `unit_cost_arrival` | `unit_price_ksh + transport_per_unit` |
| `margin_amount` | `market_wholesale_price - unit_cost_arrival` |
| `margin_percent` | `(margin_amount / market_wholesale_price) × 100` |
| `actual_total_cost` | `unit_cost_arrival × quantity` |
| `expected_sales` | `market_wholesale_price × quantity` |
| `expected_margin` | `margin_amount × quantity` |

**Verified against screenshot sample (Row 1):**
- Unit $16.5375 × 31.5 = 520.93 KSH ✓
- CBM 0.00107 × 300 packets = 0.32 total CBM ✓
- Transport/unit = (0.32 × 55033) / 300 ≈ 58.91 ✓
- Arrival = 520.93 + 58.91 = 579.84 ✓
- Margin = 750 - 579.84 = 170.16 ✓

### 7.4 Cumulative folder summary

Persist on `procurement_folders` after calculation:

| Field | Local | Import |
|-------|-------|--------|
| `total_purchase_price` | Σ line `unit_price × qty` | Σ line `total_cost_foreign` (USD) |
| `total_cbm` | — | Σ line `total_cbm` |
| `total_transport_cost` | Σ line `transport` | Σ line `transport_per_unit × quantity` |
| `total_actual_cost` | Σ line `actual_total_cost` | Σ line `actual_total_cost` |
| `total_expected_sales` | Σ line `expected_sales` | Σ line `expected_sales` |
| `total_expected_margin` | Σ line `expected_margin` | Σ line `expected_margin` |

**Import footer labels (from screenshots):**
1. TOTAL COST (USD) — `total_purchase_price` in foreign currency
2. TOTAL CBM
3. TOTAL TRANSPORT COST
4. TOTAL ACTUAL COST
5. TOTAL EXPECTED SALES
6. TOTAL EXPECTED MARGIN

**Sanity check:** `total_expected_sales - total_actual_cost = total_expected_margin` (verified: 2,492,600 - 2,139,008.59 = 353,591.41 ✓)

---

## 8. Data Model Changes

### 8.1 `procurement_folders` — add / change columns

| Column | Type | Notes |
|--------|------|-------|
| `title` | string(255) | Display name (date-supplier-description) |
| `description` | string(255) nullable | Optional suffix in title |
| `purchase_type` | enum `local`,`import` | Replaces free-text `import_type` |
| `cbm_rate` | decimal(15,2) nullable | CBM(R) — KES per CBM |
| `exchange_rate` | decimal(15,6) | Conversion(R) — keep, clarify meaning |
| `total_purchase_price` | decimal(18,2) | Cumulative |
| `total_cbm` | decimal(12,4) nullable | Import only |
| `total_transport_cost` | decimal(18,2) | Cumulative |
| `total_actual_cost` | decimal(18,2) | Cumulative |
| `total_expected_sales` | decimal(18,2) | Cumulative |
| `total_expected_margin` | decimal(18,2) | Cumulative |

**Deprecate / repurpose:**
- `import_type` (string) → replace with `purchase_type` enum
- `total_cost`, `total_freight`, `total_tax`, `total_landing_cost` → map to new cumulative fields or keep for backward compat during migration
- `total_tax` — not in new business model; drop from UI, keep column nullable for legacy data

**Status enum:** extend migration to include `quotation_draft`, `order_draft`; migrate old values.

### 8.2 `procurement_items` — add / change columns

| Column | Type | Phase | Notes |
|--------|------|-------|-------|
| `unit_price` | decimal(15,4) nullable | C | Supplier quote (foreign or KES depending on type) |
| `unit_price_foreign` | decimal(15,4) nullable | C | Import: price in folder currency |
| `unit_price_ksh` | decimal(15,2) nullable | C | Import: converted |
| `transport` | decimal(15,2) default 0 | C | Local: per-line manual |
| `width` | decimal(10,4) nullable | C | Import: metres |
| `length` | decimal(10,4) nullable | C |
| `height` | decimal(10,4) nullable | C |
| `quantity_per_packet` | decimal(15,2) default 1 | C | Import |
| `number_of_packets` | decimal(15,2) nullable | C | Import; can derive from qty |
| `cbm_per_packet` | decimal(12,6) nullable | C | Computed |
| `total_cbm` | decimal(12,4) nullable | C | Computed |
| `transport_per_unit` | decimal(15,2) nullable | C | Computed |
| `unit_cost_arrival` | decimal(15,2) nullable | C | **Landed KES cost — PO/receipt source** |
| `market_wholesale_price` | decimal(15,2) nullable | C | Snapshot from product at calc time |
| `margin_amount` | decimal(15,2) nullable | C | Computed |
| `margin_percent` | decimal(7,2) nullable | C | Computed |
| `total_purchase_price` | decimal(18,2) nullable | C | Line total (local or foreign) |
| `actual_total_cost` | decimal(18,2) nullable | C | Computed |
| `expected_sales` | decimal(18,2) nullable | C | Computed |
| `expected_margin` | decimal(18,2) nullable | C | Computed |

**Deprecate / map old columns:**

| Old column | New mapping |
|------------|-------------|
| `unit_cost` | → `unit_price` (supplier quote input) |
| `cbm` | → `total_cbm` or `cbm_per_packet` |
| `freight_charge` | → `transport` (local) or computed `transport_per_unit × qty` (import) |
| `tax_cost` | Drop from calculator |
| `total_cost` | → `total_purchase_price` |
| `landing_cost` | → `actual_total_cost` |
| `cost_per_unit` | → `unit_cost_arrival` |
| `margin` | → `margin_percent` (was % input; now computed) |
| `recommended_selling_price` | Drop — use `market_wholesale_price` snapshot instead |

### 8.3 `suppliers` — add column

| Column | Type | Notes |
|--------|------|-------|
| `purchase_type` | enum `local`,`import` | Default `local` for Kenya suppliers |

### 8.4 Product table — no changes

- `min_selling_price` — read for MKT Wholesale Price at calculation time.
- `cost_price` / selling band — **not updated** on order approve (see §10).

---

## 9. Service Layer

Replace `CostAnalysisService` with:

### 9.1 `ProcurementCalculationService` (facade)

```php
public function calculate(ProcurementFolder $folder): ProcurementFolder;
public function summarize(ProcurementFolder $folder): array;
public function confirmOrder(ProcurementFolder $folder, User $user): ProcurementFolder;
```

- Loads items with products.
- Dispatches to `LocalOrderCalculator` or `ImportOrderCalculator` based on `folder.purchase_type`.
- Writes line + folder totals.
- `confirmOrder()` validates all lines calculated, sets status `approved`, sets approver fields.

### 9.2 `LocalOrderCalculator`

```php
public function calculateLine(ProcurementItem $item, Product $product): ProcurementItem;
public function calculateFolder(Collection $items): array; // cumulative totals
```

### 9.3 `ImportOrderCalculator`

Same interface; requires folder `cbm_rate` and `exchange_rate`.

### 9.4 `ProcurementFolderService` (optional orchestration)

- `createFromSupplier(Supplier, array $data): ProcurementFolder`
- `bulkAddItems(ProcurementFolder, array $lines): void`
- `proceedToOrderProcessing(ProcurementFolder): void` — status `quotation_draft` → `order_draft`

### 9.5 Keep / adapt existing services

| Service | Change |
|---------|--------|
| `ProcurementService::generatePurchaseOrder()` | Use `unit_cost_arrival` (KES) as PO line `unit_cost` |
| `GoodsReceiptService::updateProductCost()` | Unchanged — updates product on receipt only |
| `ApprovalService` | Not called on confirm; remove from happy path |

---

## 10. Product Price Policy (Confirmed)

**Do not write order/quotation prices back to the product on approve.**

| Field | When updated |
|-------|--------------|
| `products.cost_price` | Goods receipt only (`GoodsReceiptService`) |
| `products.min_selling_price` | Product master data / manual edit only |
| `products.max_selling_price` | Product master data / manual edit only |

**Rationale:** quotation is a projection; actual landed cost differs; inventory weighted average updates on receipt; avoids circular MKT wholesale dependency.

**PO → Receipt chain:** PO line `unit_cost` = `procurement_items.unit_cost_arrival` (KES). Receipt posts that to ledger and then `updateProductCost()`.

---

## 11. UI Specification

### 11.1 Folder index (`procurement/folders/index`)

- Show `title` (display name) prominently; `folder_number` as secondary reference.
- Filter by status, supplier, purchase type.
- KPI cards: quotation drafts, approved, in transit.

### 11.2 Create folder (`create`)

- Supplier select (required) — on change, AJAX fills currency + purchase type (read-only or editable).
- Description (optional).
- CBM(R) — shown when import; required before order processing.
- Conversion(R) — shown when import; required before order processing.
- Hide freight/tax/margin fields from old UI.

### 11.3 Folder show — tabbed layout

| Tab | Phase | Content |
|-----|-------|---------|
| **Overview** | All | Supplier, purchase type, rates, status, cumulative summary (when calculated) |
| **Quotation** | B | Bulk product picker + line table (screenshot 1 columns) |
| **Order Processing** | C | Price/dimension entry form + full calculated table (screenshots 2–7) |
| **Workflow** | Post | Generate PO, in transit, close, GRN links |

### 11.4 Quotation tab — bulk add (Phase B)

**UI pattern:**
- Searchable product list with checkboxes.
- Checked row expands inline quantity input.
- "Add selected" submits array: `items[{product_id, quantity}]`.
- Table columns: N/S, Part Number (link to product), Product Name, Make, Vehicle, Unit, Quantity, Unit Price (`—`), Remove.

**Export toolbar:** Copy, CSV, Excel, PDF, Print (can use DataTables or dedicated export endpoints).

**Proceed button:** "Start Order Processing" → validates ≥1 line → status `order_draft`.

### 11.5 Order processing tab (Phase C)

**Local columns:** Qty, Unit Price (Ksh), Total (Ksh), Transport, Unit Cost (Arrival), MKT Wholesale, Margin, Margin %, Actual Total Cost, Expected Sales, Expected Margin.

**Import columns:** Qty, Unit Price ($), Total Cost/Item ($), Unit Price (Ksh), Qty in Packet, No. of Packets, Width, Length, Height, CBM, Total CBM/Item, Transport/Item, Unit Cost on Arrival, MKT Wholesale, Margin, Margin %, Actual Total Cost, Expected Sales, Expected Margin.

**Footer:** cumulative summary rows (§7.4).

**Actions:**
- Save prices (draft)
- Calculate (run service)
- Confirm Order (calculate + auto-approve)

### 11.6 Remove from UI

- "Run Cost Analysis" form (freight/tax/margin %).
- "Submit for Approval" button.
- Single-item add form (replace with bulk).

---

## 12. Routes & Controller Actions

Extend `ProcurementFolderController` (or rename views only):

| Method | Route | Action |
|--------|-------|--------|
| GET | `procurement/folders` | index |
| GET | `procurement/folders/create` | create |
| POST | `procurement/folders` | store |
| GET | `procurement/folders/{folder}` | show |
| GET | `procurement/folders/{folder}/edit` | edit |
| PATCH | `procurement/folders/{folder}` | update |
| DELETE | `procurement/folders/{folder}` | destroy |
| POST | `procurement/folders/{folder}/items/bulk` | **bulkAddItems** (new) |
| DELETE | `procurement/folders/{folder}/items/{item}` | destroyItem |
| POST | `procurement/folders/{folder}/proceed` | **proceedToOrder** (new) |
| PATCH | `procurement/folders/{folder}/items/prices` | **updatePrices** (new) |
| POST | `procurement/folders/{folder}/calculate` | **calculate** (replaces cost-analysis) |
| POST | `procurement/folders/{folder}/confirm` | **confirmOrder** (new, auto-approve) |
| GET | `procurement/folders/{folder}/export/{format}` | **export** (new) |
| POST | `procurement/folders/{folder}/generate-po` | generatePo (unchanged) |
| POST | `procurement/folders/{folder}/in-transit` | markInTransit |
| POST | `procurement/folders/{folder}/close` | close |

**Remove / deprecate routes:**
- `POST folders/{folder}/cost-analysis`
- `POST folders/{folder}/submit`

**New API for create form:**
- `GET suppliers/{supplier}/procurement-defaults` → `{currency, purchase_type}`

---

## 13. Form Requests

| Request | Purpose |
|---------|---------|
| `StoreProcurementFolderRequest` | supplier, description, cbm_rate, exchange_rate; validate import requires rates |
| `UpdateProcurementFolderRequest` | same; only when editable |
| `BulkProcurementItemsRequest` | `items[].product_id`, `items[].quantity` |
| `UpdateProcurementPricesRequest` | line price + transport/dimensions arrays |
| `ConfirmProcurementOrderRequest` | optional notes; ensures calculated state |

---

## 14. Permissions (unchanged)

| Permission | Use |
|------------|-----|
| `procurement.view` | index, show, export |
| `procurement.manage` | create, edit, bulk add, calculate, confirm |
| `procurement.approve` | **Deprecated for this flow** — confirm replaces manual approve |

Administrator-only module (per roadmap).

---

## 15. Migration & Backward Compatibility

### 15.1 Database migration (single or split)

1. Add new columns to `procurement_folders`, `procurement_items`, `suppliers`.
2. Extend status enum (MySQL: alter or migrate to string).
3. Data migration script:
   - `import_type` containing "local" → `purchase_type = local`
   - else → `import`
   - `draft` → `quotation_draft`
   - `cost_analysis` → `order_draft`
   - `pending_approval` → `order_draft` (or recalculate if possible)
4. Map old item columns to new where feasible.

### 15.2 Code removal after migration

- `CostAnalysisService` — delete after replacement tested.
- `RunCostAnalysisRequest` — delete.
- Approval submit path in controller — remove.

### 15.3 Seeder update

- `ProcurementSeeder` — create one local quotation draft + one import order draft using screenshot-like numbers for QA.

---

## 16. Implementation Order (Build Checklist)

Use this sequence when building:

- [ ] **M1.** Migration: suppliers.purchase_type, folder/item new columns, status enum
- [ ] **M2.** Model updates: fillable, casts, relationships, status helpers (`canBulkAdd`, `canProcessOrder`, `canConfirm`)
- [ ] **M3.** Supplier defaults endpoint + create form auto-fill
- [ ] **M4.** Folder create/store with title generation
- [ ] **M5.** Bulk add items endpoint + Quotation tab UI
- [ ] **M6.** `LocalOrderCalculator` + unit tests with screenshot row 1 data
- [ ] **M7.** `ImportOrderCalculator` + unit tests with screenshot ALKHAIR row 1 data
- [ ] **M8.** `ProcurementCalculationService` + folder cumulative totals
- [ ] **M9.** Order Processing tab UI (local + import column sets)
- [ ] **M10.** Confirm order → auto-approve + PO generation uses `unit_cost_arrival`
- [ ] **M11.** Export (CSV/PDF) for quotation draft
- [ ] **M12.** Remove old cost analysis / approval submit UI
- [ ] **M13.** Update `ProcurementSeeder`, `ProcurementReportQuery`, docs/ERD
- [ ] **M14.** Manual smoke test full flow: create → bulk add → process → confirm → PO → GRN → product cost updated on receipt only

---

## 17. Test Scenarios (Acceptance Criteria)

### Local — MUTYSIA sample (3 lines minimum)

| Part | Qty | Unit Ksh | Transport | Wholesale | Expected Margin |
|------|-----|----------|-----------|-----------|-------------------|
| Row1 | 22 | 160 | 0 | 200 | 880 |
| Row2 | 5 | 300 | 0 | 350 | 250 |
| Row3 | 3 | 800 | 0 | 900 | 300 |

**Folder totals:** Actual 6,020 | Sales 7,350 | Margin 1,330

### Import — ALKHAIR sample (row 1)

- Conversion R = 31.5, CBM R ≈ 55033 (derived from screenshot)
- Unit $16.5375, Qty 300, packets 300, CBM/packet 0.00107
- Expected arrival 579.84, margin 170.16, folder margin 353,591.41

### Policy tests

- [ ] Confirm order does **not** change `products.cost_price`
- [ ] GRN **does** change `products.cost_price` to receipt unit cost
- [ ] PO line cost equals `unit_cost_arrival`
- [ ] MKT Wholesale on order equals product `min_selling_price` at calc time
- [ ] Bulk add rejects duplicate products
- [ ] Import confirm blocked if CBM(R) or Conversion(R) missing

---

## 18. Open Items (Deferred)

| Item | Decision |
|------|----------|
| Synthetic Approval record on auto-confirm | Defer — add only if audit requires it |
| Update product `max_selling_price` on receipt | Keep current behaviour (only fill if empty) |
| Live exchange rate API | Out of scope — manual Conversion(R) entry |
| Quotation PDF branding | Match existing module PDF style when implementing export |

---

## 19. Reference: Legacy Screenshots Mapping

| Screenshot | Phase | View |
|------------|-------|------|
| Quotation List (no prices) | B | Quotation tab |
| Quotation Draft (local, partial) | C | Order Processing — local |
| Quotation Draft (local, margins) | C | Order Processing — local footer |
| Quotation Draft (import, prices) | C | Order Processing — import cols 1 |
| Quotation Draft (import, CBM) | C | Order Processing — import cols 2 |
| Summary labels | C | Footer |
| Summary values | C | Footer totals |

---

## 20. Document Maintenance

When implementation deviates from this spec, update this file first, then code. Link from `docs/build-roadmap.md` M13 section to this document.

**Related files:**
- `app/Models/ProcurementFolder.php`
- `app/Models/ProcurementItem.php`
- `app/Services/ProcurementService.php`
- `app/Services/GoodsReceiptService.php`
- `docs/database-erd.md` (update after migration)

---

*End of specification.*
