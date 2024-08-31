<?php
/*
Plugin Name: Sermon Filter Plugin
Description: Adds filter buttons for recent, speaker, and scripture on sermon post type.
Version: 1.0.6
Author: Wai Ho Chan
Text Domain: sermon-filter-plugin
Domain Path: /languages
*/
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
define('SFB_VERSION', '1.0.6');

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
    wp_enqueue_script('sfb-script', plugin_dir_url(__FILE__) . 'js/sfb-script.js', array('jquery'), SFB_VERSION, true);
    wp_localize_script('sfb-script', 'sfb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
    wp_enqueue_style('sfb-style', plugin_dir_url(__FILE__) . 'css/sfb-style.css', array(), SFB_VERSION);
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

  // add these for translating the 'speaker' and 'scripture' text
  $speakerTranslation = esc_html__('Speaker', 'sermon-filter-plugin');
  $scriptureTranslation = esc_html__('Scripture', 'sermon-filter-plugin');
  
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
    <div class="sfb-container">
      <div class="sfb-actions-container">
        <div class="sfb-buttons-group">
          <button class="sfb-filter-button active" data-filter="recent"><?php esc_html_e('Recent', 'sermon-filter-plugin'); ?></button>
          <?php foreach ($taxonomy_display_names as $taxonomy => $display_name) : ?>
              <button class="sfb-filter-button" data-filter="<?php echo esc_attr($taxonomy); ?>"><?php esc_html_e($display_name, 'sermon-filter-plugin'); ?></button>
          <?php endforeach; ?>
        </div>
        <div class="sfb-search-bar">
            <input type="text" class="sfb-search-input" placeholder="<?php esc_attr_e('Search...', 'sermon-filter-plugin'); ?>">
            <div class="sfb-search-icon">
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
      <div class="sfb-spinner"></div>
      <div class="sfb-results-container"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sermon_filter_buttons', 'sfb_generate_filter_buttons_shortcode');

// Handle AJAX requests
function sfb_handle_ajax_request() {
  global $wpdb;
  $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';
  $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
  $taxonomy = isset($_POST['taxonomy']) ? sanitize_title($_POST['taxonomy']) : '';
  $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

  $shortcode_atts = get_option('sfb_shortcode_atts');
  $posts_per_page = $shortcode_atts['posts_per_page'];
  $taxonomy_terms_per_page = $shortcode_atts['taxonomy_terms_per_page'];

  if ($filter == 'search') {
    $keyword = '%' . $wpdb->esc_like( $search_query ) . '%';
    // Search in all custom fields
    $post_ids_meta = $wpdb->get_col( $wpdb->prepare( "
      SELECT DISTINCT post_id FROM {$wpdb->postmeta}
      WHERE meta_value LIKE '%s'
    ", $keyword ) );

    // Search in post_title and post_content
    $post_ids_post = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT ID FROM {$wpdb->posts}
        WHERE post_title LIKE '%s'
        OR post_content LIKE '%s'
    ", $keyword, $keyword ) );

    // Search in custom taxonomy 'speaker'
    $speaker_ids = $wpdb->get_col( $wpdb->prepare( "
      SELECT DISTINCT tr.object_id FROM {$wpdb->term_relationships} AS tr
      INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
      INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
      WHERE tt.taxonomy = %s
      AND t.name LIKE %s
    ", 'speaker', $keyword ) );

    $post_ids = array_merge( $post_ids_meta, $post_ids_post, $speaker_ids );
    $post_ids = array_unique($post_ids); // Remove duplicates
    // Query arguments
    if (!empty($post_ids)) {
      $args = array(
          'post_type' => 'sermon',
          'posts_per_page' => $posts_per_page,
          'paged' => $paged,
          'post_status' => 'publish',
          'post__in'    => $post_ids,
          'orderby' => 'date',
          'order' => 'DESC'
      );
    }
  } elseif ($filter == 'recent') {
    $args = array(
        'post_type' => 'sermon',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC'
    );
  } else {
    if ($taxonomy) {
        $args = array(
            'post_type' => 'sermon',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'tax_query' => array(
                array(
                    'taxonomy' => $filter,
                    'field'    => 'slug',
                    'terms'    => $taxonomy,
                ),
            ),
        );
    } else {
        $args = array(
            'taxonomy'   => $filter,
            'hide_empty' => true,
            'parent'     => 0,
            'number'     => $taxonomy_terms_per_page,
            'offset'     => ($paged - 1) * $taxonomy_terms_per_page,
        );
        $terms = get_terms($args);
        display_taxonomies($terms, $filter);
        wp_die();
    }
}
    
  $query = new WP_Query($args);
  display_sermons($query, $filter, $taxonomy, $search_query);


  wp_die();
}
add_action('wp_ajax_sfb_filter', 'sfb_handle_ajax_request');
add_action('wp_ajax_nopriv_sfb_filter', 'sfb_handle_ajax_request');

function generatePaginationButtons($pagination, $currentPage) {
  echo '<div class="sfb-pagination">';
  foreach ($pagination as $page_link) {
    // get page number from the $page_link
    $regex = '/#page=(\d+)/';  // Regular expression to match '#page=' followed by digits

    $page_num = 1;
    if (preg_match($regex, $page_link, $matches)) {
      $page_num = $matches[1];
    } elseif (strpos($page_link, 'current') !== false) {
      $page_num = $currentPage;
    }
    $page_num = $page_num ? $page_num : 1; // Fallback to page 1 if no number found
    if (strpos($page_link, 'dots') !== false ) {
      echo '<span>' . $page_link . '</span>';
    } else {
      echo '<span class="sfb-page-link" data-page="' . abs($page_num) . '">' . $page_link . '</span>';
    }
  }
  echo '</div>';
}

function display_pagination($max_num_pages) {
  $currentPage = intval($_POST['paged']);
  $pagination = paginate_links(array(
      'base' => '%_%',
      'format' => '#page=%#%',
      'current' => max(1, intval($_POST['paged'])),
      'total' => $max_num_pages,
      'type' => 'array',
      'prev_text' => '&lt;', // < symbol
      'next_text' => '&gt;', // > symbol
  ));

  if ($pagination) {
    generatePaginationButtons($pagination, $currentPage);
  }
}

function display_sermons($query, $filter, $taxonomy, $search_query) {
  echo '<div class="sfb-sermons-grid-container" data-filter="' . esc_attr($filter) . '" data-taxonomy="' . esc_attr($taxonomy) . '" data-searchquery="' . esc_attr($search_query) . '">';
  if ($query->have_posts()) {
      echo '<div class="sfb-sermons-grid">'; // Add a container for the grid
      while ($query->have_posts()) {
          $query->the_post();
          include plugin_dir_path(__FILE__) . 'sermon-template.php';
      }
      echo '</div>'; // Close the container
      display_pagination($query->max_num_pages);
  } else {
      esc_html_e('No results found.', 'sermon-filter-plugin');
  }
  wp_reset_postdata();
}

function display_taxonomy_pagination($taxonomy_name) {
  $total_taxonomies = wp_count_terms($taxonomy_name, array('parent' => 0, 'hide_empty' => true));
  $shortcode_atts = get_option('sfb_shortcode_atts');
  $taxonomy_terms_per_page = $shortcode_atts['taxonomy_terms_per_page'];
  $total_pages = ceil($total_taxonomies / $taxonomy_terms_per_page);
  $currentPage = intval($_POST['paged']);
  if ($total_pages > 1) {
      $pagination = paginate_links(array(
          'base' => '%_%',
          'format' => '#page=%#%',
          'current' => max(1, intval($_POST['paged'])),
          'total' => $total_pages,
          'type' => 'array',
          'prev_text' => '&lt;', // < symbol
          'next_text' => '&gt;', // > symbol
      ));

      if ($pagination) {
        generatePaginationButtons($pagination, $currentPage);
      }
  }
}

function display_taxonomies($taxonomies, $taxonomy_name) {
  if (!empty($taxonomies)) {
      echo '<ul class="sfb-taxonomy-list">';
      foreach ($taxonomies as $taxonomy) {
          $term_count = $taxonomy->count;
          echo '<li class="sfb-child-taxonomy-link" data-taxonomy="' . esc_attr($taxonomy->slug) . '">';
          echo '<div class="sfb-term-name">' . $taxonomy->name . '</div>';
          echo '<div class="sfb-term-count-arrow">';
          echo '<div class="sfb-term-count">' . $term_count . '</div>';
          echo '<div class="sfb-term-arrow">&#9654;</div>'; // Unicode for a right arrow
          echo '</div>';
          echo '</li>';
      }
      display_taxonomy_pagination($taxonomy_name);
  } else {
    esc_html_e('No results found.', 'sermon-filter-plugin');
  }
}

// Special sorting Terms for custom taxonomy: scripture and speaker
// if it sort by 'Name', then change it to sort by 'slug'
function custom_sort_terms_admin( $terms, $taxonomies, $args ) {
  $custom_taxonomies = array( 'scripture', 'speaker' );

  if ( array_intersect( $taxonomies, $custom_taxonomies ) && isset( $args['orderby'] ) && $args['orderby'] == 'name' ) {
      $order = isset( $args['order'] ) && strtolower( $args['order'] ) === 'desc' ? -1 : 1;

      usort( $terms, function( $a, $b ) use ( $order ) {
          error_log('$a: ' . print_r($a, true));
          error_log('$b: ' . print_r($b, true));
          return $order * strcmp( $a->slug, $b->slug ); // Sort by slug with the correct order
      });
  }
  return $terms;
}
add_filter( 'get_terms', 'custom_sort_terms_admin', 10, 3 );

// Shortcode to display embeded video from sermon_video_url field
// it is used in page template for displaying sermon.
function sfb_video_shortcode() {
  global $post;
  // Make sure we're dealing with a sermon post type
  if ( $post && $post->post_type === 'sermon' ) {
    // Get the video URL custom field
    $video_url = get_field('sermon_video_url', $post->ID);

    if ( $video_url ) {
      $video_iframe =
        '<div class="sfb-sermon-embed-video">'
        . $video_url
        . '</div>';
      return $video_iframe;
    }
  }
  return '';
}
add_shortcode( 'sermon_video', 'sfb_video_shortcode' );

// found from GreenShiftWP to ensure that shortcodes within 
// a query loop get rendered with the correct post data. Adding this filter
// will ensure that any shortcode used in a query loop is properly executed
// within the context of each post being looped over.
function greenshift_render_block_core_shortcode( $content, $parsed_block, $block ) {
  // Replace <p> tags around the [sermon_video] shortcode before processing
  $content = preg_replace( '/<p>(\[sermon_video.*?\])<\/p>/s', '$1', $content );
  $content = do_shortcode( $content );
  return $content;
}
add_filter( 'render_block_core/shortcode', 'greenshift_render_block_core_shortcode', 10, 3, );

?>
