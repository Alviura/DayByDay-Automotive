<div
    x-data
    x-cloak
    x-show="$store.confirm.open"
    x-on:keydown.escape.window="$store.confirm.decline()"
    class="ddb-confirm-root"
    style="display: none;"
>
    <div class="ddb-confirm-backdrop" x-on:click="$store.confirm.decline()"></div>

    <div
        class="ddb-confirm-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ddb-confirm-title"
        x-on:click.stop
        x-transition:enter="ddb-confirm-enter"
        x-transition:enter-start="ddb-confirm-enter-from"
        x-transition:enter-end="ddb-confirm-enter-to"
        x-transition:leave="ddb-confirm-leave"
        x-transition:leave-start="ddb-confirm-leave-from"
        x-transition:leave-end="ddb-confirm-leave-to"
    >
        <div class="ddb-confirm-accent" :class="'ddb-confirm-accent--' + $store.confirm.variant"></div>

        <div class="ddb-confirm-body">
            <div class="ddb-confirm-icon-wrap" :class="'ddb-confirm-icon-wrap--' + $store.confirm.variant">
                <i class="fas"
                   :class="{
                       'fa-triangle-exclamation': $store.confirm.variant === 'danger',
                       'fa-circle-question': $store.confirm.variant === 'warning',
                       'fa-circle-info': $store.confirm.variant === 'default',
                   }"></i>
            </div>

            <div class="ddb-confirm-copy">
                <h2 id="ddb-confirm-title" class="ddb-confirm-title" x-text="$store.confirm.title"></h2>
                <p class="ddb-confirm-message" x-text="$store.confirm.message"></p>
            </div>

            <div class="ddb-confirm-actions">
                <button type="button" class="ddb-confirm-btn ddb-confirm-btn-cancel" x-on:click="$store.confirm.decline()">
                    <span x-text="$store.confirm.cancelLabel"></span>
                </button>
                <button
                    type="button"
                    class="ddb-confirm-btn ddb-confirm-btn-confirm"
                    :class="'ddb-confirm-btn-confirm--' + $store.confirm.variant"
                    x-on:click="$store.confirm.accept()"
                >
                    <span x-text="$store.confirm.confirmLabel"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    .ddb-confirm-root {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }

    .ddb-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(17, 17, 19, .62);
        backdrop-filter: blur(4px);
    }

    .ddb-confirm-panel {
        position: relative;
        width: 100%;
        max-width: 420px;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(0, 0, 0, .22), 0 0 0 1px rgba(255, 255, 255, .06);
    }

    .ddb-confirm-accent { height: 4px; }
    .ddb-confirm-accent--default { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
    .ddb-confirm-accent--warning { background: linear-gradient(90deg, #f97316, #f59e0b); }
    .ddb-confirm-accent--danger  { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .ddb-confirm-body {
        padding: 1.5rem 1.5rem 1.35rem;
        text-align: center;
    }

    .ddb-confirm-icon-wrap {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.2rem;
    }
    .ddb-confirm-icon-wrap--default { background: #eef2ff; color: #4f46e5; }
    .ddb-confirm-icon-wrap--warning { background: #fff7ed; color: #ea580c; }
    .ddb-confirm-icon-wrap--danger  { background: #fef2f2; color: #dc2626; }

    .ddb-confirm-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #111827;
        letter-spacing: -.01em;
        margin: 0 0 .45rem;
    }

    .ddb-confirm-message {
        font-size: .875rem;
        line-height: 1.55;
        color: #6b7280;
        margin: 0 0 1.35rem;
    }

    .ddb-confirm-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .65rem;
    }

    .ddb-confirm-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .65rem 1rem;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 700;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background .15s, border-color .15s, transform .1s;
    }
    .ddb-confirm-btn:active { transform: scale(.98); }

    .ddb-confirm-btn-cancel {
        background: #fff;
        border-color: #e5e7eb;
        color: #4b5563;
    }
    .ddb-confirm-btn-cancel:hover { background: #f9fafb; border-color: #d1d5db; }

    .ddb-confirm-btn-confirm--default { background: #4f46e5; color: #fff; }
    .ddb-confirm-btn-confirm--default:hover { background: #4338ca; }

    .ddb-confirm-btn-confirm--warning { background: #f97316; color: #fff; box-shadow: 0 2px 8px rgba(249, 115, 22, .35); }
    .ddb-confirm-btn-confirm--warning:hover { background: #ea580c; }

    .ddb-confirm-btn-confirm--danger { background: #dc2626; color: #fff; box-shadow: 0 2px 8px rgba(220, 38, 38, .3); }
    .ddb-confirm-btn-confirm--danger:hover { background: #b91c1c; }

    .ddb-confirm-enter { transition: opacity .2s ease; }
    .ddb-confirm-enter-from { opacity: 0; }
    .ddb-confirm-enter-to { opacity: 1; }
    .ddb-confirm-leave { transition: opacity .15s ease; }
    .ddb-confirm-leave-from { opacity: 1; }
    .ddb-confirm-leave-to { opacity: 0; }

    .ddb-confirm-panel.ddb-confirm-enter-from,
    .ddb-confirm-panel.ddb-confirm-leave-to {
        opacity: 0;
        transform: scale(.96) translateY(8px);
    }
    .ddb-confirm-panel {
        transition: transform .2s ease, opacity .2s ease;
    }
</style>
