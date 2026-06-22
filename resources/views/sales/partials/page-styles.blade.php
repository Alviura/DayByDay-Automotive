<style>
    .pos-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }
    .pos-queue-card {
        border: 1px solid #e5e7eb; border-radius: 12px; padding: 1rem;
        background: #fff; transition: all .15s;
    }
    .pos-queue-card:hover { border-color: #fed7aa; background: #fffbf5; }
    .pos-price-hint { font-size: .68rem; color: #9ca3af; }
    .pos-desk-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700;
        background: #fef3c7; color: #b45309;
    }
    .pos-desk-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #f59e0b; }

    /* Sales index */
    .sl-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }
    @media (max-width: 1100px) { .sl-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .sl-kpi-grid { grid-template-columns: 1fr; } }

    .sl-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .sl-analytics {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 1rem;
    }
    @media (max-width: 1024px) { .sl-analytics { grid-template-columns: 1fr; } }

    .sl-chart-card { padding: 1.15rem 1.25rem 1rem; }
    .sl-chart-title {
        font-size: .82rem; font-weight: 700; color: #374151;
        display: flex; align-items: center; gap: .45rem; margin-bottom: .85rem;
    }
    .sl-chart-title i { color: #9ca3af; font-size: .75rem; }
    .sl-chart-wrap { position: relative; height: 220px; }
    .sl-chart-wrap.sm { height: 180px; }

    .sl-pipeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (max-width: 640px) { .sl-pipeline { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

    .sl-pipe-step {
        display: flex; flex-direction: column; align-items: center; text-align: center;
        padding: .75rem .35rem; border-radius: 10px; border: 1px solid #f0f0f0;
        background: #fafafa; text-decoration: none; color: inherit; transition: all .15s;
    }
    .sl-pipe-step:hover { border-color: #fed7aa; background: #fff7ed; }
    .sl-pipe-step.active { border-color: #ff6b35; background: #fff7ed; box-shadow: 0 0 0 1px #ff6b35; }
    .sl-pipe-icon {
        width: 2rem; height: 2rem; border-radius: 8px;
        background: #fff; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; color: #ff6b35; margin-bottom: .35rem;
    }
    .sl-pipe-count { font-size: 1.1rem; font-weight: 800; color: #111827; line-height: 1; }
    .sl-pipe-label { font-size: .62rem; font-weight: 600; color: #6b7280; margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

    .sl-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .22rem .65rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .02em; white-space: nowrap;
    }
    .sl-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .sl-badge-green  { background: #dcfce7; color: #15803d; } .sl-badge-green::before  { background: #22c55e; }
    .sl-badge-amber  { background: #fef3c7; color: #b45309; } .sl-badge-amber::before  { background: #f59e0b; }
    .sl-badge-rose   { background: #ffe4e6; color: #be123c; } .sl-badge-rose::before   { background: #f43f5e; }
    .sl-badge-slate  { background: #f1f5f9; color: #475569; } .sl-badge-slate::before  { background: #94a3b8; }

    .sl-sale-cell { display: flex; align-items: flex-start; gap: .65rem; }
    .sl-sale-icon {
        width: 2.1rem; height: 2.1rem; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        border: 1px solid #fed7aa; color: #ea580c;
        display: flex; align-items: center; justify-content: center; font-size: .75rem;
    }
    .sl-sale-ref {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .82rem; font-weight: 700; color: #111827; text-decoration: none;
    }
    .sl-sale-ref:hover { color: #ea580c; }
    .sl-sale-sub { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }
    .sl-total { font-weight: 800; color: #c2410c; white-space: nowrap; }
    .sl-index-row { cursor: pointer; transition: background .12s; }
    .sl-index-row:hover { background: #fffbf5; }
    .sl-empty-icon {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #f3f4f6; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem; margin: 0 auto 1rem;
    }
    .sl-index-hint {
        display: flex; align-items: center; gap: .5rem;
        padding: .65rem 1rem; border-top: 1px solid #f3f4f6;
        font-size: .75rem; color: #6b7280; background: #fafafa;
    }
    .sl-index-hint i { color: #ea580c; }

    /* ── POS shared ── */
    .pos-page { --pos-accent: #ff6b35; }

    .pos-shop-bar {
        display: flex; flex-wrap: wrap; align-items: center; gap: .75rem;
        padding: .75rem 1rem; border-radius: 12px;
        background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
        border: 1px solid #fed7aa;
    }
    .pos-shop-bar i { color: #ea580c; }
    .pos-shop-bar strong { color: #9a3412; font-size: .875rem; }

    .pos-workflow {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .65rem;
    }
    @media (max-width: 640px) { .pos-workflow { grid-template-columns: 1fr; } }

    .pos-step {
        display: flex; align-items: center; gap: .75rem;
        padding: .85rem 1rem; border-radius: 12px;
        border: 1px solid #f0f0f0; background: #fafafa;
        transition: all .15s;
    }
    .pos-step.active {
        border-color: #fed7aa; background: #fffbf5;
        box-shadow: 0 0 0 1px rgba(255, 107, 53, .15);
    }
    .pos-step.done { border-color: #bbf7d0; background: #f0fdf4; }
    .pos-step-num {
        width: 1.75rem; height: 1.75rem; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem; font-weight: 800;
        background: #fff; border: 2px solid #e5e7eb; color: #6b7280;
    }
    .pos-step.active .pos-step-num { border-color: #ff6b35; color: #ff6b35; background: #fff7ed; }
    .pos-step.done .pos-step-num { border-color: #22c55e; color: #fff; background: #22c55e; }
    .pos-step-title { font-size: .78rem; font-weight: 700; color: #374151; line-height: 1.2; }
    .pos-step-desc { font-size: .65rem; color: #9ca3af; margin-top: .1rem; }

    .pos-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .pos-main-grid { grid-template-columns: 1fr; }
    }

    .pos-order-main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
    .pos-order-side { display: flex; flex-direction: column; gap: 1rem; }
    @media (min-width: 1025px) {
        .pos-order-side { position: sticky; top: 1rem; }
    }

    .pos-search-inline .pos-results {
        max-height: 220px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: .5rem;
    }

    .pos-panel {
        background: #fff; border-radius: 12px;
        border: 1px solid #f0f0f0; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .pos-panel-head {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .9rem 1.15rem; border-bottom: 1px solid #f3f4f6;
        background: #fafafa;
    }
    .pos-panel-title {
        font-size: .82rem; font-weight: 700; color: #374151;
        display: flex; align-items: center; gap: .45rem;
    }
    .pos-panel-title i { color: #9ca3af; font-size: .75rem; }
    .pos-panel-body { padding: 1rem 1.15rem; }

    .pos-search-wrap {
        position: relative;
    }
    .pos-search-wrap > i {
        position: absolute; left: .85rem; top: 50%; transform: translateY(-50%);
        color: #9ca3af; font-size: .8rem; pointer-events: none;
    }
    .pos-search-input {
        width: 100%; padding: .7rem .85rem .7rem 2.35rem;
        border: 1px solid #e5e7eb; border-radius: 10px;
        font-size: .875rem; background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }
    .pos-search-input:focus {
        outline: none; border-color: #fdba74;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, .12);
    }

    .pos-results { margin-top: .85rem; max-height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: .5rem; }
    .pos-product-card {
        display: block; width: 100%; text-align: left;
        padding: .75rem .85rem; border-radius: 10px;
        border: 1px solid #f3f4f6; background: #fff;
        cursor: pointer; transition: all .12s;
    }
    .pos-product-card:hover {
        border-color: #fed7aa; background: #fffbf5;
        transform: translateY(-1px); box-shadow: 0 2px 8px rgba(255, 107, 53, .08);
    }
    .pos-product-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
    .pos-product-sku {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .78rem; font-weight: 700; color: #111827;
    }
    .pos-stock-badge {
        font-size: .62rem; font-weight: 700; padding: .15rem .45rem;
        border-radius: 9999px; white-space: nowrap;
    }
    .pos-stock-ok { background: #dcfce7; color: #15803d; }
    .pos-stock-low { background: #fef3c7; color: #b45309; }
    .pos-stock-out { background: #fee2e2; color: #b91c1c; }
    .pos-product-name { font-size: .75rem; color: #6b7280; margin-top: .2rem; line-height: 1.35; }
    .pos-product-foot {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: .45rem; padding-top: .45rem; border-top: 1px dashed #f3f4f6;
        font-size: .68rem;
    }
    .pos-product-price { font-weight: 700; color: #c2410c; }

    .pos-cart-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 1.35rem; height: 1.35rem; padding: 0 .35rem;
        border-radius: 9999px; background: #ff6b35; color: #fff;
        font-size: .65rem; font-weight: 800;
    }

    .pos-cart-empty {
        text-align: center; padding: 2.5rem 1rem;
    }
    .pos-cart-empty-icon {
        width: 3.25rem; height: 3.25rem; border-radius: 50%;
        background: #f9fafb; color: #d1d5db;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; margin: 0 auto .85rem;
    }

    .pos-qty-wrap {
        display: inline-flex; align-items: center;
        border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;
    }
    .pos-qty-btn {
        width: 1.75rem; height: 1.75rem; border: none; background: #f9fafb;
        color: #6b7280; cursor: pointer; font-size: .7rem;
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
    }
    .pos-qty-btn:hover { background: #fff7ed; color: #ea580c; }
    .pos-qty-input {
        width: 2.75rem; border: none; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;
        text-align: center; font-size: .78rem; font-weight: 600; padding: .25rem;
    }
    .pos-qty-input:focus { outline: none; background: #fffbf5; }

    .pos-totals-panel {
        background: linear-gradient(160deg, #1f2937 0%, #111827 100%);
        border-radius: 12px; padding: 1.15rem 1.25rem; color: #fff;
        border: 1px solid #374151;
    }
    .pos-totals-label {
        font-size: .65rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af;
    }
    .pos-totals-amount {
        font-size: 1.85rem; font-weight: 800; color: #fdba74;
        line-height: 1.1; margin-top: .25rem;
    }
    .pos-totals-rows { margin-top: .85rem; padding-top: .85rem; border-top: 1px solid #374151; }
    .pos-totals-row {
        display: flex; justify-content: space-between; font-size: .78rem;
        color: #d1d5db; margin-bottom: .35rem;
    }
    .pos-totals-row strong { color: #fff; }

    .pos-field-group { display: flex; flex-direction: column; gap: .85rem; }
    .pos-field-group .mi-field-label { margin-bottom: .25rem; }

    .pos-submit-btn {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        width: 100%; padding: .85rem 1rem; margin-top: 1rem;
        border-radius: 10px; border: none; cursor: pointer;
        background: linear-gradient(135deg, #ff6b35, #ea580c);
        color: #fff; font-size: .875rem; font-weight: 700;
        transition: opacity .15s, transform .1s;
        box-shadow: 0 4px 14px rgba(255, 107, 53, .35);
    }
    .pos-submit-btn:hover:not(:disabled) { transform: translateY(-1px); }
    .pos-submit-btn:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

    /* Sale mode toggle */
    .pos-mode-toggle {
        display: flex; align-items: center; justify-content: center; gap: .75rem;
        padding: .65rem 1rem; border-radius: 12px;
        background: #f9fafb; border: 1px solid #f3f4f6;
    }
    .pos-mode-label {
        font-size: .78rem; font-weight: 600; color: #9ca3af;
        transition: color .2s; user-select: none;
        display: flex; align-items: center; gap: .35rem;
    }
    .pos-mode-label.active { color: #111827; }
    .pos-mode-label.active-retail { color: #c2410c; }
    .pos-mode-label.active-fleet { color: #b45309; }

    .pos-mode-switch {
        position: relative; width: 3.25rem; height: 1.75rem;
        flex-shrink: 0; cursor: pointer;
    }
    .pos-mode-switch input {
        position: absolute; opacity: 0; width: 0; height: 0;
    }
    .pos-mode-track {
        position: absolute; inset: 0; border-radius: 9999px;
        background: #e5e7eb; transition: background .25s;
    }
    .pos-mode-switch input:checked + .pos-mode-track {
        background: linear-gradient(135deg, #f59e0b, #ea580c);
    }
    .pos-mode-thumb {
        position: absolute; top: 3px; left: 3px;
        width: 1.35rem; height: 1.35rem; border-radius: 50%;
        background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.18);
        transition: transform .25s cubic-bezier(.4, 0, .2, 1);
    }
    .pos-mode-switch input:checked ~ .pos-mode-thumb {
        transform: translateX(1.5rem);
    }
    .pos-mode-badge {
        font-size: .62rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; padding: .2rem .55rem;
        border-radius: 9999px;
    }
    .pos-mode-badge-retail { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .pos-mode-badge-fleet { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

    .pos-field-required::after { content: ' *'; color: #ef4444; }
    .pos-input-invalid { border-color: #fca5a5 !important; background: #fef2f2 !important; }

    /* Cash desk queue */
    .pos-desk-kpi {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem;
    }
    @media (max-width: 1100px) { .pos-desk-kpi { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px)  { .pos-desk-kpi { grid-template-columns: 1fr; } }

    .pos-queue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem; padding: 1.15rem;
    }

    .pos-queue-item {
        display: block; text-decoration: none; color: inherit;
        border: 1px solid #f0f0f0; border-radius: 12px;
        background: #fff; padding: 1.1rem 1.15rem;
        transition: all .15s; position: relative; overflow: hidden;
    }
    .pos-queue-item::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        background: #f59e0b;
    }
    .pos-queue-item:hover {
        border-color: #fed7aa; background: #fffbf5;
        box-shadow: 0 4px 16px rgba(255, 107, 53, .1);
        transform: translateY(-2px);
    }
    .pos-queue-item.urgent::before { background: #ef4444; }
    .pos-queue-item.warn::before { background: #f59e0b; }
    .pos-queue-item.ok::before { background: #22c55e; }

    .pos-queue-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
    .pos-queue-receipt {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .85rem; font-weight: 800; color: #111827;
    }
    .pos-wait-badge {
        font-size: .62rem; font-weight: 700; padding: .2rem .5rem;
        border-radius: 9999px; white-space: nowrap;
    }
    .pos-wait-ok { background: #dcfce7; color: #15803d; }
    .pos-wait-warn { background: #fef3c7; color: #b45309; }
    .pos-wait-urgent { background: #fee2e2; color: #b91c1c; }

    .pos-queue-customer { font-size: .875rem; font-weight: 600; color: #374151; margin-top: .65rem; }
    .pos-queue-phone { font-size: .72rem; color: #9ca3af; margin-top: .1rem; }
    .pos-queue-meta {
        display: flex; flex-wrap: wrap; gap: .65rem; margin-top: .75rem;
        padding-top: .75rem; border-top: 1px solid #f3f4f6;
        font-size: .72rem; color: #6b7280;
    }
    .pos-queue-meta span { display: inline-flex; align-items: center; gap: .3rem; }
    .pos-queue-meta i { color: #d1d5db; font-size: .65rem; }
    .pos-queue-total {
        margin-top: .75rem; font-size: 1.15rem; font-weight: 800; color: #c2410c;
    }
    .pos-queue-action {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: .85rem; padding-top: .75rem; border-top: 1px dashed #f3f4f6;
        font-size: .75rem; font-weight: 600; color: #ff6b35;
    }

    .pos-desk-empty {
        text-align: center; padding: 3rem 1.5rem;
    }
    .pos-desk-empty-icon {
        width: 4rem; height: 4rem; border-radius: 50%;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #22c55e; font-size: 1.5rem;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
    }

    .pos-desk-layout {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .pos-desk-layout { grid-template-columns: 1fr; }
    }

    .pos-desk-main { min-width: 0; }
    .pos-desk-side { display: flex; flex-direction: column; gap: 1rem; }
    @media (min-width: 1025px) {
        .pos-desk-side { position: sticky; top: 1rem; }
    }

    /* Checkout */
    .pos-checkout-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) { .pos-checkout-grid { grid-template-columns: 1fr; } }

    .pos-customer-banner {
        display: flex; flex-wrap: wrap; gap: 1rem 1.5rem;
        padding: 1rem 1.15rem; border-radius: 12px;
        background: #eff6ff; border: 1px solid #dbeafe;
        font-size: .82rem; color: #1e40af;
    }
    .pos-customer-banner strong { color: #1d4ed8; }
    .pos-customer-banner i { color: #3b82f6; margin-right: .35rem; }

    .pos-price-ok { border-color: #bbf7d0 !important; background: #f0fdf4 !important; }
    .pos-price-bad { border-color: #fecaca !important; background: #fef2f2 !important; }

    .pos-payment-overlay {
        position: fixed; inset: 0; z-index: 50;
        display: flex; align-items: center; justify-content: center;
        background: rgba(17, 24, 39, .55); backdrop-filter: blur(4px);
        padding: 1rem;
    }
    .pos-payment-modal {
        width: 100%; max-width: 28rem;
        background: #fff; border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0,0,0,.2);
        overflow: hidden;
    }
    .pos-payment-head {
        padding: 1.25rem 1.5rem; background: linear-gradient(135deg, #1f2937, #111827);
        color: #fff;
    }
    .pos-payment-head h2 { font-size: 1rem; font-weight: 700; }
    .pos-payment-amount { font-size: 1.75rem; font-weight: 800; color: #fdba74; margin-top: .25rem; }
    .pos-payment-body { padding: 1.25rem 1.5rem 1.5rem; }
    .pos-tender-row {
        padding: .85rem; border-radius: 10px; border: 1px solid #f3f4f6;
        background: #fafafa; margin-bottom: .65rem;
    }
    .pos-change-bar {
        display: flex; justify-content: space-between; align-items: center;
        padding: .75rem 1rem; border-radius: 10px;
        background: #f0fdf4; border: 1px solid #bbf7d0;
        font-size: .82rem; margin: 1rem 0;
    }
    .pos-change-bar strong { color: #15803d; font-size: 1rem; }

    /* Sale show */
    .sl-show-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) { .sl-show-grid { grid-template-columns: 1fr; } }

    .sl-show-side { display: flex; flex-direction: column; gap: 1rem; }
    @media (min-width: 1025px) { .sl-show-side { position: sticky; top: 1rem; } }

    .sl-show-kpi-sub { font-size: .68rem; color: #9ca3af; margin-top: .2rem; }

    .sl-show-banner {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;
        padding: 1rem 1.25rem; border-radius: 12px; border: 1px solid;
    }
    .sl-show-banner-held { background: #fffbeb; border-color: #fde68a; }
    .sl-show-banner-reversed { background: #fff1f2; border-color: #fecdd3; }
    .sl-show-banner-text { font-size: .875rem; color: #374151; }
    .sl-show-banner-text strong { color: #111827; }

    .sl-line-cell { display: flex; align-items: flex-start; gap: .65rem; }
    .sl-line-icon {
        width: 2rem; height: 2rem; border-radius: 8px; flex-shrink: 0;
        background: #f9fafb; border: 1px solid #f3f4f6; color: #9ca3af;
        display: flex; align-items: center; justify-content: center; font-size: .7rem;
    }
    .sl-line-name { font-size: .82rem; font-weight: 600; color: #111827; }
    .sl-line-sku {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .68rem; color: #9ca3af; margin-top: .1rem;
    }

    .sl-detail-card { padding: 1.15rem 1.25rem; }
    .sl-detail-title {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #9ca3af; margin-bottom: .85rem;
        display: flex; align-items: center; gap: .4rem;
    }
    .sl-detail-title i { font-size: .65rem; }
    .sl-detail-list { list-style: none; margin: 0; padding: 0; }
    .sl-detail-list > div {
        display: flex; justify-content: space-between; gap: .75rem;
        padding: .5rem 0; border-bottom: 1px solid #f9fafb;
        font-size: .82rem;
    }
    .sl-detail-list > div:last-child { border-bottom: none; }
    .sl-detail-list dt { color: #6b7280; flex-shrink: 0; }
    .sl-detail-list dd { font-weight: 600; color: #374151; text-align: right; word-break: break-word; margin: 0; }

    .sl-pay-pill {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .65rem .85rem; border-radius: 10px;
        background: #f9fafb; border: 1px solid #f3f4f6; margin-bottom: .5rem;
    }
    .sl-pay-pill:last-child { margin-bottom: 0; }
    .sl-pay-method {
        display: flex; align-items: center; gap: .5rem;
        font-size: .82rem; font-weight: 600; color: #374151;
    }
    .sl-pay-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 7px;
        display: flex; align-items: center; justify-content: center; font-size: .68rem;
    }
    .sl-pay-cash  { background: #dcfce7; color: #15803d; }
    .sl-pay-mpesa { background: #dbeafe; color: #1d4ed8; }
    .sl-pay-card  { background: #f3e8ff; color: #7c3aed; }
    .sl-pay-bank  { background: #fef3c7; color: #b45309; }
    .sl-pay-other { background: #f1f5f9; color: #475569; }
    .sl-pay-amount { font-weight: 800; color: #111827; font-size: .875rem; }
    .sl-pay-ref { font-size: .68rem; color: #9ca3af; margin-top: .1rem; }

    .sl-show-actions { display: flex; flex-direction: column; gap: .5rem; }
    .sl-show-actions .mi-btn-orange,
    .sl-show-actions .mi-btn-ghost { justify-content: center; width: 100%; }
</style>
