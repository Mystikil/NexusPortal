document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.main-nav');
    if (!nav) {
        return;
    }

    const toggle = nav.querySelector('.nav-toggle');
    const menu = nav.querySelector('ul');
    const dropdownToggles = Array.from(nav.querySelectorAll('.dropdown-toggle'));

    const setDropdownState = (button, open) => {
        const dropdown = button.closest('.has-dropdown');
        if (!dropdown) {
            return;
        }
        dropdown.classList.toggle('open', open);
        button.setAttribute('aria-expanded', String(open));
    };

    const closeAllDropdowns = (exception) => {
        dropdownToggles.forEach((button) => {
            if (exception && button === exception) {
                return;
            }
            setDropdownState(button, false);
        });
    };

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!expanded));
            menu.classList.toggle('open');
            closeAllDropdowns();
        });
    }

    dropdownToggles.forEach((button) => {
        button.setAttribute('aria-expanded', button.getAttribute('aria-expanded') || 'false');

        button.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = button.getAttribute('aria-expanded') === 'true';
            closeAllDropdowns(isOpen ? null : button);
            setDropdownState(button, !isOpen);
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' || event.key === 'Esc') {
                if (button.getAttribute('aria-expanded') === 'true') {
                    setDropdownState(button, false);
                    button.focus();
                }
                closeAllDropdowns();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!nav.contains(event.target)) {
            closeAllDropdowns();
        }
    });

    window.addEventListener('resize', () => {
        closeAllDropdowns();
        if (toggle && menu && window.innerWidth > 960) {
            toggle.setAttribute('aria-expanded', 'false');
            menu.classList.remove('open');
        }
    });
});
