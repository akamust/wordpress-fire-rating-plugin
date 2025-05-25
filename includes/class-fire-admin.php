<?php
class FIRE_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'save_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            'FIRE Ratings',
            'FIRE Ratings',
            'manage_options',
            'fire-settings',
            [$this, 'render_settings_page']
        );
    }

    public function save_settings() {
        if (isset($_POST['fire_settings_nonce']) && wp_verify_nonce($_POST['fire_settings_nonce'], 'save_fire_settings')) {
            update_option('sypesr_enabled_post_types', array_map('sanitize_text_field', $_POST['fire_enabled_post_types'] ?? []));

            $raw = $_POST['fire_criteria'] ?? [];
            $cleaned = [];

            foreach ($raw as $pt => $fields) {
                foreach ($fields as $field) {
                    if (!empty($field['label']) && is_numeric($field['weight'])) {
                        $slug_raw = !empty($field['slug']) ? $field['slug'] : $field['label'];
                        $slug = 'fire-editorial-stars-' . sanitize_title($slug_raw);
                        $cleaned[$pt][] = [
                            'label' => sanitize_text_field($field['label']),
                            'slug'  => $slug,
                            'weight' => floatval($field['weight']),
                        ];
                    }
                }
            }

            update_option('sypesr_criteria_config', $cleaned);
            update_option('sypesr_template_html', wp_kses_post($_POST['fire_template_html'] ?? ''));
        }
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>FIRE Rating Settings</h1>
            <form method="post">
                <?php wp_nonce_field('save_fire_settings', 'fire_settings_nonce'); ?>
                <h2 class="nav-tab-wrapper">
                    <a href="#tab-general" class="nav-tab nav-tab-active">General</a>
                    <a href="#tab-template" class="nav-tab">Template</a>
                    <a href="#tab-help" class="nav-tab">Help</a>
                </h2>

                <div id="tab-general" class="tab-content" style="display:block;">
                    <h3>General</h3>
                    <p>Select post types and configure rating criteria in other sections.</p>
                </div>

                <div id="tab-template" class="tab-content" style="display:none;">
                    <h3>Custom Output Template</h3>
                    <p>Use placeholders: <code>{fields}</code>, <code>{field:slug}</code>, <code>{overall}</code>, <code>{stars}</code></p>
                    <textarea name="fire_template_html" rows="10" cols="100" style="width:100%;"><?php echo esc_textarea(get_option('sypesr_template_html', '')); ?></textarea>
                </div>

                <div id="tab-help" class="tab-content" style="display:none;">
                    <h3>FIRE Plugin Help</h3>
                    <ul>
                        <li><strong>General:</strong> Select post types where you want to use editorial ratings.</li>
                        <li><strong>Criteria:</strong> Define rating fields (label, slug, and weight %). Slugs are auto-prefixed with <code>fire-editorial-stars-</code>.</li>
                        <li><strong>Weight:</strong> The sum of weights for all fields should equal 100%.</li>
                        <li><strong>Post Editor:</strong> Use the FIRE meta box to enter scores (0.0 to 5.0). The plugin will calculate a weighted average.</li>
                        <li><strong>Shortcodes:</strong>
                            <ul>
                                <li><code>[fire_total]</code> — Displays the overall score</li>
                                <li><code>[fire_field slug="fire-editorial-stars-games"]</code> — Displays a single field</li>
                                <li><code>[fire_template]</code> — Uses your custom layout</li>
                            </ul>
                        </li>
                        <li><strong>Template placeholders:</strong>
                            <ul>
                                <li><code>{fields}</code> — Full field list</li>
                                <li><code>{field:slug}</code> — Specific field by slug</li>
                                <li><code>{overall}</code> — Numeric total</li>
                                <li><code>{stars}</code> — Star rendering of total</li>
                            </ul>
                        </li>
                        <li><strong>Schema:</strong> JSON-LD is auto-injected into the page head for search engines.</li>
                        <li><strong>Styling:</strong> Use CSS to style <code>.fire-rating</code> and <code>.fire-stars</code>.</li>
                    </ul>
                </div>

                <p><input type="submit" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-tab');
            const contents = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    tabs.forEach(t => t.classList.remove('nav-tab-active'));
                    contents.forEach(c => c.style.display = 'none');
                    tab.classList.add('nav-tab-active');
                    document.querySelector(tab.getAttribute('href')).style.display = 'block';
                });
            });
        });
        </script>
        <?php
    }
}
