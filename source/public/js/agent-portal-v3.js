/* PHASE 16E-2A AGENT PORTAL SHARED SHELL START */
(() => {
    'use strict';

    const root = document.querySelector(
        '[data-kes-agent-portal="v3"]',
    );

    if (!root) {
        return;
    }

    const toggle = root.querySelector(
        '[data-agent-nav-toggle]',
    );
    const navigation = root.querySelector(
        '[data-agent-nav]',
    );
    const closeControls = root.querySelectorAll(
        '[data-agent-nav-close]',
    );
    const mobileQuery = window.matchMedia(
        '(max-width: 960px)',
    );

    const setOpen = (open) => {
        const shouldOpen =
            Boolean(open && mobileQuery.matches);

        root.classList.toggle(
            'kes-agent-nav-open',
            shouldOpen,
        );
        root.classList.toggle(
            'cp-menu-open',
            shouldOpen,
        );
        document.documentElement.classList.toggle(
            'kes-agent-nav-lock',
            shouldOpen,
        );

        if (toggle) {
            toggle.setAttribute(
                'aria-expanded',
                shouldOpen ? 'true' : 'false',
            );
        }

        if (shouldOpen && navigation) {
            const firstLink =
                navigation.querySelector('a, button');

            window.requestAnimationFrame(() => {
                firstLink?.focus({
                    preventScroll: true,
                });
            });
        }
    };

    toggle?.addEventListener('click', () => {
        setOpen(
            !root.classList.contains(
                'kes-agent-nav-open',
            ),
        );
    });

    closeControls.forEach((control) => {
        control.addEventListener(
            'click',
            () => setOpen(false),
        );
    });

    navigation?.addEventListener(
        'click',
        (event) => {
            if (
                mobileQuery.matches
                && event.target.closest('a')
            ) {
                setOpen(false);
            }
        },
    );

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape'
            && root.classList.contains(
                'kes-agent-nav-open',
            )
        ) {
            setOpen(false);
            toggle?.focus({
                preventScroll: true,
            });
        }
    });

    const handleViewportChange = (event) => {
        if (!event.matches) {
            setOpen(false);
        }
    };

    if (
        typeof mobileQuery.addEventListener
        === 'function'
    ) {
        mobileQuery.addEventListener(
            'change',
            handleViewportChange,
        );
    } else {
        mobileQuery.addListener(
            handleViewportChange,
        );
    }

    setOpen(false);
})();
/* PHASE 16E-2A AGENT PORTAL SHARED SHELL END */
