document.querySelectorAll(".ogft-featured-slider").forEach((slider) => {
    const mainImg = slider.querySelector(".fs-main-img");
    const thumbs = Array.from(slider.querySelectorAll(".fs-thumb"));
    const thumbsWrap = slider.querySelector(".fs-thumbs");
    const loader = slider.querySelector(".fs-loader");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (!mainImg || thumbs.length === 0) {
        return;
    }

    let active = 0;

    const setActive = (index) => {
        const btn = thumbs[index];
        if (!btn) return;
        active = index;

        thumbs.forEach((t, i) => {
            t.classList.toggle("is-active", i === index);
            t.setAttribute("aria-selected", i === index ? "true" : "false");
        });

        const nextSrc = btn.dataset.image;
        const nextTitle = btn.dataset.title || "";

        const updateContent = () => {
            if (nextSrc) {
                slider.classList.add("is-loading");
                mainImg.style.opacity = "0.4";
                mainImg.onload = () => {
                    slider.classList.remove("is-loading");
                    mainImg.style.opacity = "1";
                    mainImg.onload = null;
                };
                mainImg.onerror = () => {
                    slider.classList.remove("is-loading");
                    mainImg.style.opacity = "1";
                    mainImg.onerror = null;
                };
                mainImg.src = nextSrc;
                mainImg.alt = nextTitle || "Featured slide";
                if (loader) {
                    loader.setAttribute("aria-hidden", "false");
                }
            }
        };

        if (reduceMotion || typeof gsap === "undefined") {
            updateContent();
            return;
        }

        const tl = gsap.timeline();
        tl.to([mainImg].filter(Boolean), {
            opacity: 0,
            y: 6,
            duration: 0.18,
            stagger: 0.02,
        })
            .add(updateContent)
            .to([mainImg].filter(Boolean), {
                opacity: 1,
                y: 0,
                duration: 0.28,
                stagger: 0.02,
                ease: "power2.out",
            });
    };

    thumbs.forEach((thumb, idx) => {
        thumb.addEventListener("click", () => setActive(idx));
    });

    // Arrow scroll for thumbs
    if (thumbsWrap) {
        const prevBtn = slider.querySelector(".fs-arrow-prev");
        const nextBtn = slider.querySelector(".fs-arrow-next");
        const scrollBy = () => {
            const thumb = thumbs[0];
            if (!thumb) return 300;
            return thumb.offsetWidth + 12;
        };

        const scrollPrev = () => thumbsWrap.scrollBy({ left: -scrollBy(), behavior: "smooth" });
        const scrollNext = () => thumbsWrap.scrollBy({ left: scrollBy(), behavior: "smooth" });

        if (prevBtn) prevBtn.addEventListener("click", scrollPrev);
        if (nextBtn) nextBtn.addEventListener("click", scrollNext);
    }
});
