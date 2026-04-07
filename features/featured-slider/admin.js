document.addEventListener("DOMContentLoaded", () => {
    const pickers = Array.from(document.querySelectorAll(".ogft-media-picker"));
    if (!pickers.length) return;

    const renderPreview = (input, preview) => {
        preview.innerHTML = "";
        const ids = input.value
            .split(",")
            .map((s) => parseInt(s.trim(), 10))
            .filter(Boolean);
        if (!ids.length) {
            preview.textContent = "No images selected.";
            return;
        }

        const attachments = ids.map((id) => {
            const a = wp.media.attachment(id);
            return a.fetch().then(() => a);
        });

        Promise.all(attachments).then((atts) => {
            preview.innerHTML = "";
            atts.forEach((att) => {
                const sizes = att.get("sizes") || {};
                const url =
                    (sizes.medium_large && sizes.medium_large.url) ||
                    (sizes.medium && sizes.medium.url) ||
                    att.get("url");
                if (!url) return;
                const img = document.createElement("img");
                img.dataset.id = att.get("id");
                img.alt = att.get("title") || "Selected image";
                img.loading = "lazy";
                img.src = url;
                preview.appendChild(img);
            });
        });
    };

    pickers.forEach((picker) => {
        const input = picker.querySelector('input[type="hidden"]');
        const button = picker.querySelector(".ogft-media-picker-btn");
        const preview = picker.querySelector(".ogft-media-picker__preview");
        if (!input || !button || !preview) return;

        renderPreview(input, preview);

        button.addEventListener("click", (e) => {
            e.preventDefault();
            const frame = wp.media({
                title: "Select images",
                button: { text: "Use selected images" },
                multiple: true,
            });

            frame.on("open", () => {
                const selection = frame.state().get("selection");
                const ids = input.value
                    .split(",")
                    .map((s) => parseInt(s.trim(), 10))
                    .filter(Boolean);
                ids.forEach((id) => {
                    const attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                });
            });

            frame.on("select", () => {
                const selection = frame.state().get("selection").toArray();
                const ids = selection.map((item) => item.get("id"));
                input.value = ids.join(",");
                renderPreview(input, preview);
            });

            frame.open();
        });
    });
});
