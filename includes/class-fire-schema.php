<?php
class FIRE_Schema {
    public function __construct() {
        add_action('wp_head', [$this, 'output_schema']);
    }

    public function output_schema() {
        if (!is_single()) return;

        $post_id = get_the_ID();
        $post_title = get_the_title($post_id);
        $overall = get_post_meta($post_id, '_fire_editorial_stars_overall', true);
        if (!$overall) return;

        $post_type = get_post_type($post_id);
        $config = get_option('sypesr_criteria_config', []);
        $fields = $config[$post_type] ?? [];

        $aspects = [];
        foreach ($fields as $field) {
            $slug = $field['slug'];
            $label = $field['label'];
            $score = get_post_meta($post_id, "_{$slug}", true);
            if ($score !== '') {
                $aspects[] = [
                    "@type" => "Thing",
                    "name" => $label
                ];
            }
        }

        $author_id = get_post_field('post_author', $post_id);
        $author_name = get_the_author_meta('display_name', $author_id);

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Review",
            "author" => [
                "@type" => "Person",
                "name" => $author_name
            ],
            "itemReviewed" => [
                "@type" => "Organization",
                "name" => $post_title
            ],
            "reviewRating" => [
                "@type" => "Rating",
                "ratingValue" => $overall,
                "bestRating" => 5
            ],
        ];

        if (!empty($aspects)) {
            $schema["reviewAspect"] = $aspects;
        }

        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}
