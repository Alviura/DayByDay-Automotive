{{-- Shared styles for inventory module --}}
<style>
    .inv-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .inv-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .inv-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .inv-badge-green  { background: #dcfce7; color: #15803d; } .inv-badge-green::before  { background: #22c55e; }
    .inv-badge-rose   { background: #ffe4e6; color: #be123c; } .inv-badge-rose::before   { background: #f43f5e; }
    .inv-badge-amber  { background: #fef3c7; color: #b45309; } .inv-badge-amber::before  { background: #f59e0b; }
    .inv-badge-blue   { background: #dbeafe; color: #1d4ed8; } .inv-badge-blue::before   { background: #3b82f6; }
    .inv-badge-indigo { background: #e0e7ff; color: #4338ca; } .inv-badge-indigo::before { background: #6366f1; }
    .inv-badge-orange { background: #ffedd5; color: #c2410c; } .inv-badge-orange::before { background: #f97316; }
    .inv-badge-teal   { background: #ccfbf1; color: #0f766e; } .inv-badge-teal::before   { background: #14b8a6; }
    .inv-badge-violet { background: #ede9fe; color: #6d28d9; } .inv-badge-violet::before { background: #8b5cf6; }
    .inv-badge-slate  { background: #f1f5f9; color: #475569; } .inv-badge-slate::before  { background: #94a3b8; }
    .inv-badge-cyan   { background: #cffafe; color: #0e7490; } .inv-badge-cyan::before   { background: #06b6d4; }

    .inv-section-title {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 700; color: #111827;
    }
    .inv-section-title i { color: #9ca3af; font-size: .8rem; }
    .inv-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .inv-phase-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1.1rem; border-radius: 10px;
        font-size: .82rem; color: #374151; line-height: 1.5;
    }
    .inv-phase-banner i { margin-top: .15rem; flex-shrink: 0; }
    .inv-phase-banner-blue {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #bfdbfe; color: #1e40af;
    }
    .inv-phase-banner-blue i { color: #2563eb; }

    .inv-qty-in  { font-weight: 700; color: #059669; }
    .inv-qty-out { font-weight: 700; color: #dc2626; }
    .inv-qty-low { font-weight: 700; color: #d97706; }

    .inv-ref-link {
        font-size: .78rem; font-weight: 600; color: #ea580c;
        text-decoration: none; transition: color .15s;
    }
    .inv-ref-link:hover { color: #c2410c; text-decoration: underline; }

    .inv-sidebar { display: flex; flex-direction: column; }
    .inv-sidebar-hero {
        display: flex; align-items: center; gap: .85rem;
        padding: 1.1rem 1.15rem;
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-bottom: 1px solid #fed7aa;
    }
    .inv-sidebar-hero-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 12px; flex-shrink: 0;
        background: #fff; border: 1px solid #fdba74;
        color: #ea580c; font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(234, 88, 12, .12);
    }
    .inv-sidebar-hero-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #c2410c; opacity: .85;
    }
    .inv-sidebar-hero-title {
        font-size: .95rem; font-weight: 700; color: #7c2d12;
        line-height: 1.3; margin-top: .1rem;
    }
    .inv-sidebar-hero-sub { font-size: .72rem; color: #ea580c; margin-top: .15rem; }

    .inv-sidebar-body { padding: .35rem 0 .5rem; }
    .inv-sidebar-block { padding: .85rem 1.15rem .75rem; }
    .inv-sidebar-block + .inv-sidebar-block { border-top: 1px solid #f3f4f6; }

    .inv-detail-list { display: flex; flex-direction: column; gap: .5rem; margin: 0; }
    .inv-detail-row {
        display: grid; grid-template-columns: 5.5rem 1fr;
        gap: .65rem; align-items: baseline;
        font-size: .8rem; line-height: 1.35;
    }
    .inv-detail-row dt { color: #9ca3af; font-weight: 500; margin: 0; }
    .inv-detail-row dd { color: #374151; margin: 0; word-break: break-word; }

    .inv-link-card {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .75rem .85rem; border: 1px solid #f0f0f0; border-radius: 10px;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .inv-link-card:hover { border-color: #fed7aa; background: #fff7ed; }
    .inv-link-card-icon {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }

    .inv-loc-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 600; color: #374151;
    }
    .inv-loc-chip-wh { color: #047857; }
    .inv-loc-chip-sh { color: #4338ca; }

    .inv-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .inv-val-bar-track {
        height: 6px; border-radius: 9999px; background: #f3f4f6; overflow: hidden;
    }
    .inv-val-bar-fill {
        height: 100%; border-radius: 9999px;
        background: linear-gradient(90deg, #fb923c, #ea580c);
    }

    /* Index — one row per product */
    .inv-index-table tbody tr.inv-index-row {
        cursor: pointer;
        transition: background .12s ease;
    }
    .inv-index-table tbody tr.inv-index-row:hover { background: #fffbf5; }
    .inv-index-table tbody tr.inv-index-row:hover td:first-child .inv-product-name {
        color: #ea580c;
    }

    .inv-product-cell { display: flex; align-items: flex-start; gap: .75rem; min-width: 0; }
    .inv-product-thumb {
        width: 2.35rem; height: 2.35rem; border-radius: 10px; flex-shrink: 0;
        background: linear-gradient(135deg, #f9fafb, #f3f4f6);
        border: 1px solid #e5e7eb;
        color: #9ca3af; font-size: .85rem;
        display: flex; align-items: center; justify-content: center;
    }
    .inv-product-meta { min-width: 0; }
    .inv-product-part {
        font-size: .82rem; font-weight: 700; color: #111827;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .inv-product-name {
        font-size: .8rem; color: #6b7280; margin-top: .1rem;
        transition: color .12s;
    }
    .inv-product-tags { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .35rem; }

    .inv-qty-cell { min-width: 5.5rem; }
    .inv-qty-cell-head { display: flex; align-items: center; gap: .45rem; }
    .inv-qty-cell-icon {
        width: 1.35rem; height: 1.35rem; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: .58rem; flex-shrink: 0;
    }
    .inv-qty-cell-wh .inv-qty-cell-icon { background: #d1fae5; color: #047857; }
    .inv-qty-cell-sh .inv-qty-cell-icon { background: #e0e7ff; color: #4338ca; }
    .inv-qty-cell-value { font-size: 1rem; font-weight: 700; color: #111827; line-height: 1; }
    .inv-qty-cell-loc { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }
    .inv-qty-cell-empty { color: #d1d5db; font-weight: 600; }
    .inv-qty-cell-breakdown {
        list-style: none; margin: .35rem 0 0; padding: 0;
        display: flex; flex-direction: column; gap: .15rem;
    }
    .inv-qty-cell-breakdown li {
        display: flex; justify-content: space-between; gap: .5rem;
        font-size: .66rem; color: #6b7280;
    }
    .inv-qty-cell-breakdown strong { color: #374151; font-weight: 700; }

    .inv-total-cell { min-width: 4.5rem; }
    .inv-total-qty { font-size: 1.05rem; font-weight: 800; color: #111827; }
    .inv-total-sub { font-size: .66rem; color: #9ca3af; margin-top: .15rem; }

    .inv-split-bar {
        display: flex; height: 5px; border-radius: 9999px; overflow: hidden;
        background: #f3f4f6; margin-top: .4rem; max-width: 7rem;
    }
    .inv-split-bar-wh { background: #34d399; }
    .inv-split-bar-sh { background: #818cf8; }

    .inv-index-hint {
        display: flex; align-items: center; gap: .5rem;
        padding: .65rem 1rem; border-top: 1px solid #f3f4f6;
        font-size: .75rem; color: #6b7280; background: #fafafa;
    }
    .inv-index-hint i { color: #ea580c; }

    /* Valuation */
    .inv-val-split-card {
        padding: 1rem 1.25rem; background: #fff;
        border: 1px solid #f0f0f0; border-radius: 12px;
    }
    .inv-val-split-head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .75rem; margin-bottom: .85rem;
    }
    .inv-val-split-title { font-size: .78rem; font-weight: 700; color: #374151; }
    .inv-val-split-legend { display: flex; flex-wrap: wrap; gap: 1rem; font-size: .72rem; color: #6b7280; }
    .inv-val-split-legend span { display: inline-flex; align-items: center; gap: .35rem; }
    .inv-val-split-legend i { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .inv-val-split-bar {
        display: flex; height: 10px; border-radius: 9999px; overflow: hidden; background: #f3f4f6;
    }
    .inv-val-split-wh { background: linear-gradient(90deg, #34d399, #059669); }
    .inv-val-split-sh { background: linear-gradient(90deg, #818cf8, #4f46e5); }

    .inv-val-loc-cell { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .inv-val-loc-icon {
        width: 2rem; height: 2rem; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .inv-val-loc-icon-wh { background: #d1fae5; color: #047857; }
    .inv-val-loc-icon-sh { background: #e0e7ff; color: #4338ca; }
    .inv-val-loc-name { font-weight: 700; color: #111827; font-size: .85rem; }
    .inv-val-loc-code { font-size: .68rem; color: #9ca3af; margin-top: .05rem; }

    .inv-val-row { cursor: pointer; transition: background .12s; }
    .inv-val-row:hover { background: #fffbf5; }
    .inv-val-row.is-active { background: #fff7ed; }
    .inv-val-row.is-empty { opacity: .55; }

    .inv-val-detail-hero {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.25rem; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border-bottom: 1px solid #fed7aa;
    }
    .inv-val-detail-hero-title { font-size: 1rem; font-weight: 800; color: #7c2d12; }
    .inv-val-detail-hero-sub { font-size: .75rem; color: #c2410c; margin-top: .15rem; }
    .inv-val-detail-kpis { display: flex; flex-wrap: wrap; gap: 1.25rem; }
    .inv-val-detail-kpi { text-align: right; }
    .inv-val-detail-kpi-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #ea580c; }
    .inv-val-detail-kpi-value { font-size: .95rem; font-weight: 800; color: #7c2d12; }

    .inv-val-share-pill {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 2.25rem; font-size: .72rem; font-weight: 700; color: #6b7280;
    }
</style>
