<style>
    .sp-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .sp-pipeline { display: flex; flex-wrap: wrap; gap: .5rem; }
    .sp-pipe-step {
        flex: 1 1 5rem; min-width: 5rem;
        display: flex; flex-direction: column; align-items: center; gap: .25rem;
        padding: .75rem .5rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        text-decoration: none; color: inherit;
    }
    .sp-pipe-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; color: #6366f1;
    }
    .sp-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .sp-pipe-label { font-size: .65rem; font-weight: 600; color: #9ca3af; text-align: center; line-height: 1.2; }

    .sp-spend-chart { display: flex; align-items: flex-end; gap: .5rem; height: 120px; padding-top: .5rem; }
    .sp-spend-bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .35rem; min-width: 0; }
    .sp-spend-bar {
        width: 100%; max-width: 3rem; border-radius: 6px 6px 2px 2px;
        background: linear-gradient(180deg, #818cf8, #6366f1);
        min-height: 4px; transition: height .2s;
    }
    .sp-spend-label { font-size: .6rem; color: #9ca3af; font-weight: 600; text-align: center; }
    .sp-spend-value { font-size: .58rem; color: #6b7280; text-align: center; }

    .sp-type-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .2rem .6rem;
        font-size: .68rem; font-weight: 700;
    }
    .sp-type-local { background: #ecfdf5; color: #047857; }
    .sp-type-import { background: #eff6ff; color: #1d4ed8; }

    .sp-index-row { cursor: pointer; transition: background .12s; }
    .sp-index-row:hover { background: #faf5ff; }

    .sp-score-ring {
        display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem;
        border-radius: 12px; background: linear-gradient(135deg, #faf5ff, #eef2ff);
        border: 1px solid #e0e7ff;
    }
    .sp-score-icon {
        width: 3rem; height: 3rem; border-radius: 50%;
        background: #fff; border: 2px solid #c7d2fe;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #6366f1;
    }
</style>
