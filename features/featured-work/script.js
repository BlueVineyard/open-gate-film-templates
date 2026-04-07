document.querySelectorAll(".ogft-featured-work .fw-card").forEach((card) => {
    const video = card.querySelector(".fw-video");
    const iframe = card.querySelector(".fw-embed");
    const overlayImg = card.querySelector(".fw-overlay-img");
    const type = iframe?.dataset.videoType;

    const hideOverlay = () => {
        if (overlayImg) {
            overlayImg.style.opacity = "0";
        }
    };

    const showOverlay = () => {
        if (overlayImg) {
            overlayImg.style.opacity = "";
        }
    };

    if (video) {
        video.muted = true;

        const playVideo = async () => {
            try {
                await video.play();
                hideOverlay();
            } catch (e) {
                // Autoplay might be blocked; keep overlay visible.
            }
        };

        const stopVideo = () => {
            video.pause();
            video.currentTime = 0;
            showOverlay();
        };

        card.addEventListener("mouseenter", playVideo);
        card.addEventListener("mouseleave", stopVideo);
        card.addEventListener("focusin", playVideo);
        card.addEventListener("focusout", stopVideo);
        return;
    }

    if (iframe && type) {
        const postMessagePlay = () => {
            if (!iframe.contentWindow) return;
            if (type === "youtube") {
                iframe.contentWindow.postMessage(
                    JSON.stringify({ event: "command", func: "playVideo", args: [] }),
                    "*"
                );
            } else if (type === "vimeo") {
                iframe.contentWindow.postMessage({ method: "play" }, "*");
            }
            hideOverlay();
        };

        const postMessagePause = () => {
            if (!iframe.contentWindow) return;
            if (type === "youtube") {
                iframe.contentWindow.postMessage(
                    JSON.stringify({ event: "command", func: "pauseVideo", args: [] }),
                    "*"
                );
            } else if (type === "vimeo") {
                iframe.contentWindow.postMessage({ method: "pause" }, "*");
            }
            showOverlay();
        };

        card.addEventListener("mouseenter", postMessagePlay);
        card.addEventListener("mouseleave", postMessagePause);
        card.addEventListener("focusin", postMessagePlay);
        card.addEventListener("focusout", postMessagePause);
    }
});
