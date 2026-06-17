<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f0f11">
    <title>{{ $heading ?? 'Sign In' }} — {{ config('app.name', 'DayByDay Automotive') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --accent:#f97316; --accent-dk:#c2410c; }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; min-height: 100%; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
    </style>
    @stack('styles')
</head>
<body class="flex min-h-screen">

    {{-- ============================================================
         LEFT BRAND PANEL  (desktop only — hidden on mobile)
         Dark near-black garage theme with orange accents + tool icons
    ============================================================ --}}
    <div class="relative hidden w-[44%] flex-col justify-between overflow-hidden p-10 lg:flex"
         style="background:linear-gradient(160deg, #0d0d0f 0%, #161618 55%, #1a1a1d 100%)">

        {{-- Radial glow — top left --}}
        <div class="pointer-events-none absolute -left-16 -top-16 h-72 w-72 rounded-full"
             style="background:radial-gradient(circle, rgba(249,115,22,.2) 0%, transparent 65%)"></div>

        {{-- Radial glow — bottom right --}}
        <div class="pointer-events-none absolute -bottom-12 -right-8 h-56 w-56 rounded-full"
             style="background:radial-gradient(circle, rgba(249,115,22,.12) 0%, transparent 65%)"></div>

        {{-- Decorative oversized icons --}}
        <i class="fas fa-wrench pointer-events-none absolute -bottom-10 right-2 -rotate-[30deg] text-[15rem] leading-none"
           style="color:rgba(255,255,255,.028)"></i>
        <i class="fas fa-gear pointer-events-none absolute right-12 top-20 text-[7rem] leading-none"
           style="color:rgba(249,115,22,.065)"></i>

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-3">
            <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-xl text-white"
                  style="background:linear-gradient(135deg,var(--accent),var(--accent-dk));
                         box-shadow:0 8px 24px rgba(249,115,22,.45)">
                <i class="fas fa-gauge-high"></i>
            </span>
            <div class="leading-tight">
                <div class="text-lg font-extrabold tracking-tight text-white">DayByDay</div>
                <div class="text-[.58rem] font-medium uppercase tracking-[.17em] text-white/40">Automotive System</div>
            </div>
        </div>

        {{-- Main body --}}
        <div class="relative z-10 max-w-[340px]">
            {{-- Eyebrow badge --}}
            <span class="mb-5 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white/80"
                  style="border:1px solid rgba(249,115,22,.32);background:rgba(249,115,22,.1)">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full"
                      style="background:var(--accent);box-shadow:0 0 6px rgba(249,115,22,.8)"></span>
                Parts · Sales · Stock · Procurement
            </span>

            {{-- Headline --}}
            <h1 class="mb-4 text-[2.1rem] font-extrabold leading-[1.15] tracking-tight text-white">
                Your workshop,<br>
                <span style="background:linear-gradient(90deg,var(--accent),#fbbf24);
                             -webkit-background-clip:text;-webkit-text-fill-color:transparent">
                    run smarter.
                </span>
            </h1>

            <p class="mb-8 text-[.875rem] leading-relaxed text-white/50">
                A complete POS and inventory system built for autospare shops — multi-location stock,
                procurement, transfers, sales and returns, all in one platform.
            </p>

            {{-- Feature list --}}
            <ul class="space-y-4">
                @foreach ([
                    ['fa-cash-register',       'rgba(249,115,22,.2)',  '#fb923c', 'Point of Sale',           'Fast counter sales, receipts & split payments'],
                    ['fa-boxes-stacked',        'rgba(139,92,246,.2)', '#a78bfa', 'Multi-location Inventory', 'Real-time stock across all shops & warehouses'],
                    ['fa-file-invoice-dollar',  'rgba(52,211,153,.2)', '#34d399', 'Procurement & GRN',        'Purchase orders through to goods receipt notes'],
                    ['fa-user-shield',          'rgba(96,165,250,.2)', '#60a5fa', 'Role-based Access',        'Granular permissions for every team member'],
                ] as [$ico, $bg, $col, $ttl, $sub])
                    <li class="flex items-start gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl text-sm"
                              style="background:{{ $bg }};color:{{ $col }}">
                            <i class="fas {{ $ico }}"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-white">{{ $ttl }}</p>
                            <p class="text-xs text-white/42">{{ $sub }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Footer --}}
        <div class="relative z-10 text-xs text-white/28">
            &copy; {{ now()->year }} {{ config('app.name', 'DayByDay Automotive') }}. All rights reserved.
        </div>
    </div>

    {{-- ============================================================
         RIGHT FORM PANEL
    ============================================================ --}}
    <div class="flex flex-1 flex-col items-center justify-center bg-stone-100 px-5 py-10 sm:px-8">
        <div class="w-full max-w-[420px]">

            {{-- Mobile-only logo --}}
            <div class="mb-7 flex flex-col items-center lg:hidden">
                <span class="mb-2.5 flex h-13 w-13 items-center justify-center rounded-2xl text-2xl text-white shadow-xl"
                      style="background:linear-gradient(135deg,var(--accent),var(--accent-dk));
                             box-shadow:0 8px 24px rgba(249,115,22,.4);
                             height:3.25rem;width:3.25rem">
                    <i class="fas fa-gauge-high"></i>
                </span>
                <div class="text-2xl font-extrabold tracking-tight text-zinc-900">DayByDay</div>
                <div class="mt-0.5 text-[.63rem] font-semibold uppercase tracking-[.15em] text-zinc-400">Automotive System</div>
            </div>

            {{-- Card --}}
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-[0_8px_48px_rgba(0,0,0,0.11)] ring-1 ring-zinc-200/70">
                {{-- Accent top bar --}}
                <div class="h-[3px] w-full"
                     style="background:linear-gradient(90deg, var(--accent) 0%, #fbbf24 100%)"></div>

                <div class="px-7 pb-8 pt-6">
                    {{-- Card header --}}
                    <div class="mb-6">
                        <h2 class="text-[1.4rem] font-extrabold tracking-tight text-zinc-900">{{ $heading }}</h2>
                        <p class="mt-1 text-sm text-zinc-400">{{ $subheading }}</p>
                    </div>

                    {{ $slot }}
                </div>
            </div>

            {{-- Trust badges --}}
            <div class="mt-5 flex flex-wrap items-center justify-center gap-5 text-[.67rem] font-medium text-zinc-400">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-lock" style="color:#4ade80"></i> Secured
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-shield-halved" style="color:#4ade80"></i> Data Protected
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-user-shield" style="color:#4ade80"></i> Role-based Access
                </span>
            </div>

        </div>
    </div>

    @stack('scripts')
</body>
</html>
