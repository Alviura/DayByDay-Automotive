<style>
    .rt-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .rt-pipeline { display: flex; flex-wrap: wrap; gap: .5rem; }
    .rt-pipe-step {
        flex: 1 1 5.5rem; min-width: 5.5rem;
        display: flex; flex-direction: column; align-items: center; gap: .25rem;
        padding: .75rem .5rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .rt-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .rt-pipe-step.active {
        border-color: #fdba74; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        box-shadow: 0 2px 8px rgba(234, 88, 12, .1);
    }
    .rt-pipe-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; color: #9ca3af;
    }
    .rt-pipe-step.active .rt-pipe-icon { color: #ea580c; border-color: #fed7aa; }
    .rt-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .rt-pipe-step.active .rt-pipe-count { color: #c2410c; }
    .rt-pipe-label { font-size: .65rem; font-weight: 600; color: #9ca3af; text-align: center; line-height: 1.2; }
    .rt-pipe-step.active .rt-pipe-label { color: #ea580c; }

    .rt-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .rt-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .rt-badge-green  { background: #dcfce7; color: #15803d; } .rt-badge-green::before  { background: #22c55e; }
    .rt-badge-rose   { background: #ffe4e6; color: #be123c; } .rt-badge-rose::before   { background: #f43f5e; }
    .rt-badge-amber  { background: #fef3c7; color: #b45309; } .rt-badge-amber::before  { background: #f59e0b; }
    .rt-badge-blue   { background: #dbeafe; color: #1d4ed8; } .rt-badge-blue::before   { background: #3b82f6; }
    .rt-badge-slate  { background: #f1f5f9; color: #475569; } .rt-badge-slate::before  { background: #94a3b8; }

    .rt-index-row { cursor: pointer; transition: background .12s; }
    .rt-index-row:hover { background: #fffbf5; }

    .rt-ref { font-family: ui-monospace, monospace; font-size: .78rem; font-weight: 700; color: #374151; }
    .rt-ref:hover { color: #ea580c; }

    .rt-sale-pick {
        display: block; width: 100%; text-align: left; padding: .75rem 1rem;
        border: 1px solid #f0f0f0; border-radius: 10px; background: #fafafa;
        transition: all .12s;
    }
    .rt-sale-pick:hover { border-color: #fed7aa; background: #fff7ed; }
    .rt-sale-pick.active { border-color: #fdba74; background: #fff7ed; box-shadow: 0 0 0 2px rgba(234, 88, 12, .12); }

    .rt-avail-ok { color: #059669; font-size: .75rem; font-weight: 600; }
    .rt-avail-low { color: #dc2626; font-size: .75rem; font-weight: 600; }

    .rt-show-workflow {
        display: flex; flex-wrap: wrap; gap: .5rem; align-items: flex-start;
    }
    .rt-show-step {
        flex: 1 1 4.5rem; display: flex; flex-direction: column; align-items: center; gap: .35rem;
        text-align: center; opacity: .45;
    }
    .rt-show-step.done, .rt-show-step.current { opacity: 1; }
    .rt-show-step-icon {
        width: 2rem; height: 2rem; border-radius: 50%;
        border: 2px solid #e5e7eb; background: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; color: #9ca3af;
    }
    .rt-show-step.done .rt-show-step-icon { border-color: #86efac; background: #dcfce7; color: #16a34a; }
    .rt-show-step.current .rt-show-step-icon { border-color: #fdba74; background: #fff7ed; color: #ea580c; box-shadow: 0 0 0 3px rgba(234, 88, 12, .12); }
    .rt-show-step-label { font-size: .65rem; font-weight: 700; color: #6b7280; line-height: 1.2; }
    .rt-show-step.current .rt-show-step-label { color: #c2410c; }

    .rt-empty-icon {
        width: 3rem; height: 3rem; border-radius: 12px; margin: 0 auto .75rem;
        background: #fff7ed; color: #ea580c;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
</style>
