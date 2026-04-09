<?php
if (!defined('ABSPATH')) {
    exit;
}

$json = esc_attr(wp_json_encode($lightbox_video));
?>
<?php if ($lightbox_type === 'button'): ?>
    <button
        type="button"
        class="ogft-btn ogft-video-lightbox-trigger"
        data-video="<?php echo $json; ?>"
        id="<?php echo esc_attr($lightbox_id); ?>"
    >
        <span><?php echo esc_html($lightbox_text); ?></span>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="10" cy="10" r="9.5" stroke="currentColor"/>
            <polygon points="8,6 15,10 8,14" fill="currentColor"/>
        </svg>
    </button>
<?php else: ?>
    <div
        class="ogft-video-lightbox-trigger ogft-video-lightbox-thumb"
        role="button"
        tabindex="0"
        aria-label="Play video"
        data-video="<?php echo $json; ?>"
        id="<?php echo esc_attr($lightbox_id); ?>"
        style="position:relative;display:inline-block;cursor:pointer;border-radius:18px;overflow:hidden;aspect-ratio:16/9;background:#0b0b0b;width:100%;"
    >
        <?php if ($lightbox_thumb): ?>
            <img
                src="<?php echo esc_url($lightbox_thumb); ?>"
                alt="Video thumbnail"
                style="display:block;width:100%;height:100%;object-fit:cover;"
                loading="lazy"
            />
        <?php endif; ?>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.25);">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="32" cy="32" r="31" stroke="#fff" stroke-width="2" fill="rgba(0,0,0,0.3)"/>
                <polygon points="26,20 46,32 26,44" fill="#fff"/>
            </svg>
        </div>
    </div>
<?php endif; ?>
