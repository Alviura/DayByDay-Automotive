<style>
    .sp-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    /* Sub-navigation */
    .sp-nav {
        display: flex; flex-wrap: wrap; gap: .35rem;
        padding: .35rem; background: #f8fafc; border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .sp-nav-link {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .5rem .85rem; border-radius: 8px;
        font-size: .78rem; font-weight: 600; color: #64748b;
        text-decoration: none; transition: all .15s;
    }
    .sp-nav-link:hover { background: #fff; color: #ea580c; }
    .sp-nav-link.active {
        background: #fff; color: #c2410c;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        border: 1px solid #fed7aa;
    }
    .sp-nav-link i { font-size: .72rem; opacity: .85; }

    /* Pipeline */
    .sp-pipeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7rem, 1fr));
        gap: .5rem;
    }
    .sp-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .sp-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .sp-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .sp-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #84cc16; margin-bottom: .35rem;
    }
    .sp-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .sp-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    /* Badges */
    .sp-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .sp-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .sp-badge-green  { background: #dcfce7; color: #15803d; } .sp-badge-green::before  { background: #22c55e; }
    .sp-badge-rose   { background: #ffe4e6; color: #be123c; } .sp-badge-rose::before   { background: #f43f5e; }
    .sp-badge-slate  { background: #f1f5f9; color: #475569; } .sp-badge-slate::before  { background: #94a3b8; }
    .sp-badge-amber  { background: #fef3c7; color: #b45309; } .sp-badge-amber::before  { background: #f59e0b; }

    /* Method pills */
    .sp-method-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: 6px; border: 1px solid transparent; white-space: nowrap;
    }
    .sp-method-pill i { font-size: .62rem; }
    .sp-method-cash  { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .sp-method-mpesa { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .sp-method-card  { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .sp-method-bank  { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .sp-method-cheque { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }
    .sp-method-default { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    /* Allocation chips */
    .sp-alloc-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 600; color: #374151;
        padding: .2rem .5rem; border-radius: 6px; background: #f8fafc; border: 1px solid #e5e7eb;
    }
    .sp-alloc-chip i { font-size: .62rem; color: #ea580c; }
    .sp-alloc-chip.muted { color: #9ca3af; border-style: dashed; }

    .sp-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; letter-spacing: -.02em; }
    .sp-amt { font-variant-numeric: tabular-nums; font-weight: 800; color: #111827; }
    .sp-amt-lg { font-size: 1.35rem; line-height: 1.1; }

    .sp-index-row { cursor: pointer; transition: background .12s; }
    .sp-index-row:hover { background: #fffbf5; }

    .sp-supplier-cell { display: flex; align-items: center; gap: .6rem; min-width: 0; }
    .sp-supplier-icon {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #ecfccb, #d9f99d);
        border: 1px solid #bef264; color: #4d7c0f;
        display: flex; align-items: center; justify-content: center; font-size: .7rem;
    }

    /* Show layout */
    .sp-show-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1100px) { .sp-show-grid { grid-template-columns: 1fr; } }

    .sp-show-banner {
        border-radius: 10px; border: 1px solid; padding: .75rem 1rem;
        font-size: .82rem; display: flex; align-items: center; gap: .5rem;
    }
    .sp-show-banner-voided { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
    .sp-show-banner-posted { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }

    .sp-doc-card {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .sp-doc-head {
        padding: 1.1rem 1.35rem; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;
    }
    .sp-doc-head h2 { font-size: 1rem; font-weight: 700; color: #111827; }
    .sp-doc-head p { font-size: .78rem; color: #6b7280; margin-top: .15rem; }
    .sp-doc-body { padding: 1.25rem 1.35rem; }
    .sp-doc-foot {
        padding: .85rem 1.25rem; border-top: 2px solid #f3f4f6;
        background: #fafafa; display: flex; justify-content: space-between; align-items: center;
        font-size: .82rem; font-weight: 700;
    }

    .sp-section-title {
        font-size: .68rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .06em; color: #64748b;
        display: flex; align-items: center; gap: .4rem;
    }

    .sp-alloc-summary {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr)); gap: .75rem;
    }
    .sp-alloc-stat {
        border: 1px solid #e5e7eb; border-radius: 10px; padding: .85rem 1rem; background: #fafafa;
    }
    .sp-alloc-stat-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; }
    .sp-alloc-stat-value { font-size: 1rem; font-weight: 800; color: #111827; margin-top: .25rem; font-variant-numeric: tabular-nums; }
    .sp-alloc-stat-value.accent { color: #c2410c; }

    /* Create form */
    .sp-create-grid {
        display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; align-items: start;
    }
    @media (max-width: 1000px) { .sp-create-grid { grid-template-columns: 1fr; } }

    .sp-form-section {
        border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff;
    }
    .sp-form-section-head {
        padding: .85rem 1.15rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb;
        font-size: .78rem; font-weight: 700; color: #374151;
        display: flex; align-items: center; gap: .5rem;
    }
    .sp-form-section-head .sp-step {
        width: 1.35rem; height: 1.35rem; border-radius: 50%;
        background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c;
        font-size: .68rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .sp-form-section-body { padding: 1.15rem; }

    .sp-balance-banner {
        border-radius: 10px; border: 1px solid #fed7aa; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        padding: 1rem 1.15rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .sp-balance-banner strong { font-size: 1.15rem; color: #c2410c; font-variant-numeric: tabular-nums; }

    .sp-grn-picker { display: grid; gap: .5rem; }
    .sp-grn-option {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .75rem 1rem; border: 1px solid #e5e7eb; border-radius: 10px;
        background: #fafafa; cursor: pointer; transition: all .12s; text-align: left; width: 100%;
    }
    .sp-grn-option:hover { border-color: #fed7aa; background: #fff7ed; }
    .sp-grn-option.selected { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .sp-grn-option input { display: none; }

    .sp-method-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr)); gap: .5rem;
    }
    .sp-method-option {
        display: flex; flex-direction: column; align-items: center; gap: .35rem;
        padding: .65rem .5rem; border: 1px solid #e5e7eb; border-radius: 10px;
        background: #fff; cursor: pointer; transition: all .12s; font-size: .72rem; font-weight: 600; color: #64748b;
    }
    .sp-method-option i { font-size: 1rem; color: #9ca3af; }
    .sp-method-option:hover { border-color: #fed7aa; }
    .sp-method-option.selected { border-color: #ff6b35; background: #fff7ed; color: #c2410c; }
    .sp-method-option.selected i { color: #ea580c; }
    .sp-method-option input { display: none; }

    .sp-related-row {
        display: flex; justify-content: space-between; align-items: center; gap: .75rem;
        padding: .65rem 0; border-bottom: 1px solid #f3f4f6; font-size: .82rem;
    }
    .sp-related-row:last-child { border-bottom: none; }

    @media print {
        .no-print { display: none !important; }
        .sp-doc-card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
