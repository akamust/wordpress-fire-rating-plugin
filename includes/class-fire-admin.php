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
        }

        if (isset($_POST['sypesr_reset']) && check_admin_referer('sypesr_reset_action', 'sypesr_reset_nonce')) {
            delete_option('sypesr_enabled_post_types');
            delete_option('sypesr_criteria_config');
            wp_redirect(admin_url('options-general.php?page=fire-settings&reset=1'));
            exit;
        }
    }

    public function render_settings_page() {
        $post_types = get_post_types(['public' => true], 'objects');
        $enabled = get_option('sypesr_enabled_post_types', []);
        $criteria = get_option('sypesr_criteria_config', []);
        ?>
        <div class="wrap">
            <h1>FIRE Rating Settings</h1>
            <form method="post">
                <?php wp_nonce_field('save_fire_settings', 'fire_settings_nonce'); ?>

                <h2 class="nav-tab-wrapper">
                    <a href="#tab-general" class="nav-tab nav-tab-active">General</a>
                    <?php foreach ($enabled as $pt): ?>
                        <a href="#tab-<?php echo esc_attr($pt); ?>" class="nav-tab"><?php echo esc_html($post_types[$pt]->labels->name); ?></a>
                    <?php endforeach; ?>
                </h2>

                <div id="tab-general" class="tab-content" style="display:block;">
                    <h3>Select post types for FIRE:</h3>
                    <?php foreach ($post_types as $pt): ?>
                        <label><input type="checkbox" name="fire_enabled_post_types[]" value="<?php echo esc_attr($pt->name); ?>" <?php checked(in_array($pt->name, $enabled)); ?>> <?php echo esc_html($pt->labels->name); ?></label><br>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($enabled as $pt): ?>
                <div id="tab-<?php echo esc_attr($pt); ?>" class="tab-content" style="display:none;">
                    <h3><?php echo esc_html($post_types[$pt]->labels->name); ?> Rating Fields</h3>
                    <table class="form-table fire-table" data-post-type="<?php echo esc_attr($pt); ?>">
                        <thead><tr><th>Label</th><th>Slug</th><th>Weight</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($criteria[$pt] ?? [] as $index => $field): ?>
                            <tr>
                                <td><input name="fire_criteria[<?php echo $pt; ?>][<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>"></td>
                                <td><input name="fire_criteria[<?php echo $pt; ?>][<?php echo $index; ?>][slug]" value="<?php echo esc_attr($field['slug']); ?>"></td>
                                <td><input name="fire_criteria[<?php echo $pt; ?>][<?php echo $index; ?>][weight]" type="number" step="0.1" value="<?php echo esc_attr($field['weight']); ?>"></td>
                                <td><button type="button" class="remove-row button">×</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><button type="button" class="add-row button">Add Field</button></p>
                    <p class="weight-warning" style="color:red;"></p>
                </div>
                <?php endforeach; ?>

                <p><input type="submit" class="button button-primary" value="Save Settings"></p>
                <?php wp_nonce_field('sypesr_reset_action', 'sypesr_reset_nonce'); ?>
                <p><input type="submit" name="sypesr_reset" class="button" value="Reset All Settings" onclick="return confirm('Reset all settings?');"></p>
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

            document.querySelectorAll('.add-row').forEach(button => {
                button.addEventListener('click', function () {
                    const table = this.closest('.tab-content').querySelector('tbody');
                    const rows = table.querySelectorAll('tr').length;
                    const pt = table.closest('.fire-table').dataset.postType;
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input name="fire_criteria[${pt}][${rows}][label]"></td>
                        <td><input name="fire_criteria[${pt}][${rows}][slug]"></td>
                        <td><input name="fire_criteria[${pt}][${rows}][weight]" type="number" step="0.1"></td>
                        <td><button type="button" class="remove-row button">×</button></td>`;
                    table.appendChild(row);
                });
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                }
            });

            document.querySelectorAll('.tab-content').forEach(tab => {
                document.addEventListener('blur', function(e) {
                    if (e.target.matches('input[name*="[label]"]')) {
                        const row = e.target.closest('tr');
                        const slugInput = row.querySelector('input[name*="[slug]"]');
                        if (slugInput && slugInput.value.trim() === '') {
                            const label = e.target.value;
                            const slug = 'fire-editorial-stars-' + label.toLowerCase()
                                .replace(/[^a-z0-9\s-]/g, '')
                                .replace(/\s+/g, '-')
                                .replace(/-+/g, '-')
                                .replace(/^-|-$/g, '');
                            slugInput.value = slug;
                        }
                    }
                });
            });

            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', function () {
                    document.querySelectorAll('.fire-table').forEach(table => {
                        const rows = table.querySelectorAll('tbody tr');
                        let total = 0;
                        rows.forEach(row => {
                            const weightInput = row.querySelector('input[name*="[weight]"]');
                            if (weightInput) total += parseFloat(weightInput.value || 0);
                        });
                        const warning = table.closest('.tab-content').querySelector('.weight-warning');
                        warning.textContent = total.toFixed(1) != 100 ? `⚠️ Total weight is ${total.toFixed(1)}%. Must equal 100%.` : '';
                    });
                });
            });
        });
        </script>
        <?php
    }
}
