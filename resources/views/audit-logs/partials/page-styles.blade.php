<style>
    .aud-page .mi-page-icon {
        background: linear-gradient(135deg, #475569, #334155);
    }

    .aud-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .aud-pipeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (max-width: 768px) { .aud-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    .aud-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .aud-pipe-step:hover { border-color: #cbd5e1; background: #f8fafc; }
    .aud-pipe-step.active { border-color: #475569; background: #f8fafc; box-shadow: 0 0 0 1px #475569; }
    .aud-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #475569; margin-bottom: .35rem;
    }
    .aud-pipe-step.active .aud-pipe-icon { color: #334155; border-color: #cbd5e1; }
    .aud-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .aud-pipe-label {
        font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem;
        text-transform: uppercase; letter-spacing: .04em;
    }

    .aud-action, .aud-module {
        display: inline-flex; align-items: center; gap: .35rem;
        border-radius: 9999px; padding: .22rem .6rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .aud-action i, .aud-module i { font-size: .58rem; }

    .aud-action-create { background: #dcfce7; color: #15803d; }
    .aud-action-update { background: #dbeafe; color: #1d4ed8; }
    .aud-action-delete { background: #ffe4e6; color: #be123c; }
    .aud-action-default { background: #f1f5f9; color: #475569; }

    .aud-module-amber  { background: #fef3c7; color: #b45309; }
    .aud-module-blue   { background: #dbeafe; color: #1d4ed8; }
    .aud-module-indigo { background: #e0e7ff; color: #4338ca; }
    .aud-module-green  { background: #dcfce7; color: #15803d; }
    .aud-module-rose   { background: #ffe4e6; color: #be123c; }
    .aud-module-purple { background: #f3e8ff; color: #7e22ce; }
    .aud-module-orange { background: #ffedd5; color: #c2410c; }
    .aud-module-slate  { background: #f1f5f9; color: #475569; }

    .aud-actor {
        display: flex; align-items: center; gap: .65rem; min-width: 0;
    }
    .aud-actor-avatar {
        width: 2.1rem; height: 2.1rem; border-radius: 9px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .68rem; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, #64748b, #475569);
        border: 2px solid #fff; box-shadow: 0 1px 3px rgba(71,85,105,.2);
    }
    .aud-actor-avatar.lg {
        width: 2.75rem; height: 2.75rem; font-size: .85rem; border-radius: 12px;
    }
    .aud-actor-name { font-size: .82rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .aud-actor-sub { font-size: .65rem; color: #9ca3af; margin-top: .08rem; }

    .aud-time-main { font-size: .82rem; font-weight: 600; color: #374151; }
    .aud-time-sub { font-size: .65rem; color: #9ca3af; margin-top: .1rem; }

    .aud-ref {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .78rem; font-weight: 700; color: #4338ca;
    }
    .aud-ref.muted { color: #9ca3af; font-weight: 500; }

    .aud-index-row { cursor: pointer; transition: background .12s; }
    .aud-index-row:hover { background: #f8fafc; }

    .aud-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f1f5f9; color: #94a3b8;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }

    /* ── Show page ── */
    .aud-show-top {
        display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;
    }
    .aud-show-identity {
        display: flex; align-items: flex-start; gap: 1rem; min-width: 0;
    }
    .aud-hero-icon {
        width: 3.75rem; height: 3.75rem; border-radius: 16px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem; color: #fff;
        border: 3px solid #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .12);
    }
    .aud-hero-create { background: linear-gradient(135deg, #22c55e, #15803d); }
    .aud-hero-update { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .aud-hero-delete { background: linear-gradient(135deg, #f43f5e, #be123c); }
    .aud-hero-default { background: linear-gradient(135deg, #64748b, #475569); }

    .aud-show-ref {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .85rem; font-weight: 700; color: #4338ca;
    }

    .aud-show-summary {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .9rem 1.15rem; border-radius: 12px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border: 1px solid #e2e8f0; font-size: .84rem; color: #475569; line-height: 1.5;
    }
    .aud-show-summary i { color: #64748b; margin-top: .15rem; flex-shrink: 0; }
    .aud-show-summary strong { color: #1e293b; }

    .aud-show-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1100px) { .aud-show-grid { grid-template-columns: 1fr; } }

    .aud-section-head { padding: 0; }
    .aud-section-title {
        display: flex; align-items: center; gap: .45rem;
        font-size: .9rem; font-weight: 700; color: #111827;
    }
    .aud-section-sub { font-size: .72rem; color: #9ca3af; margin-top: .15rem; }

    .aud-diff-table .aud-cell-old {
        background: #fffbfb; color: #9f1239;
        border-left: 3px solid #fda4af;
    }
    .aud-diff-table .aud-cell-new {
        background: #f7fef9; color: #166534;
        border-left: 3px solid #86efac;
    }
    .aud-diff-table .aud-cell-field {
        font-weight: 600; color: #374151; white-space: nowrap;
    }
    .aud-diff-table td { font-size: .8rem; line-height: 1.45; vertical-align: top; }
    .aud-diff-table .aud-diff-empty { color: #d1d5db; font-style: italic; }

    .aud-payload-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem 1.25rem;
        padding: 1rem 1.15rem;
    }
    @media (max-width: 768px) { .aud-payload-grid { grid-template-columns: 1fr; } }
    .aud-payload-item {
        padding: .75rem .85rem; border-radius: 10px;
        background: #fafafa; border: 1px solid #f3f4f6;
    }
    .aud-payload-label {
        font-size: .65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #9ca3af; margin-bottom: .3rem;
    }
    .aud-payload-value {
        font-size: .82rem; color: #374151; line-height: 1.45; word-break: break-word;
    }

    .aud-sidebar { display: flex; flex-direction: column; }
    .aud-sidebar-hero {
        display: flex; align-items: center; gap: .85rem;
        padding: 1.1rem 1.15rem;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-bottom: 1px solid #cbd5e1;
    }
    .aud-sidebar-hero-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 12px; flex-shrink: 0;
        background: #fff; border: 1px solid #cbd5e1;
        color: #475569; font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(71, 85, 105, .1);
    }
    .aud-sidebar-hero-label {
        font-size: .62rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #64748b;
    }
    .aud-sidebar-hero-title {
        font-size: .95rem; font-weight: 700; color: #1e293b;
        line-height: 1.3; margin-top: .1rem;
    }
    .aud-sidebar-hero-sub { font-size: .72rem; color: #64748b; margin-top: .15rem; }

    .aud-tech-panel {
        margin: .85rem 1.15rem 0;
        padding: .75rem .85rem;
        background: #f9fafb; border: 1px solid #f3f4f6;
        border-radius: 10px; font-size: .72rem; color: #6b7280; line-height: 1.45;
        word-break: break-word;
    }
    .aud-tech-panel strong {
        display: block; font-size: .62rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: .35rem;
    }

    .aud-show-empty {
        padding: 3rem 1.5rem; text-align: center;
    }
</style>
