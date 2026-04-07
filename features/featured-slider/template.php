<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = isset($ogft_slider_items) ? $ogft_slider_items : [];
$slider_id = isset($ogft_slider_id) ? $ogft_slider_id : 'ogft-featured-slider';

if (empty($items) || !is_array($items)) {
    return;
}

$first = $items[0];
$first_image = isset($first['image']) ? esc_url($first['image']) : '';
$first_title = isset($first['title']) ? esc_html($first['title']) : '';
$first_label = isset($first['label']) ? esc_html($first['label']) : '';
$first_description = isset($first['description']) ? esc_html($first['description']) : '';
?>
<div class="ogft-featured-slider" id="<?php echo esc_attr($slider_id); ?>">
    <div class="fs-main">
        <?php if ($first_image): ?>
            <img src="<?php echo $first_image; ?>"
                alt="<?php echo $first_title ? esc_attr($first_title) : 'Featured slide'; ?>" class="fs-main-img" />
            <div class="fs-loader" aria-hidden="true"></div>
        <?php endif; ?>
    </div>
    <div class="fs-thumbs-wrap">
        <div class="fs-thumbs" role="tablist" aria-label="Featured slider thumbnails">
            <?php foreach ($items as $index => $item):
                $image = isset($item['image']) ? esc_url($item['image']) : '';
                $thumb = isset($item['thumb']) ? esc_url($item['thumb']) : $image;
                $title = isset($item['title']) ? esc_html($item['title']) : '';
                ?>
                <button type="button" class="fs-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" role="tab"
                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>" data-index="<?php echo esc_attr($index); ?>"
                    data-image="<?php echo esc_attr($image); ?>" data-title="<?php echo esc_attr($title); ?>">
                    <?php if ($thumb): ?>
                        <img src="<?php echo $thumb; ?>" alt="<?php echo $title ? esc_attr($title) : 'Slide thumbnail'; ?>" />
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="fs-thumbs-arrows" aria-hidden="true">
            <button type="button" class="fs-arrow fs-arrow-prev" aria-label="Previous slides">&larr;</button>
            <button type="button" class="fs-arrow fs-arrow-next" aria-label="Next slides">&rarr;</button>
        </div>
    </div>
</div>
