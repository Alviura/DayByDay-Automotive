# Reports Module — Specification & Build Reference

**Project:** DayByDay Automotive  
**Module:** M17 — Reporting & Analytics  
**Status:** Foundation spec — **do not expand report catalogue until Phase R0 is complete**  
**Last updated:** 2026-06-20  
**Related docs:** [build-roadmap.md](./build-roadmap.md) (M17), [financial-module-groundwork.md](./financial-module-groundwork.md) (GL reconciliation), [database-erd.md](./database-erd.md)

---

## 1. Purpose

This document is the **single source of truth** when designing, building, and reviewing reports. It defines:

1. **What must be built first** (Phase R0 — reporting foundation) before adding new reports.
2. **How reports are architected** in this codebase (patterns, files, permissions).
3. **The full report catalogue** — existing and planned — with data sources, filters, and priority.
4. **Data contracts** — which statuses, dates, and tables each report must use so numbers reconcile.

**Design principle:** Reports are **read-only views** over operational data (and, where applicable, GL data). They do not mutate business state. Operational modules remain the source of truth; reports aggregate and export.

**Out of scope for this doc:** Dashboard KPIs (M20 — `DashboardService`), real-time analytics warehouses, external BI tools.

---

## 2. Current state (baseline)

### 2.1 Implemented reports

| Slug | Route | Query class | View | CSV export |
|------|-------|-------------|------|------------|
| `sales` | `reports.sales` | `SalesReportQuery` | `reports.sales` | Yes |
| `inventory` | `reports.inventory` | `InventoryReportQuery` | `reports.inventory` | Yes |
| `procurement` | `reports.procurement` | `ProcurementReportQuery` | `reports.procurement` | Yes |
| `transfers` | `reports.transfers` | `TransferReportQuery` | `reports.transfers` | Yes |
| `financial` | `reports.financial` | `FinancialReportQuery` | `reports.financial` | Yes |

**Controller:** `App\Http\Controllers\ReportController`  
**Index:** `resources/views/reports/index.blade.php` (flat card grid, no grouping)  
**Shared filter partial:** `resources/views/components/reports/filters.blade.php`  
**Filter DTO:** `App\Services\Reports\ReportFilters` (date range + optional `shop_id`)

### 2.2 Permissions

| Permission | Purpose |
|------------|---------|
| `reports.view` | Open reports index and all report pages |
| `reports.export` | Download CSV via `reports.export` |

**Role defaults (from `config/permissions.php`):**

- **Administrator** — all reports.
- **Shop Manager** — `reports.view` only (shop-scoped at controller level).
- **Warehouse Manager** — no reports by default (add if needed).

### 2.3 Scoping today

`ReportController::scopedShopId()` forces Shop Managers to their `shop_id`. Administrators may filter by shop on reports that support it (`sales`, `inventory`, `transfers`, `financial`). **Warehouse scoping does not exist yet.**

### 2.4 Known limitations in current queries

These are **data accuracy gaps** — Phase R0 must address before trusting expanded reporting:

| Issue | Location | Impact |
|-------|----------|--------|
| No `unit_cost` on `sale_items` | `sale_items` table | Gross margin reports impossible without joining `stock_ledger` at sale time |
| Returns use `updated_at` for period filter | `FinancialReportQuery` | Refund timing may not match `completed` / `approved_at` |
| Transfer report is request-centric | `TransferReportQuery` | Stock transfer dispatch/receive detail under-reported |
| Procurement report ignores shop | `ProcurementReportQuery` | OK for warehouse-centric procurement; document as global |
| Inventory valuation is point-in-time | `InventoryReportQuery` | No historical snapshot by date without ledger replay |
| Financial report mixes operational + inventory snapshot | `FinancialReportQuery` | Not reconciled to trial balance / journal entries |
| CSV export exports subset of on-screen data | Each `*ReportQuery::csvRows()` | e.g. sales CSV = recent 20 rows only, not full period |
| No report registry | — | Adding reports requires manual route/controller/view/index updates |
| Filter UI limited | `x-reports.filters` | No warehouse, supplier, preset ranges, or comparison period |

---

## 3. Phase R0 — Reporting foundation (build this first)

> **Rule:** No new report types (beyond bug fixes to existing five) until Phase R0 exit criteria are met.  
> This phase is the “something important before the report module” — infrastructure and data contracts, not individual report screens.

### 3.1 Goals

- One **registry** drives the reports index, routes metadata, permissions, and scoping rules.
- One **filter framework** supports all dimensions the business needs.
- One **scoping service** centralises shop/warehouse/admin rules (mirrors access services elsewhere).
- **Data contracts** documented and enforced in query classes.
- **Export behaviour** standardised (full dataset vs summary, CSV columns documented per report).
- Fix **critical data gaps** that would make new reports misleading.

### 3.2 Deliverables

| ID | Deliverable | Description | Files (planned) |
|----|-------------|-------------|-----------------|
| R0.1 | **Report registry** | Central config: slug, label, description, icon, category, query class, route name, scoping, exportable, status (`live` / `planned`) | `config/reports.php`, `App\Support\ReportRegistry` |
| R0.2 | **Extended filters** | `ReportFilters` with: `from`, `to`, `shopId`, `warehouseId`, `supplierId`, `preset` (today, 7d, 30d, mtd, ytd), `comparePrevious` (bool) | `ReportFilters.php`, `x-reports.filters` |
| R0.3 | **Report scope service** | `ReportScopeService::forUser($user)` → allowed shop IDs, warehouse IDs, force-global flag | `App\Services\Reports\ReportScopeService` |
| R0.4 | **Query contract** | Interface `ReportQuery` with `run(ReportFilters): array` and `csvRows(ReportFilters): Collection` + optional `csvHeaders()` | `App\Contracts\ReportQuery` |
| R0.5 | **Export standard** | Document and implement: filename pattern, UTF-8 BOM for Excel, max row limits, streaming for large sets | `ReportController::export`, per-query `csvRows` |
| R0.6 | **Index UI grouping** | Reports index grouped by category from registry (Sales, Inventory, Procurement, …) | `reports/index.blade.php` |
| R0.7 | **Data contracts doc in code** | PHP enum or config `config/report-data-contracts.php` mirroring §5 of this doc | `config/report-data-contracts.php` |
| R0.8 | **Sale line cost capture** | Persist `unit_cost` on `sale_items` at checkout (from `stock_ledger` / average cost) for margin reports | migration, `SaleService`, `SaleItem` |
| R0.9 | **Return date field** | Use `completed_at` or `approved_at` for return reports (add column if missing) | migration, `ReturnService`, `FinancialReportQuery` |
| R0.10 | **CSV parity** | Existing five reports: CSV exports full filtered dataset with documented columns | each `*ReportQuery` |

### 3.3 Phase R0 exit criteria

- [ ] `config/reports.php` lists all **live** and **planned** reports; index page reads from registry only.
- [ ] `ReportScopeService` used by `ReportController` (replace inline `scopedShopId()`).
- [ ] Filter partial supports date presets + warehouse dropdown where applicable.
- [ ] `config/report-data-contracts.php` committed and referenced in every query class header comment.
- [ ] `sale_items.unit_cost` populated on new sales; backfill strategy documented (optional seeder/command).
- [ ] Return reports use consistent completion timestamp.
- [ ] All five existing reports: CSV matches filter scope (not just “recent 20”).
- [ ] Unit or feature tests for at least one query per category (sales, inventory).

### 3.4 Registry shape (draft)

```php
// config/reports.php — illustrative
return [
    'categories' => [
        'sales' => ['label' => 'Sales & POS', 'icon' => 'fa-cash-register'],
        'inventory' => ['label' => 'Inventory', 'icon' => 'fa-boxes-stacked'],
        // ...
    ],
    'reports' => [
        'sales' => [
            'label' => 'Sales Summary',
            'description' => 'Revenue, tickets, top sellers',
            'category' => 'sales',
            'query' => \App\Services\Reports\SalesReportQuery::class,
            'route' => 'reports.sales',
            'status' => 'live',
            'scopes' => ['shop'],
            'export' => true,
        ],
        // planned reports: status => 'planned', route => null
    ],
];
```

### 3.5 Architecture (target)

```
┌──────────────────┐     ┌─────────────────────┐     ┌────────────────────┐
│ reports.index    │────▶│ ReportRegistry      │────▶│ config/reports.php │
│ (grouped cards)  │     │ (metadata)          │     └────────────────────┘
└──────────────────┘     └─────────────────────┘
         │
         ▼
┌──────────────────┐     ┌─────────────────────┐     ┌────────────────────┐
│ ReportController │────▶│ ReportScopeService  │     │ ReportFilters      │
│ show + export    │     │ (shop/warehouse)    │     │ (from request)     │
└────────┬─────────┘     └─────────────────────┘     └────────────────────┘
         │
         ▼
┌──────────────────┐     ┌─────────────────────┐
│ *ReportQuery     │────▶│ Operational models  │
│ run() / csvRows()│     │ (+ GL when needed)  │
└──────────────────┘     └─────────────────────┘
         │
         ▼
┌──────────────────┐
│ reports/{slug}   │  + x-reports.filters + mi-page KPIs/tables
└──────────────────┘
```

**Do not** put business logic in Blade views. **Do not** duplicate aggregation SQL in controllers.

---

## 4. Data contracts (status & date rules)

Every report query **must** document which rows are included. Use these conventions unless a report explicitly documents an exception.

### 4.1 Sales (`sales`, `sale_items`, `payments`)

| Metric | Include when | Date column |
|--------|--------------|-------------|
| Revenue, tax, tickets | `sales.status = 'completed'` | `sales.sold_at` |
| Held orders | `sales.status = 'held'` | `sales.updated_at` |
| Reversed sales | `sales.status = 'reversed'` | `sales.reversed_at` or `updated_at` (define in R0.9) |
| Payment mix | Payments linked to completed sales | `sales.sold_at` |

### 4.2 Inventory (`stock_balances`, `stock_ledger`, `stock_adjustments`)

| Metric | Source | Notes |
|--------|--------|-------|
| On-hand qty / value | `stock_balances` | Point-in-time; `qty × average_cost` |
| Movements in period | `stock_ledger` | Filter `created_at`; group by `transaction_type` |
| Low stock | `stock_balances` + `products.reorder_level` | `quantity_on_hand <= reorder_level` |
| Adjustments | `stock_adjustments` | `status = 'approved'`; date = `approved_at` |

**Stock ledger transaction types** (from `StockLedger::TYPE_LABELS`):

`opening_balance`, `purchase_receipt`, `purchase_receipt_void`, `transfer_out`, `transfer_in`, `sale`, `customer_return`, `supplier_return`, `adjustment`

### 4.3 Procurement (`quotation_series`, `purchase_orders`, `goods_receipt_notes`, `supplier_payments`)

| Document | Primary date | Status notes |
|----------|--------------|--------------|
| Quotation series | `created_at` / `approved_at` | Exclude `cancelled` from open counts |
| Purchase order | `order_date` | `status` drives open vs closed |
| GRN | `received_at` | Posted vs voided |
| Supplier payment | `paid_at` or `created_at` | Voided payments excluded |

### 4.4 Transfers (`transfer_requests`, `stock_transfers`)

| Metric | Date column | Status |
|--------|-------------|--------|
| Request pipeline | `transfer_requests.created_at` | `submitted`, `accepted`, `rejected`, `fulfilled` |
| Dispatch | `stock_transfers.dispatched_at` | `dispatched`, `in_transit` |
| Receipt complete | `stock_transfers.received_at` | `received`, `closed` |

### 4.5 Returns (`returns`, `return_items`)

| Type | Completed when | Amount field |
|------|----------------|--------------|
| Customer | `status = 'completed'` | `refund_amount` |
| Supplier | `status = 'completed'` | cost-based (inventory) |

### 4.6 Fleet / AR (`customer_accounts`, `customer_invoices`, `customer_invoice_payments`)

| Metric | Rule |
|--------|------|
| Outstanding balance | `customer_accounts.balance` or sum open invoices |
| Invoice aging | `customer_invoices.due_date` vs today |
| Payments | `customer_invoice_payments` in period |

### 4.7 Finance / GL (`journal_entries`, `journal_lines`, `chart_of_accounts`)

| Report type | Source | Reconciliation |
|-------------|--------|----------------|
| Trial balance | Posted journal lines | Authoritative for GL |
| Operational financial summary | `sales`, `returns`, `payments` | Must footnote “operational — not GL” until reconciled |
| VAT | `sales.tax_total` vs `tax_remittances` | Period alignment required |

See [financial-module-groundwork.md](./financial-module-groundwork.md) §6.4 — operational reports and GL reports serve different purposes until explicit reconciliation reports are built.

### 4.8 Payroll (`payroll_periods`, `payroll_runs`, `payroll_lines`)

| Metric | Include when |
|--------|--------------|
| Payroll register | `payroll_periods.status` in (`locked`, `paid`) |
| Employer cost | Sum `payroll_lines` gross + employer contributions |

---

## 5. Report catalogue

### 5.1 Legend

| Column | Meaning |
|--------|---------|
| **Phase** | R0 = foundation only; R1 = first wave; R2 = second; R3 = advanced |
| **Status** | `live` / `planned` |
| **Scope** | `global`, `shop`, `warehouse`, `supplier` |

---

### 5.2 Sales & POS

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-S01 | **Sales Summary** | R0 | live | shop | `sales`, `sale_items` | Revenue, tax, tickets, daily trend, top products |
| R-S02 | Sales by Shop | R1 | planned | global | `sales` | Compare revenue & tickets across shops |
| R-S03 | Sales by Cashier | R1 | planned | shop | `sales`, `users` | Revenue per attendant |
| R-S04 | Payment Method Mix | R1 | planned | shop | `payments`, `sales` | Cash, M-Pesa, card, etc. (extend financial) |
| R-S05 | On-Account vs Cash | R1 | planned | shop | `sales` | Credit vs immediate payment |
| R-S06 | Held / Abandoned Orders | R1 | planned | shop | `sales` | Held count, aging, value at risk |
| R-S07 | Reversed Sales | R1 | planned | shop | `sales` | Reversals in period, by cashier |
| R-S08 | Gross Margin | R2 | planned | shop | `sale_items` (+ `unit_cost` R0.8) | Revenue − COGS, margin % |
| R-S09 | Sales by Category | R2 | planned | shop | `sale_items`, `products`, `categories` | Category revenue mix |
| R-S10 | Hourly / Day-of-Week | R2 | planned | shop | `sales` | Peak trading patterns |
| R-S11 | Top Customers / Fleet | R3 | planned | shop | `sales`, `customer_accounts` | Repeat buyers, AR sales |

---

### 5.3 Inventory

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-I01 | **Inventory Summary** | R0 | live | shop/wh | `stock_balances` | Valuation by location, low stock |
| R-I02 | Stock Movements | R1 | planned | shop/wh | `stock_ledger` | Entries by `transaction_type` |
| R-I03 | Stock Ledger Detail | R1 | planned | shop/wh | `stock_ledger` | Full exportable journal |
| R-I04 | Stock Adjustments | R1 | planned | shop/wh | `stock_adjustments` | By reason, approver, qty |
| R-I05 | Dead / Slow Moving | R2 | planned | global | `stock_ledger`, `sales` | No movement in N days |
| R-I06 | Zero Stock | R2 | planned | global | `stock_balances`, `sales` | Out of stock with demand |
| R-I07 | Reorder Worksheet | R1 | planned | global | `stock_balances`, `products` | Below reorder level |
| R-I08 | Inter-Location Matrix | R2 | planned | global | `stock_balances` | Same SKU across sites |
| R-I09 | Damage & Shrinkage | R2 | planned | global | GRN items, transfer items | Damaged qty summary |
| R-I10 | Inventory Snapshot History | R3 | planned | global | `stock_ledger` (reconstructed) | Month-end valuation trend |

---

### 5.4 Procurement

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-P01 | **Procurement Summary** | R0 | live | global | `quotation_series`, `purchase_orders`, `goods_receipt_notes` | Series status, PO value, GRN count |
| R-P02 | Supplier Performance | R1 | planned | global | suppliers, POs, GRNs, returns | Fill rate, lead time, return rate |
| R-P03 | Open Purchase Orders | R1 | planned | global | `purchase_orders`, `purchase_order_items` | Unreceived lines |
| R-P04 | PO vs GRN Variance | R1 | planned | global | PO items, GRN items | Short/over receipt |
| R-P05 | Supplier Payables Aging | R1 | planned | global | `supplier_payments`, POs, GRNs | Outstanding AP |
| R-P06 | Supplier Payment Register | R1 | planned | global | `supplier_payments` | Payments & voids in period |
| R-P07 | Landed Cost Analysis | R2 | planned | global | `quotation_series`, `quotation_items` | Quoted vs actual |
| R-P08 | Procurement Lead Time | R2 | planned | global | series → PO → GRN dates | Days per stage |

---

### 5.5 Distribution & transfers

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-T01 | **Transfer Activity** | R0 | live | shop | `transfer_requests`, `stock_transfers` | Request status, in-transit (partial) |
| R-T02 | Stock Transfer Detail | R1 | planned | shop/wh | `stock_transfers`, items | Dispatched vs received, damaged |
| R-T03 | Transfer Fill Rate | R1 | planned | shop | `transfer_requests` | Accepted vs rejected |
| R-T04 | In-Transit Aging | R1 | planned | global | `stock_transfers` | Days since dispatch |
| R-T05 | Warehouse Dispatch Performance | R2 | planned | warehouse | `stock_transfers` | Lines/units dispatched |

---

### 5.6 Returns

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-R01 | Customer Returns Summary | R1 | planned | shop | `returns` (customer) | Refunds, reasons, products |
| R-R02 | Supplier Returns Summary | R1 | planned | warehouse | `returns` (supplier) | Qty returned to suppliers |
| R-R03 | Return Rate vs Sales | R2 | planned | shop | returns + sales | % returns by product |

---

### 5.7 Fleet & accounts receivable

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-A01 | AR Aging | R1 | planned | global | `customer_invoices`, accounts | 30/60/90+ buckets |
| R-A02 | Customer Statement | R1 | planned | account | invoices, payments | Opening, activity, closing |
| R-A03 | Overdue Invoices | R1 | planned | global | `customer_invoices` | Past due list |
| R-A04 | Credit Utilization | R2 | planned | global | `customer_accounts` | Limit vs balance |

---

### 5.8 Finance & GL

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-F01 | **Financial Summary** | R0 | live | shop | `sales`, `returns`, `payments` | Net revenue, refunds, payment mix |
| R-F02 | Trial Balance Export | R1 | planned | global | `journal_lines` | Debits = credits by period |
| R-F03 | General Ledger Detail | R1 | planned | global | `journal_lines`, COA | Account activity |
| R-F04 | Journal Entry Register | R1 | planned | global | `journal_entries` | Manual journals, approval status |
| R-F05 | VAT Remittance Summary | R2 | planned | global | sales tax, `tax_remittances` | Collected vs filed |
| R-F06 | Bank Reconciliation Status | R2 | planned | global | `bank_reconciliations` | Open vs complete |
| R-F07 | Period Close Summary | R2 | planned | global | `financial_periods` | Closed periods, draft entries |
| R-F08 | P&L Summary | R3 | planned | global | GL accounts | Revenue, COGS, expenses |
| R-F09 | Balance Sheet Snapshot | R3 | planned | global | GL | Assets, liabilities, equity |
| R-F10 | Operational vs GL Reconciliation | R3 | planned | global | sales + journals | Variance explanation |

---

### 5.9 Payroll & HR

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-H01 | Payroll Register | R2 | planned | global | `payroll_runs`, `payroll_lines` | Gross, deductions, net |
| R-H02 | Payroll Cost by Location | R2 | planned | global | payroll + employees | Cost per shop/warehouse |
| R-H03 | Headcount | R2 | planned | global | `employees` | Active staff by role/site |

---

### 5.10 Governance & system

| ID | Report | Phase | Status | Scope | Primary tables | Key metrics |
|----|--------|-------|--------|-------|----------------|-------------|
| R-G01 | Approval Pipeline Aging | R2 | planned | global | `approvals` | Pending by module & age |
| R-G02 | Approval Turnaround | R3 | planned | global | `approval_actions` | Avg time to decision |
| R-G03 | Audit Log Summary | R2 | planned | global | `audit_logs` | Actions by user/module |
| R-G04 | User Login Activity | R2 | planned | global | `user_logins` | Logins per user |
| R-G05 | Product Master Export | R1 | planned | global | `products` | SKU list with attributes |

---

## 6. Implementation phases (after R0)

### Phase R1 — Operations depth (highest business value)

**Goal:** Day-to-day decisions for shop managers, warehouse managers, and procurement.

| Priority | Reports |
|----------|---------|
| 1 | R-S02, R-S03, R-S04, R-S06, R-S07 |
| 2 | R-I02, R-I03, R-I04, R-I07 |
| 3 | R-P02, R-P03, R-P04, R-P05, R-P06 |
| 4 | R-T02, R-T03, R-T04 |
| 5 | R-R01, R-R02 |
| 6 | R-A01, R-A03 |
| 7 | R-G05 |

**UI:** Grouped index; each report uses standard filter bar + KPI row + 1–2 tables.

### Phase R2 — Management & finance ops

R-S08, R-S09, R-I05–R-I09, R-P07–R-P08, R-F02–R-F07, R-H01–R-H03, R-G01, R-G03, R-G04

### Phase R3 — Advanced analytics & GL reconciliation

R-S10, R-S11, R-I10, R-A02, R-A04, R-F08–R-F10, R-G02

---

## 7. Per-report specification template

When implementing any report, copy this template into the PR description or a comment block at the top of the query class.

```markdown
### Report: {ID} — {Name}

**Slug:** `{slug}`
**Phase:** R{n}
**Owner query:** `{QueryClass}`

#### Business question
What decision does this report support?

#### Audience & scope
- Roles: …
- Scoping: shop / warehouse / global

#### Filters
| Filter | Required | Default |
|--------|----------|---------|
| date_from / date_to | yes | last 30 days |
| shop_id | … | … |

#### Metrics (summary KPIs)
| KPI | Formula / source |
|-----|------------------|
| … | … |

#### Tables / charts
| Section | Columns | Sort | Limit |
|---------|---------|------|-------|
| … | … | … | … |

#### Data contract
- Status filter: …
- Date column: …
- Exclusions: …

#### CSV export
- Filename: `{slug}-report-{from}-{to}.csv`
- Columns: …
- Row set: full filtered / summary only

#### Permissions
- View: `reports.view`
- Export: `reports.export`

#### Tests
- [ ] Empty period returns zero KPIs
- [ ] Shop scope restricts rows
- [ ] Totals match manual SQL spot check
```

---

## 8. Example — R-S02 Sales by Shop (planned)

**Business question:** Which shops are performing vs target this period?

**Query sketch:**

```sql
SELECT shops.name,
       COUNT(sales.id) AS transactions,
       SUM(sales.total) AS revenue,
       AVG(sales.total) AS avg_ticket
FROM sales
JOIN shops ON shops.id = sales.shop_id
WHERE sales.status = 'completed'
  AND sales.sold_at BETWEEN :from AND :to
GROUP BY shops.id, shops.name
ORDER BY revenue DESC
```

**Scoping:** Shop Manager sees only their shop (single row). Administrator sees all.

**CSV:** One row per shop; columns: Shop, Code, Transactions, Revenue, Avg Ticket, Tax.

---

## 9. Export & performance guidelines

| Topic | Rule |
|-------|------|
| Default period | Last 30 days if not specified |
| Max CSV rows | 50,000 (configurable); warn in UI if truncated |
| Streaming | Use `response()->streamDownload()` (already in place) |
| Heavy joins | Eager-load relationships; avoid N+1 in recent lists |
| Indexes | Ensure date columns used in WHERE are indexed (`sold_at`, `received_at`, etc.) |
| PDF | Phase R2+ — defer until CSV parity achieved |
| Caching | Defer until R1 complete; optional Redis per report+filter hash |

---

## 10. Relationship to dashboard (M20)

| Dashboard widget | Report overlap | Action |
|------------------|----------------|--------|
| Daily sales KPI | R-S01 | Share query method or trait |
| Low stock list | R-I01 / R-I07 | Dashboard = top N; report = full list |
| Pending approvals | Not a report | Stays in dashboard + approvals module |
| Revenue chart | R-S01 daily breakdown | Extract `SalesReportQuery::dailySeries()` |

**Rule:** Dashboard may call shared query methods from report query classes; do not duplicate SQL.

---

## 11. Definition of done (per report)

- [ ] Entry in `config/reports.php` with `status: live`
- [ ] Query class implements `ReportQuery` contract
- [ ] Data contract documented (§4 + class docblock)
- [ ] Route + controller action + permission middleware
- [ ] Blade view uses `mi-page` patterns + `x-reports.filters`
- [ ] CSV export with full filtered dataset
- [ ] Shop/warehouse scoping via `ReportScopeService`
- [ ] Empty state when no data
- [ ] Spot-checked against raw SQL or known seed data
- [ ] Listed on grouped reports index

---

## 12. Open questions (resolve during R0)

| # | Question | Default recommendation |
|---|----------|------------------------|
| Q1 | Split `reports.export` per category? | Keep single permission until a customer requires segregation |
| Q2 | Warehouse Manager gets which reports? | Inventory + transfers + procurement; no sales |
| Q3 | Backfill `sale_items.unit_cost` for historical sales? | Optional artisan command; margin report notes “from date X only” |
| Q4 | PDF export priority? | After R1 CSV parity |
| Q5 | Fiscal year start month? | Config `config/company.php` when adding YTD presets |
| Q6 | Multi-currency in reports? | Show document currency; no FX conversion in R1 |

---

## 13. Build order checklist (for agents & developers)

Use this order every time reporting work is scheduled:

```
1. Read this doc (reports-module-spec.md)
2. Confirm Phase R0 exit criteria — if not met, implement R0 items first
3. Pick report ID from catalogue (§5) for target phase
4. Fill per-report template (§7)
5. Add registry entry (planned → live when done)
6. Implement query → controller → view → export
7. Verify data contract (§4) with sample data
8. Update §5 status column in this doc (live/planned)
```

---

## 14. Document history

| Date | Change |
|------|--------|
| 2026-06-20 | Initial comprehensive spec: R0 foundation, catalogue, data contracts, phases |
