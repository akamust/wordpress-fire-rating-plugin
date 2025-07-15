<?php
class FIRE_Template {
    public static function render($post_id = null) {
        if (!$post_id) $post_id = get_the_ID();
        $post_type = get_post_type($post_id);
        $config = get_option('sypesr_criteria_config', []);
        $template = get_option('sypesr_template_html', '');

        if (!$template || !isset($config[$post_type])) return '';

        $fields = $config[$post_type];
        $output_fields = '';

        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = esc_html($field['label']);
            $value = get_post_meta($post_id, "_{$slug}", true);
            if ($value !== '') {
                $output_fields .= "<p>{$label}: " . self::stars($value, false) . "</p>";
            }
        }

        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        $stars = self::stars($overall, true);

        $replaced = $template;
        $replaced = str_replace('{fields}', $output_fields, $replaced);
        $replaced = str_replace('{overall}', number_format($overall, 1), $replaced);
        $replaced = str_replace('{stars}', $stars, $replaced);

        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = esc_html($field['label']);
            $value = get_post_meta($post_id, "_{$slug}", true);
            $replaced = str_replace("{field:$slug}", "{$label}: " . self::stars($value, false) . " " . number_format($value, 1) . " / 5", $replaced);
        }

        return $replaced;
    }

    public static function stars($value, $is_overall = false) {
        if ($value === '' || $value === false) return '';
        $value = floatval($value);
        $stars_html = '';
        $unique = uniqid('star');
        for ($i = 1; $i <= 5; $i++) {
            if ($value >= $i) {
                // Full star
                $stars_html .= '<svg width="20" height="20" viewBox="0 0 20 20" style="vertical-align:middle;"><polygon points="10,1 12.59,7.36 19.51,7.36 13.96,11.64 16.55,18 10,13.72 3.45,18 6.04,11.64 0.49,7.36 7.41,7.36" fill="#FFD700" stroke="#FFD700"/></svg>';
            } elseif ($value > $i - 1) {
                // Partial star
                $percent = ($value - ($i - 1));
                $grad_id = $unique . '_starGrad' . $i;
                $stars_html .= '<svg width="20" height="20" viewBox="0 0 20 20" style="vertical-align:middle;">
                    <defs>
                        <clipPath id="' . $grad_id . '">
                            <rect x="0" y="0" width="' . (20 * $percent) . '" height="20" />
                        </clipPath>
                    </defs>
                    <polygon points="10,1 12.59,7.36 19.51,7.36 13.96,11.64 16.55,18 10,13.72 3.45,18 6.04,11.64 0.49,7.36 7.41,7.36" fill="#e0e0e0" stroke="#FFD700"/>
                    <polygon points="10,1 12.59,7.36 19.51,7.36 13.96,11.64 16.55,18 10,13.72 3.45,18 6.04,11.64 0.49,7.36 7.41,7.36" fill="#FFD700" stroke="#FFD700" clip-path="url(#' . $grad_id . ')" />
                </svg>';
            } else {
                // Empty star
                $stars_html .= '<svg width="20" height="20" viewBox="0 0 20 20" style="vertical-align:middle;"><polygon points="10,1 12.59,7.36 19.51,7.36 13.96,11.64 16.55,18 10,13.72 3.45,18 6.04,11.64 0.49,7.36 7.41,7.36" fill="#e0e0e0" stroke="#FFD700"/></svg>';
            }
        }
        $display_value = $is_overall ? number_format($value, 1) : (fmod($value, 1) == 0.0 ? number_format($value, 0) : number_format($value, 1));
        return '<span class="fire-stars" style="display:inline-block;line-height:1;">' . $stars_html . '</span> <span class="fire-stars-value" style="font-weight:bold;vertical-align:middle;">' . $display_value . ' / 5</span>';
    }
}
