(function () {
    var config = window.ogftVideoLightboxConfig || {};

    function openLightbox(videoData) {
        var backdrop = document.createElement("div");
        backdrop.className = "ogft-work-modal-backdrop";

        var modal = document.createElement("div");
        modal.className = "ogft-work-modal";

        // ── Topbar ──
        var topbar = document.createElement("div");
        topbar.className = "ogft-work-modal__topbar";
        var brandContent = config.brandLogo
            ? '<img class="ogft-work-modal__brand-logo" src="' + config.brandLogo + '" alt="' + (config.brand || "Open Gate") + '" />'
            : "<span>" + (config.brand || "Open Gate").toUpperCase() + "</span>";
        topbar.innerHTML =
            '<div class="ogft-work-modal__brand">' + brandContent + "</div>" +
            '<div class="ogft-work-modal__actions"></div>';

        var actions = topbar.querySelector(".ogft-work-modal__actions");
        var ctaLabel = (config.ctaLabel || "Request a quote").toUpperCase();
        var ctaIcon =
            '<svg class="ogft-btn__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<circle cx="10" cy="10" r="9.5" stroke="currentColor"/>' +
            '<path d="M6 10H14M14 10L11 7M14 10L11 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
            "</svg>";

        if (config.ctaUrl) {
            var cta = document.createElement("a");
            cta.className = "ogft-btn";
            cta.href = config.ctaUrl;
            cta.innerHTML = "<span>" + ctaLabel + "</span>" + ctaIcon;
            actions.appendChild(cta);
        } else {
            var ctaBtn = document.createElement("button");
            ctaBtn.type = "button";
            ctaBtn.className = "ogft-btn";
            ctaBtn.innerHTML = "<span>" + ctaLabel + "</span>" + ctaIcon;
            actions.appendChild(ctaBtn);
        }

        var closeBtn = document.createElement("button");
        closeBtn.type = "button";
        closeBtn.className = "ogft-btn--close";
        closeBtn.setAttribute("aria-label", "Close");
        closeBtn.innerHTML =
            '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
            '<path d="M1 1L13 13M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
            "</svg>";
        actions.appendChild(closeBtn);

        // ── Media / Plyr ──
        var mediaWrap = document.createElement("div");
        mediaWrap.className = "ogft-work-modal__media";

        var element;

        if (videoData.video_type === "youtube" || videoData.video_type === "vimeo") {
            element = document.createElement("div");
            element.className = "ogft-plyr-embed";
            element.setAttribute("data-plyr-provider", videoData.video_type);
            element.setAttribute("data-plyr-embed-id", videoData.embed_id || "");
            if (videoData.video_type === "vimeo" && videoData.vimeo_hash) {
                element.setAttribute(
                    "data-plyr-config",
                    JSON.stringify({ vimeo: { h: videoData.vimeo_hash } })
                );
            }
        } else {
            element = document.createElement("video");
            element.className = "ogft-video-lightbox__video";
            element.playsInline = true;
            element.controls = false;
            element.preload = "metadata";
            if (videoData.thumbnail) element.poster = videoData.thumbnail;
            var source = document.createElement("source");
            source.src = videoData.url || "";
            source.type = "video/mp4";
            element.appendChild(source);
        }

        mediaWrap.appendChild(element);

        var controls = [
            "play-large",
            "play",
            "progress",
            "current-time",
            "mute",
            "volume",
            "settings",
            "pip",
            "fullscreen",
        ];

        // ── Assemble modal ──
        modal.appendChild(topbar);
        modal.appendChild(mediaWrap);
        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);

        // eslint-disable-next-line no-undef
        var player = new Plyr(element, {
            autoplay: true,
            controls: controls,
            settings: ["quality", "speed", "loop"],
            invertTime: false,
            resetOnEnd: true,
            tooltips: { controls: true, seek: true },
            keyboard: { focused: true, global: false },
            clickToPlay: true,
            vimeo: { dnt: true },
            youtube: { rel: 0, showinfo: 0, iv_load_policy: 3, modestbranding: 1 },
        });

        // ── Scroll lock ──
        var prevOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";

        // ── Close logic ──
        function close() {
            player.destroy();
            backdrop.remove();
            document.body.style.overflow = prevOverflow;
            document.removeEventListener("keydown", onEscape);
        }

        function onEscape(e) {
            if (e.key === "Escape") close();
        }

        closeBtn.addEventListener("click", close);
        backdrop.addEventListener("click", function (e) {
            if (e.target === backdrop) close();
        });
        document.addEventListener("keydown", onEscape);
    }

    // ── Event delegation ──
    document.addEventListener("click", function (e) {
        var trigger = e.target.closest(".ogft-video-lightbox-trigger");
        if (!trigger) return;
        e.preventDefault();
        var videoData = JSON.parse(trigger.getAttribute("data-video") || "{}");
        if (videoData.url || videoData.embed_id) {
            openLightbox(videoData);
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key !== "Enter" && e.key !== " ") return;
        var trigger = e.target.closest(".ogft-video-lightbox-trigger");
        if (!trigger || trigger.tagName === "BUTTON") return;
        e.preventDefault();
        var videoData = JSON.parse(trigger.getAttribute("data-video") || "{}");
        if (videoData.url || videoData.embed_id) {
            openLightbox(videoData);
        }
    });
})();
