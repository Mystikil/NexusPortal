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

    const carousels = Array.from(document.querySelectorAll('[data-carousel]'));
    carousels.forEach((carousel) => {
        const slides = Array.from(carousel.querySelectorAll('.carousel-slide'));
        if (!slides.length) {
            return;
        }

        const indicators = Array.from(carousel.querySelectorAll('[data-slide-to]'));
        const prevButton = carousel.querySelector('.carousel-button.prev');
        const nextButton = carousel.querySelector('.carousel-button.next');
        let current = slides.findIndex((slide) => slide.classList.contains('is-active'));
        if (current < 0) {
            current = 0;
            slides[0].classList.add('is-active');
            if (indicators[0]) {
                indicators[0].classList.add('active');
            }
        }

        let autoTimer;
        const setSlide = (index) => {
            const total = slides.length;
            if (!total) {
                return;
            }
            const nextIndex = ((index % total) + total) % total;
            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === nextIndex);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === nextIndex);
            });
            current = nextIndex;
        };

        const stopAuto = () => {
            if (autoTimer) {
                window.clearInterval(autoTimer);
                autoTimer = undefined;
            }
        };

        const startAuto = () => {
            stopAuto();
            if (slides.length <= 1) {
                return;
            }
            autoTimer = window.setInterval(() => {
                setSlide(current + 1);
            }, 8000);
        };

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                setSlide(current - 1);
                startAuto();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                setSlide(current + 1);
                startAuto();
            });
        }

        indicators.forEach((indicator) => {
            indicator.addEventListener('click', () => {
                const target = Number(indicator.getAttribute('data-slide-to'));
                if (Number.isFinite(target)) {
                    setSlide(target);
                    startAuto();
                }
            });
        });

        carousel.addEventListener('mouseenter', stopAuto);
        carousel.addEventListener('mouseleave', startAuto);

        startAuto();
    });
});
