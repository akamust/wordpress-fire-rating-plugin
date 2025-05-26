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
                        $slug_part = !empty($field['slug']) ? $field['slug'] : $field['label'];
                        $slug_sanitized = sanitize_title($slug_part);
                        $slug = 'fire-editorial-stars-' . $slug_sanitized;
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
        // Fetch all public post types except 'attachment'
        $all_post_types = get_post_types(['public' => true], 'objects');
        unset($all_post_types['attachment']);
        $enabled_post_types = get_option('sypesr_enabled_post_types', []);
        $criteria_config = get_option('sypesr_criteria_config', []);
        ?>
        <div class="wrap">
            <h1>FIRE Rating Settings</h1>
            <form method="post">
                <?php wp_nonce_field('save_fire_settings', 'fire_settings_nonce'); ?>
                <h2 class="nav-tab-wrapper">
                    <a href="#tab-general" class="nav-tab nav-tab-active">General</a>
                    <?php foreach ($enabled_post_types as $pt) : ?>
                        <a href="#tab-<?php echo esc_attr($pt); ?>" class="nav-tab"><?php echo esc_html($all_post_types[$pt]->labels->singular_name); ?></a>
                    <?php endforeach; ?>
                    <a href="#tab-template" class="nav-tab">Template</a>
                    <a href="#tab-help" class="nav-tab">Help</a>
                    <a href="#tab-debug" class="nav-tab">Debug</a>
                </h2>

                <div id="tab-general" class="tab-content" style="display:block;">
                    <h3>General</h3>
                    <p>Select post types and configure rating criteria in other sections.</p>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable for Post Types</th>
                            <td>
                                <?php foreach ($all_post_types as $pt) : ?>
                                    <label style="display:block;margin-bottom:4px;">
                                        <input type="checkbox" name="fire_enabled_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $enabled_post_types)); ?> />
                                        <?php echo esc_html($pt->labels->singular_name); ?> (<?php echo esc_html($pt->name); ?>)
                                    </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php foreach ($enabled_post_types as $pt) :
                    $fields = $criteria_config[$pt] ?? [];
                ?>
                <div id="tab-<?php echo esc_attr($pt); ?>" class="tab-content" style="display:none;">
                    <h3><?php echo esc_html($all_post_types[$pt]->labels->singular_name); ?> Criteria</h3>
                    <div class="fire-weight-warning" style="display:none;color:#b32d2e;font-weight:bold;margin-bottom:10px;">Total weight must equal 100%!</div>
                    <table class="form-table fire-fields-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Slug</th>
                                <th>Weight (%)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($fields)) :
                            foreach ($fields as $i => $field) : ?>
                            <tr>
                                <td><input type="text" name="fire_criteria[<?php echo esc_attr($pt); ?>][<?php echo $i; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" /></td>
                                <td><span style="white-space:nowrap;font-size:90%;color:#666;">fire-editorial-stars-</span><input type="text" name="fire_criteria[<?php echo esc_attr($pt); ?>][<?php echo $i; ?>][slug]" value="<?php echo esc_attr(str_replace('fire-editorial-stars-', '', $field['slug'])); ?>" style="width: 180px; display: inline-block; margin-left: 2px;" /></td>
                                <td><input type="number" class="fire-weight-input" step="0.1" min="0" max="100" name="fire_criteria[<?php echo esc_attr($pt); ?>][<?php echo $i; ?>][weight]" value="<?php echo esc_attr($field['weight']); ?>" /></td>
                                <td><button type="button" class="button fire-remove-field">Remove</button></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                    <p><button type="button" class="button fire-add-field" data-pt="<?php echo esc_attr($pt); ?>">Add Field</button></p>
                    <p><em>Slugs are auto-prefixed with <code>fire-editorial-stars-</code> when saved.</em></p>
                </div>
                <?php endforeach; ?>

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

                <div id="tab-debug" class="tab-content" style="display:none;">
                    <h3>Debug: All Configured Slugs</h3>
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Post Type</th>
                                <th>Field Label</th>
                                <th>Slug (meta key)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($enabled_post_types as $pt) :
                            $fields = $criteria_config[$pt] ?? [];
                            foreach ($fields as $field) : ?>
                                <tr>
                                    <td><?php echo esc_html($pt); ?></td>
                                    <td><?php echo esc_html($field['label']); ?></td>
                                    <td><code>_<?php echo esc_html($field['slug']); ?></code></td>
                                </tr>
                        <?php endforeach; endforeach; ?>
                        </tbody>
                    </table>
                    <p style="margin-top:1em;color:#666;">These are the meta keys used for each field. If you change a slug, you may need to re-save the post to update the value for the new key.</p>
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

            // Add/remove fields
            document.querySelectorAll('.fire-add-field').forEach(btn => {
                btn.addEventListener('click', function() {
                    const pt = btn.getAttribute('data-pt');
                    const table = document.querySelector(`#tab-${pt} .fire-fields-table tbody`);
                    const idx = table.querySelectorAll('tr').length;
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input type="text" name="fire_criteria[${pt}][${idx}][label]" /></td>
                        <td><span style="white-space:nowrap;font-size:90%;color:#666;">fire-editorial-stars-</span><input type="text" name="fire_criteria[${pt}][${idx}][slug]" /></td>
                        <td><input type="number" class="fire-weight-input" step="0.1" min="0" max="100" name="fire_criteria[${pt}][${idx}][weight]" /></td>
                        <td><button type="button" class="button fire-remove-field">Remove</button></td>
                    `;
                    table.appendChild(row);
                    checkWeights(pt);
                });
            });
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('fire-remove-field')) {
                    const tr = e.target.closest('tr');
                    const tab = tr.closest('.tab-content');
                    const pt = tab.id.replace('tab-', '');
                    tr.remove();
                    checkWeights(pt);
                }
            });
            // Live weight validation
            function checkWeights(pt) {
                const tab = document.getElementById('tab-' + pt);
                const weights = tab.querySelectorAll('.fire-weight-input');
                let total = 0;
                weights.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                const warning = tab.querySelector('.fire-weight-warning');
                if (Math.abs(total - 100) > 0.01 && weights.length > 0) {
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            }
            <?php foreach ($enabled_post_types as $pt) : ?>
            document.querySelectorAll('#tab-<?php echo esc_attr($pt); ?> .fire-weight-input').forEach(input => {
                input.addEventListener('input', function() {
                    checkWeights('<?php echo esc_js($pt); ?>');
                });
            });
            checkWeights('<?php echo esc_js($pt); ?>');
            <?php endforeach; ?>

            // Prevent form submission if weights are not 100%
            document.querySelector('form').addEventListener('submit', function(e) {
                let valid = true;
                <?php foreach ($enabled_post_types as $pt) : ?>
                var tab = document.getElementById('tab-<?php echo esc_js($pt); ?>');
                var weights = tab.querySelectorAll('.fire-weight-input');
                var total = 0;
                weights.forEach(function(input) {
                    total += parseFloat(input.value) || 0;
                });
                if (Math.abs(total - 100) > 0.01 && weights.length > 0) {
                    valid = false;
                    tab.querySelector('.fire-weight-warning').style.display = 'block';
                }
                <?php endforeach; ?>
                if (!valid) {
                    alert('Each post type criteria must have a total weight of exactly 100%.');
                    e.preventDefault();
                }
            });

            // Auto-fill slug field on label blur if slug is empty
            document.addEventListener('blur', function(e) {
                if (e.target && e.target.matches('input[type="text"][name*="[label]"]')) {
                    var labelInput = e.target;
                    var tr = labelInput.closest('tr');
                    var slugInput = tr.querySelector('input[type="text"][name*="[slug]"]');
                    if (slugInput && !slugInput.value) {
                        var label = labelInput.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                        slugInput.value = label;
                    }
                }
            }, true);
        });
        </script>
        <?php
    }
}
