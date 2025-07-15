<?php
class FIRE_Shortcodes {
    public function __construct() {
        add_shortcode('fire_total', [$this, 'shortcode_total']);
        add_shortcode('fire_field', [$this, 'shortcode_field']);
        add_shortcode('fire_template', [$this, 'shortcode_template']);
        add_shortcode('fire_debug_meta', [$this, 'shortcode_debug_meta']);
        add_shortcode('fire_stars_only', [ $this, 'shortcode_stars_only' ]);
    }

    public function shortcode_total($atts) {
        $post_id = get_the_ID();
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        if (!class_exists('FIRE_Template')) {
            require_once plugin_dir_path(__FILE__) . 'class-fire-template.php';
        }
        if ($overall !== '' && $overall !== false) {
            $rounded_overall = round($overall * 2) / 2;
            $stars = FIRE_Template::stars($rounded_overall);
            $display_overall = (fmod($rounded_overall, 1) == 0.0) ? number_format($rounded_overall, 0) : number_format($rounded_overall, 1);
            return '<p><span class="fire-stars-label" style="font-weight:bold;">Overall rating:</span> ' . preg_replace('/<span class="fire-stars-value"[^>]*>.*?<\/span>/', '<span class="fire-stars-value" style="font-weight:bold;vertical-align:middle;">' . $display_overall . ' / 5</span>', $stars) . '</p>';
        } else {
            return '';
        }
    }

    public function shortcode_field($atts) {
        $atts = shortcode_atts(['slug' => ''], $atts, 'fire_field');
        if (empty($atts['slug'])) return '';
        $post_id = get_the_ID();
        $score = get_post_meta($post_id, "_" . $atts['slug'], true);
        $label = ucwords(str_replace('-', ' ', str_replace('fire-editorial-stars-', '', $atts['slug'])));
        if ($score === '' || $score === false) {
            $score = 0;
        }
        if (!class_exists('FIRE_Template')) {
            require_once plugin_dir_path(__FILE__) . 'class-fire-template.php';
        }
        $display_score = (fmod($score, 1) == 0.0) ? number_format($score, 0) : number_format($score, 1);
        $stars = FIRE_Template::stars($score);
        $stars = preg_replace('/<span class="fire-stars-value"[^>]*>.*?<\/span>/', '<span class="fire-stars-value" style="font-weight:bold;vertical-align:middle;">' . $display_score . ' / 5</span>', $stars);
        return '<p>' . esc_html($label) . ': ' . $stars . '</p>';
    }

/*    public function shortcode_template($atts) {
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);
        $config = get_option('sypesr_criteria_config', []);
        $fields = $config[$post_type] ?? [];
        if (!$fields) return '';

        $output = '<div class="fire-rating-template">';
        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = $field['label'];
            $score = get_post_meta($post_id, "_{$slug}", true);
            if ($score !== '') {
                $display_score = (fmod($score, 1) == 0.0) ? number_format($score, 0) : number_format($score, 1);
                $output .= '<p>' . esc_html($label) . ': ⭐ ' . $display_score . ' / 5</p>';
            }
        }
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        if ($overall) {
            $rounded_overall = round($overall * 2) / 2;
            $display_overall = (fmod($rounded_overall, 1) == 0.0) ? number_format($rounded_overall, 0) : number_format($rounded_overall, 1);
            $output .= '<p><strong>Overall:</strong> ⭐ ' . $display_overall . ' / 5</p>';
        }
        $output .= '</div>';
        return $output;
    }
*/

public function shortcode_template($atts) {
    if (!class_exists('FIRE_Template')) {
        require_once plugin_dir_path(__FILE__) . 'class-fire-template.php';
    }

    $rendered = FIRE_Template::render();

    if (!empty($rendered)) {
        return $rendered;
    }

    // fallback output (your current logic)
    $post_id = get_the_ID();
    $post_type = get_post_type($post_id);
    $config = get_option('sypesr_criteria_config', []);
    $fields = $config[$post_type] ?? [];
    if (!$fields) return '';

    $output = '<div class="fire-rating-template">';
    foreach ($fields as $field) {
        $slug = $field['slug'];
        $label = $field['label'];
        $score = get_post_meta($post_id, "_{$slug}", true);
        if ($score !== '') {
            $display_score = (fmod($score, 1) == 0.0) ? number_format($score, 0) : number_format($score, 1);
            $output .= '<p>' . esc_html($label) . ': ⭐ ' . $display_score . ' / 5</p>';
        }
    }

    $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
    if ($overall) {
        $rounded_overall = round($overall * 2) / 2;
        $display_overall = (fmod($rounded_overall, 1) == 0.0) ? number_format($rounded_overall, 0) : number_format($rounded_overall, 1);
        $output .= '<p><strong>Overall:</strong> ⭐ ' . $display_overall . ' / 5</p>';
    }

    $output .= '</div>';
    return $output;
}



    public function shortcode_debug_meta($atts) {
        $post_id = get_the_ID();
        $meta = get_post_meta($post_id);
        ob_start();
        echo '<pre>';
        print_r($meta);
        echo '</pre>';
        return ob_get_clean();
    }

    public function shortcode_stars_only($atts) {
        $post_id = get_the_ID();
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        if (!class_exists('FIRE_Template')) {
            require_once plugin_dir_path(__FILE__) . 'class-fire-template.php';
        }
        if ($overall !== '' && $overall !== false) {
            $rounded_overall = round($overall * 2) / 2;
            $stars_html = FIRE_Template::stars($rounded_overall);
            // Remove the value span
            $stars_html = preg_replace('/<span class="fire-stars-value"[^>]*>.*?<\/span>/', '', $stars_html);
            return $stars_html;
        } else {
            return '';
        }
    }
}
