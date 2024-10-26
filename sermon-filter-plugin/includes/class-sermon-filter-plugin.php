<?php

class Sermon_Filter_Plugin {

    private static $instance = null;

    // Constructor
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    // Singleton instance
    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Include other class files
    private function includes() {
        require_once SFB_PLUGIN_PATH . 'includes/class-sermon-filter-ajax-handler.php';
        require_once SFB_PLUGIN_PATH . 'includes/class-sermon-filter-shortcodes.php';
    }

    // Hook into WordPress
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    // Load plugin textdomain
    public function load_textdomain() {
        load_plugin_textdomain('sermon-filter-plugin', false, dirname(plugin_basename(__FILE__)) . '/../languages');
    }

    // Enqueue styles and scripts
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('sfb-script', plugin_dir_url(__FILE__) . '../js/sfb-script.js', array('jquery'), SFB_VERSION, true);
        wp_localize_script('sfb-script', 'sfb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_enqueue_style('sfb-style', plugin_dir_url(__FILE__) . '../css/sfb-style.css', array(), SFB_VERSION);
    }
}
