(function () {
    const MOBILE_BP = 640;

    document.querySelectorAll(".fw-has-mobile-slider").forEach(function (section) {
        const swiperEl = section.querySelector(".fw-mobile-swiper");
        if (!swiperEl || typeof Swiper === "undefined") {
            return;
        }

        let swiperInstance = null;

        function init() {
            if (swiperInstance) {
                return;
            }
            swiperInstance = new Swiper(swiperEl, {
                slidesPerView: "auto",
                spaceBetween: 16,
                grabCursor: true,
                pagination: {
                    el: swiperEl.querySelector(".fw-swiper-pagination"),
                    clickable: true,
                },
            });
        }

        function destroy() {
            if (!swiperInstance) {
                return;
            }
            swiperInstance.destroy(true, true);
            swiperInstance = null;
        }

        function handleResize() {
            if (window.innerWidth <= MOBILE_BP) {
                init();
            } else {
                destroy();
            }
        }

        handleResize();

        var resizeTimer;
        window.addEventListener("resize", function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(handleResize, 150);
        });
    });
})();
