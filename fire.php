<?php
/*
Plugin Name: FIRE - Field-based Individual Rating Engine
Plugin URI: https://stefanogorgoni.com
Description: Editorial star rating plugin with weighted individual rating fields, structured data, and customizable output.
Version: 1.0
Author: Stefano Gorgoni
Author URI: https://stefanogorgoni.com
License: GPL2+
Text Domain: fire
*/

defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'includes/class-fire-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fire-columns.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fire-meta-box.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fire-schema.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fire-shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fire-template.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

add_action('plugins_loaded', function() {
    new FIRE_Admin();
	new FIRE_Columns();
    new FIRE_Meta_Box();
    new FIRE_Schema();
    new FIRE_Shortcodes();
    new FIRE_Template();
});
