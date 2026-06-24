<style>
    .tr-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .tr-pipeline {
        display: flex; flex-wrap: wrap; gap: .5rem;
    }
    .tr-pipe-step {
        flex: 1 1 5.5rem; min-width: 5.5rem;
        display: flex; flex-direction: column; align-items: center; gap: .25rem;
        padding: .75rem .5rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .tr-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .tr-pipe-step.active {
        border-color: #fdba74; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        box-shadow: 0 2px 8px rgba(234, 88, 12, .1);
    }
    .tr-pipe-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; color: #9ca3af;
    }
    .tr-pipe-step.active .tr-pipe-icon { color: #ea580c; border-color: #fed7aa; background: #fff; }
    .tr-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .tr-pipe-step.active .tr-pipe-count { color: #c2410c; }
    .tr-pipe-label { font-size: .65rem; font-weight: 600; color: #9ca3af; text-align: center; }
    .tr-pipe-step.active .tr-pipe-label { color: #ea580c; }

    .tr-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .tr-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .tr-badge-green  { background: #dcfce7; color: #15803d; } .tr-badge-green::before  { background: #22c55e; }
    .tr-badge-rose   { background: #ffe4e6; color: #be123c; } .tr-badge-rose::before   { background: #f43f5e; }
    .tr-badge-amber  { background: #fef3c7; color: #b45309; } .tr-badge-amber::before  { background: #f59e0b; }
    .tr-badge-blue   { background: #dbeafe; color: #1d4ed8; } .tr-badge-blue::before   { background: #3b82f6; }
    .tr-badge-indigo { background: #e0e7ff; color: #4338ca; } .tr-badge-indigo::before { background: #6366f1; }
    .tr-badge-slate  { background: #f1f5f9; color: #475569; } .tr-badge-slate::before  { background: #94a3b8; }

    .tr-route {
        display: flex; flex-wrap: wrap; align-items: center; gap: .4rem;
        font-size: .78rem; min-width: 0;
    }
    .tr-route-node {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .25rem .55rem; border-radius: 8px;
        font-weight: 600; max-width: 11rem;
    }
    .tr-route-node span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tr-route-node-wh { background: #d1fae5; color: #047857; }
    .tr-route-node-sh { background: #e0e7ff; color: #4338ca; }
    .tr-route-arrow { color: #d1d5db; font-size: .65rem; flex-shrink: 0; }

    .tr-ref {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .78rem; font-weight: 700; color: #4338ca;
        text-decoration: none;
    }
    .tr-ref:hover { color: #ea580c; }

    .tr-index-row { cursor: pointer; transition: background .12s; }
    .tr-index-row:hover { background: #fffbf5; }

    .tr-type-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 600; color: #6b7280;
    }

    .tr-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .tr-index-hint {
        display: flex; align-items: center; gap: .5rem;
        padding: .65rem 1rem; border-top: 1px solid #f3f4f6;
        font-size: .75rem; color: #6b7280; background: #fafafa;
    }
    .tr-index-hint i { color: #ea580c; }

    .tr-line-warn { border-color: #fecaca !important; background: #fffbfb; }

    .tr-show-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) { .tr-show-grid { grid-template-columns: 1fr; } }

    .tr-workflow-track {
        display: flex; align-items: flex-start; gap: 0;
        overflow-x: auto; padding: .25rem 0;
    }
    .tr-workflow-step {
        flex: 1; min-width: 5.5rem; text-align: center; position: relative;
    }
    .tr-workflow-step:not(:last-child)::after {
        content: ''; position: absolute; top: 1rem; left: 50%; width: 100%; height: 2px;
        background: #e5e7eb; z-index: 0;
    }
    .tr-workflow-step.done:not(:last-child)::after { background: #fdba74; }
    .tr-workflow-dot {
        width: 2rem; height: 2rem; border-radius: 50%; margin: 0 auto .4rem;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: 2px solid #e5e7eb; background: #fff;
        color: #9ca3af; position: relative; z-index: 1;
    }
    .tr-workflow-step.done .tr-workflow-dot {
        border-color: #ff6b35; background: #fff7ed; color: #ea580c;
    }
    .tr-workflow-step.current .tr-workflow-dot {
        border-color: #ff6b35; background: #ff6b35; color: #fff;
        box-shadow: 0 0 0 4px rgba(255,107,53,.15);
    }
    .tr-workflow-label {
        font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #9ca3af; line-height: 1.3;
    }
    .tr-workflow-step.current .tr-workflow-label { color: #ea580c; }
    .tr-workflow-step.done .tr-workflow-label { color: #6b7280; }
    form.tr-workflow-step-action { margin: 0; padding: 0; border: none; background: transparent; }
    .tr-workflow-step-btn {
        display: block; width: 100%; margin: 0; padding: 0; border: none; background: transparent;
        cursor: pointer; font: inherit; text-align: center;
    }
    .tr-workflow-step-btn:focus-visible .tr-workflow-dot {
        outline: 2px solid #ea580c; outline-offset: 2px;
    }
    .tr-workflow-step-action.current .tr-workflow-dot {
        border-color: #ff6b35; background: #ff6b35; color: #fff;
        box-shadow: 0 0 0 4px rgba(255,107,53,.15);
    }
    .tr-workflow-step-action:hover .tr-workflow-dot {
        background: #ea580c; border-color: #ea580c;
    }

    .tr-show-workflow {
        display: flex; flex-wrap: wrap; gap: .5rem;
    }
    .tr-show-step {
        flex: 1 1 5rem; min-width: 4.5rem;
        display: flex; flex-direction: column; align-items: center; gap: .3rem;
        padding: .65rem .4rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        opacity: .55;
    }
    .tr-show-step.done { opacity: 1; background: #f0fdf4; border-color: #bbf7d0; }
    .tr-show-step.current { opacity: 1; background: #fff7ed; border-color: #fdba74; box-shadow: 0 2px 8px rgba(234,88,12,.12); }
    .tr-show-step-icon {
        width: 1.65rem; height: 1.65rem; border-radius: 50%;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; color: #9ca3af;
    }
    .tr-show-step.done .tr-show-step-icon { color: #15803d; border-color: #bbf7d0; }
    .tr-show-step.current .tr-show-step-icon { color: #ea580c; border-color: #fed7aa; }
    .tr-show-step-label { font-size: .62rem; font-weight: 700; color: #6b7280; text-align: center; text-transform: uppercase; letter-spacing: .04em; }
    .tr-show-step.current .tr-show-step-label { color: #c2410c; }
    button.tr-show-step-action {
        font: inherit; width: 100%;
    }
    button.tr-show-step-action:focus-visible {
        outline: 2px solid #ea580c; outline-offset: 2px;
    }

    .tr-show-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1.15rem; border-radius: 10px;
        background: #fff7ed; border: 1px solid #fed7aa;
    }

    .tr-banner {
        border-radius: 10px; border: 1px solid; padding: .75rem 1rem;
        font-size: .82rem; display: flex; align-items: center; gap: .5rem;
    }
    .tr-banner-info { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }

    /* ── Receive form ── */
    .tr-receive-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em;
        background: #d1fae5; color: #047857;
    }
    .tr-receive-badge::before {
        content: ''; width: 6px; height: 6px; border-radius: 50%; background: #10b981;
    }

    .tr-receive-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1.1rem; border-radius: 10px;
        font-size: .82rem; color: #065f46; line-height: 1.5;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0;
    }
    .tr-receive-banner i { margin-top: .15rem; flex-shrink: 0; color: #059669; }

    .tr-receive-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .tr-receive-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .65rem; padding: .75rem 1rem;
        background: #fafafa; border-bottom: 1px solid #f3f4f6;
    }
    .tr-receive-toolbar-actions { display: flex; flex-wrap: wrap; gap: .4rem; }

    .tr-receive-qty-input { max-width: 7rem; text-align: right; font-variant-numeric: tabular-nums; }
    .tr-receive-qty-input.has-damage { border-color: #fdba74; background: #fffbeb; }

    .tr-receive-good {
        display: inline-flex; align-items: center; justify-content: flex-end;
        min-width: 3.5rem; font-size: .85rem; font-weight: 700;
        font-variant-numeric: tabular-nums; color: #15803d;
    }
    .tr-receive-good.warn { color: #b45309; }
    .tr-receive-good.error { color: #be123c; }

    .tr-receive-summary {
        display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;
        padding: .85rem 1rem; background: #f9fafb; border-top: 1px solid #f3f4f6;
        font-size: .8rem;
    }
    .tr-receive-summary-stat strong {
        font-size: 1rem; font-weight: 800; color: #111827;
        font-variant-numeric: tabular-nums;
    }
    .tr-receive-summary-stat span { display: block; font-size: .65rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; }

    .tr-receive-transfer-card {
        border: 1px dashed #bfdbfe;
        background: linear-gradient(135deg, #eff6ff, #f0f9ff);
        border-radius: 10px; padding: .85rem 1rem; margin-bottom: 1rem;
    }
    .tr-receive-transfer-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af; margin-bottom: .35rem;
    }

    .tr-receive-row-warn td { background: #fffbfb; }
</style>
