const submitForm = (form) => {
    if (!form) {
        return;
    }

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
        return;
    }

    form.submit();
};

document.addEventListener('DOMContentLoaded', () => {
    const timers = new WeakMap();

    document.querySelectorAll('[data-debounce-submit]').forEach((field) => {
        field.addEventListener('input', () => {
            const delay = Number(field.dataset.debounceSubmit || '420');

            window.clearTimeout(timers.get(field));

            const timer = window.setTimeout(() => {
                submitForm(field.form);
            }, delay);

            timers.set(field, timer);
        });
    });

    document.querySelectorAll('[data-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => {
            submitForm(field.form);
        });
    });
});
