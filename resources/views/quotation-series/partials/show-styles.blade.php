{{-- Shared styles for quotation series show page --}}
<style>
    .qs-show-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .qs-workflow-track {
        display: flex; align-items: flex-start; gap: 0;
        overflow-x: auto; padding: .25rem 0;
    }
    .qs-workflow-step {
        flex: 1; min-width: 5.5rem; text-align: center; position: relative;
    }
    .qs-workflow-step:not(:last-child)::after {
        content: ''; position: absolute; top: 1rem; left: 50%; width: 100%; height: 2px;
        background: #e5e7eb; z-index: 0;
    }
    .qs-workflow-step.done:not(:last-child)::after { background: #fdba74; }
    .qs-workflow-dot {
        width: 2rem; height: 2rem; border-radius: 50%; margin: 0 auto .4rem;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: 2px solid #e5e7eb; background: #fff;
        color: #9ca3af; position: relative; z-index: 1;
    }
    .qs-workflow-step.done .qs-workflow-dot {
        border-color: #ff6b35; background: #fff7ed; color: #ea580c;
    }
    .qs-workflow-step.current .qs-workflow-dot {
        border-color: #ff6b35; background: #ff6b35; color: #fff;
        box-shadow: 0 0 0 4px rgba(255,107,53,.15);
    }
    .qs-workflow-label {
        font-size: .62rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #9ca3af; line-height: 1.3;
    }
    .qs-workflow-step.current .qs-workflow-label { color: #ea580c; }
    .qs-workflow-step.done .qs-workflow-label { color: #6b7280; }

    .qs-tab-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 1.15rem; height: 1.15rem; padding: 0 .3rem;
        border-radius: 9999px; background: #f3f4f6; color: #6b7280;
        font-size: .62rem; font-weight: 700; margin-left: .25rem;
    }
    .mi-tab-bar button.active .qs-tab-badge { background: #ffedd5; color: #c2410c; }

    .qs-section-head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem;
    }
    .qs-section-title {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 700; color: #111827;
    }
    .qs-section-title i { color: #9ca3af; font-size: .8rem; }
    .qs-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .qs-phase-banner {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem;
        font-size: .8rem; line-height: 1.5;
    }
    .qs-phase-banner i { margin-top: .15rem; flex-shrink: 0; }
    .qs-phase-banner-violet { background: #f5f3ff; border: 1px solid #ddd6fe; color: #5b21b6; }
    .qs-phase-banner-amber  { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .qs-phase-banner-blue   { background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af; }
    .qs-phase-banner-green  { background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; }

    .qs-product-picker {
        border: 1px solid #f0f0f0; border-radius: 10px; overflow: hidden;
    }
    .qs-product-row {
        display: flex; flex-wrap: wrap; align-items: center; gap: .75rem;
        padding: .75rem 1rem; border-bottom: 1px solid #f9fafb;
        transition: background .12s;
    }
    .qs-product-row:hover { background: #fafafa; }
    .qs-product-row:last-child { border-bottom: none; }
    .qs-product-row.selected { background: #fff7ed; }
    .qs-product-icon {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        background: #f3f4f6; color: #6b7280;
        display: flex; align-items: center; justify-content: center; font-size: .7rem;
    }
    .qs-product-row.selected .qs-product-icon { background: #ffedd5; color: #ea580c; }
    .qs-picker-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .75rem 1rem; background: #fafafa; border-bottom: 1px solid #f0f0f0;
    }
    .qs-picker-count {
        font-size: .72rem; font-weight: 600; color: #6b7280;
    }
    .qs-picker-count strong { color: #ea580c; }

    .qs-empty {
        text-align: center; padding: 2.5rem 1.5rem;
    }
    .qs-empty-icon {
        width: 3rem; height: 3rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin: 0 auto .85rem;
    }

    .qs-action-bar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.25rem; background: #fafafa; border-top: 1px solid #f3f4f6;
        border-radius: 0 0 12px 12px;
    }

    .qs-timeline { display: flex; flex-direction: column; gap: 0; }
    .qs-timeline-item {
        display: flex; gap: 1rem; padding-bottom: 1.25rem; position: relative;
    }
    .qs-timeline-item:not(:last-child)::before {
        content: ''; position: absolute; left: .95rem; top: 2rem; bottom: 0;
        width: 2px; background: #e5e7eb;
    }
    .qs-timeline-item.done:not(:last-child)::before { background: #fed7aa; }
    .qs-timeline-dot {
        width: 1.9rem; height: 1.9rem; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; border: 2px solid #e5e7eb; background: #fff; color: #9ca3af;
        position: relative; z-index: 1;
    }
    .qs-timeline-item.done .qs-timeline-dot { border-color: #ff6b35; background: #fff7ed; color: #ea580c; }
    .qs-timeline-item.current .qs-timeline-dot { border-color: #ff6b35; background: #ff6b35; color: #fff; }
    .qs-timeline-body { flex: 1; min-width: 0; padding-top: .15rem; }
    .qs-timeline-title { font-size: .84rem; font-weight: 700; color: #374151; }
    .qs-timeline-item.current .qs-timeline-title { color: #ea580c; }
    .qs-timeline-desc { font-size: .75rem; color: #9ca3af; margin-top: .15rem; }

    .qs-link-card {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .85rem 1rem; border: 1px solid #f0f0f0; border-radius: 10px;
        text-decoration: none; color: inherit; transition: all .15s;
    }
    .qs-link-card:hover { border-color: #fed7aa; background: #fffbeb; }
    .qs-link-card--voided {
        opacity: .72;
        background: #fafafa;
        border-color: #e5e7eb;
    }
    .qs-link-card--voided:hover {
        border-color: #fecaca;
        background: #fff1f2;
    }
    .qs-link-card--voided .qs-link-card-title {
        text-decoration: line-through;
        color: #9ca3af;
    }
    .qs-link-card--voided .qs-link-card-icon {
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
    }
    .qs-link-card-title { font-size: .875rem; font-weight: 600; color: #111827; line-height: 1.3; }
    .qs-grn-badge-voided {
        display: inline-flex; align-items: center; gap: .25rem;
        font-size: .62rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: #be123c;
        background: #ffe4e6; border-radius: 9999px; padding: .12rem .45rem;
    }
    .qs-link-card-icon {
        width: 2.25rem; height: 2.25rem; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; font-size: .85rem;
    }

    .qs-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 1024px) { .qs-grid-2 { grid-template-columns: 1fr; } }

    .qs-workflow-grid { align-items: start; }
    .qs-workflow-actions-card .mi-card-head { padding-bottom: .65rem; }
    .qs-workflow-actions {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        padding: 0 1.25rem 1.35rem;
    }
    .qs-workflow-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1rem 1.15rem;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
        background: #fff;
    }
    .qs-workflow-action-body { min-width: 0; flex: 1; }
    .qs-workflow-action-title {
        font-size: .875rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.35;
    }
    .qs-workflow-action-desc {
        font-size: .75rem;
        color: #6b7280;
        margin-top: .2rem;
        line-height: 1.45;
    }
    .qs-workflow-action--primary {
        border-color: #fed7aa;
        background: linear-gradient(135deg, #fffbeb, #fff7ed);
    }
    .qs-workflow-action--transit {
        border-color: #cffafe;
        background: #f0fdfa;
    }
    .qs-workflow-action--close {
        border-color: #e5e7eb;
        background: #fafafa;
    }
    .qs-workflow-action.is-disabled {
        border-color: #e5e7eb;
        background: #f9fafb;
    }
    .qs-workflow-action.is-disabled .qs-workflow-action-title { color: #6b7280; }
    .qs-workflow-action.is-disabled .qs-workflow-action-desc { color: #9ca3af; }
    .qs-workflow-card-body { padding: 1rem 1.25rem 1.25rem; }
    .qs-workflow-timeline-body { padding: 1.25rem 1.35rem 1.5rem; }

    .qs-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .qs-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .qs-badge-violet  { background: #f3e8ff; color: #7c3aed; } .qs-badge-violet::before  { background: #8b5cf6; }
    .qs-badge-amber   { background: #fef3c7; color: #b45309; } .qs-badge-amber::before   { background: #f59e0b; }
    .qs-badge-orange  { background: #ffedd5; color: #c2410c; } .qs-badge-orange::before  { background: #ff6b35; }
    .qs-badge-blue    { background: #dbeafe; color: #1d4ed8; } .qs-badge-blue::before    { background: #3b82f6; }
    .qs-badge-indigo  { background: #e0e7ff; color: #4338ca; } .qs-badge-indigo::before  { background: #6366f1; }
    .qs-badge-cyan    { background: #cffafe; color: #0e7490; } .qs-badge-cyan::before    { background: #06b6d4; }
    .qs-badge-green   { background: #dcfce7; color: #15803d; } .qs-badge-green::before   { background: #22c55e; }
    .qs-badge-slate   { background: #f1f5f9; color: #475569; } .qs-badge-slate::before   { background: #94a3b8; }
    .qs-badge-red     { background: #fee2e2; color: #b91c1c; } .qs-badge-red::before     { background: #ef4444; }

    .qs-type-local  { background: #ecfdf5 !important; color: #047857 !important; }
    .qs-type-import { background: #eff6ff !important; color: #1d4ed8 !important; }

    .qs-picker-card { display: flex; flex-direction: column; }
    .qs-search-results {
        max-height: 280px; overflow-y: auto;
        border-bottom: 1px solid #f3f4f6;
    }
    .qs-search-result {
        display: flex; align-items: center; gap: .75rem;
        width: 100%; padding: .75rem 1rem;
        border: none; border-bottom: 1px solid #f9fafb;
        background: #fff; text-align: left; cursor: pointer;
        transition: background .12s;
    }
    .qs-search-result:hover:not(:disabled) { background: #fff7ed; }
    .qs-search-result:disabled { cursor: default; opacity: .65; }
    .qs-search-result.is-added { background: #fafafa; }
    .qs-search-add {
        width: 1.75rem; height: 1.75rem; border-radius: 50%;
        background: #ffedd5; color: #ea580c;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem; flex-shrink: 0;
    }
    .qs-search-added {
        font-size: .65rem; font-weight: 700; color: #9ca3af;
        text-transform: uppercase; letter-spacing: .04em; flex-shrink: 0;
    }
    .qs-basket { border-top: 1px solid #f3f4f6; }
    .qs-basket-list { max-height: 220px; overflow-y: auto; }
    .qs-basket-row {
        display: flex; align-items: center; gap: .75rem;
        padding: .75rem 1rem; border-bottom: 1px solid #f9fafb;
    }
    .qs-basket-row:last-child { border-bottom: none; }

    .qs-summary-locked {
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .qs-summary-body.qs-summary-stale {
        opacity: 0.55;
        filter: grayscale(0.15);
    }

    .qs-packets-field,
    .qs-lockable-field {
        position: relative;
        display: inline-block;
        width: 5.25rem;
        vertical-align: middle;
    }
    .qs-lockable-field--wide { width: 6.25rem; }
    .qs-packets-input,
    .qs-lockable-input {
        width: 100%;
        padding-right: 1.85rem !important;
    }
    .qs-lockable-input:read-only {
        background: #f9fafb;
        color: #6b7280;
        cursor: default;
    }
    .qs-lockable-field.is-override .qs-lockable-input {
        background: #fff;
        color: #111827;
    }
    .qs-packets-lock,
    .qs-lockable-toggle {
        position: absolute;
        right: 1px;
        top: 1px;
        bottom: 1px;
        width: 1.65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        color: #9ca3af;
        cursor: pointer;
        border-radius: 0 5px 5px 0;
        font-size: .68rem;
        transition: color .12s, background .12s;
    }
    .qs-packets-lock:hover,
    .qs-lockable-toggle:hover {
        color: #ea580c;
        background: #fff7ed;
    }
    .qs-lockable-field.is-override .qs-lockable-toggle {
        color: #ea580c;
    }

    .qs-collapsible-head {
        cursor: pointer;
        user-select: none;
        gap: 1rem;
    }
    .qs-collapsible-head:hover { background: #fafafa; }
    .qs-collapsible-card.is-collapsed .mi-card-head {
        border-bottom: none;
    }

    .qs-order-input { min-width: 5rem; }
    .qs-summary-scroll { max-height: none; }
    @media (min-width: 1280px) {
        .qs-summary-scroll { max-height: 520px; overflow: auto; }
    }
</style>
