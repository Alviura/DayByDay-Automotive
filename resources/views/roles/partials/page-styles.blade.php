<style>
    .rol-page .mi-page-icon {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
    }

    .rol-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .rol-avatar {
        width: 2.35rem; height: 2.35rem; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .85rem; color: #fff;
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        border: 2px solid #fff; box-shadow: 0 1px 3px rgba(139,92,246,.25);
    }
    .rol-avatar.admin { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
    .rol-avatar.shop { background: linear-gradient(135deg, #f97316, #ea580c); }
    .rol-avatar.warehouse { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .rol-avatar.attendant { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .rol-avatar.custom { background: linear-gradient(135deg, #64748b, #475569); }

    .rol-person-cell { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .rol-person-name { font-size: .85rem; font-weight: 700; color: #111827; line-height: 1.2; }
    .rol-person-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .rol-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: 6px; border: 1px solid transparent; white-space: nowrap;
    }
    .rol-pill-core { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .rol-pill-custom { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    .rol-count {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .8rem; font-weight: 700; color: #374151;
    }
    .rol-count i { color: #a78bfa; font-size: .72rem; }

    .rol-index-row { cursor: pointer; transition: background .12s; }
    .rol-index-row:hover { background: #faf5ff; }

    .rol-perm-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .75rem; margin-bottom: 1rem;
    }
    .rol-perm-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 1100px) { .rol-perm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px)  { .rol-perm-grid { grid-template-columns: 1fr; } }

    .rol-perm-group {
        border: 1px solid #f0f0f0; border-radius: 12px;
        background: #fafafa; overflow: hidden;
    }
    .rol-perm-group-head {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .75rem .9rem; background: #fff;
        border-bottom: 1px solid #f3f4f6;
    }
    .rol-perm-group-title {
        font-size: .78rem; font-weight: 700; color: #374151;
        text-transform: capitalize;
    }
    .rol-perm-group-toggle {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 600; color: #8b5cf6; cursor: pointer;
    }
    .rol-perm-group-toggle input { accent-color: #8b5cf6; }
    .rol-perm-list {
        padding: .65rem .9rem .85rem;
        display: flex; flex-direction: column; gap: .45rem;
        max-height: 14rem; overflow-y: auto;
    }
    .rol-perm-item {
        display: flex; align-items: flex-start; gap: .5rem;
        font-size: .76rem; color: #4b5563; line-height: 1.35; cursor: pointer;
    }
    .rol-perm-item input { margin-top: .15rem; accent-color: #8b5cf6; flex-shrink: 0; }
    .rol-perm-item code {
        font-size: .7rem; background: #f3f4f6; color: #374151;
        padding: .08rem .3rem; border-radius: 4px;
    }
</style>
