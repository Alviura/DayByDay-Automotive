{{-- Shared styles for purchase order pages --}}
<style>
    .po-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 1100px) { .po-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .po-kpi-grid { grid-template-columns: 1fr; } }

    .po-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .po-pipeline {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (max-width: 900px) { .po-pipeline { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .po-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    .po-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .po-pipe-step:hover { border-color: #bfdbfe; background: #eff6ff; }
    .po-pipe-step.active { border-color: #3b82f6; background: #eff6ff; box-shadow: 0 0 0 1px #3b82f6; }
    .po-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #2563eb; margin-bottom: .35rem;
    }
    .po-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .po-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    .po-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .po-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .po-badge-blue    { background: #dbeafe; color: #1d4ed8; } .po-badge-blue::before    { background: #3b82f6; }
    .po-badge-amber   { background: #fef3c7; color: #b45309; } .po-badge-amber::before   { background: #f59e0b; }
    .po-badge-green   { background: #dcfce7; color: #15803d; } .po-badge-green::before   { background: #22c55e; }
    .po-badge-cyan    { background: #cffafe; color: #0e7490; } .po-badge-cyan::before    { background: #06b6d4; }
    .po-badge-slate   { background: #f1f5f9; color: #475569; } .po-badge-slate::before   { background: #94a3b8; }
    .po-badge-red     { background: #fee2e2; color: #b91c1c; } .po-badge-red::before     { background: #ef4444; }
    .po-badge-indigo  { background: #e0e7ff; color: #4338ca; } .po-badge-indigo::before  { background: #6366f1; }

    .po-row-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #bfdbfe; color: #2563eb;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .po-cell-main { display: flex; align-items: flex-start; gap: .65rem; }
    .po-cost { font-weight: 700; color: #111827; white-space: nowrap; }

    .po-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    .po-section-title {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 700; color: #111827;
    }
    .po-section-title i { color: #9ca3af; font-size: .8rem; }
    .po-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .po-delivery-track {
        display: flex; align-items: flex-start; gap: 0;
        overflow-x: auto; padding: .25rem 0;
    }
    .po-delivery-step {
        flex: 1; min-width: 5rem; text-align: center; position: relative;
    }
    .po-delivery-step:not(:last-child)::after {
        content: ''; position: absolute; top: 1rem; left: 50%; width: 100%; height: 2px;
        background: #e5e7eb; z-index: 0;
    }
    .po-delivery-step.done:not(:last-child)::after { background: #93c5fd; }
    .po-delivery-dot {
        width: 2rem; height: 2rem; border-radius: 50%; margin: 0 auto .4rem;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: 2px solid #e5e7eb; background: #fff;
        color: #9ca3af; position: relative; z-index: 1;
    }
    .po-delivery-step.done .po-delivery-dot { border-color: #3b82f6; background: #eff6ff; color: #2563eb; }
    .po-delivery-step.current .po-delivery-dot { border-color: #3b82f6; background: #3b82f6; color: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,.15); }
    .po-delivery-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; }
    .po-delivery-step.current .po-delivery-label { color: #2563eb; }

    .po-progress {
        height: .35rem; border-radius: 9999px; background: #f3f4f6; overflow: hidden; min-width: 4rem;
    }
    .po-progress-bar { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #3b82f6, #22c55e); }

    .po-link-card {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border: 1px solid #f0f0f0; border-radius: 10px;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .po-link-card:hover { border-color: #bfdbfe; background: #eff6ff; }
    .po-link-card-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }
</style>
