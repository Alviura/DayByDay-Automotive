{{-- Shared styles for goods receipt pages --}}
<style>
    .grn-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 1100px) { .grn-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .grn-kpi-grid { grid-template-columns: 1fr; } }

    .grn-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .grn-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .grn-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .grn-badge-green  { background: #dcfce7; color: #15803d; } .grn-badge-green::before  { background: #22c55e; }
    .grn-badge-amber  { background: #fef3c7; color: #b45309; } .grn-badge-amber::before  { background: #f59e0b; }
    .grn-badge-emerald { background: #d1fae5; color: #047857; } .grn-badge-emerald::before { background: #10b981; }
    .grn-badge-slate  { background: #f1f5f9; color: #475569; } .grn-badge-slate::before  { background: #94a3b8; }

    .grn-row-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0; color: #059669;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .grn-cell-main { display: flex; align-items: flex-start; gap: .65rem; }
    .grn-qty-good { font-weight: 700; color: #059669; }
    .grn-qty-damaged { font-weight: 600; color: #d97706; }

    .grn-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .grn-section-title {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 700; color: #111827;
    }
    .grn-section-title i { color: #9ca3af; font-size: .8rem; }
    .grn-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .grn-link-card {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border: 1px solid #f0f0f0; border-radius: 10px;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .grn-link-card:hover { border-color: #a7f3d0; background: #ecfdf5; }
    .grn-link-card-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }

    .grn-phase-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1.1rem; border-radius: 10px;
        font-size: .82rem; color: #374151; line-height: 1.5;
    }
    .grn-phase-banner i { margin-top: .15rem; flex-shrink: 0; }
    .grn-phase-banner-emerald {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0; color: #065f46;
    }
    .grn-phase-banner-emerald i { color: #059669; }

    .grn-po-summary {
        border: 1px dashed #bfdbfe;
        background: linear-gradient(135deg, #eff6ff, #f0f9ff);
        border-radius: 10px; padding: .85rem 1rem;
    }
    .grn-po-summary-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af; margin-bottom: .35rem;
    }

    .grn-input-qty { max-width: 7rem; }
    .grn-input-cost { max-width: 8rem; }

    .grn-damage-flag {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .65rem; font-weight: 700; color: #b45309;
        background: #fef3c7; border-radius: 9999px; padding: .15rem .5rem;
    }
</style>
