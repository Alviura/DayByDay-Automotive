<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111113">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'DayByDay Automotive') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ================================================================
           DayByDay Automotive — Design System
           Palette: Near-black charcoal (#111113) + Vivid Orange (#f97316)
           ================================================================ */
        :root {
            --sb-w: 260px;
            --sb-bg: #111113;
            --topbar-bg: #18181b;
            --accent: #f97316;
            --accent-dk: #c2410c;
            --page-bg: #f3f4f6;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: var(--page-bg);
            -webkit-font-smoothing: antialiased;
        }

        /* === Sidebar === */
        #ddb-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 50;
            width: var(--sb-w);
            background: var(--sb-bg);
            display: flex; flex-direction: column;
            overflow-y: auto; overflow-x: hidden;
            /* Transform-based show/hide — controlled by Alpine :class binding */
            transition: transform .28s cubic-bezier(.4, 0, .2, 1);
            will-change: transform;
        }
        #ddb-sidebar.sidebar-hidden { transform: translateX(-100%); }
        #ddb-sidebar.sidebar-visible { transform: translateX(0); }
        #ddb-sidebar::-webkit-scrollbar { width: 3px; }
        #ddb-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

        /* Sidebar brand header */
        .sb-brand {
            flex-shrink: 0; padding: 1rem 1.15rem;
            background: linear-gradient(135deg, #1c1c1f 0%, #232326 100%);
            border-bottom: 1px solid rgba(249,115,22,.22);
        }

        /* Sidebar nav links */
        .sb-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem 1.15rem;
            border-left: 3px solid transparent;
            color: #71717a; font-size: .81rem;
            text-decoration: none;
            transition: background .15s, color .15s, border-color .15s;
            white-space: nowrap;
            /* also used on <button> */
            background: none; border-top: none; border-right: none; border-bottom: none;
            width: 100%; text-align: left; cursor: pointer;
        }
        .sb-link:hover {
            background: rgba(255,255,255,.05);
            color: #e4e4e7;
            border-left-color: rgba(249,115,22,.3);
        }
        .sb-link.active {
            background: linear-gradient(90deg, rgba(249,115,22,.22), rgba(249,115,22,.03));
            color: #fff;
            border-left-color: var(--accent);
        }
        .sb-link.active i { color: var(--accent) !important; }

        .sb-section {
            padding: .85rem 1.15rem .22rem;
            font-size: .58rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .12em; color: #3f3f46;
        }

        /* === Topbar === */
        #ddb-topbar {
            background: var(--topbar-bg);
            border-bottom: 2px solid var(--accent);
            box-shadow: 0 4px 20px rgba(0,0,0,.45);
            height: 62px; flex-shrink: 0;
            display: flex; align-items: center;
            position: sticky; top: 0; z-index: 40;
        }

        /* === Main content area (global spacing for all pages) === */
        .ddb-content {
            width: 100%;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            padding: 1.25rem 0.75rem;
        }
        @media (min-width: 640px)  { .ddb-content { padding-left: 1rem;  padding-right: 1rem;  } }
        @media (min-width: 1024px) { .ddb-content { padding-left: 1.25rem; padding-right: 1.25rem; } }

        /* === Main content wrapper === */
        #ddb-main {
            min-height: 100vh; display: flex; flex-direction: column;
            transition: padding-left .28s cubic-bezier(.4, 0, .2, 1);
        }

        /* === Dropdown animations === */
        .ddb-dropdown-enter { opacity: 0; transform: scale(.95) translateY(-4px); }
        .ddb-dropdown-enter-to { opacity: 1; transform: scale(1) translateY(0); }

        /* === Scrollbar global === */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d4d4d8; border-radius: 4px; }

        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>

{{-- ================================================================
     Alpine root — sidebar state fully managed here (no responsive
     class prefixes in :class because Alpine can't resolve media queries).
     Logic: on desktop (isLg) → collapsed flag; on mobile → mobileOpen.
     The computed `open` drives a single class on the sidebar element.
     Main padding-left is set via :style to avoid responsive-prefix issues.
     ================================================================ --}}
<body class="text-zinc-800 antialiased"
      x-data="{
          collapsed: false,
          mobileOpen: false,
          isLg: window.innerWidth >= 1024,

          get open() { return this.isLg ? !this.collapsed : this.mobileOpen; },

          init() {
              this.collapsed = localStorage.getItem('ddb_sb') === '1';
              this.$watch('collapsed', v => localStorage.setItem('ddb_sb', v ? '1' : '0'));

              /* Track viewport changes */
              const mq = window.matchMedia('(min-width:1024px)');
              mq.addEventListener('change', e => {
                  this.isLg = e.matches;
                  if (e.matches) this.mobileOpen = false;
              });
          },

          toggleSidebar() {
              if (this.isLg) this.collapsed = !this.collapsed;
              else this.mobileOpen = !this.mobileOpen;
          }
      }">

    {{-- ==================== SIDEBAR ==================== --}}
    <aside id="ddb-sidebar"
           :class="open ? 'sidebar-visible' : 'sidebar-hidden'"
           aria-label="Main navigation">

        {{-- Brand --}}
        <div class="sb-brand">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 no-underline">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl text-lg text-white"
                          style="background:linear-gradient(135deg,var(--accent),var(--accent-dk));
                                 box-shadow:0 4px 16px rgba(249,115,22,.45)">
                        <i class="fas fa-gauge-high"></i>
                    </span>
                    <div class="leading-tight">
                        <div class="text-[.94rem] font-extrabold tracking-tight text-white">DayByDay</div>
                        <div class="text-[.58rem] font-medium uppercase tracking-[.14em] text-white/45">Automotive</div>
                    </div>
                </a>

                {{-- Collapse (desktop) / Close (mobile) --}}
                <button @click="isLg ? (collapsed = true) : (mobileOpen = false)"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-white/35 transition hover:bg-white/10 hover:text-white/80"
                        title="Close">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
            </div>
        </div>

        {{-- User card --}}
        @php
            $ddbUser     = auth()->user();
            $ddbInitials = collect(explode(' ', trim($ddbUser->name)))->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
            $ddbRole     = $ddbUser->getRoleNames()->first() ?? 'Staff';
        @endphp
        <div class="flex flex-shrink-0 items-center gap-3 border-b border-white/[.06] px-4 py-3"
             style="background:rgba(255,255,255,.03)">
            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white ring-2 ring-white/10"
                  style="background:linear-gradient(135deg,#f97316,#dc2626)">
                {{ $ddbInitials ?: 'U' }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-zinc-100">{{ $ddbUser->name }}</p>
                <p class="text-[.68rem] text-zinc-500">{{ $ddbRole }}</p>
            </div>
            <span class="flex items-center gap-1 rounded-full px-2 py-0.5 text-[.58rem] font-bold"
                  style="background:rgba(34,197,94,.14);color:#4ade80">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background:#4ade80"></span>
                Live
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 pb-6">
            <p class="sb-section">Main</p>
            <a href="{{ route('dashboard') }}" class="sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge-high w-4 text-center text-orange-400"></i> Dashboard
            </a>

            <p class="sb-section">Operations</p>
            @can('approvals.act')
                <a href="{{ route('approvals.index') }}" class="sb-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-4 text-center text-sky-400"></i>
                    Approvals
                    @if (($pendingApprovalCount ?? 0) > 0)
                        <span class="ml-auto rounded-full bg-orange-500 px-1.5 py-0.5 text-[.58rem] font-bold text-white">
                            {{ $pendingApprovalCount > 99 ? '99+' : $pendingApprovalCount }}
                        </span>
                    @endif
                </a>
            @endcan
            @can('inventory.view')
                <a href="{{ route('inventory.index') }}" class="sb-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes-stacked w-4 text-center text-violet-400"></i> Inventory
                </a>
            @endcan
            @can('transfers.view')
                <a href="{{ route('transfer-requests.index') }}" class="sb-link {{ request()->routeIs('transfer-requests.*') ? 'active' : '' }}">
                    <i class="fas fa-right-left w-4 text-center text-teal-400"></i> Transfer Requests
                </a>
                <a href="{{ route('stock-transfers.index') }}" class="sb-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck w-4 text-center text-teal-300"></i> Stock Transfers
                </a>
            @endcan
            @can('sales.view')
                <a href="{{ route('sales.index') }}" class="sb-link {{ request()->routeIs('sales.index', 'sales.show') ? 'active' : '' }}">
                    <i class="fas fa-receipt w-4 text-center text-orange-300"></i> Sales
                </a>
            @endcan
            @can('sales.create')
                <a href="{{ route('sales.pos') }}" class="sb-link {{ request()->routeIs('sales.pos', 'receipts.*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register w-4 text-center text-orange-400"></i> Point of Sale
                </a>
            @endcan
            @can('returns.view')
                <a href="{{ route('customer-returns.index') }}" class="sb-link {{ request()->routeIs('customer-returns.*') ? 'active' : '' }}">
                    <i class="fas fa-rotate-left w-4 text-center text-rose-400"></i> Customer Returns
                </a>
                <a href="{{ route('supplier-returns.index') }}" class="sb-link {{ request()->routeIs('supplier-returns.*') ? 'active' : '' }}">
                    <i class="fas fa-truck-ramp-box w-4 text-center text-rose-300"></i> Supplier Returns
                </a>
            @endcan

            <p class="sb-section">Catalog &amp; Procurement</p>
            @can('products.view')
                <a href="{{ route('products.index') }}" class="sb-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="fas fa-car-side w-4 text-center text-orange-300"></i> Products
                </a>
            @endcan
            @can('suppliers.view')
                <a href="{{ route('suppliers.index') }}" class="sb-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                    <i class="fas fa-truck w-4 text-center text-emerald-400"></i> Suppliers
                </a>
            @endcan
            @can('procurement.view')
                <a href="{{ route('procurement.folders.index') }}" class="sb-link {{ request()->routeIs('procurement.*', 'purchase-orders.*', 'goods-receipts.*') ? 'active' : '' }}">
                    <i class="fas fa-folder-open w-4 text-center text-amber-400"></i> Procurement Folders
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="sb-link {{ request()->routeIs('purchase-orders.*', 'goods-receipts.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar w-4 text-center text-amber-300"></i> Purchase Orders
                </a>
            @endcan

            <p class="sb-section">Insights</p>
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

            @canany(['warehouses.view', 'shops.view', 'suppliers.view', 'master-data.view'])
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
                @can('suppliers.view')
                    <a href="{{ route('suppliers.index') }}" class="sb-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck w-4 text-center text-orange-400"></i> Suppliers
                    </a>
                @endcan
                @can('master-data.view')
                    <a href="{{ route('vehicle-catalog.index') }}" class="sb-link {{ request()->routeIs('vehicle-catalog.*', 'vehicle-makes.*', 'vehicle-models.*') ? 'active' : '' }}">
                        <i class="fas fa-car-side w-4 text-center text-orange-400"></i> Vehicle Makes & Models
                    </a>
                    <a href="{{ route('product-catalog.index') }}" class="sb-link {{ request()->routeIs('product-catalog.*', 'categories.*', 'product-names.*', 'units.*') ? 'active' : '' }}">
                        <i class="fas fa-tags w-4 text-center text-orange-400"></i> Categories, Names & Units
                    </a>
                @endcan
            @endcanany

            @canany(['users.view', 'roles.view'])
                <p class="sb-section">Administration</p>
                @can('users.view')
                    <a href="{{ route('users.index') }}" class="sb-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-gear w-4 text-center text-sky-400"></i> Users
                    </a>
                @endcan
                @can('roles.view')
                    <a href="{{ route('roles.index') }}" class="sb-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="fas fa-user-shield w-4 text-center text-indigo-400"></i> Roles
                    </a>
                @endcan
            @endcanany

            <p class="sb-section">Account</p>
            <a href="{{ route('profile.edit') }}" class="sb-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-id-badge w-4 text-center text-cyan-400"></i> My Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                @csrf
                <button type="submit" class="sb-link">
                    <i class="fas fa-right-from-bracket w-4 text-center text-rose-400"></i> Sign Out
                </button>
            </form>
            <div class="h-6"></div>
        </nav>
    </aside>

    {{-- Mobile overlay — visible when sidebar is open on small screens --}}
    <div x-cloak
         x-show="mobileOpen && !isLg"
         @click="mobileOpen = false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    {{-- Floating reopen button — desktop, sidebar collapsed --}}
    <button x-cloak
            x-show="isLg && collapsed"
            @click="collapsed = false"
            class="fixed left-4 top-4 z-50 flex h-9 w-9 items-center justify-center rounded-xl text-white shadow-lg transition hover:scale-105 hover:brightness-110"
            style="background:var(--accent); box-shadow:0 4px 16px rgba(249,115,22,.5)"
            title="Open sidebar">
        <i class="fas fa-bars text-sm"></i>
    </button>

    {{-- ==================== MAIN ==================== --}}
    <div id="ddb-main"
         :style="isLg && !collapsed ? 'padding-left:260px' : 'padding-left:0'">

        {{-- Topbar --}}
        <header id="ddb-topbar">
            <div class="flex w-full items-center justify-between px-4">

                {{-- Left: hamburger + page title --}}
                <div class="flex items-center gap-3">
                    <button @click="toggleSidebar()"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white/65 transition hover:bg-white/10 hover:text-white">
                        <i class="fas fa-bars text-base"></i>
                    </button>
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-white">{{ $title ?? 'Dashboard' }}</p>
                        <p class="hidden text-[.65rem] text-white/40 sm:block">{{ now()->format('l, d M Y') }}</p>
                    </div>
                </div>

                {{-- Center search bar (desktop) --}}
                <div class="relative hidden flex-1 max-w-sm px-8 lg:block">
                    <i class="fas fa-magnifying-glass pointer-events-none absolute left-11 top-1/2 -translate-y-1/2 text-xs text-white/35"></i>
                    <input type="text"
                           placeholder="Search parts, suppliers, jobs…"
                           class="w-full rounded-lg border border-white/[.12] py-1.5 pl-9 pr-3 text-sm text-white/80 outline-none placeholder-white/30 transition focus:border-orange-500/50 focus:ring-0"
                           style="background:rgba(255,255,255,.08)">
                </div>

                {{-- Right: notifications + user --}}
                <div class="flex items-center gap-0.5">

                    {{-- Notifications --}}
                    @php $ddbUnread = $ddbUser->unreadNotifications()->count(); @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="relative flex h-9 w-9 items-center justify-center rounded-lg text-white/65 transition hover:bg-white/10 hover:text-white">
                            <i class="far fa-bell text-[1.05rem]"></i>
                            @if ($ddbUnread > 0)
                                <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full px-0.5 text-[.5rem] font-bold text-white ring-2"
                                      style="background:var(--accent);ring-color:#18181b">
                                    {{ $ddbUnread > 9 ? '9+' : $ddbUnread }}
                                </span>
                            @endif
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-80 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/[.08]"
                             style="transform-origin:top right">
                            <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
                                <p class="text-sm font-bold text-zinc-900">Notifications</p>
                                <span class="text-[.68rem] font-semibold" style="color:var(--accent)">{{ $ddbUnread }} new</span>
                            </div>
                            <div class="max-h-72 divide-y divide-zinc-50 overflow-y-auto">
                                @forelse ($ddbUser->notifications()->latest()->take(5)->get() as $note)
                                    <div class="flex items-start gap-3 px-4 py-3 transition hover:bg-stone-50">
                                        <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $note->read_at ? 'bg-zinc-100 text-zinc-400' : '' }}"
                                              style="{{ !$note->read_at ? 'background:rgba(249,115,22,.12);color:var(--accent)' : '' }}">
                                            <i class="fas fa-circle-info text-xs"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs leading-snug text-zinc-600">{{ \Illuminate\Support\Str::limit($note->data['message'] ?? 'Notification', 65) }}</p>
                                            <p class="mt-0.5 text-[.6rem] text-zinc-400">{{ $note->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-zinc-400">
                                        <i class="far fa-bell-slash mb-2 block text-2xl text-zinc-200"></i>
                                        <p class="text-xs">You're all caught up</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mx-1 hidden h-5 w-px sm:block" style="background:rgba(255,255,255,.12)"></div>

                    {{-- User dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-2 rounded-xl px-1.5 py-1 transition"
                                :class="open ? 'bg-white/10' : 'hover:bg-white/10'"
                                style="border:1px solid transparent"
                                :style="open ? 'border-color:rgba(255,255,255,.15)' : ''">
                            <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-xs font-bold text-white"
                                  style="background:linear-gradient(135deg,#f97316,#dc2626)">
                                {{ $ddbInitials ?: 'U' }}
                            </span>
                            <span class="hidden text-left leading-tight md:block">
                                <span class="block max-w-[110px] truncate text-xs font-semibold text-white">{{ $ddbUser->name }}</span>
                                <span class="block text-[.57rem] uppercase tracking-wide text-white/45">{{ $ddbRole }}</span>
                            </span>
                            <i class="fas fa-chevron-down hidden text-[.55rem] text-white/45 transition sm:inline"
                               :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-cloak x-show="open" @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl bg-white p-1.5 shadow-2xl ring-1 ring-black/[.08]"
                             style="transform-origin:top right">
                            <div class="mb-1.5 rounded-xl px-3.5 py-3 text-white"
                                 style="background:linear-gradient(135deg,#111113,#222226)">
                                <p class="text-sm font-bold">{{ $ddbUser->name }}</p>
                                <p class="mt-0.5 truncate text-[.67rem] text-white/60">{{ $ddbUser->email }}</p>
                                <span class="mt-1.5 inline-block rounded px-2 py-0.5 text-[.57rem] font-bold uppercase tracking-wide"
                                      style="background:rgba(249,115,22,.25);color:var(--accent)">
                                    {{ $ddbRole }}
                                </span>
                            </div>
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-zinc-700 transition hover:bg-stone-100">
                                <i class="fas fa-gauge-high w-4 text-orange-500"></i> Dashboard
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-zinc-700 transition hover:bg-stone-100">
                                <i class="fas fa-user-gear w-4 text-zinc-400"></i> My Profile
                            </a>
                            <hr class="my-1 border-zinc-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-rose-600 transition hover:bg-rose-50">
                                    <i class="fas fa-right-from-bracket w-4"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>

                </div>{{-- right actions --}}
            </div>
        </header>

        {{-- Optional page-header band --}}
        @isset($header)
            <div class="border-b border-stone-200 bg-white">
                <div class="ddb-content">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Content --}}
        <main class="flex-1">
            <div class="ddb-content">
                <x-flash class="mb-5" />
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-stone-200 bg-white">
            <div class="ddb-content flex flex-wrap items-center justify-between gap-2 py-3 text-xs text-zinc-400">
                <span>&copy; {{ now()->year }} <strong class="text-zinc-700">{{ config('app.name', 'DayByDay Automotive') }}</strong>. All rights reserved.</span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-wrench" style="color:var(--accent)"></i>
                    Automotive POS &amp; Inventory
                </span>
            </div>
        </footer>
    </div>{{-- #ddb-main --}}

    @stack('scripts')
</body>
</html>
