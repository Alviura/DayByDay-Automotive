<style>
    .ca-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .ca-pipeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (max-width: 640px) { .ca-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    .ca-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .ca-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .ca-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .ca-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #f59e0b; margin-bottom: .35rem;
    }
    .ca-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .ca-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    .ca-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .ca-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .ca-badge-active  { background: #dcfce7; color: #15803d; } .ca-badge-active::before  { background: #22c55e; }
    .ca-badge-inactive { background: #f1f5f9; color: #64748b; } .ca-badge-inactive::before { background: #94a3b8; }

    .ca-account-cell { display: flex; align-items: flex-start; gap: .65rem; }
    .ca-account-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 1px solid #fcd34d; color: #b45309;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .ca-account-name { font-size: .82rem; font-weight: 700; color: #111827; text-decoration: none; }
    .ca-account-name:hover { color: #ea580c; }
    .ca-account-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .ca-balance { font-weight: 800; white-space: nowrap; }
    .ca-balance-due { color: #c2410c; }
    .ca-balance-clear { color: #9ca3af; font-weight: 600; }

    .ca-credit-bar {
        width: 4.5rem; height: 4px; border-radius: 9999px;
        background: #f3f4f6; overflow: hidden; margin-top: .25rem;
    }
    .ca-credit-fill { height: 100%; border-radius: 9999px; background: #f59e0b; }
    .ca-credit-fill.warn { background: #ef4444; }

    .ca-terms-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .68rem; font-weight: 600; padding: .2rem .5rem;
        border-radius: 6px; background: #eff6ff; color: #1d4ed8;
        text-transform: capitalize;
    }

    .ca-index-row { cursor: pointer; transition: background .12s; }
    .ca-index-row:hover { background: #fffbf5; }

    .ca-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .ca-uninvoiced {
        font-size: .68rem; font-weight: 600; color: #b45309;
        background: #fffbeb; border: 1px solid #fde68a;
        border-radius: 9999px; padding: .15rem .45rem;
    }

    /* Show page */
    .ca-show-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) { .ca-show-grid { grid-template-columns: 1fr; } }

    .ca-show-banner {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .85rem 1.15rem; border-radius: 10px; border: 1px solid;
    }
    .ca-show-banner-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .ca-show-banner-ok   { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
    .ca-show-banner-text { font-size: .82rem; }

    .ca-credit-meter {
        margin-top: .5rem;
        height: 6px; border-radius: 9999px; background: #f3f4f6; overflow: hidden;
    }
    .ca-credit-meter-fill { height: 100%; border-radius: 9999px; background: #f59e0b; transition: width .2s; }
    .ca-credit-meter-fill.warn { background: #ef4444; }

    .ca-sale-row { cursor: pointer; transition: background .12s; }
    .ca-sale-row:hover { background: #fffbf5; }

    .ca-receipt { font-family: ui-monospace, monospace; font-size: .78rem; font-weight: 700; color: #374151; }

    .ca-pay-badge {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .65rem; font-weight: 700; padding: .18rem .5rem;
        border-radius: 9999px; text-transform: capitalize;
    }
    .ca-pay-unpaid  { background: #fef3c7; color: #b45309; }
    .ca-pay-partial { background: #ffedd5; color: #c2410c; }
    .ca-pay-paid    { background: #dcfce7; color: #15803d; }

    .ca-plate {
        font-size: .72rem; font-weight: 700; letter-spacing: .04em;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 4px; padding: .15rem .4rem; color: #475569;
    }

    .ci-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .ci-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .ci-badge-green  { background: #dcfce7; color: #15803d; } .ci-badge-green::before  { background: #22c55e; }
    .ci-badge-amber  { background: #fef3c7; color: #b45309; } .ci-badge-amber::before  { background: #f59e0b; }
    .ci-badge-slate  { background: #f1f5f9; color: #475569; } .ci-badge-slate::before  { background: #94a3b8; }
</style>
