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
            color: #18181b;
        }

        /* Auth form panel — ensure readable text on white card */
        .auth-form label,
        .auth-form p,
        .auth-form span,
        .auth-form a { color: inherit; }
        .auth-form .auth-label {
            display: block; font-size: .82rem; font-weight: 600; color: #3f3f46; margin-bottom: .35rem;
        }
        .auth-form .auth-input {
            display: block; width: 100%;
            border: 1px solid #d4d4d8; border-radius: .65rem;
            background: #fff; color: #18181b;
            padding: .7rem .75rem .7rem 2.35rem;
            font-size: .875rem; line-height: 1.4;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
            transition: border-color .15s, box-shadow .15s;
        }
        .auth-form .auth-input--password { padding-right: 2.65rem; }
        .auth-form .auth-input::placeholder { color: #a1a1aa; }
        .auth-form .auth-input:focus {
            outline: none; border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249,115,22,.15);
        }
        .auth-form .auth-toggle-pw {
            position: absolute; right: .55rem; top: 50%; transform: translateY(-50%);
            display: flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; border: none; border-radius: .4rem;
            background: transparent; color: #a1a1aa; cursor: pointer;
            transition: color .15s, background .15s;
        }
        .auth-form .auth-toggle-pw:hover { color: #f97316; background: #fff7ed; }
        .auth-form .auth-check-label { color: #52525b; font-size: .875rem; }
        .auth-form .auth-link { color: #ea580c; font-weight: 600; }
        .auth-form .auth-link:hover { color: #c2410c; text-decoration: underline; }
        .auth-form .auth-error { color: #dc2626; font-size: .8rem; margin-top: .35rem; }
        .auth-form .auth-status {
            color: #15803d; font-size: .875rem; font-weight: 500;
            padding: .65rem .85rem; border-radius: .5rem;
            background: #f0fdf4; border: 1px solid #bbf7d0;
        }
        .auth-form .auth-submit {
            width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            border: none; border-radius: .65rem; padding: .8rem 1rem;
            font-size: .875rem; font-weight: 700; color: #fff; cursor: pointer;
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 4px 14px rgba(249,115,22,.35);
            transition: transform .15s, box-shadow .15s, filter .15s;
        }
        .auth-form .auth-submit:hover {
            filter: brightness(1.05);
            box-shadow: 0 6px 20px rgba(249,115,22,.45);
            transform: translateY(-1px);
        }

        /* ── Animated workshop gears ── */
        @keyframes auth-gear-cw {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes auth-gear-ccw {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        .auth-gears {
            position: absolute; pointer-events: none; z-index: 1;
        }
        .auth-gears--hero {
            right: -1.5rem; top: 42%; width: 220px; height: 220px;
            opacity: .9;
        }
        .auth-gears--bg {
            left: -2rem; bottom: 18%; width: 180px; height: 180px;
            opacity: .35;
        }
        .auth-gear {
            position: absolute; display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, rgba(255,255,255,.08), rgba(255,255,255,.02));
            border: 1px solid rgba(249,115,22,.12);
            color: rgba(249,115,22,.55);
            box-shadow: inset 0 0 20px rgba(249,115,22,.06);
        }
        .auth-gear--lg {
            width: 7.5rem; height: 7.5rem; font-size: 3.2rem;
            right: 0; top: 0;
            animation: auth-gear-cw 18s linear infinite;
        }
        .auth-gear--md {
            width: 5rem; height: 5rem; font-size: 2.1rem;
            left: 0; bottom: 1rem;
            animation: auth-gear-ccw 14s linear infinite;
        }
        .auth-gear--sm {
            width: 3.25rem; height: 3.25rem; font-size: 1.35rem;
            right: 5.5rem; bottom: 0;
            animation: auth-gear-cw 10s linear infinite;
        }
        .auth-gear--xs {
            width: 2.25rem; height: 2.25rem; font-size: .95rem;
            left: 4.5rem; top: 1.25rem;
            animation: auth-gear-ccw 8s linear infinite;
            color: rgba(251,191,36,.45);
            border-color: rgba(251,191,36,.15);
        }

        /* ── Right panel ── */
        .auth-panel-right {
            position: relative; flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 2.5rem 1.25rem;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(249,115,22,.08), transparent 55%),
                radial-gradient(ellipse 60% 40% at 100% 100%, rgba(249,115,22,.05), transparent 50%),
                linear-gradient(180deg, #f8f8f7 0%, #f3f4f6 100%);
        }
        .auth-panel-right::before {
            content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .4;
            background-image:
                linear-gradient(rgba(0,0,0,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,.03) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .auth-card {
            position: relative; z-index: 1; width: 100%; max-width: 420px;
            overflow: hidden; border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 12px 40px rgba(0,0,0,.08), 0 0 0 1px rgba(0,0,0,.04);
        }
        .auth-card-accent {
            height: 4px; width: 100%;
            background: linear-gradient(90deg, var(--accent), #fbbf24, var(--accent));
            background-size: 200% 100%;
            animation: auth-shimmer 4s ease-in-out infinite;
        }
        @keyframes auth-shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .auth-mobile-gears {
            position: relative; width: 4.5rem; height: 4.5rem; margin-bottom: .75rem;
        }
        .auth-mobile-gears .auth-gear--lg {
            width: 3rem; height: 3rem; font-size: 1.35rem;
            right: 0; top: 0; animation-duration: 12s;
        }
        .auth-mobile-gears .auth-gear--sm {
            width: 1.75rem; height: 1.75rem; font-size: .75rem;
            left: 0; bottom: .25rem; right: auto; animation-duration: 8s;
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

        {{-- Animated gears — background layer --}}
        <div class="auth-gears auth-gears--bg" aria-hidden="true">
            <div class="auth-gear auth-gear--md"><i class="fas fa-gear"></i></div>
            <div class="auth-gear auth-gear--xs"><i class="fas fa-gear"></i></div>
        </div>

        {{-- Animated gears — hero cluster --}}
        <div class="auth-gears auth-gears--hero" aria-hidden="true">
            <div class="auth-gear auth-gear--lg"><i class="fas fa-gear"></i></div>
            <div class="auth-gear auth-gear--sm"><i class="fas fa-gear"></i></div>
            <div class="auth-gear auth-gear--xs"><i class="fas fa-gear"></i></div>
        </div>

        {{-- Subtle wrench watermark --}}
        <i class="fas fa-wrench pointer-events-none absolute -bottom-10 right-2 -rotate-[30deg] text-[15rem] leading-none"
           style="color:rgba(255,255,255,.028)"></i>

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
    <div class="auth-panel-right px-5 sm:px-8">
        <div class="w-full max-w-[420px] relative z-10">

            {{-- Mobile logo + mini gears --}}
            <div class="mb-7 flex flex-col items-center lg:hidden">
                <div class="auth-mobile-gears" aria-hidden="true">
                    <div class="auth-gear auth-gear--lg"><i class="fas fa-gear"></i></div>
                    <div class="auth-gear auth-gear--sm"><i class="fas fa-gear"></i></div>
                </div>
                <span class="mb-2 flex h-14 w-14 items-center justify-center rounded-2xl text-2xl text-white"
                      style="background:linear-gradient(135deg,var(--accent),var(--accent-dk));
                             box-shadow:0 8px 24px rgba(249,115,22,.4)">
                    <i class="fas fa-gauge-high"></i>
                </span>
                <div class="text-2xl font-extrabold tracking-tight text-zinc-900">DayByDay</div>
                <div class="mt-0.5 text-[.63rem] font-semibold uppercase tracking-[.15em] text-zinc-500">Automotive System</div>
            </div>

            <div class="auth-card">
                <div class="auth-card-accent"></div>

                <div class="px-7 pb-8 pt-6 auth-form">
                    <div class="mb-6">
                        <h2 class="text-[1.45rem] font-extrabold tracking-tight text-zinc-900">{{ $heading }}</h2>
                        <p class="mt-1.5 text-sm text-zinc-500 leading-relaxed">{{ $subheading }}</p>
                    </div>

                    {{ $slot }}
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-4 sm:gap-5 text-[.67rem] font-semibold text-zinc-500">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1 ring-1 ring-zinc-200/80">
                    <i class="fas fa-lock text-emerald-500"></i> Secured
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1 ring-1 ring-zinc-200/80">
                    <i class="fas fa-shield-halved text-emerald-500"></i> Protected
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-1 ring-1 ring-zinc-200/80">
                    <i class="fas fa-user-shield text-emerald-500"></i> Role-based
                </span>
            </div>

        </div>
    </div>

    @stack('scripts')
</body>
</html>
