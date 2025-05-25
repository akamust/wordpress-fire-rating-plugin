<?php
class FIRE_Columns {
    public function __construct() {
        add_action('admin_init', [$this, 'init']);
    }

    public function init() {
        $post_types = get_option('sypesr_enabled_post_types', []);
        foreach ($post_types as $pt) {
            add_filter("manage_{$pt}_posts_columns", function($columns) {
                $columns['fire_overall'] = 'FIRE Rating';
                return $columns;
            });

            add_action("manage_{$pt}_posts_custom_column", function($column, $post_id) {
                if ($column === 'fire_overall') {
                    $score = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
                    echo $score ? esc_html($score . ' / 5') : '—';
                }
            }, 10, 2);

            add_filter("manage_edit-{$pt}_sortable_columns", function($columns) {
                $columns['fire_overall'] = 'fire_overall';
                return $columns;
            });

            add_action('pre_get_posts', function($query) use ($pt) {
                if (!is_admin() || !$query->is_main_query()) return;
                if ($query->get('orderby') === 'fire_overall') {
                    $query->set('meta_key', '_fire_editorial_stars_overall');
                    $query->set('orderby', 'meta_value_num');
                }
            });
        }
    }
}
