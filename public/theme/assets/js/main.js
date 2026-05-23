(function () {
    const storageKey = 'lt-sidebar-collapsed';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function initializeSidebar() {
        const body = document.body;
        const sidebar = document.querySelector('[data-lt-sidebar]');
        const backdrop = document.querySelector('[data-lt-sidebar-backdrop]');
        const openButtons = document.querySelectorAll('[data-lt-sidebar-open]');
        const closeButtons = document.querySelectorAll('[data-lt-sidebar-close]');
        const collapseButtons = document.querySelectorAll('[data-lt-sidebar-collapse]');

        if (localStorage.getItem(storageKey) === 'true') {
            body.classList.add('lt-sidebar-collapsed');
        }

        const open = () => {
            sidebar?.classList.add('show');
            backdrop?.classList.add('show');
        };

        const close = () => {
            sidebar?.classList.remove('show');
            backdrop?.classList.remove('show');
        };

        openButtons.forEach((button) => button.addEventListener('click', open));
        closeButtons.forEach((button) => button.addEventListener('click', close));
        backdrop?.addEventListener('click', close);

        collapseButtons.forEach((button) => {
            button.addEventListener('click', () => {
                body.classList.toggle('lt-sidebar-collapsed');
                localStorage.setItem(storageKey, body.classList.contains('lt-sidebar-collapsed') ? 'true' : 'false');
            });
        });

        document.querySelectorAll('[data-lt-menu-toggle]').forEach((button) => {
            const target = document.getElementById(button.dataset.ltMenuToggle || '');
            const section = button.closest('.lt-menu-section');

            button.addEventListener('click', () => {
                if (body.classList.contains('lt-sidebar-collapsed') && window.innerWidth >= 992) {
                    body.classList.remove('lt-sidebar-collapsed');
                    localStorage.setItem(storageKey, 'false');
                }

                const expanded = button.getAttribute('aria-expanded') === 'true';
                const sidebarRoot = button.closest('[data-lt-sidebar]');

                if (!expanded) {
                    sidebarRoot?.querySelectorAll('[data-lt-menu-toggle][aria-expanded="true"]').forEach((openButton) => {
                        if (openButton === button) {
                            return;
                        }

                        const openTarget = document.getElementById(openButton.dataset.ltMenuToggle || '');
                        openButton.setAttribute('aria-expanded', 'false');
                        openButton.closest('.lt-menu-section')?.classList.remove('open');
                        openTarget?.classList.remove('show');
                    });
                }

                button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                section?.classList.toggle('open', !expanded);
                target?.classList.toggle('show', !expanded);
            });
        });

        document.querySelectorAll('.lt-sidebar a[href]').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    close();
                }
            });
        });
    }

    function initializeBootstrap() {
        if (!window.bootstrap) {
            return;
        }

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
            window.bootstrap.Tooltip.getOrCreateInstance(element);
        });

        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((element) => {
            window.bootstrap.Dropdown.getOrCreateInstance(element);
        });
    }

    function initializePlugins() {
        if (window.Waves) {
            window.Waves.init();
            window.Waves.attach('.btn, .lt-menu-link, .lt-menu-toggle, .lt-icon-button, .lt-user-button', ['waves-light']);
        }

        if (window.flatpickr) {
            window.flatpickr('input[type="date"], [data-flatpickr]', {
                allowInput: true,
            });
        }

        if (window.Choices) {
            document.querySelectorAll('select[data-choices]').forEach((select) => {
                if (select.dataset.choicesReady === 'true') {
                    return;
                }

                select.dataset.choicesReady = 'true';
                new window.Choices(select, {
                    searchEnabled: select.options.length > 8,
                    shouldSort: false,
                    itemSelectText: '',
                });
            });
        }
    }

    ready(() => {
        initializeSidebar();
        initializeBootstrap();
        initializePlugins();
    });
})();
