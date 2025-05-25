<?php
class FIRE_Shortcodes {
    public function __construct() {
        add_shortcode('fire_total', [$this, 'shortcode_total']);
        add_shortcode('fire_field', [$this, 'shortcode_field']);
        add_shortcode('fire_template', [$this, 'shortcode_template']);
    }

    public function shortcode_total($atts) {
        $post_id = get_the_ID();
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        return $overall ? "⭐ " . esc_html($overall) . " / 5" : '';
    }

    public function shortcode_field($atts) {
        $atts = shortcode_atts(['slug' => ''], $atts, 'fire_field');
        if (empty($atts['slug'])) return '';
        $post_id = get_the_ID();
        $score = get_post_meta($post_id, "_" . $atts['slug'], true);
        $label = ucwords(str_replace('-', ' ', str_replace('fire-editorial-stars-', '', $atts['slug'])));
        return $score ? esc_html($label . ': ⭐ ' . $score . ' / 5') : '';
    }

    public function shortcode_template($atts) {
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
                $output .= '<p>' . esc_html($label) . ': ⭐ ' . esc_html($score) . ' / 5</p>';
            }
        }
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        if ($overall) {
            $output .= '<p><strong>Overall:</strong> ⭐ ' . esc_html($overall) . ' / 5</p>';
        }
        $output .= '</div>';
        return $output;
    }
}
