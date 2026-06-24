<style>
    .fin-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    /* Sub-navigation */
    .fin-nav {
        display: flex; flex-wrap: wrap; gap: .35rem;
        padding: .35rem; background: #f8fafc; border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .fin-nav-link {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .5rem .85rem; border-radius: 8px;
        font-size: .78rem; font-weight: 600; color: #64748b;
        text-decoration: none; transition: all .15s;
    }
    .fin-nav-link:hover { background: #fff; color: #ea580c; }
    .fin-nav-link.active {
        background: #fff; color: #c2410c;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        border: 1px solid #fed7aa;
    }
    .fin-nav-link i { font-size: .72rem; opacity: .85; }

    /* Pipeline */
    .fin-pipeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7rem, 1fr));
        gap: .5rem;
    }
    .fin-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .fin-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .fin-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .fin-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #6366f1; margin-bottom: .35rem;
    }
    .fin-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .fin-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    /* Badges */
    .fin-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; white-space: nowrap;
    }
    .fin-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .fin-badge-green  { background: #dcfce7; color: #15803d; } .fin-badge-green::before  { background: #22c55e; }
    .fin-badge-amber  { background: #fef3c7; color: #b45309; } .fin-badge-amber::before  { background: #f59e0b; }
    .fin-badge-rose   { background: #ffe4e6; color: #be123c; } .fin-badge-rose::before   { background: #f43f5e; }
    .fin-badge-slate  { background: #f1f5f9; color: #475569; } .fin-badge-slate::before  { background: #94a3b8; }
    .fin-badge-blue   { background: #dbeafe; color: #1d4ed8; } .fin-badge-blue::before   { background: #3b82f6; }
    .fin-badge-indigo { background: #e0e7ff; color: #4338ca; } .fin-badge-indigo::before { background: #6366f1; }

    /* Account type pills */
    .fin-type-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        padding: .2rem .5rem; border-radius: 6px; border: 1px solid transparent;
    }
    .fin-type-pill i { font-size: .58rem; }
    .fin-type-asset     { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .fin-type-liability { background: #fce7f3; color: #be185d; border-color: #fbcfe8; }
    .fin-type-equity    { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .fin-type-revenue   { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .fin-type-expense   { background: #ffedd5; color: #c2410c; border-color: #fed7aa; }

    /* Account row icon */
    .fin-acct-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; border: 1px solid;
    }
    .fin-acct-cell { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .fin-acct-name { font-size: .82rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .fin-acct-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .fin-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; letter-spacing: -.02em; }
    .fin-index-row { cursor: pointer; transition: background .12s; }
    .fin-index-row:hover { background: #fffbf5; }

    /* Amount columns */
    .fin-amt { font-variant-numeric: tabular-nums; }
    .fin-tb-debit { color: #15803d; font-weight: 700; font-variant-numeric: tabular-nums; }
    .fin-tb-credit { color: #1d4ed8; font-weight: 700; font-variant-numeric: tabular-nums; }
    .fin-tb-balance { font-weight: 800; font-variant-numeric: tabular-nums; color: #111827; }
    .fin-tb-balance.negative { color: #be123c; }
    .fin-tb-dash { color: #d1d5db; font-weight: 500; }

    /* Show layout */
    .fin-show-grid {
        display: grid; grid-template-columns: 1fr 320px;
        gap: 1.25rem; align-items: start;
    }
    @media (max-width: 1100px) { .fin-show-grid { grid-template-columns: 1fr; } }

    .fin-doc-card {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .fin-doc-head {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa 0%, #fff 100%);
    }
    .fin-doc-body { padding: 0; }
    .fin-doc-body .mi-table { margin: 0; }
    .fin-doc-body .mi-table th { background: #f9fafb; font-size: .68rem; }
    .fin-doc-body .mi-table td { font-size: .82rem; vertical-align: middle; }
    .fin-doc-foot {
        padding: .85rem 1.25rem; border-top: 2px solid #f3f4f6;
        background: #fafafa; display: flex; justify-content: flex-end; gap: 2rem;
        font-size: .82rem; font-weight: 700;
    }

    .fin-section-head {
        padding: .65rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb;
        font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b;
        display: flex; align-items: center; justify-content: space-between;
    }

    .fin-banner {
        border-radius: 10px; border: 1px solid; padding: .75rem 1rem;
        font-size: .82rem; display: flex; align-items: center; gap: .5rem;
    }
    .fin-banner-balanced { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
    .fin-banner-unbalanced { background: #fff1f2; border-color: #fecdd3; color: #be123c; }
    .fin-banner-voided { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }

    .fin-empty {
        text-align: center; padding: 3rem 1.5rem;
    }
    .fin-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        color: #9ca3af; display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    /* Journal entry number chip */
    .fin-entry-chip {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .35rem .65rem; border-radius: 8px;
        background: #f8fafc; border: 1px solid #e2e8f0;
        font-size: .78rem; font-weight: 700;
    }
    .fin-entry-chip i { color: #6366f1; font-size: .65rem; }

    /* Manual journal form */
    .fin-journal-line {
        display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr auto;
        gap: .65rem; align-items: end; padding: 1rem;
        border: 1px solid #e5e7eb; border-radius: 10px;
        background: linear-gradient(180deg, #fafafa, #fff); margin-bottom: .5rem;
    }
    @media (max-width: 900px) { .fin-journal-line { grid-template-columns: 1fr; } }
    .fin-balance-bar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border-radius: 10px; border: 1px solid #e5e7eb; background: #f8fafc;
    }
    .fin-balance-stat { font-size: .82rem; color: #64748b; }
    .fin-balance-stat strong { color: #111827; font-variant-numeric: tabular-nums; }
    .fin-balance-ok { color: #15803d; font-weight: 700; font-size: .82rem; }
    .fin-balance-bad { color: #be123c; font-weight: 700; font-size: .82rem; }

    /* TB group */
    .fin-tb-group { border-top: 1px solid #f3f4f6; }
    .fin-tb-group:first-child { border-top: none; }

    /* Statement tabs */
    .fin-tab-bar { display: flex; gap: .35rem; }
    .fin-tab {
        padding: .55rem 1rem; border-radius: 8px; font-size: .78rem; font-weight: 600;
        color: #64748b; text-decoration: none; border: 1px solid #e5e7eb; background: #fff;
    }
    .fin-tab:hover { border-color: #fed7aa; color: #c2410c; }
    .fin-tab.active { background: #fff7ed; border-color: #ff6b35; color: #c2410c; }

    .fin-doc-head h2 { font-size: 1rem; font-weight: 700; color: #111827; }
    .fin-doc-head span { font-size: .78rem; color: #6b7280; }

    .fin-status-pill {
        display: inline-flex; padding: .2rem .55rem; border-radius: 9999px;
        font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
    }
    .fin-status-open { background: #dbeafe; color: #1d4ed8; }
    .fin-status-filed { background: #fef3c7; color: #b45309; }
    .fin-status-paid { background: #dcfce7; color: #15803d; }
    .fin-status-draft { background: #f1f5f9; color: #475569; }
    .fin-status-reconciled { background: #dcfce7; color: #15803d; }

    .fin-dl { display: grid; gap: .65rem; font-size: .82rem; }
    .fin-dl > div { display: flex; justify-content: space-between; gap: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: .5rem; }
    .fin-dl dt { color: #6b7280; font-weight: 600; }
    .fin-dl dd { color: #111827; font-weight: 600; text-align: right; }

    @media print {
        .no-print { display: none !important; }
        .fin-doc-card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
