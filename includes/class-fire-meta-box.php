<?php
class FIRE_Meta_Box {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
    }

    public function register_meta_box() {
        $enabled_post_types = get_option('sypesr_enabled_post_types', []);
        foreach ($enabled_post_types as $post_type) {
            add_meta_box(
                'fire_editorial_rating',
                'FIRE Editorial Rating',
                [$this, 'render_meta_box'],
                $post_type,
                'normal',
                'default'
            );
        }
    }

    public function render_meta_box($post) {
        $config = get_option('sypesr_criteria_config', []);
        $post_type = get_post_type($post);
        $fields = $config[$post_type] ?? [];

        wp_nonce_field('fire_meta_box_nonce', 'fire_meta_box_nonce_field');

        echo '<table class="form-table">';
        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = $field['label'];
            $value = get_post_meta($post->ID, "_{$slug}", true);
            echo "<tr><th scope='row'><label for='{$slug}'>{$label}</label></th>";
            echo "<td><input type='number' step='0.1' min='0' max='5' name='fire_rating[{$slug}]' id='{$slug}' value='" . esc_attr($value) . "' class='small-text' /></td></tr>";
        }
        echo '</table>';

        $overall = get_post_meta($post->ID, '_fire_editorial_stars_overall', true);
        if ($overall) {
            echo '<p><strong>Overall Score:</strong> ' . esc_html($overall) . ' / 5</p>';
        }
    }

    public function save_meta_box($post_id) {
        if (!isset($_POST['fire_meta_box_nonce_field']) || !wp_verify_nonce($_POST['fire_meta_box_nonce_field'], 'fire_meta_box_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (!isset($_POST['fire_rating'])) return;

        $ratings = $_POST['fire_rating'];
        $config = get_option('sypesr_criteria_config', []);
        $post_type = get_post_type($post_id);
        $fields = $config[$post_type] ?? [];

        $total = 0;
        $weight_sum = 0;

        foreach ($fields as $field) {
            $slug = $field['slug'];
            $weight = floatval($field['weight']);
            $val = isset($ratings[$slug]) ? floatval($ratings[$slug]) : 0;
            $val = max(0, min(5, $val)); // clamp between 0–5
            update_post_meta($post_id, "_{$slug}", $val);
            $total += $val * $weight;
            $weight_sum += $weight;
        }

        if ($weight_sum > 0) {
            $overall = round($total / $weight_sum, 1);
            update_post_meta($post_id, '_fire_editorial_stars_overall', $overall);
        }
    }
}
