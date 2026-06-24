<style>
    .emp-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .emp-nav {
        display: flex; flex-wrap: wrap; gap: .35rem;
        padding: .35rem; background: #f8fafc; border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .emp-nav-link {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .5rem .85rem; border-radius: 8px;
        font-size: .78rem; font-weight: 600; color: #64748b;
        text-decoration: none; transition: all .15s;
    }
    .emp-nav-link:hover { background: #fff; color: #ea580c; }
    .emp-nav-link.active {
        background: #fff; color: #c2410c;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        border: 1px solid #fed7aa;
    }
    .emp-nav-link i { font-size: .72rem; opacity: .85; }

    .emp-pipeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7rem, 1fr));
        gap: .5rem;
    }
    .emp-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .emp-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .emp-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .emp-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #6366f1; margin-bottom: .35rem;
    }
    .emp-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .emp-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    .emp-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .emp-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .emp-badge-green  { background: #dcfce7; color: #15803d; } .emp-badge-green::before  { background: #22c55e; }
    .emp-badge-slate  { background: #f1f5f9; color: #475569; } .emp-badge-slate::before  { background: #94a3b8; }
    .emp-badge-blue   { background: #dbeafe; color: #1d4ed8; } .emp-badge-blue::before   { background: #3b82f6; }

    .emp-station-pill, .emp-type-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: 6px; border: 1px solid transparent; white-space: nowrap;
    }
    .emp-station-pill i, .emp-type-pill i { font-size: .62rem; }
    .emp-station-shop      { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .emp-station-warehouse { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .emp-station-field     { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .emp-station-office    { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .emp-type-permanent { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .emp-type-contract  { background: #fef3c7; color: #b45309; border-color: #fde68a; }
    .emp-type-casual    { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .emp-type-default   { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    .emp-avatar {
        width: 2.25rem; height: 2.25rem; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: 2px solid #fff; box-shadow: 0 1px 3px rgba(99,102,241,.25);
    }
    .emp-avatar.inactive { background: linear-gradient(135deg, #9ca3af, #6b7280); }

    .emp-person-cell { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .emp-person-name { font-size: .85rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .emp-person-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .emp-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; letter-spacing: -.02em; }
    .emp-amt { font-variant-numeric: tabular-nums; font-weight: 700; color: #111827; }

    .emp-index-row { cursor: pointer; transition: background .12s; }
    .emp-index-row:hover { background: #fffbf5; }

    .emp-doc-card {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }

    .emp-login-yes { color: #15803d; font-size: .72rem; font-weight: 700; }
    .emp-login-no  { color: #d1d5db; font-size: .72rem; }

    /* Show layout */
    .emp-show-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1100px) { .emp-show-grid { grid-template-columns: 1fr; } }

    .emp-show-hero {
        display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    }
    .emp-show-hero .emp-avatar {
        width: 3.5rem; height: 3.5rem; font-size: 1.1rem; border-radius: 14px;
    }

    .emp-doc-card {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .emp-doc-head {
        padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex; justify-content: space-between; align-items: center; gap: .75rem;
    }
    .emp-doc-head h2 { font-size: .95rem; font-weight: 700; color: #111827; }
    .emp-doc-head p { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }
    .emp-doc-body { padding: 1.15rem 1.25rem; }
    .emp-doc-foot {
        padding: .85rem 1.25rem; border-top: 2px solid #f3f4f6; background: #fafafa;
        display: flex; justify-content: space-between; align-items: center;
        font-size: .85rem; font-weight: 700;
    }

    .emp-meta-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .85rem 1.25rem;
    }
    @media (max-width: 640px) { .emp-meta-grid { grid-template-columns: 1fr; } }
    .emp-meta-item dt {
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #9ca3af; margin-bottom: .2rem;
    }
    .emp-meta-item dt i { margin-right: .25rem; opacity: .7; }
    .emp-meta-item dd { font-size: .85rem; font-weight: 600; color: #111827; word-break: break-word; }
    .emp-meta-item.full { grid-column: 1 / -1; }

    .emp-salary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: .5rem 0; border-bottom: 1px solid #f3f4f6; font-size: .82rem;
    }
    .emp-salary-row:last-child { border-bottom: none; }
    .emp-salary-row span:last-child { font-weight: 700; font-variant-numeric: tabular-nums; }

    .emp-banner {
        border-radius: 10px; border: 1px solid; padding: .75rem 1rem;
        font-size: .82rem; display: flex; align-items: center; gap: .5rem;
    }
    .emp-banner-active { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
    .emp-banner-inactive { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
    .emp-banner-terminated { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }

    .emp-history-row {
        display: flex; justify-content: space-between; align-items: center; gap: .75rem;
        padding: .65rem 0; border-bottom: 1px solid #f3f4f6; font-size: .82rem;
    }
    .emp-history-row:last-child { border-bottom: none; }

    /* Form sections */
    .emp-form-section {
        border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff;
        margin-bottom: 1rem;
    }
    .emp-form-section-head {
        padding: .75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e5e7eb;
        font-size: .78rem; font-weight: 700; color: #374151;
        display: flex; align-items: center; gap: .5rem;
    }
    .emp-form-section-head .emp-step {
        width: 1.35rem; height: 1.35rem; border-radius: 50%;
        background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c;
        font-size: .68rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .emp-form-section-body {
        padding: 1rem; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;
    }
    @media (max-width: 768px) { .emp-form-section-body { grid-template-columns: 1fr; } }
    .emp-form-section-body .col-span-full { grid-column: 1 / -1; }

    .emp-gross-bar {
        border-radius: 10px; border: 1px solid #fed7aa; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        padding: .85rem 1rem; display: flex; justify-content: space-between; align-items: center;
    }
    .emp-gross-bar strong { font-size: 1.1rem; color: #c2410c; font-variant-numeric: tabular-nums; }

    .emp-login-panel {
        border: 1px dashed #d1d5db; border-radius: 10px; padding: 1rem; background: #fafafa;
    }

    @media print {
        .no-print { display: none !important; }
        .emp-doc-card { box-shadow: none; border: 1px solid #ddd; }
    }
</style>
