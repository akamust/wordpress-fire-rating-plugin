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
            $label = $field['label'];
            $value = get_post_meta($post_id, "_{$slug}", true);
            if ($value !== '') {
                $output_fields .= "<p>{$label}: " . self::stars($value) . " {$value} / 5</p>";
            }
        }

        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        $stars = self::stars($overall);

        $replaced = $template;
        $replaced = str_replace('{fields}', $output_fields, $replaced);
        $replaced = str_replace('{overall}', $overall, $replaced);
        $replaced = str_replace('{stars}', $stars, $replaced);

        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = $field['label'];
            $value = get_post_meta($post_id, "_{$slug}", true);
            $replaced = str_replace("{field:$slug}", "{$label}: " . self::stars($value) . " {$value} / 5", $replaced);
        }

        return $replaced;
    }

    public static function stars($value) {
        if ($value === '') return '';
        $stars = floor($value);
        $half = ($value - $stars >= 0.25 && $value - $stars <= 0.75);
        $full = str_repeat('★', $stars);
        $halfStar = $half ? '½' : '';
        $empty = str_repeat('☆', 5 - $stars - ($half ? 1 : 0));
        return "<span class='fire-stars'>{$full}{$halfStar}{$empty}</span>";
    }
}
