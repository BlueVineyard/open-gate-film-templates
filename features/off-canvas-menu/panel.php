<?php
if (!defined('ABSPATH')) {
    exit;
}

if (empty($menu_items) || !is_array($menu_items)) {
    return;
}
?>
<div class="ogft-off-canvas" id="ogft-off-canvas" aria-hidden="true">
    <div class="ogft-off-canvas__backdrop"></div>
    <div class="ogft-off-canvas__panel" role="dialog" aria-modal="true" aria-label="Navigation menu">
        <div class="ogft-off-canvas__header">
            <button class="ogft-off-canvas__close" type="button" aria-label="Close menu">
                <span class="ogft-off-canvas__close-label">Menu</span>
                <span class="ogft-off-canvas__close-dot" aria-hidden="true"></span>
            </button>
        </div>

        <nav class="ogft-off-canvas__nav">
            <ul class="ogft-off-canvas__list">
                <?php foreach ($menu_items as $item): ?>
                    <li class="ogft-off-canvas__item<?php echo !empty($item->children) ? ' has-children' : ''; ?>">
                        <div class="ogft-off-canvas__link-wrap">
                            <a class="ogft-off-canvas__link" href="<?php echo esc_url($item->url); ?>">
                                <span class="ogft-off-canvas__link-text">
                                    <span><?php echo esc_html($item->title); ?></span>
                                    <span aria-hidden="true"><?php echo esc_html($item->title); ?></span>
                                </span>
                            </a>
                            <?php if (!empty($item->children)): ?>
                                <button class="ogft-off-canvas__submenu-toggle" type="button" aria-expanded="false" aria-label="Show submenu for <?php echo esc_attr($item->title); ?>">
                                    <svg aria-hidden="true" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="20" cy="20" r="20" fill="black"/>
                                        <path d="M14 17L20 23L26 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item->children)): ?>
                            <ul class="ogft-off-canvas__submenu">
                                <?php foreach ($item->children as $child): ?>
                                    <li>
                                        <a class="ogft-off-canvas__submenu-link" href="<?php echo esc_url($child->url); ?>">
                                            <?php echo esc_html($child->title); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="ogft-off-canvas__decoration" aria-hidden="true">
            <svg width="154" height="148" viewBox="0 0 154 148" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.3874 0C22.8266 0 25.6146 2.788 25.6146 6.22718V105.191C25.6146 115.509 33.9786 123.873 44.2961 123.873H147.773C151.212 123.873 154 126.661 154 130.1V141.773C154 145.212 151.212 148 147.773 148H6.22718C2.788 148 0 145.212 0 141.773V6.22718C0 2.788 2.788 0 6.22718 0H19.3874Z" fill="black"/>
            </svg>
        </div>
    </div>
</div>
