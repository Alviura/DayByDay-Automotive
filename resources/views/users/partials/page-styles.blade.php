<style>
    .usr-page .mi-page-icon {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
    }

    .usr-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .usr-pipeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(7rem, 1fr));
        gap: .5rem;
    }
    .usr-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .usr-pipe-step:hover { border-color: #c7d2fe; background: #eef2ff; }
    .usr-pipe-step.active { border-color: #6366f1; background: #eef2ff; box-shadow: 0 0 0 1px #6366f1; }
    .usr-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #6366f1; margin-bottom: .35rem;
    }
    .usr-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .usr-pipe-label {
        font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem;
        text-transform: uppercase; letter-spacing: .04em;
    }

    .usr-avatar {
        width: 2.35rem; height: 2.35rem; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: 2px solid #fff; box-shadow: 0 1px 3px rgba(99,102,241,.25);
    }
    .usr-avatar.inactive { background: linear-gradient(135deg, #9ca3af, #6b7280); }
    .usr-avatar.lg {
        width: 3.5rem; height: 3.5rem; font-size: 1.1rem; border-radius: 14px;
    }

    .usr-person-cell { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .usr-person-name { font-size: .85rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .usr-person-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .usr-role-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: 6px; border: 1px solid transparent; white-space: nowrap;
    }
    .usr-role-admin      { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .usr-role-shop       { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .usr-role-warehouse  { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .usr-role-attendant  { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .usr-role-default    { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    .usr-loc-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 600; color: #475569;
    }
    .usr-loc-pill i { color: #94a3b8; font-size: .65rem; }
    .usr-loc-empty { color: #d1d5db; font-size: .78rem; }

    .usr-index-row { cursor: pointer; transition: background .12s; }
    .usr-index-row:hover { background: #f5f3ff; }

    .usr-show-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1100px) { .usr-show-grid { grid-template-columns: 1fr; } }

    .usr-show-hero {
        display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    }

    .usr-doc-card {
        background: #fff; border: 1px solid #f0f0f0; border-radius: 12px;
        overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .usr-doc-head {
        padding: 1rem 1.25rem; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex; justify-content: space-between; align-items: center; gap: .75rem;
    }
    .usr-doc-head h2 { font-size: .95rem; font-weight: 700; color: #111827; }
    .usr-doc-head p { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }

    .usr-login-row td { font-size: .8rem; }
    .usr-login-agent {
        max-width: 14rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        color: #9ca3af; font-size: .72rem;
    }
</style>
