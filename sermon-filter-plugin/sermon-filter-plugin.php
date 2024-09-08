<?php
/*
Plugin Name: Sermon Filter Plugin
Description: Adds filter buttons for recent, speaker, and scripture on sermon post type.
Version: 1.0.8
Author: Wai Ho Chan
Text Domain: sermon-filter-plugin
Domain Path: /languages
*/
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
define('SFB_VERSION', '1.0.8');
define('SFB_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Autoload necessary files
require_once SFB_PLUGIN_PATH . 'includes/class-sermon-filter-plugin.php';

// Initialize the plugin
Sermon_Filter_Plugin::get_instance();
