<style>
    .ci-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .ci-pipeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (max-width: 640px) { .ci-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    .ci-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .ci-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .ci-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .ci-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #6366f1; margin-bottom: .35rem;
    }
    .ci-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .ci-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    .ci-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .ci-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .ci-badge-green  { background: #dcfce7; color: #15803d; } .ci-badge-green::before  { background: #22c55e; }
    .ci-badge-amber  { background: #fef3c7; color: #b45309; } .ci-badge-amber::before  { background: #f59e0b; }
    .ci-badge-rose   { background: #ffe4e6; color: #be123c; } .ci-badge-rose::before   { background: #f43f5e; }
    .ci-badge-slate  { background: #f1f5f9; color: #475569; } .ci-badge-slate::before  { background: #94a3b8; }

    .ci-inv-num { font-family: ui-monospace, monospace; font-size: .82rem; font-weight: 700; color: #374151; }
    .ci-inv-num:hover { color: #ea580c; }

    .ci-index-row { cursor: pointer; transition: background .12s; }
    .ci-index-row:hover { background: #fffbf5; }

    .ci-balance-due { font-weight: 800; color: #c2410c; }
    .ci-balance-clear { font-weight: 600; color: #9ca3af; }

    .ci-account-link {
        display: flex; align-items: center; gap: .5rem; text-decoration: none; color: inherit;
    }
    .ci-account-icon {
        width: 1.85rem; height: 1.85rem; border-radius: 7px; flex-shrink: 0;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border: 1px solid #fcd34d; color: #b45309;
        display: flex; align-items: center; justify-content: center; font-size: .65rem;
    }
    .ci-account-name { font-size: .82rem; font-weight: 700; color: #111827; }
    .ci-account-name:hover { color: #ea580c; }

    .ci-plate {
        font-size: .72rem; font-weight: 700; letter-spacing: .04em;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 4px; padding: .15rem .4rem; color: #475569;
    }

    .ci-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    /* Show layout */
    .ci-show-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1100px) { .ci-show-grid { grid-template-columns: 1fr; } }

    .ci-show-banner {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .85rem 1.15rem; border-radius: 10px; border: 1px solid;
        font-size: .82rem;
    }
    .ci-show-banner-overdue { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
    .ci-show-banner-paid    { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

    .ci-pay-panel { padding: 1.15rem 1.25rem; }
    .ci-pay-row {
        display: grid; grid-template-columns: 1fr 1fr; gap: .75rem;
        padding: .85rem; background: #f9fafb; border: 1px solid #f0f0f0;
        border-radius: 10px; margin-bottom: .65rem;
    }
    @media (max-width: 520px) { .ci-pay-row { grid-template-columns: 1fr; } }

    /* Invoice document */
    .ci-doc {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
    }
    .ci-doc-accent {
        height: 5px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #ff6b35);
    }
    .ci-doc-body { padding: 2rem 2.25rem; }
    @media (max-width: 640px) { .ci-doc-body { padding: 1.25rem 1rem; } }

    .ci-doc-header {
        display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1.5rem;
        padding-bottom: 1.5rem; margin-bottom: 1.5rem;
        border-bottom: 2px solid #f3f4f6;
    }
    .ci-doc-brand { display: flex; align-items: flex-start; gap: .85rem; }
    .ci-doc-logo {
        width: 3rem; height: 3rem; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; box-shadow: 0 4px 12px rgba(99, 102, 241, .35);
    }
    .ci-doc-company { font-size: 1.2rem; font-weight: 800; color: #111827; letter-spacing: -.02em; }
    .ci-doc-tagline { font-size: .72rem; color: #9ca3af; margin-top: .15rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 600; }

    .ci-doc-title-block { text-align: right; }
    .ci-doc-type {
        font-size: .68rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .12em; color: #6366f1; margin-bottom: .35rem;
    }
    .ci-doc-number { font-family: ui-monospace, monospace; font-size: 1.35rem; font-weight: 800; color: #111827; }
    .ci-doc-status { margin-top: .5rem; }

    .ci-doc-meta {
        display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 640px) { .ci-doc-meta { grid-template-columns: 1fr; } }

    .ci-doc-block-label {
        font-size: .62rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .1em; color: #9ca3af; margin-bottom: .5rem;
    }
    .ci-doc-bill-name { font-size: 1.05rem; font-weight: 800; color: #111827; margin-bottom: .25rem; }
    .ci-doc-bill-detail { font-size: .82rem; color: #6b7280; line-height: 1.6; }

    .ci-doc-dates { display: flex; flex-direction: column; gap: .45rem; }
    .ci-doc-date-row {
        display: flex; justify-content: space-between; align-items: baseline;
        font-size: .82rem; padding: .35rem 0; border-bottom: 1px dashed #f0f0f0;
    }
    .ci-doc-date-row:last-child { border-bottom: none; }
    .ci-doc-date-label { color: #9ca3af; font-weight: 600; }
    .ci-doc-date-value { font-weight: 700; color: #374151; }
    .ci-doc-date-value.overdue { color: #dc2626; }

    .ci-doc-period {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca;
        font-size: .75rem; font-weight: 700; padding: .4rem .75rem;
        border-radius: 8px; margin-bottom: 1.25rem;
    }

    .ci-doc-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .ci-doc-table thead th {
        text-align: left; padding: .65rem .75rem;
        font-size: .62rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .06em; color: #6b7280;
        background: #f9fafb; border-bottom: 2px solid #e5e7eb;
    }
    .ci-doc-table thead th:last-child { text-align: right; }
    .ci-doc-table tbody td {
        padding: .7rem .75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle;
    }
    .ci-doc-table tbody tr:nth-child(even) { background: #fafafa; }
    .ci-doc-table tbody tr:last-child td { border-bottom: none; }
    .ci-doc-table tbody td:last-child { text-align: right; font-weight: 700; color: #374151; }
    .ci-doc-receipt { font-family: ui-monospace, monospace; font-weight: 700; font-size: .78rem; color: #4b5563; }

    .ci-doc-footer-grid {
        display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1.5rem;
        margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #f3f4f6;
    }
    .ci-doc-notes { flex: 1; min-width: 200px; }
    .ci-doc-notes p { font-size: .78rem; color: #6b7280; line-height: 1.55; }

    .ci-doc-totals {
        min-width: 240px; background: #f9fafb; border: 1px solid #e5e7eb;
        border-radius: 12px; padding: 1rem 1.15rem;
    }
    .ci-doc-total-row {
        display: flex; justify-content: space-between; align-items: baseline;
        padding: .4rem 0; font-size: .82rem; color: #6b7280;
    }
    .ci-doc-total-row.grand {
        margin-top: .5rem; padding-top: .75rem; border-top: 2px solid #e5e7eb;
        font-size: 1rem; font-weight: 800; color: #111827;
    }
    .ci-doc-total-row.grand .ci-doc-total-val { color: #c2410c; font-size: 1.15rem; }
    .ci-doc-total-row.paid { color: #15803d; }
    .ci-doc-total-row.balance { font-weight: 800; color: #111827; }
    .ci-doc-total-val { font-weight: 700; color: #374151; }

    .ci-doc-payments {
        margin-top: 1.25rem; padding: 1rem 1.15rem;
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    }
    .ci-doc-payments-title {
        font-size: .68rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .08em; color: #15803d; margin-bottom: .65rem;
    }
    .ci-doc-payment-item {
        display: flex; justify-content: space-between; align-items: center;
        font-size: .82rem; padding: .35rem 0; border-bottom: 1px dashed #bbf7d0;
    }
    .ci-doc-payment-item:last-child { border-bottom: none; }

    .ci-doc-closing {
        text-align: center; margin-top: 1.75rem; padding-top: 1.25rem;
        border-top: 1px solid #f0f0f0; font-size: .78rem; color: #9ca3af;
    }

    /* Print */
    @media print {
        .no-print { display: none !important; }
        .mi-page { padding: 0 !important; max-width: none !important; }
        .ci-show-grid { display: block !important; }
        .ci-doc {
            box-shadow: none; border: none; border-radius: 0;
        }
        .ci-doc-body { padding: 0; }
        body { background: #fff !important; }
    }
</style>
