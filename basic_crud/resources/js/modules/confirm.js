const ensureDialog = () => {
    let dialog = document.querySelector('[data-confirm-dialog]');

    if (dialog) {
        return dialog;
    }

    dialog = document.createElement('div');
    dialog.className = 'app-confirm';
    dialog.dataset.confirmDialog = 'true';
    dialog.hidden = true;
    dialog.innerHTML = `
        <div class="app-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="app-confirm-title">
            <div class="app-confirm__header">
                <h2 class="app-confirm__title" id="app-confirm-title">Konfirmasi tindakan</h2>
            </div>
            <div class="app-confirm__body">
                <p class="app-confirm__text"></p>
            </div>
            <div class="app-confirm__footer">
                <button type="button" class="app-confirm__button app-confirm__button--cancel" data-confirm-cancel>Batal</button>
                <button type="button" class="app-confirm__button app-confirm__button--confirm" data-confirm-accept>Konfirmasi</button>
            </div>
        </div>
    `;

    document.body.append(dialog);

    return dialog;
};

document.addEventListener('DOMContentLoaded', () => {
    const dialog = ensureDialog();
    const title = dialog.querySelector('.app-confirm__title');
    const text = dialog.querySelector('.app-confirm__text');
    const confirmButton = dialog.querySelector('[data-confirm-accept]');
    const cancelButton = dialog.querySelector('[data-confirm-cancel]');

    let onConfirm = null;

    const closeDialog = () => {
        dialog.hidden = true;
        document.body.style.overflow = '';
        onConfirm = null;
        confirmButton?.classList.remove('is-danger');
    };

    const openDialog = ({ heading, body, confirmText, cancelText, variant, confirmAction }) => {
        title.textContent = heading || 'Konfirmasi tindakan';
        text.textContent = body || 'Apakah Anda yakin ingin melanjutkan?';
        confirmButton.textContent = confirmText || 'Lanjutkan';
        cancelButton.textContent = cancelText || 'Batal';
        confirmButton.classList.toggle('is-danger', variant === 'danger');
        onConfirm = confirmAction;
        dialog.hidden = false;
        document.body.style.overflow = 'hidden';
        confirmButton.focus();
    };

    confirmButton?.addEventListener('click', () => {
        const confirmAction = onConfirm;

        closeDialog();
        confirmAction?.();
    });

    cancelButton?.addEventListener('click', closeDialog);

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dialog.hidden) {
            closeDialog();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm]')) {
            return;
        }

        if (form.dataset.confirmBypass === 'true') {
            form.dataset.confirmBypass = 'false';
            return;
        }

        event.preventDefault();

        openDialog({
            heading: form.dataset.confirmTitle,
            body: form.dataset.confirmText,
            confirmText: form.dataset.confirmConfirmText,
            cancelText: form.dataset.confirmCancelText,
            variant: form.dataset.confirmVariant,
            confirmAction: () => {
                form.dataset.confirmBypass = 'true';
                form.submit();
            },
        });
    });
});
