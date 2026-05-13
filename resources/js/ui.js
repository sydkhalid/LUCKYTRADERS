function initializeSubmitLoading() {
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.matches('[data-ajax-form]')) {
                return;
            }

            const submitter = event.submitter || form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');

            if (!submitter || submitter.dataset.loadingAttached === 'true') {
                return;
            }

            submitter.dataset.loadingAttached = 'true';
            submitter.setAttribute('aria-disabled', 'true');
            submitter.classList.add('is-loading');

            if (submitter.tagName === 'BUTTON') {
                submitter.dataset.originalText = submitter.textContent.trim();
                submitter.textContent = submitter.dataset.loadingText || 'Saving...';
            } else if (submitter.tagName === 'INPUT') {
                submitter.dataset.originalValue = submitter.value;
                submitter.value = submitter.dataset.loadingText || 'Saving...';
            }
        });
    });
}

function initializeTableHints() {
    document.querySelectorAll('.erp-content .overflow-x-auto').forEach((wrapper) => {
        if (wrapper.dataset.tableHintReady === 'true') {
            return;
        }

        wrapper.dataset.tableHintReady = 'true';
        wrapper.setAttribute('tabindex', '0');
        wrapper.setAttribute('role', wrapper.getAttribute('role') || 'region');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initializeSubmitLoading();
    initializeTableHints();
});
