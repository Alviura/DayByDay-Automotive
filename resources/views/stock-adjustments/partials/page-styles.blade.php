<style>
    .adj-section {
        padding: 1.25rem 1.35rem;
        border-top: 1px solid #f3f4f6;
    }
    .adj-section:first-child { border-top: none; }
    .adj-section-head {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
        margin-bottom: 1rem;
    }
    .adj-section-title {
        font-size: .875rem; font-weight: 700; color: #111827;
        display: flex; align-items: center; gap: .45rem;
    }
    .adj-section-title i { color: #9ca3af; font-size: .78rem; }
    .adj-section-sub { font-size: .75rem; color: #9ca3af; margin-top: .2rem; }

    .adj-lines-wrap {
        border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;
        background: #fafafa;
    }
    .adj-lines-toolbar {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .75rem 1rem; background: #fff; border-bottom: 1px solid #f3f4f6;
    }
    .adj-lines-table { width: 100%; border-collapse: collapse; background: #fff; }
    .adj-lines-table th {
        padding: .55rem .85rem; text-align: left;
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: #9ca3af;
        background: #f9fafb; border-bottom: 1px solid #f3f4f6;
    }
    .adj-lines-table td {
        padding: .65rem .85rem; vertical-align: middle;
        border-bottom: 1px solid #f3f4f6; font-size: .82rem;
    }
    .adj-lines-table tbody tr:last-child td { border-bottom: none; }
    .adj-lines-table tbody tr:hover { background: #fffbf5; }

    .adj-line-num {
        width: 1.75rem; height: 1.75rem; border-radius: 8px;
        background: #f3f4f6; color: #6b7280;
        font-size: .72rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .adj-system-qty {
        display: inline-flex; align-items: center; gap: .35rem;
        font-weight: 700; color: #374151; min-width: 3rem;
    }
    .adj-system-qty.loading { color: #9ca3af; font-weight: 500; }
    .adj-system-qty i { font-size: .65rem; }

    .adj-variance {
        display: inline-flex; align-items: center; gap: .3rem;
        border-radius: 9999px; padding: .2rem .55rem;
        font-size: .72rem; font-weight: 700; white-space: nowrap;
    }
    .adj-variance-up { background: #dcfce7; color: #15803d; }
    .adj-variance-down { background: #ffe4e6; color: #be123c; }
    .adj-variance-zero { background: #f3f4f6; color: #6b7280; }
    .adj-variance-pending { background: #f9fafb; color: #d1d5db; }

    .adj-lines-empty {
        padding: 2.5rem 1rem; text-align: center; color: #9ca3af;
        font-size: .82rem; background: #fff;
    }
    .adj-lines-empty i { font-size: 1.5rem; color: #e5e7eb; margin-bottom: .65rem; display: block; }

    .adj-summary-bar {
        display: flex; flex-wrap: wrap; gap: 1rem;
        padding: .75rem 1rem; background: #fff7ed;
        border-top: 1px solid #fed7aa; font-size: .78rem; color: #9a3412;
    }
    .adj-summary-bar strong { color: #7c2d12; }
</style>
