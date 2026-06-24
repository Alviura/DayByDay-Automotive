{{-- Sidebar navigation — single source for module grouping and links --}}
<nav class="flex-1 pb-6">

    <p class="sb-section">Main</p>
    <a href="{{ route('dashboard') }}" class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-gauge-high w-4 text-center text-orange-400"></i> Dashboard
    </a>

    @canany(['approvals.act', 'inventory.view', 'transfer_requests.view', 'transfers.view', 'sales.view', 'sales.hold', 'sales.create', 'customer_accounts.view', 'customer_invoices.view', 'returns.view'])
        <p class="sb-section">Operations</p>
        @can('approvals.act')
            <a href="{{ $navBadges['approvals']['url'] ?? route('approvals.index') }}" class="sb-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check w-4 text-center text-sky-400"></i>
                Approvals
                <x-nav-badge :badge="$navBadges['approvals'] ?? null" />
            </a>
        @endcan
        @can('inventory.view')
            <a href="{{ $navBadges['inventory']['url'] ?? route('inventory.index') }}" class="sb-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked w-4 text-center text-violet-400"></i> Inventory
                <x-nav-badge :badge="$navBadges['inventory'] ?? null" />
            </a>
        @endcan
        @can('transfer_requests.view')
            <a href="{{ $navBadges['transfer_requests']['url'] ?? route('transfer-requests.index') }}" class="sb-link {{ request()->routeIs('transfer-requests.*') ? 'active' : '' }}">
                <i class="fas fa-inbox w-4 text-center text-teal-400"></i> Transfer Requests
                <x-nav-badge :badge="$navBadges['transfer_requests'] ?? null" />
            </a>
        @endcan
        @can('transfers.view')
            <a href="{{ $navBadges['stock_transfers']['url'] ?? route('stock-transfers.index') }}" class="sb-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}">
                <i class="fas fa-right-left w-4 text-center text-teal-400"></i> Stock Transfers
                <x-nav-badge :badge="$navBadges['stock_transfers'] ?? null" />
            </a>
        @endcan
        @can('sales.view')
            <a href="{{ route('sales.index') }}" class="sb-link {{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}">
                <i class="fas fa-receipt w-4 text-center text-orange-300"></i> Sales History
            </a>
        @endcan
        @can('sales.hold')
            <a href="{{ route('sales.order') }}" class="sb-link {{ request()->routeIs('sales.order') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list w-4 text-center text-orange-300"></i> Order Entry
            </a>
        @endcan
        @can('sales.create')
            <a href="{{ $navBadges['cash_desk']['url'] ?? route('sales.desk') }}" class="sb-link {{ request()->routeIs('sales.desk', 'sales.desk.checkout', 'receipts.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register w-4 text-center text-orange-400"></i> Cash Desk
                <x-nav-badge :badge="$navBadges['cash_desk'] ?? null" />
            </a>
        @endcan
        @can('customer_accounts.view')
            <a href="{{ route('customer-accounts.index') }}" class="sb-link {{ request()->routeIs('customer-accounts.*') ? 'active' : '' }}">
                <i class="fas fa-bus w-4 text-center text-amber-300"></i> Fleet Accounts
            </a>
        @endcan
        @can('customer_invoices.view')
            <a href="{{ route('customer-invoices.index') }}" class="sb-link {{ request()->routeIs('customer-invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar w-4 text-center text-amber-400"></i> Customer Invoices
            </a>
        @endcan
        @can('returns.view')
            <a href="{{ $navBadges['customer_returns']['url'] ?? route('customer-returns.index') }}" class="sb-link {{ request()->routeIs('customer-returns.*') ? 'active' : '' }}">
                <i class="fas fa-rotate-left w-4 text-center text-rose-400"></i> Customer Returns
                <x-nav-badge :badge="$navBadges['customer_returns'] ?? null" />
            </a>
            <a href="{{ $navBadges['supplier_returns']['url'] ?? route('supplier-returns.index') }}" class="sb-link {{ request()->routeIs('supplier-returns.*') ? 'active' : '' }}">
                <i class="fas fa-truck-ramp-box w-4 text-center text-rose-300"></i> Supplier Returns
                <x-nav-badge :badge="$navBadges['supplier_returns'] ?? null" />
            </a>
        @endcan
    @endcanany

    @can('products.view')
        <p class="sb-section">Catalog</p>
        <a href="{{ route('products.index') }}" class="sb-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-car-side w-4 text-center text-orange-300"></i> Products
        </a>
    @endcan

    @canany(['suppliers.view', 'procurement.view', 'supplier_payments.view'])
        <p class="sb-section">Procurement</p>
        @can('suppliers.view')
            <a href="{{ route('suppliers.index') }}" class="sb-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="fas fa-truck w-4 text-center text-emerald-400"></i> Suppliers
            </a>
        @endcan
        @can('procurement.view')
            <a href="{{ $navBadges['quotation_series']['url'] ?? route('quotation-series.index') }}" class="sb-link {{ request()->routeIs('quotation-series.*') ? 'active' : '' }}">
                <i class="fas fa-folder-open w-4 text-center text-amber-400"></i> Quotation Series
                <x-nav-badge :badge="$navBadges['quotation_series'] ?? null" />
            </a>
            <a href="{{ $navBadges['purchase_orders']['url'] ?? route('purchase-orders.index') }}" class="sb-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice w-4 text-center text-amber-300"></i> Purchase Orders
                <x-nav-badge :badge="$navBadges['purchase_orders'] ?? null" />
            </a>
            <a href="{{ route('goods-receipts.index') }}" class="sb-link {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}">
                <i class="fas fa-truck-ramp-box w-4 text-center text-emerald-400"></i> Goods Receipts
            </a>
        @endcan
        @can('supplier_payments.view')
            <a href="{{ route('supplier-payments.index') }}" class="sb-link {{ request()->routeIs('supplier-payments.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar w-4 text-center text-lime-400"></i> Supplier Payments
            </a>
        @endcan
    @endcanany

    @can('finance.view')
        <p class="sb-section">Finance</p>
        <a href="{{ route('chart-of-accounts.index') }}" class="sb-link {{ request()->routeIs('chart-of-accounts.*') ? 'active' : '' }}">
            <i class="fas fa-sitemap w-4 text-center text-indigo-400"></i> Chart of Accounts
        </a>
        <a href="{{ $navBadges['journal_entries']['url'] ?? route('journal-entries.index') }}" class="sb-link {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
            <i class="fas fa-book w-4 text-center text-indigo-300"></i> Journal Entries
            <x-nav-badge :badge="$navBadges['journal_entries'] ?? null" />
        </a>
        <a href="{{ route('trial-balance.index') }}" class="sb-link {{ request()->routeIs('trial-balance.*') ? 'active' : '' }}">
            <i class="fas fa-scale-balanced w-4 text-center text-violet-400"></i> Trial Balance
        </a>
        <a href="{{ route('financial-statements.index') }}" class="sb-link {{ request()->routeIs('financial-statements.*') ? 'active' : '' }}">
            <i class="fas fa-chart-pie w-4 text-center text-violet-300"></i> Financial Statements
        </a>
        <a href="{{ route('tax-remittances.index') }}" class="sb-link {{ request()->routeIs('tax-remittances.*') ? 'active' : '' }}">
            <i class="fas fa-percent w-4 text-center text-emerald-400"></i> VAT Remittance
        </a>
        <a href="{{ route('bank-reconciliations.index') }}" class="sb-link {{ request()->routeIs('bank-reconciliations.*') ? 'active' : '' }}">
            <i class="fas fa-building-columns w-4 text-center text-sky-400"></i> Bank Reconciliation
        </a>
        <a href="{{ route('financial-periods.index') }}" class="sb-link {{ request()->routeIs('financial-periods.*') ? 'active' : '' }}">
            <i class="fas fa-lock w-4 text-center text-amber-400"></i> Period Close
        </a>
    @endcan

    @canany(['notifications.view', 'reports.view', 'audit.view'])
        <p class="sb-section">Insights</p>
        @can('notifications.view')
            <a href="{{ route('notifications.index') }}" class="sb-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell w-4 text-center text-orange-400"></i> Notifications
                @if (($unreadNotificationCount ?? 0) > 0)
                    <span class="ml-auto rounded-full bg-orange-500 px-1.5 py-0.5 text-[.58rem] font-bold text-white">
                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                    </span>
                @endif
            </a>
        @endcan
        @can('reports.view')
            <a href="{{ route('reports.index') }}" class="sb-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-4 text-center text-pink-400"></i> Reports
            </a>
        @endcan
        @can('audit.view')
            <a href="{{ route('audit-logs.index') }}" class="sb-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                <i class="fas fa-shield-halved w-4 text-center text-slate-400"></i> Audit Log
            </a>
        @endcan
    @endcanany

    @canany(['warehouses.view', 'shops.view', 'master-data.view'])
        <p class="sb-section">Master Data</p>
        @can('warehouses.view')
            <a href="{{ route('warehouses.index') }}" class="sb-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                <i class="fas fa-warehouse w-4 text-center text-orange-400"></i> Warehouses
            </a>
        @endcan
        @can('shops.view')
            <a href="{{ route('shops.index') }}" class="sb-link {{ request()->routeIs('shops.*') ? 'active' : '' }}">
                <i class="fas fa-store w-4 text-center text-orange-400"></i> Shops
            </a>
        @endcan
        @can('master-data.view')
            <a href="{{ route('vehicle-catalog.index') }}" class="sb-link {{ request()->routeIs('vehicle-catalog.*', 'vehicle-makes.*', 'vehicle-models.*') ? 'active' : '' }}">
                <i class="fas fa-car w-4 text-center text-orange-400"></i> Vehicle Catalog
            </a>
            <a href="{{ route('product-catalog.index') }}" class="sb-link {{ request()->routeIs('product-catalog.*', 'categories.*', 'product-names.*', 'units.*') ? 'active' : '' }}">
                <i class="fas fa-tags w-4 text-center text-orange-400"></i> Product Catalog
            </a>
        @endcan
    @endcanany

    @canany(['users.view', 'roles.view', 'employees.view', 'payroll.view'])
        <p class="sb-section">Administration</p>
        @can('employees.view')
            <a href="{{ route('employees.index') }}" class="sb-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="fas fa-id-badge w-4 text-center text-teal-400"></i> Employees
            </a>
        @endcan
        @can('payroll.view')
            <a href="{{ route('payroll.index') }}" class="sb-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                <i class="fas fa-money-check-dollar w-4 text-center text-lime-400"></i> Payroll
            </a>
        @endcan
        @can('users.view')
            <a href="{{ route('users.index') }}" class="sb-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users-gear w-4 text-center text-sky-400"></i> Users
            </a>
        @endcan
        @can('roles.view')
            <a href="{{ route('roles.index') }}" class="sb-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield w-4 text-center text-indigo-400"></i> Roles &amp; Permissions
            </a>
        @endcan
    @endcanany

    <p class="sb-section">Account</p>
    <a href="{{ route('profile.edit') }}" class="sb-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="fas fa-circle-user w-4 text-center text-cyan-400"></i> My Profile
    </a>
    <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
        @csrf
        <button type="submit" class="sb-link">
            <i class="fas fa-right-from-bracket w-4 text-center text-rose-400"></i> Sign Out
        </button>
    </form>

    <div class="h-6"></div>
</nav>
