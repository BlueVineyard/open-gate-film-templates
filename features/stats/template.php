<?php
if (!defined('ABSPATH')) {
    exit;
}

if (empty($items) || !is_array($items)) {
    return;
}
?>
<div class="stats-marquee ogft-stats-marquee" aria-label="Stats" id="<?php echo esc_attr($div_id); ?>">
    <div class="stats-marquee__tilt">
        <div class="stats-marquee__viewport">
            <div class="stats-marquee__track">
                <?php foreach ($items as $item) :
                    $kicker = isset($item['kicker']) ? esc_html($item['kicker']) : '';
                    $value = isset($item['value']) ? esc_html($item['value']) : '';
                    $label = isset($item['label']) ? esc_html($item['label']) : '';
                    $bg_image = isset($item['bg_image']) ? esc_url($item['bg_image']) : '';
                    $style = $bg_image ? '--bg:url(\'' . $bg_image . '\')' : '';
                    ?>
                    <article class="stat-card"<?php echo $style ? ' style="' . esc_attr($style) . '"' : ''; ?>>
                        <div class="stat-card__overlay"></div>
                        <div class="stat-card__content">
                            <?php if ($kicker) : ?>
                                <p class="stat-card__kicker"><?php echo $kicker; ?></p>
                            <?php endif; ?>
                            <?php if ($value) : ?>
                                <h3 class="stat-card__value"><?php echo $value; ?></h3>
                            <?php endif; ?>
                            <?php if ($label) : ?>
                                <p class="stat-card__label"><?php echo $label; ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
