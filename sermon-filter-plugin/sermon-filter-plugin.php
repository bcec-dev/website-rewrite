<?php
/*
Plugin Name: Sermon Filter Plugin
Description: Adds filter buttons for recent, speaker, and scripture on sermon post type.
Version: 1.0
Author: Wai Ho Chan
Text Domain: sermon-filter-plugin
Domain Path: /languages
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
// Define the default number of posts per page
define('DEFAULT_NUM_POSTS_PER_PAGE', 6);
// Define the default number terms per page
define('DEFAULT_NUM_TERMS_PER_PAGE', 10);

// Load plugin textdomain for translations
function sfb_load_textdomain() {
  load_plugin_textdomain('sermon-filter-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'sfb_load_textdomain');

// Enqueue scripts and styles
function sfb_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('sfb-script', plugin_dir_url(__FILE__) . 'js/sfb-script.js', array('jquery'), null, true);
    wp_localize_script('sfb-script', 'sfb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    wp_enqueue_style('sfb-style', plugin_dir_url(__FILE__) . 'css/sfb-style.css');
}
add_action('wp_enqueue_scripts', 'sfb_enqueue_scripts');

// Shortcode to generate filter buttons
function sfb_generate_filter_buttons_shortcode($atts = []) {
  // normalize attribute keys, lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );

  // Shortcode attributes with default value for 'taxonomy'
  $atts = shortcode_atts(array(
    'taxonomy' => '',
    'posts_per_page' => DEFAULT_NUM_POSTS_PER_PAGE,
    'taxonomy_terms_per_page' => DEFAULT_NUM_TERMS_PER_PAGE,
    'display_names' => ''
  ), $atts);

  // Update option with shortcode attributes
  update_option('sfb_shortcode_atts', $atts);

  // Parse the taxonomy attribute into an array
  $taxonomies = array_map('trim', explode(',', $atts['taxonomy']));

  // Parse the display_names attribute into an array
  $display_names = array_map('trim', explode(',', $atts['display_names']));

  // Combine taxonomies and display names into an associative array
  $taxonomy_display_names = array();
  foreach ($taxonomies as $index => $taxonomy) {
      $taxonomy_object = get_taxonomy($taxonomy);
      if ($taxonomy_object) {
          $display_name = isset($display_names[$index]) && !empty($display_names[$index]) 
              ? $display_names[$index] 
              : $taxonomy_object->labels->singular_name;
          $taxonomy_display_names[$taxonomy] = $display_name;
      }
  }
  ob_start();
    ?>
    <div class="sermon-filter-buttons-container">
      <div class="sermon-filter-buttons">
        <button class="sermon-filter-button active" data-filter="recent"><?php esc_html_e('Recent', 'sermon-filter-plugin'); ?></button>
        <?php foreach ($taxonomy_display_names as $taxonomy => $display_name) : ?>
            <button class="sermon-filter-button" data-filter="<?php echo esc_attr($taxonomy); ?>"><?php echo esc_html($display_name); ?></button>
        <?php endforeach; ?>
      </div>
      <div class="sermon-search-bar">
          <input type="text" id="sermon-search-input" placeholder="<?php esc_attr_e('Search...', 'sermon-filter-plugin'); ?>">
          <div id="sermon-search-button" class="sermon-search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" id="icon" width="20" height="20" viewBox="0 0 32 32">
              <defs>
                <style>
                  .cls-1 {
                    fill: none;
                  }
                </style>
              </defs>
              <path d="M29,27.5859l-7.5521-7.5521a11.0177,11.0177,0,1,0-1.4141,1.4141L27.5859,29ZM4,13a9,9,0,1,1,9,9A9.01,9.01,0,0,1,4,13Z" transform="translate(0 0)"/>
              <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32"/>
            </svg>
          </div>
      </div>
    </div>
    <div class="sermon-filter-results"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('sermon_filter_buttons', 'sfb_generate_filter_buttons_shortcode');

?>