<?php
// Define the default number of posts per page
define('DEFAULT_NUM_POSTS_PER_PAGE', 6);
// Define the default number terms per page
define('DEFAULT_NUM_TERMS_PER_PAGE', 10);


class Sermon_Filter_Shortcodes {

  public function __construct() {
    add_shortcode('sermon_filter_buttons', array($this, 'generate_filter_buttons_shortcode'));
    add_shortcode('sermon_video', array($this, 'video_shortcode'));
    add_shortcode('sermon_back_to_previous_page', array($this, 'back_to_previous_page_shortcode'));
    add_filter('render_block_core/shortcode', array($this, 'greenshift_render_block_core_shortcode'), 10, 3, );
    add_filter('get_terms', array($this,'custom_sort_terms_admin'), 10, 3 );
  }

  // Shortcode to generate filter buttons
  public function generate_filter_buttons_shortcode($atts = []) {
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
          <button class="sfb-filter-button active" data-filter="recent"><?php esc_html_e('Recent', 'sermon-filter-plugin'); ?></button>
          <?php foreach ($taxonomy_display_names as $taxonomy => $display_name) : ?>
              <button class="sfb-filter-button" data-filter="<?php echo esc_attr($taxonomy); ?>"><?php echo esc_html_e($display_name, 'sermon-filter-plugin'); ?></button>
          <?php endforeach; ?>
          <div class="sfb-search-bar">
            <input type="text" class="sfb-search-input" placeholder="<?php esc_attr_e('Search...', 'sermon-filter-plugin'); ?>">
            <div class="sfb-search-icon">
              <svg xmlns="http://www.w3.org/2000/svg" id="icon" width="20" height="20" viewBox="0 0 32 32">
                <path d="M29,27.5859l-7.5521-7.5521a11.0177,11.0177,0,1,0-1.4141,1.4141L27.5859,29ZM4,13a9,9,0,1,1,9,9A9.01,9.01,0,0,1,4,13Z" transform="translate(0 0)"/>
                <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" fill="none" width="32" height="32"/>
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

  // Shortcode to display embedded video from the sermon_video_url field
  public function video_shortcode() {
    global $post;

    // Make sure we're dealing with a sermon post type
    if ($post && $post->post_type === 'sermon') {
        // Get the video URL custom field
        $video_url = get_field('sermon_video_url', $post->ID);

        if ($video_url) {
            return '<div class="sfb-sermon-embed-video">' . $video_url . '</div>';
        }
    }
    return '';
  }

  // Shortcode to generate a 'back to previous page' link
  // used in the sermon post
  public function back_to_previous_page_shortcode() {
    // Check if the referrer URL exists
    if (!isset($_SERVER['HTTP_REFERER'])) {
      return ''; // Return empty if no referrer URL is found
    }
    // Get the referrer URL
    $previous_url = esc_url($_SERVER['HTTP_REFERER']);

    // Add a query parameter to indicate it was accessed from a sermon post
    $previous_url_with_param = add_query_arg('from_sermon_post', 'true', $previous_url);

    // Return the back button HTML, applying esc_url() for the final URL
    $back_button_text = __('Back to previous page', 'sermon-filter-plugin');

    return '
        <div class="sfb-back-to-previous-page">
            <a href="' . esc_url($previous_url_with_param) . '">
                <div class="sfb-back-to-left-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24.57 19.61" width="25px" height="20px">
                        <g>
                            <polygon points="15.33 19.61 7.32 11.8 24.57 11.8 24.57 7.8 7.32 7.8 15.33 0 9.93 0 0 9.8 9.93 19.61 15.33 19.61"/>
                        </g>
                    </svg>
                </div>
                <div class="bcec-back-to-search-result-text">' . esc_html($back_button_text) . '</div>
            </a>
        </div>';
    
  }


  // found from GreenShiftWP to ensure that shortcodes within 
  // a query loop get rendered with the correct post data (e.g post_type === 'sermon').
  // Adding this filter
  // will ensure that any shortcode used in a query loop is properly executed
  // within the context of each post being looped over.
  public function greenshift_render_block_core_shortcode( $content, $parsed_block, $block ) {
    // Replace <p> tags around the [sermon_video] shortcode before processing
    $content = preg_replace( '/<p>(\[sermon_video.*?\])<\/p>/s', '$1', $content );
    $content = do_shortcode( $content );
    return $content;
  }

  // Special sorting Terms for custom taxonomy: scripture and speaker
  // if it sort by 'Name', then change it to sort by 'slug'
  public function custom_sort_terms_admin( $terms, $taxonomies, $args ) {
    $custom_taxonomies = array( 'scripture', 'speaker' );

    if ( array_intersect( $taxonomies, $custom_taxonomies ) && isset( $args['orderby'] ) && $args['orderby'] == 'name' ) {
        $order = isset( $args['order'] ) && strtolower( $args['order'] ) === 'desc' ? -1 : 1;

        // Get all terms without pagination for accurate sorting
        $all_terms = get_terms(array(
          'taxonomy' => $taxonomies,
          'orderby' => 'none', // Avoid additional sorting
          'hide_empty' => $args['hide_empty'],
          'fields' => 'all',
          'number' => 0 // Retrieve all terms
        ));

        // Sort the entire set of terms by slug
        usort($all_terms, function ($a, $b) use ($order) {
          return $order * strcmp($a->slug, $b->slug); // Sort by slug with the correct order
        });
        // Apply pagination after sorting
        $offset = isset($args['offset']) ? $args['offset'] : 0;
        $number = isset($args['number']) ? $args['number'] : count($all_terms);

        // Slice the sorted array to return only the paginated part
        $terms = array_slice($all_terms, $offset, $number);
    }
    return $terms;
  }
}

// Instantiate shortcodes
new Sermon_Filter_Shortcodes();
