<?php
/**
 * Plugin Name: Open Gate Film Templates
 * Description: Feature templates and shortcodes for Open Gate Film.
 * Version: 0.1.0
 * Author: Open Gate Film
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OGFT_VERSION', '0.1.0');
define('OGFT_PATH', plugin_dir_path(__FILE__));
define('OGFT_URL', plugin_dir_url(__FILE__));

function ogft_register_nav_menus()
{
    register_nav_menus([
        'ogft_off_canvas' => __('Off-Canvas Menu', 'open-gate-film-templates'),
    ]);
}
add_action('after_setup_theme', 'ogft_register_nav_menus');

function ogft_default_stats_items()
{
    return [
        [
            'kicker' => 'Performance',
            'value' => '98%',
            'label' => 'Client satisfaction',
            'bg_image' => 'https://picsum.photos/id/1011/900/600',
        ],
        [
            'kicker' => 'Growth',
            'value' => '+142%',
            'label' => 'Organic traffic',
            'bg_image' => 'https://picsum.photos/id/1015/900/600',
        ],
        [
            'kicker' => 'Delivery',
            'value' => '7 days',
            'label' => 'Avg. turnaround',
            'bg_image' => 'https://picsum.photos/id/1005/900/600',
        ],
        [
            'kicker' => 'Impact',
            'value' => '1.2M',
            'label' => 'Impressions served',
            'bg_image' => 'https://picsum.photos/id/1025/900/600',
        ],
    ];
}

function ogft_get_settings()
{
    $defaults = [
        'enable_featured_work' => '1',
        'enable_stats' => '1',
        'enable_featured_slider' => '0',
        'enable_logo_slider' => '0',
        'enable_off_canvas_menu' => '0',
        'featured_work_detail_page_id' => '',
        'featured_work_cta_url' => '',
        'stats_items' => wp_json_encode(ogft_default_stats_items(), JSON_PRETTY_PRINT),
        'stats_marquee_speed' => '22',
        'featured_slider_image_ids' => '',
        'logo_slider_image_ids' => '',
        'logo_slider_marquee_speed' => '24',
    ];

    $settings = get_option('ogft_settings', []);

    return wp_parse_args($settings, $defaults);
}

function ogft_parse_json_items($value, $fallback)
{
    if (!is_string($value) || $value === '') {
        return $fallback;
    }

    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return $fallback;
    }

    return $decoded;
}

function ogft_parse_video_data($url)
{
    if (!$url) {
        return [
            'type' => '',
            'embed_src' => '',
            'embed_id' => '',
        ];
    }

    $parsed = wp_parse_url($url);
    if (!$parsed || empty($parsed['host'])) {
        return [
            'type' => 'mp4',
            'embed_src' => '',
            'embed_id' => '',
        ];
    }

    $host = strtolower($parsed['host']);
    $path = isset($parsed['path']) ? trim($parsed['path'], '/') : '';

    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
    if (in_array($ext, ['mp4', 'webm', 'ogg'], true)) {
        return [
            'type' => 'mp4',
            'embed_src' => '',
            'embed_id' => '',
        ];
    }

    if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
        $video_id = '';
        if (str_contains($host, 'youtu.be')) {
            $video_id = $path;
        } else {
            parse_str($parsed['query'] ?? '', $query);
            if (!empty($query['v'])) {
                $video_id = $query['v'];
            } elseif (str_starts_with($path, 'embed/')) {
                $video_id = substr($path, strlen('embed/'));
            }
        }

        if ($video_id) {
            return [
                'type' => 'youtube',
                'embed_src' => sprintf(
                    'https://www.youtube.com/embed/%s?enablejsapi=1&controls=0&rel=0&playsinline=1&mute=1',
                    rawurlencode($video_id)
                ),
                'embed_id' => $video_id,
            ];
        }
    }

    if (str_contains($host, 'vimeo.com')) {
        $segments = explode('/', $path);
        $segments = array_values(array_filter($segments));
        $video_id = '';
        $hash = '';
        foreach ($segments as $seg) {
            if (ctype_digit($seg)) {
                $video_id = $seg;
            } elseif ($video_id !== '') {
                $hash = $seg;
                break;
            }
        }
        if ($video_id) {
            $embed_src = sprintf(
                'https://player.vimeo.com/video/%s?background=1&muted=1&autopause=0',
                $video_id
            );
            if ($hash) {
                $embed_src .= '&h=' . rawurlencode($hash);
            }
            return [
                'type' => 'vimeo',
                'embed_src' => $embed_src,
                'embed_id' => $video_id,
                'vimeo_hash' => $hash,
            ];
        }
    }

    return [
        'type' => 'mp4',
        'embed_src' => '',
        'embed_id' => '',
    ];
}

function ogft_build_slider_items_from_ids($ids_string)
{
    if (!$ids_string) {
        return [];
    }

    $ids = array_filter(array_map('absint', explode(',', $ids_string)));
    if (!$ids) {
        return [];
    }

    $items = [];
    foreach ($ids as $id) {
        $image = wp_get_attachment_image_url($id, 'full');
        $thumb = wp_get_attachment_image_url($id, 'medium_large') ?: wp_get_attachment_image_url($id, 'large');
        $title = get_the_title($id);

        if ($image) {
            $items[] = [
                'image' => $image,
                'thumb' => $thumb ?: $image,
                'title' => $title ?: '',
            ];
        }
    }

    return $items;
}

function ogft_build_logo_items_from_ids($ids_string)
{
    $items = ogft_build_slider_items_from_ids($ids_string);
    return array_map(function ($item) {
        return [
            'image' => isset($item['image']) ? $item['image'] : '',
            'thumb' => isset($item['thumb']) ? $item['thumb'] : '',
            'title' => isset($item['title']) ? $item['title'] : '',
        ];
    }, $items);
}

function ogft_get_work_item_by_id($post_id)
{
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'work' || $post->post_status !== 'publish') {
        return null;
    }

    $terms = get_the_terms($post, 'work-type');
    $kicker = (!is_wp_error($terms) && $terms) ? implode(' / ', wp_list_pluck($terms, 'name')) : '';

    $meta = get_post_meta($post->ID, 'ogft_meta', true);
    if ($meta === '') {
        $meta = get_post_meta($post->ID, 'meta', true);
    }
    if ($meta === '') {
        $meta = get_the_date('', $post);
    }

    $video_src = get_post_meta($post->ID, 'video_src', true);
    if (!$video_src && function_exists('get_field')) {
        $acf_video = get_field('video_src', $post->ID);
        if (is_array($acf_video) && isset($acf_video['url'])) {
            $video_src = $acf_video['url'];
        } elseif (is_string($acf_video)) {
            $video_src = $acf_video;
        }
    }
    $video_data = ogft_parse_video_data($video_src);

    $poster_src = get_post_meta($post->ID, 'poster_src', true);
    $overlay_src = get_post_meta($post->ID, 'overlay_src', true);
    $thumbnail = get_the_post_thumbnail_url($post, 'large');
    if (!$poster_src && $thumbnail) {
        $poster_src = $thumbnail;
    }
    if (!$overlay_src && $thumbnail) {
        $overlay_src = $thumbnail;
    }

    $excerpt = get_the_excerpt($post);
    if (!$excerpt) {
        $excerpt = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $post->ID)), 60);
    }

    return [
        'id' => $post->ID,
        'link' => get_permalink($post),
        'kicker' => $kicker,
        'title' => get_the_title($post),
        'meta' => $meta,
        'video_src' => $video_src,
        'video_type' => $video_data['type'],
        'embed_src' => $video_data['embed_src'],
        'embed_id' => isset($video_data['embed_id']) ? $video_data['embed_id'] : '',
        'vimeo_hash' => isset($video_data['vimeo_hash']) ? $video_data['vimeo_hash'] : '',
        'poster_src' => $poster_src ?: '',
        'overlay_src' => $overlay_src ?: '',
        'description' => $excerpt,
    ];
}

function ogft_get_featured_work_items_from_posts($atts = [])
{
    $atts = shortcode_atts(
        [
            'id' => '',
            'work_type' => '',
            'limit' => 6,
        ],
        $atts,
        'open_gate_featured_work'
    );

    $post_ids = [];
    if (!empty($atts['id'])) {
        $post_ids = array_filter(array_map('absint', array_map('trim', explode(',', $atts['id']))));
    }

    $query_args = [
        'post_type' => 'work',
        'post_status' => 'publish',
        'no_found_rows' => true,
    ];

    if ($post_ids) {
        $query_args['post__in'] = $post_ids;
        $query_args['orderby'] = 'post__in';
        $query_args['posts_per_page'] = count($post_ids);
    } else {
        $query_args['posts_per_page'] = (int)$atts['limit'] > 0 ? (int)$atts['limit'] : -1;

        $tax_terms = [];
        if (!empty($atts['work_type'])) {
            $tax_terms = array_filter(array_map('sanitize_title', array_map('trim', explode(',', $atts['work_type']))));
        }
        if ($tax_terms) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'work-type',
                    'field' => 'slug',
                    'terms' => $tax_terms,
                ],
            ];
        }
    }

    $query = new WP_Query($query_args);

    if (!$query->have_posts()) {
        return [];
    }

    $settings = ogft_get_settings();
    $detail_page_id = isset($settings['featured_work_detail_page_id']) ? absint($settings['featured_work_detail_page_id']) : 0;

    $items = [];

    foreach ($query->posts as $post) {
        $terms = get_the_terms($post, 'work-type');
        $kicker = (!is_wp_error($terms) && $terms) ? implode(' / ', wp_list_pluck($terms, 'name')) : '';

        $meta = get_post_meta($post->ID, 'ogft_meta', true);
        if ($meta === '') {
            $meta = get_post_meta($post->ID, 'meta', true);
        }
        if ($meta === '') {
            $meta = get_the_date('', $post);
        }

        $video_src = get_post_meta($post->ID, 'video_src', true);
        if (!$video_src && function_exists('get_field')) {
            $acf_video = get_field('video_src', $post->ID);
            // Support ACF URL or File field (array) return formats.
            if (is_array($acf_video) && isset($acf_video['url'])) {
                $video_src = $acf_video['url'];
            } elseif (is_string($acf_video)) {
                $video_src = $acf_video;
            }
        }
        $video_data = ogft_parse_video_data($video_src);

        $poster_src = get_post_meta($post->ID, 'poster_src', true);
        $overlay_src = get_post_meta($post->ID, 'overlay_src', true);
        $thumbnail = get_the_post_thumbnail_url($post, 'large');
        if (!$poster_src && $thumbnail) {
            $poster_src = $thumbnail;
        }
        if (!$overlay_src && $thumbnail) {
            $overlay_src = $thumbnail;
        }

        $items[] = [
            'id' => $post->ID,
            'link' => get_permalink($post),
            'kicker' => $kicker,
            'title' => (function_exists('get_field') && ($alt = get_field('alternate_title', $post->ID)) ? $alt : get_the_title($post)),
            'meta' => $meta,
            'video_src' => $video_src,
            'video_type' => $video_data['type'],
            'embed_src' => $video_data['embed_src'],
            'embed_id' => isset($video_data['embed_id']) ? $video_data['embed_id'] : '',
            'vimeo_hash' => isset($video_data['vimeo_hash']) ? $video_data['vimeo_hash'] : '',
            'poster_src' => $poster_src ?: '',
            'overlay_src' => $overlay_src ?: '',
            'detail_url' => $detail_page_id ? add_query_arg('ogft_work', $post->ID, get_permalink($detail_page_id)) : get_permalink($post),
        ];
    }

    wp_reset_postdata();

    return $items;
}

function ogft_register_settings_page()
{
    add_menu_page(
        'Open Gate Film Templates',
        'Open Gate Film',
        'manage_options',
        'open-gate-film-templates',
        'ogft_render_settings_page',
        'dashicons-layout',
        58
    );
}
add_action('admin_menu', 'ogft_register_settings_page');

function ogft_register_settings()
{
    register_setting('ogft_settings_group', 'ogft_settings', [
        'sanitize_callback' => 'ogft_sanitize_settings',
    ]);
}
add_action('admin_init', 'ogft_register_settings');

function ogft_sanitize_settings($input)
{
    $sanitized = [];

    $sanitized['enable_featured_work'] = empty($input['enable_featured_work']) ? '0' : '1';
    $sanitized['enable_stats'] = empty($input['enable_stats']) ? '0' : '1';
    $sanitized['enable_featured_slider'] = empty($input['enable_featured_slider']) ? '0' : '1';
    $sanitized['enable_logo_slider'] = empty($input['enable_logo_slider']) ? '0' : '1';
    $sanitized['enable_off_canvas_menu'] = empty($input['enable_off_canvas_menu']) ? '0' : '1';
    $sanitized['featured_work_detail_page_id'] = isset($input['featured_work_detail_page_id'])
        ? (string)absint($input['featured_work_detail_page_id'])
        : '';
    $sanitized['featured_work_cta_url'] = isset($input['featured_work_cta_url'])
        ? esc_url_raw(trim($input['featured_work_cta_url']))
        : '';
    $sanitized['featured_slider_image_ids'] = isset($input['featured_slider_image_ids'])
        ? implode(',', array_filter(array_map('absint', explode(',', $input['featured_slider_image_ids']))))
        : '';
    $sanitized['logo_slider_image_ids'] = isset($input['logo_slider_image_ids'])
        ? implode(',', array_filter(array_map('absint', explode(',', $input['logo_slider_image_ids']))))
        : '';

    $sanitized['stats_items'] = isset($input['stats_items'])
        ? sanitize_textarea_field($input['stats_items'])
        : '';

    $sanitized['stats_marquee_speed'] = isset($input['stats_marquee_speed'])
        ? (string)max(5, min(120, absint($input['stats_marquee_speed'])))
        : '22';

    $sanitized['logo_slider_marquee_speed'] = isset($input['logo_slider_marquee_speed'])
        ? (string)max(5, min(120, absint($input['logo_slider_marquee_speed'])))
        : '24';

    return $sanitized;
}

function ogft_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = ogft_get_settings();
    ?>
    <div class="wrap">
        <h1>Open Gate Film Templates</h1>
        <p>Enable features and update their content. Shortcodes are listed below each section.</p>

        <form method="post" action="options.php">
            <?php settings_fields('ogft_settings_group'); ?>

            <h2>Featured Work</h2>
            <p><strong>Shortcode:</strong> <code>[open_gate_featured_work]</code> (optionally filter by work type slug: <code>[open_gate_featured_work work_type="commercial"]</code>). Uses published <code>work</code> posts.</p>
            <label>
                <input type="checkbox" name="ogft_settings[enable_featured_work]" value="1" <?php checked($settings['enable_featured_work'], '1'); ?> />
                Enable Featured Work
            </label>
            <p>Select the page to open for work detail modals:</p>
            <?php
            wp_dropdown_pages([
                'name' => 'ogft_settings[featured_work_detail_page_id]',
                'selected' => isset($settings['featured_work_detail_page_id']) ? absint($settings['featured_work_detail_page_id']) : 0,
                'show_option_none' => '— Select a page —',
                'option_none_value' => '',
            ]);
            ?>
            <p>CTA button URL (shown in the modal topbar):</p>
            <input type="url" name="ogft_settings[featured_work_cta_url]" value="<?php echo esc_attr($settings['featured_work_cta_url']); ?>" class="regular-text" placeholder="https://example.com/contact" />

            <hr />

            <h2>Stats</h2>
            <p><strong>Shortcode:</strong> <code>[open_gate_stats]</code></p>
            <label>
                <input type="checkbox" name="ogft_settings[enable_stats]" value="1" <?php checked($settings['enable_stats'], '1'); ?> />
                Enable Stats
            </label>
            <p>JSON array of cards (kicker, value, label, bg_image).</p>
            <textarea name="ogft_settings[stats_items]" rows="14" class="large-text code"><?php echo esc_textarea($settings['stats_items']); ?></textarea>

            <p>
                <label for="ogft_stats_marquee_speed">Marquee Speed (seconds per loop, 5–120):</label><br />
                <input type="number" id="ogft_stats_marquee_speed" name="ogft_settings[stats_marquee_speed]" value="<?php echo esc_attr($settings['stats_marquee_speed']); ?>" min="5" max="120" step="1" style="width:80px;" />
            </p>

            <hr />

            <h2>Featured Image Slider</h2>
            <p><strong>Shortcode:</strong> <code>[open_gate_featured_slider]</code>. Renders a main image with clickable thumbnails.</p>
            <label>
                <input type="checkbox" name="ogft_settings[enable_featured_slider]" value="1" <?php checked($settings['enable_featured_slider'], '1'); ?> />
                Enable Featured Slider
            </label>
            <p>Select images for the slider (order is preserved). Thumbnails use medium_large where available.</p>
            <div class="ogft-media-picker" data-input="featured_slider_image_ids">
                <input type="hidden" name="ogft_settings[featured_slider_image_ids]" value="<?php echo esc_attr($settings['featured_slider_image_ids']); ?>" />
                <button type="button" class="button ogft-media-picker-btn">Choose Images</button>
                <div class="ogft-media-picker__preview"></div>
            </div>

            <hr />

            <h2>Logo Slider</h2>
            <p><strong>Shortcode:</strong> <code>[open_gate_logo_slider]</code>. Auto-scrolls logo stacks with GSAP.</p>
            <label>
                <input type="checkbox" name="ogft_settings[enable_logo_slider]" value="1" <?php checked($settings['enable_logo_slider'], '1'); ?> />
                Enable Logo Slider
            </label>
            <p>Select logo images (order is preserved). Layout repeats: two containers with 2 stacked logos, then one container with a single logo.</p>
            <div class="ogft-media-picker" data-input="logo_slider_image_ids">
                <input type="hidden" name="ogft_settings[logo_slider_image_ids]" value="<?php echo esc_attr($settings['logo_slider_image_ids']); ?>" />
                <button type="button" class="button ogft-media-picker-btn">Choose Logos</button>
                <div class="ogft-media-picker__preview"></div>
            </div>

            <p>
                <label for="ogft_logo_slider_marquee_speed">Marquee Speed (seconds per loop, 5–120):</label><br />
                <input type="number" id="ogft_logo_slider_marquee_speed" name="ogft_settings[logo_slider_marquee_speed]" value="<?php echo esc_attr($settings['logo_slider_marquee_speed']); ?>" min="5" max="120" step="1" style="width:80px;" />
            </p>

            <hr />

            <h2>Off-Canvas Menu</h2>
            <p><strong>Shortcode:</strong> <code>[open_gate_menu]</code>. Renders a trigger button that opens a slide-in menu panel.</p>
            <label>
                <input type="checkbox" name="ogft_settings[enable_off_canvas_menu]" value="1" <?php checked($settings['enable_off_canvas_menu'], '1'); ?> />
                Enable Off-Canvas Menu
            </label>
            <p>Assign a menu to the <strong>Off-Canvas Menu</strong> location under <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>">Appearance &rarr; Menus</a>.</p>

            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

function ogft_enqueue_featured_slider_assets()
{
    wp_enqueue_style(
        'ogft-featured-slider',
        OGFT_URL . 'features/featured-slider/style.css',
        [],
        OGFT_VERSION
    );

    wp_enqueue_script(
        'gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
        [],
        '3.12.5',
        true
    );

    wp_enqueue_script(
        'ogft-featured-slider',
        OGFT_URL . 'features/featured-slider/script.js',
        ['gsap'],
        OGFT_VERSION,
        true
    );
}

function ogft_enqueue_logo_slider_assets()
{
    wp_enqueue_style(
        'ogft-logo-slider',
        OGFT_URL . 'features/logo-slider/style.css',
        [],
        OGFT_VERSION
    );

    wp_enqueue_script(
        'gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
        [],
        '3.12.5',
        true
    );

    wp_enqueue_script(
        'ogft-logo-slider',
        OGFT_URL . 'features/logo-slider/script.js',
        ['gsap'],
        OGFT_VERSION,
        true
    );

    $settings = ogft_get_settings();
    wp_localize_script('ogft-logo-slider', 'ogftLogoSliderData', [
        'speed' => $settings['logo_slider_marquee_speed'],
    ]);
}

function ogft_enqueue_featured_work_modal_assets()
{
    wp_enqueue_style(
        'plyr',
        'https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css',
        [],
        '3.7.8'
    );
    wp_enqueue_script(
        'plyr',
        'https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.js',
        [],
        '3.7.8',
        true
    );

    wp_enqueue_style(
        'ogft-featured-work-modal',
        OGFT_URL . 'features/featured-work/modal.css',
        [],
        OGFT_VERSION
    );
    wp_enqueue_script(
        'ogft-featured-work-modal',
        OGFT_URL . 'features/featured-work/modal.js',
        ['plyr'],
        OGFT_VERSION,
        true
    );
}

function ogft_enqueue_stats_assets()
{
    wp_enqueue_style(
        'ogft-stats',
        OGFT_URL . 'features/stats/style.css',
        [],
        OGFT_VERSION
    );

    wp_enqueue_script(
        'gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
        [],
        '3.12.5',
        true
    );

    wp_enqueue_script(
        'ogft-stats',
        OGFT_URL . 'features/stats/script.js',
        ['gsap'],
        OGFT_VERSION,
        true
    );

    $settings = ogft_get_settings();
    wp_localize_script('ogft-stats', 'ogftStatsData', [
        'speed' => $settings['stats_marquee_speed'],
    ]);
}

function ogft_shortcode_featured_work($atts = [])
{
    $settings = ogft_get_settings();
    if (empty($settings['enable_featured_work'])) {
        return '';
    }

    $raw_atts = is_array($atts) ? $atts : [];
    $slider_in_mobile = isset($raw_atts['slider-in-mobile']) && strtolower($raw_atts['slider-in-mobile']) === 'yes';
    $title_limit = isset($raw_atts['title-limit']) ? absint($raw_atts['title-limit']) : 0;
    $autoplay_always = isset($raw_atts['autoplayalways']) && strtolower($raw_atts['autoplayalways']) === 'yes';

    $items = ogft_get_featured_work_items_from_posts($raw_atts);

    if (!$items) {
        return '';
    }

    ogft_enqueue_featured_work_assets($slider_in_mobile);
    ob_start();
    $template = OGFT_PATH . 'features/featured-work/template.php';
    include $template;
    return ob_get_clean();
}
add_shortcode('open_gate_featured_work', 'ogft_shortcode_featured_work');

function ogft_shortcode_featured_slider()
{
    $settings = ogft_get_settings();
    if (empty($settings['enable_featured_slider'])) {
        return '';
    }

    $items = ogft_build_slider_items_from_ids($settings['featured_slider_image_ids']);
    if (!$items) {
        return '';
    }

    ogft_enqueue_featured_slider_assets();

    static $instance = 0;
    $instance++;
    $slider_id = 'ogft-featured-slider-' . $instance;

    ob_start();
    $template = OGFT_PATH . 'features/featured-slider/template.php';
    $ogft_slider_items = $items;
    $ogft_slider_id = $slider_id;
    include $template;
    return ob_get_clean();
}
add_shortcode('open_gate_featured_slider', 'ogft_shortcode_featured_slider');

function ogft_shortcode_logo_slider()
{
    $settings = ogft_get_settings();
    if (empty($settings['enable_logo_slider'])) {
        return '';
    }

    $items = ogft_build_logo_items_from_ids($settings['logo_slider_image_ids']);
    if (!$items) {
        return '';
    }

    ogft_enqueue_logo_slider_assets();

    static $instance = 0;
    $instance++;
    $section_id = 'ogft-logo-slider-' . $instance;

    ob_start();
    $template = OGFT_PATH . 'features/logo-slider/template.php';
    $ogft_logo_items = $items;
    $ogft_logo_id = $section_id;
    include $template;
    return ob_get_clean();
}
add_shortcode('open_gate_logo_slider', 'ogft_shortcode_logo_slider');

function ogft_render_work_modal_root()
{
    echo '<div id="ogft-work-modal-root"></div>';
}

function ogft_maybe_bootstrap_featured_work_modal()
{
    $settings = ogft_get_settings();
    $page_id = isset($settings['featured_work_detail_page_id']) ? absint($settings['featured_work_detail_page_id']) : 0;
    $work_id = isset($_GET['ogft_work']) ? absint($_GET['ogft_work']) : 0;

    if (!$page_id || !$work_id) {
        return;
    }

    if (!is_page($page_id)) {
        return;
    }

    $item = ogft_get_work_item_by_id($work_id);
    if (!$item) {
        return;
    }

    ogft_enqueue_featured_work_modal_assets();

    wp_localize_script('ogft-featured-work-modal', 'ogftWorkModalData', [
        'item' => $item,
        'strings' => [
            'brand' => get_bloginfo('name'),
            'ctaLabel' => __('Request a quote', 'open-gate-film-templates'),
        ],
        'brandLogo' => OGFT_URL . 'assets/open-gate-white.svg',
        'ctaUrl' => !empty($settings['featured_work_cta_url']) ? $settings['featured_work_cta_url'] : '',
    ]);

    add_action('wp_footer', 'ogft_render_work_modal_root');
}
add_action('wp', 'ogft_maybe_bootstrap_featured_work_modal');

function ogft_enqueue_featured_work_assets($slider_in_mobile = false)
{
    wp_enqueue_style(
        'ogft-featured-work',
        OGFT_URL . 'features/featured-work/style.css',
        [],
        OGFT_VERSION
    );
    wp_enqueue_script(
        'ogft-featured-work',
        OGFT_URL . 'features/featured-work/script.js',
        [],
        OGFT_VERSION,
        true
    );

    if ($slider_in_mobile) {
        if (!wp_style_is('swiper-css', 'enqueued') && !wp_style_is('swiper-css', 'registered')) {
            wp_enqueue_style(
                'swiper-css',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                [],
                '11'
            );
        } else {
            wp_enqueue_style('swiper-css');
        }

        if (!wp_script_is('swiper-js', 'enqueued') && !wp_script_is('swiper-js', 'registered')) {
            wp_enqueue_script(
                'swiper-js',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                [],
                '11',
                true
            );
        } else {
            wp_enqueue_script('swiper-js');
        }

        wp_enqueue_script(
            'ogft-featured-work-slider',
            OGFT_URL . 'features/featured-work/slider.js',
            ['swiper-js'],
            OGFT_VERSION,
            true
        );
    }
}

function ogft_admin_assets($hook)
{
    if ($hook !== 'toplevel_page_open-gate-film-templates') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'ogft-admin',
        OGFT_URL . 'features/featured-slider/admin.css',
        [],
        OGFT_VERSION
    );
    wp_enqueue_script(
        'ogft-slider-admin',
        OGFT_URL . 'features/featured-slider/admin.js',
        ['jquery', 'media-editor'],
        OGFT_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'ogft_admin_assets');

function ogft_shortcode_stats()
{
    $settings = ogft_get_settings();
    if (empty($settings['enable_stats'])) {
        return '';
    }

    $items = ogft_parse_json_items($settings['stats_items'], ogft_default_stats_items());
    if (!$items) {
        return '';
    }

    ogft_enqueue_stats_assets();

    static $instance = 0;
    $instance++;
    $section_id = 'ogft-stats-' . $instance;

    ob_start();
    $template = OGFT_PATH . 'features/stats/template.php';
    include $template;
    return ob_get_clean();
}
add_shortcode('open_gate_stats', 'ogft_shortcode_stats');

function ogft_get_off_canvas_menu_items()
{
    $locations = get_nav_menu_locations();
    if (empty($locations['ogft_off_canvas'])) {
        return [];
    }

    $menu_items = wp_get_nav_menu_items($locations['ogft_off_canvas']);
    if (!$menu_items) {
        return [];
    }

    $top_level = [];
    $children = [];

    foreach ($menu_items as $item) {
        $item->children = [];
        if ((int)$item->menu_item_parent === 0) {
            $top_level[$item->ID] = $item;
        } else {
            $children[] = $item;
        }
    }

    foreach ($children as $child) {
        if (isset($top_level[$child->menu_item_parent])) {
            $top_level[$child->menu_item_parent]->children[] = $child;
        }
    }

    return array_values($top_level);
}

function ogft_enqueue_off_canvas_menu_assets()
{
    wp_enqueue_style(
        'ogft-off-canvas-menu',
        OGFT_URL . 'features/off-canvas-menu/style.css',
        [],
        OGFT_VERSION
    );
    wp_enqueue_script(
        'ogft-off-canvas-menu',
        OGFT_URL . 'features/off-canvas-menu/script.js',
        [],
        OGFT_VERSION,
        true
    );
}

function ogft_render_off_canvas_panel()
{
    $menu_items = ogft_get_off_canvas_menu_items();
    if (empty($menu_items)) {
        return;
    }
    include OGFT_PATH . 'features/off-canvas-menu/panel.php';
}

function ogft_shortcode_off_canvas_menu($atts = [])
{
    $settings = ogft_get_settings();
    if (empty($settings['enable_off_canvas_menu'])) {
        return '';
    }

    ogft_enqueue_off_canvas_menu_assets();

    static $panel_hooked = false;
    if (!$panel_hooked) {
        add_action('wp_footer', 'ogft_render_off_canvas_panel');
        $panel_hooked = true;
    }

    ob_start();
    include OGFT_PATH . 'features/off-canvas-menu/template.php';
    return ob_get_clean();
}
add_shortcode('open_gate_menu', 'ogft_shortcode_off_canvas_menu');

function ogft_enqueue_video_lightbox_assets()
{
    wp_enqueue_style(
        'plyr',
        'https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css',
        [],
        '3.7.8'
    );
    wp_enqueue_script(
        'plyr',
        'https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.js',
        [],
        '3.7.8',
        true
    );

    wp_enqueue_style(
        'ogft-featured-work-modal',
        OGFT_URL . 'features/featured-work/modal.css',
        [],
        OGFT_VERSION
    );

    wp_enqueue_script(
        'ogft-video-lightbox',
        OGFT_URL . 'features/video-lightbox/script.js',
        ['plyr'],
        OGFT_VERSION,
        true
    );

    static $localized = false;
    if (!$localized) {
        $settings = ogft_get_settings();
        wp_localize_script('ogft-video-lightbox', 'ogftVideoLightboxConfig', [
            'brand' => get_bloginfo('name'),
            'brandLogo' => OGFT_URL . 'assets/open-gate-white.svg',
            'ctaLabel' => __('Request a quote', 'open-gate-film-templates'),
            'ctaUrl' => !empty($settings['featured_work_cta_url']) ? $settings['featured_work_cta_url'] : '',
        ]);
        $localized = true;
    }
}

function ogft_shortcode_video_lightbox($atts = [])
{
    $atts = shortcode_atts([
        'video' => '',
        'thumbnail' => '',
        'type' => 'thumbnail',
        'button-text' => 'Watch Video',
    ], $atts, 'open_gate_video_lightbox');

    if (empty($atts['video'])) {
        return '';
    }

    $video_data = ogft_parse_video_data($atts['video']);

    $lightbox_video = [
        'url' => $atts['video'],
        'video_type' => $video_data['type'],
        'embed_src' => $video_data['embed_src'],
        'embed_id' => isset($video_data['embed_id']) ? $video_data['embed_id'] : '',
        'vimeo_hash' => isset($video_data['vimeo_hash']) ? $video_data['vimeo_hash'] : '',
        'thumbnail' => $atts['thumbnail'],
    ];

    static $instance = 0;
    $instance++;

    $lightbox_id = 'ogft-video-lightbox-' . $instance;
    $lightbox_type = $atts['type'];
    $lightbox_thumb = $atts['thumbnail'];
    $lightbox_text = $atts['button-text'];

    ogft_enqueue_video_lightbox_assets();

    ob_start();
    include OGFT_PATH . 'features/video-lightbox/template.php';
    return ob_get_clean();
}
add_shortcode('open_gate_video_lightbox', 'ogft_shortcode_video_lightbox');
