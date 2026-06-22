<style>
    .ap-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .ap-pipeline {
        display: flex; flex-wrap: wrap; gap: .5rem;
    }
    .ap-pipe-step {
        flex: 1 1 5.5rem; min-width: 5.5rem;
        display: flex; flex-direction: column; align-items: center; gap: .25rem;
        padding: .75rem .5rem; border-radius: 10px;
        border: 1px solid #f0f0f0; background: #fafafa;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .ap-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .ap-pipe-step.active {
        border-color: #fdba74; background: linear-gradient(135deg, #fff7ed, #ffedd5);
        box-shadow: 0 2px 8px rgba(234, 88, 12, .1);
    }
    .ap-pipe-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; color: #9ca3af;
    }
    .ap-pipe-step.active .ap-pipe-icon { color: #ea580c; border-color: #fed7aa; background: #fff; }
    .ap-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .ap-pipe-step.active .ap-pipe-count { color: #c2410c; }
    .ap-pipe-label { font-size: .65rem; font-weight: 600; color: #9ca3af; text-align: center; line-height: 1.2; }
    .ap-pipe-step.active .ap-pipe-label { color: #ea580c; }

    .ap-index-row { cursor: pointer; transition: background .12s; }
    .ap-index-row:hover { background: #fffbf5; }

    .ap-preview-meta {
        display: flex; flex-wrap: wrap; gap: .5rem;
        padding: .75rem 1.25rem; border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .ap-preview-meta-item {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .75rem; color: #6b7280;
    }
    .ap-preview-meta-item strong { color: #374151; font-weight: 600; }

    .ap-legacy-note {
        display: flex; align-items: flex-start; gap: .65rem;
        padding: .75rem 1rem; border-radius: 10px;
        background: #fffbeb; border: 1px solid #fde68a;
        font-size: .78rem; color: #92400e;
    }
    .ap-legacy-note i { margin-top: .1rem; color: #d97706; }
</style>
