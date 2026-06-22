document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', {
        open: false,
        title: 'Confirm',
        message: '',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        variant: 'default',
        _resolve: null,

        ask(options = {}) {
            return new Promise((resolve) => {
                this.title = options.title ?? inferTitle(options.message, options.variant);
                this.message = options.message ?? 'Are you sure?';
                this.confirmLabel = options.confirmLabel ?? 'Confirm';
                this.cancelLabel = options.cancelLabel ?? 'Cancel';
                this.variant = options.variant ?? inferVariant(this.message);
                this._resolve = resolve;
                this.open = true;
            });
        },

        accept() {
            this._resolve?.(true);
            this._finish();
        },

        decline() {
            this._resolve?.(false);
            this._finish();
        },

        _finish() {
            this.open = false;
            this._resolve = null;
        },
    });
});

function inferVariant(message, explicit) {
    if (explicit) {
        return explicit;
    }

    const text = String(message ?? '').toLowerCase();

    if (/delete|void|reverse|discard|archive|abandon|remove this line/.test(text)) {
        return 'danger';
    }

    if (/issue|confirm|submit|dispatch|lock|generate|close|proceed|clear all/.test(text)) {
        return 'warning';
    }

    return 'default';
}

function inferTitle(message, variant) {
    if (variant === 'danger') {
        return 'Are you sure?';
    }

    if (variant === 'warning') {
        return 'Please confirm';
    }

    const text = String(message ?? '').toLowerCase();

    if (text.includes('submit')) {
        return 'Submit for approval?';
    }

    return 'Confirm action';
}

window.appConfirm = (message, options = {}) => {
    const normalized = typeof message === 'object'
        ? message
        : { ...options, message };

    return window.Alpine.store('confirm').ask(normalized);
};

document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const message = form.dataset.confirm;

    if (!message || form.dataset.confirmBypass === '1') {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const confirmed = await window.appConfirm({
        message,
        title: form.dataset.confirmTitle || undefined,
        confirmLabel: form.dataset.confirmLabel || undefined,
        cancelLabel: form.dataset.confirmCancel || undefined,
        variant: form.dataset.confirmVariant || undefined,
    });

    if (!confirmed) {
        return;
    }

    form.dataset.confirmBypass = '1';

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }

    delete form.dataset.confirmBypass;
}, true);
