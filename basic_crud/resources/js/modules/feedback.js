const icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-exclamation-octagon-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill',
};

const titles = {
    success: 'Berhasil',
    error: 'Terjadi kesalahan',
    warning: 'Perhatian',
    info: 'Informasi',
};

const getToastRoot = () => {
    let root = document.querySelector('[data-toast-root]');

    if (root) {
        return root;
    }

    root = document.createElement('div');
    root.className = 'app-toast-stack';
    root.dataset.toastRoot = 'true';
    root.setAttribute('aria-live', 'polite');
    root.setAttribute('aria-atomic', 'true');

    document.body.append(root);

    return root;
};

const removeToast = (toast) => {
    toast.classList.remove('is-visible');

    window.setTimeout(() => {
        toast.remove();
    }, 180);
};

const createToast = ({ type = 'info', message }) => {
    const toast = document.createElement('article');
    toast.className = `app-toast app-toast--${type}`;
    toast.setAttribute('role', 'status');

    toast.innerHTML = `
        <div class="app-toast__icon" aria-hidden="true">
            <i class="bi ${icons[type] ?? icons.info}"></i>
        </div>
        <div class="app-toast__content">
            <p class="app-toast__title">${titles[type] ?? titles.info}</p>
            <p class="app-toast__message"></p>
        </div>
        <button type="button" class="app-toast__close" aria-label="Tutup notifikasi">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    `;

    toast.querySelector('.app-toast__message').textContent = message;
    toast.querySelector('.app-toast__close')?.addEventListener('click', () => removeToast(toast));

    return toast;
};

document.addEventListener('DOMContentLoaded', () => {
    const flashData = document.getElementById('app-flash-data');

    if (!flashData) {
        return;
    }

    let flashes = [];

    try {
        flashes = JSON.parse(flashData.textContent || '[]');
    } catch {
        flashes = [];
    }

    if (!Array.isArray(flashes) || flashes.length === 0) {
        return;
    }

    const root = getToastRoot();

    flashes.forEach((flash, index) => {
        if (!flash?.message) {
            return;
        }

        window.setTimeout(() => {
            const toast = createToast(flash);

            root.append(toast);
            requestAnimationFrame(() => {
                toast.classList.add('is-visible');
            });

            window.setTimeout(() => removeToast(toast), 4200);
        }, index * 140);
    });
});
