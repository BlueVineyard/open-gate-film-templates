(function () {
    const trigger = document.querySelector(".ogft-menu-trigger");
    const offCanvas = document.getElementById("ogft-off-canvas");

    if (!trigger || !offCanvas) return;

    const panel = offCanvas.querySelector(".ogft-off-canvas__panel");
    const backdrop = offCanvas.querySelector(".ogft-off-canvas__backdrop");
    const closeBtn = offCanvas.querySelector(".ogft-off-canvas__close");
    const submenuToggles = offCanvas.querySelectorAll(".ogft-off-canvas__submenu-toggle");

    const focusableSelector =
        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const open = () => {
        offCanvas.classList.add("is-open");
        offCanvas.setAttribute("aria-hidden", "false");
        trigger.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
        closeBtn.focus();
    };

    const close = () => {
        offCanvas.classList.remove("is-open");
        offCanvas.setAttribute("aria-hidden", "true");
        trigger.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
        trigger.focus();
    };

    trigger.addEventListener("click", open);
    closeBtn.addEventListener("click", close);
    backdrop.addEventListener("click", close);

    // Escape key
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && offCanvas.classList.contains("is-open")) {
            close();
        }
    });

    // Focus trap
    panel.addEventListener("keydown", (e) => {
        if (e.key !== "Tab") return;

        const focusable = [...panel.querySelectorAll(focusableSelector)].filter(
            (el) => el.offsetParent !== null
        );
        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });

    // Submenu toggles
    submenuToggles.forEach((toggle) => {
        toggle.addEventListener("click", () => {
            const expanded = toggle.getAttribute("aria-expanded") === "true";
            toggle.setAttribute("aria-expanded", String(!expanded));

            const submenu = toggle
                .closest(".ogft-off-canvas__item")
                .querySelector(".ogft-off-canvas__submenu");

            if (submenu) {
                submenu.classList.toggle("is-open");
            }
        });
    });
})();
