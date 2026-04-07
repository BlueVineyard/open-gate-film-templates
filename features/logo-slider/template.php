<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = isset($ogft_logo_items) ? $ogft_logo_items : [];
$section_id = isset($ogft_logo_id) ? $ogft_logo_id : 'ogft-logo-slider';

if (empty($items) || !is_array($items)) {
    return;
}

// Build containers following pattern [2,2,1] repeating.
$pattern = [2, 2, 1];
$containers = [];
$index = 0;
$pattern_index = 0;
$total = count($items);

while ($index < $total) {
    $take = $pattern[$pattern_index % count($pattern)];
    $group = array_slice($items, $index, $take);
    if ($group) {
        $containers[] = $group;
    }
    $index += $take;
    $pattern_index++;
}
?>
<section class="ogft-logo-slider" id="<?php echo esc_attr($section_id); ?>" aria-label="Client logos">
    <div class="logo-slider__tilt">
        <div class="logo-slider__viewport">
            <div class="logo-slider__track">
                <?php foreach ($containers as $container) :
                    $is_single = count($container) === 1;
                    ?>
                    <article class="logo-stack<?php echo $is_single ? ' logo-stack--single' : ''; ?>">
                        <?php foreach ($container as $logo) :
                            $src = isset($logo['thumb']) && $logo['thumb'] ? esc_url($logo['thumb']) : (isset($logo['image']) ? esc_url($logo['image']) : '');
                            $alt = isset($logo['title']) ? esc_attr($logo['title']) : '';
                            if (!$src) {
                                continue;
                            }
                            ?>
                            <div class="logo-stack__item">
                                <img src="<?php echo $src; ?>" alt="<?php echo $alt; ?>" loading="lazy" />
                            </div>
                        <?php endforeach; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
