(() => {
    "use strict";

    const reducedMotionQuery = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    );
    const finePointerQuery = window.matchMedia(
        "(hover: hover) and (pointer: fine)",
    );

    const ready = () => {
        const body = document.body;

        if (! body || body.dataset.kesUiReady === "true") {
            return;
        }

        body.dataset.kesUiReady = "true";
        body.classList.add("kes-premium-ui");

        const interactiveSelector = [
            "a[href]",
            "button",
            "[role='button']",
            "input",
            "select",
            "textarea",
            ".fi-btn",
            ".fi-link",
        ].join(",");

        document.querySelectorAll(interactiveSelector).forEach((element) => {
            element.classList.add("kes-interactive");
        });

        const updateScrolledState = () => {
            body.classList.toggle(
                "kes-is-scrolled",
                window.scrollY > 48,
            );
        };

        updateScrolledState();
        window.addEventListener(
            "scroll",
            updateScrolledState,
            { passive: true },
        );

        const revealTargets = [
            "[data-kes-reveal]",
            "main > section",
            ".package-card",
            ".service-card",
            ".portal-card",
            ".fi-section",
            ".fi-wi-stats-overview-stat",
        ].join(",");

        const targets = Array.from(
            document.querySelectorAll(revealTargets),
        );

        targets.forEach((element, index) => {
            element.classList.add("kes-reveal");
            element.style.setProperty(
                "--kes-reveal-delay",
                `${Math.min(index % 6, 5) * 55}ms`,
            );
        });

        if (
            reducedMotionQuery.matches
            || typeof IntersectionObserver === "undefined"
        ) {
            targets.forEach((element) => {
                element.classList.add("kes-in-view");
            });
        } else {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (! entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add("kes-in-view");
                        observer.unobserve(entry.target);
                    });
                },
                {
                    threshold: 0.12,
                    rootMargin: "0px 0px -6% 0px",
                },
            );

            targets.forEach((element) => observer.observe(element));
        }

        if (
            reducedMotionQuery.matches
            || ! finePointerQuery.matches
        ) {
            return;
        }

        const cursor = document.createElement("div");
        const halo = document.createElement("div");

        cursor.className = "kes-cursor";
        cursor.setAttribute("aria-hidden", "true");
        halo.className = "kes-cursor-halo";
        halo.setAttribute("aria-hidden", "true");

        body.append(cursor, halo);
        body.classList.add("kes-cursor-active");

        let targetX = -100;
        let targetY = -100;
        let cursorX = -100;
        let cursorY = -100;
        let haloX = -100;
        let haloY = -100;
        let frameId = null;

        const render = () => {
            cursorX += (targetX - cursorX) * 0.42;
            cursorY += (targetY - cursorY) * 0.42;
            haloX += (targetX - haloX) * 0.18;
            haloY += (targetY - haloY) * 0.18;

            cursor.style.transform =
                `translate3d(${cursorX - 11}px, ${cursorY - 11}px, 0)`;
            halo.style.transform =
                `translate3d(${haloX - 24}px, ${haloY - 24}px, 0)`;

            frameId = window.requestAnimationFrame(render);
        };

        const startRender = () => {
            if (frameId !== null) {
                return;
            }

            frameId = window.requestAnimationFrame(render);
        };

        window.addEventListener(
            "pointermove",
            (event) => {
                targetX = event.clientX;
                targetY = event.clientY;
                startRender();
            },
            { passive: true },
        );

        document.addEventListener("pointerover", (event) => {
            const target = event.target;

            if (
                target instanceof Element
                && target.closest(interactiveSelector)
            ) {
                body.classList.add("kes-cursor-hover");
            }
        });

        document.addEventListener("pointerout", (event) => {
            const target = event.target;

            if (
                target instanceof Element
                && target.closest(interactiveSelector)
            ) {
                body.classList.remove("kes-cursor-hover");
            }
        });

        document.addEventListener("visibilitychange", () => {
            if (document.hidden && frameId !== null) {
                window.cancelAnimationFrame(frameId);
                frameId = null;
            } else if (! document.hidden) {
                startRender();
            }
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", ready, {
            once: true,
        });
    } else {
        ready();
    }

    document.addEventListener("livewire:navigated", ready);
})();


/* KES CINEMATIC HOMEPAGE V3 START */
(() => {
    "use strict";

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    );

    const initCinematicHero = () => {
        const hero = document.querySelector(
            "[data-kes-cinematic-hero]",
        );

        if (
            ! (hero instanceof HTMLElement)
            || hero.dataset.kesCinematicReady === "true"
        ) {
            return;
        }

        hero.dataset.kesCinematicReady = "true";

        const media = hero.querySelector(
            ".kes-cinematic-hero__media",
        );
        const spotlight = hero.querySelector(
            "[data-kes-hero-spotlight]",
        );

        if (reducedMotion.matches) {
            return;
        }

        let pointerFrame = null;
        let scrollFrame = null;
        let pointerX = 68;
        let pointerY = 38;

        const renderPointer = () => {
            hero.style.setProperty(
                "--kes-hero-x",
                `${pointerX}%`,
            );
            hero.style.setProperty(
                "--kes-hero-y",
                `${pointerY}%`,
            );
            pointerFrame = null;
        };

        hero.addEventListener(
            "pointermove",
            (event) => {
                if (! (event instanceof PointerEvent)) {
                    return;
                }

                const bounds = hero.getBoundingClientRect();

                if (bounds.width <= 0 || bounds.height <= 0) {
                    return;
                }

                pointerX = Math.max(
                    0,
                    Math.min(
                        100,
                        ((event.clientX - bounds.left) / bounds.width) * 100,
                    ),
                );
                pointerY = Math.max(
                    0,
                    Math.min(
                        100,
                        ((event.clientY - bounds.top) / bounds.height) * 100,
                    ),
                );

                if (pointerFrame === null) {
                    pointerFrame = window.requestAnimationFrame(
                        renderPointer,
                    );
                }
            },
            { passive: true },
        );

        hero.addEventListener(
            "pointerleave",
            () => {
                pointerX = 68;
                pointerY = 38;

                if (pointerFrame === null) {
                    pointerFrame = window.requestAnimationFrame(
                        renderPointer,
                    );
                }

                if (spotlight instanceof HTMLElement) {
                    spotlight.style.opacity = "0.35";
                    window.setTimeout(() => {
                        spotlight.style.removeProperty("opacity");
                    }, 300);
                }
            },
            { passive: true },
        );

        const renderParallax = () => {
            scrollFrame = null;

            if (! (media instanceof HTMLElement)) {
                return;
            }

            const bounds = hero.getBoundingClientRect();
            const viewport = window.innerHeight || 1;

            if (bounds.bottom < 0 || bounds.top > viewport) {
                return;
            }

            const progress = Math.max(
                -1,
                Math.min(1, -bounds.top / Math.max(bounds.height, 1)),
            );

            media.style.transform =
                `translate3d(0, ${progress * 34}px, 0) scale(1.065)`;
        };

        window.addEventListener(
            "scroll",
            () => {
                if (scrollFrame === null) {
                    scrollFrame = window.requestAnimationFrame(
                        renderParallax,
                    );
                }
            },
            { passive: true },
        );

        renderParallax();
    };

    if (document.readyState === "loading") {
        document.addEventListener(
            "DOMContentLoaded",
            initCinematicHero,
            { once: true },
        );
    } else {
        initCinematicHero();
    }

    document.addEventListener(
        "livewire:navigated",
        initCinematicHero,
    );
})();
/* KES CINEMATIC HOMEPAGE V3 END */
