document.querySelectorAll(".ogft-stats-marquee").forEach((section) => {
    const track = section.querySelector(".stats-marquee__track");
    const viewport = section.querySelector(".stats-marquee__viewport");

    if (!track || !viewport || typeof gsap === "undefined") {
        return;
    }

    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion) {
        return;
    }

    const original = track.innerHTML;
    track.insertAdjacentHTML("beforeend", original);

    const cards = Array.from(track.children);
    const half = Math.floor(cards.length / 2);

    let distance = 0;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || "0");

    for (let i = 0; i < half; i += 1) {
        const el = cards[i];
        distance += el.getBoundingClientRect().width + (i === half - 1 ? 0 : gap);
    }

    gsap.set(track, { x: -distance / 2 });

    const tween = gsap.to(track, {
        x: `-=${distance}`,
        duration: window.ogftStatsData?.speed ? parseFloat(window.ogftStatsData.speed) : 22,
        ease: "none",
        repeat: -1,
        modifiers: {
            x: (x) => {
                const v = parseFloat(x);
                return (v % -distance) + "px";
            },
        },
    });

    const pause = () => tween.pause();
    const resume = () => tween.resume();

    viewport.addEventListener("mouseenter", pause);
    viewport.addEventListener("mouseleave", resume);
    viewport.addEventListener("focusin", pause);
    viewport.addEventListener("focusout", resume);
});
