<?php
if (!defined('ABSPATH')) {
    exit;
}

if (empty($items) || !is_array($items)) {
    return;
}

$has_slider = !empty($slider_in_mobile);
$wrapper_class = 'featured-work ogft-featured-work' . ($has_slider ? ' fw-has-mobile-slider' : '');

/**
 * Render a single fw-card.
 */
if (!function_exists('ogft_render_fw_card')) {
function ogft_render_fw_card($item, $title_limit = 0)
{
    $link = isset($item['link']) ? esc_url($item['link']) : '#';
    $kicker = isset($item['kicker']) ? esc_html($item['kicker']) : '';
    $title = isset($item['title']) ? esc_html($item['title']) : '';
    if ($title_limit > 0 && mb_strlen($title) > $title_limit) {
        $title = mb_substr($title, 0, $title_limit) . '...';
    }
    $meta = isset($item['meta']) ? esc_html($item['meta']) : '';
    $video_src = isset($item['video_src']) ? esc_url($item['video_src']) : '';
    $video_type = isset($item['video_type']) ? $item['video_type'] : '';
    $embed_src = isset($item['embed_src']) ? esc_url($item['embed_src']) : '';
    $poster_src = isset($item['poster_src']) ? esc_url($item['poster_src']) : '';
    $overlay_src = isset($item['overlay_src']) ? esc_url($item['overlay_src']) : '';
    $detail_url = isset($item['detail_url']) ? esc_url($item['detail_url']) : $link;
    ?>
    <a class="fw-card" href="<?php echo $detail_url; ?>">
        <div class="fw-media">
            <?php if (($video_type === 'mp4' || $video_type === '') && $video_src): ?>
                <video class="fw-video" muted playsinline preload="metadata" <?php echo $poster_src ? ' poster="' . $poster_src . '"' : ''; ?>>
                    <source src="<?php echo $video_src; ?>" type="video/mp4" />
                </video>
            <?php elseif ($embed_src): ?>
                <iframe
                    class="fw-embed"
                    title="Video player"
                    data-video-type="<?php echo esc_attr($video_type); ?>"
                    src="<?php echo $embed_src; ?>"
                    allow="autoplay; fullscreen; encrypted-media; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                ></iframe>
            <?php endif; ?>

            <?php if ($overlay_src): ?>
                <img class="fw-overlay-img" src="<?php echo $overlay_src; ?>" alt="" aria-hidden="true" />
            <?php endif; ?>

            <div class="fw-overlay">
                <div class="fw-play-btn">
                    <svg aria-hidden="true" width="48" height="48" viewBox="0 0 48 48" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_214_371)">
                            <path
                                d="M24 4.5C20.1433 4.5 16.3731 5.64366 13.1664 7.78634C9.95963 9.92903 7.46027 12.9745 5.98436 16.5377C4.50845 20.1008 4.12228 24.0216 4.8747 27.8043C5.62711 31.5869 7.4843 35.0615 10.2114 37.7886C12.9386 40.5157 16.4131 42.3729 20.1957 43.1253C23.9784 43.8777 27.8992 43.4916 31.4623 42.0156C35.0255 40.5397 38.071 38.0404 40.2137 34.8336C42.3564 31.6269 43.5 27.8567 43.5 24C43.4945 18.83 41.4383 13.8732 37.7826 10.2174C34.1268 6.56167 29.1701 4.50546 24 4.5ZM31.6031 25.2337L21.8531 31.9837C21.628 32.1394 21.3647 32.2305 21.0915 32.2472C20.8184 32.2639 20.5459 32.2055 20.3035 32.0785C20.0611 31.9514 19.8581 31.7604 19.7165 31.5263C19.5749 31.2921 19.5 31.0237 19.5 30.75V17.25C19.5 16.9763 19.5749 16.7079 19.7165 16.4737C19.8581 16.2396 20.0611 16.0486 20.3035 15.9215C20.5459 15.7945 20.8184 15.7361 21.0915 15.7528C21.3647 15.7695 21.628 15.8606 21.8531 16.0162L31.6031 22.7663C31.8027 22.9042 31.9658 23.0886 32.0785 23.3035C32.1911 23.5184 32.25 23.7574 32.25 24C32.25 24.2426 32.1911 24.4816 32.0785 24.6965C31.9658 24.9114 31.8027 25.0958 31.6031 25.2337Z"
                                fill="white" />
                        </g>
                        <defs>
                            <clipPath id="clip0_214_371">
                                <rect width="48" height="48" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>

                </div>

                <div class="fw-header">
                    <?php if ($title): ?>
                        <h3 class="fw-title"><?php echo $title; ?></h3>
                    <?php endif; ?>

                    <div class="fw-learn-more-btn">
                        <svg aria-hidden="true" width="32" height="32" viewBox="0 0 32 32" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_87_587)">
                                <path
                                    d="M16 3C13.4288 3 10.9154 3.76244 8.77759 5.1909C6.63975 6.61935 4.97351 8.64968 3.98957 11.0251C3.00563 13.4006 2.74819 16.0144 3.2498 18.5362C3.75141 21.0579 4.98953 23.3743 6.80762 25.1924C8.6257 27.0105 10.9421 28.2486 13.4638 28.7502C15.9856 29.2518 18.5995 28.9944 20.9749 28.0104C23.3503 27.0265 25.3807 25.3603 26.8091 23.2224C28.2376 21.0846 29 18.5712 29 16C28.9964 12.5533 27.6256 9.24882 25.1884 6.81163C22.7512 4.37445 19.4467 3.00364 16 3ZM21 18C21 18.2652 20.8946 18.5196 20.7071 18.7071C20.5196 18.8946 20.2652 19 20 19C19.7348 19 19.4804 18.8946 19.2929 18.7071C19.1054 18.5196 19 18.2652 19 18V14.4137L12.7075 20.7075C12.6146 20.8004 12.5043 20.8741 12.3829 20.9244C12.2615 20.9747 12.1314 21.0006 12 21.0006C11.8686 21.0006 11.7385 20.9747 11.6171 20.9244C11.4957 20.8741 11.3854 20.8004 11.2925 20.7075C11.1996 20.6146 11.1259 20.5043 11.0756 20.3829C11.0253 20.2615 10.9994 20.1314 10.9994 20C10.9994 19.8686 11.0253 19.7385 11.0756 19.6171C11.1259 19.4957 11.1996 19.3854 11.2925 19.2925L17.5863 13H14C13.7348 13 13.4804 12.8946 13.2929 12.7071C13.1054 12.5196 13 12.2652 13 12C13 11.7348 13.1054 11.4804 13.2929 11.2929C13.4804 11.1054 13.7348 11 14 11H20C20.2652 11 20.5196 11.1054 20.7071 11.2929C20.8946 11.4804 21 11.7348 21 12V18Z"
                                    fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_87_587">
                                    <rect width="32" height="32" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>

                    </div>

                </div>

                <?php if ($kicker): ?>
                    <p class="fw-kicker"><?php echo $kicker; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php
}
} // end function_exists
?>
<div class="<?php echo esc_attr($wrapper_class); ?>" aria-label="Featured work"<?php echo !empty($autoplay_always) ? ' data-autoplay-always="yes"' : ''; ?>>
    <div class="fw-grid">
        <?php foreach ($items as $item): ?>
            <?php ogft_render_fw_card($item, $title_limit); ?>
        <?php endforeach; ?>
    </div>

    <?php if ($has_slider): ?>
        <div class="fw-mobile-swiper swiper">
            <div class="swiper-wrapper">
                <?php foreach ($items as $item): ?>
                    <div class="swiper-slide">
                        <?php ogft_render_fw_card($item, $title_limit); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="fw-swiper-pagination"></div>
        </div>
    <?php endif; ?>
</div>
