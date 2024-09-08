<?php

// Make sure to include the helper class
require_once SFB_PLUGIN_PATH . 'includes/class-sermon-filter-helper.php';

class Sermon_Filter_Ajax_Handler {

    public function __construct() {
        add_action('wp_ajax_sfb_filter', array($this, 'handle_ajax_request'));
        add_action('wp_ajax_nopriv_sfb_filter', array($this, 'handle_ajax_request'));
    }

    // Handle the AJAX request logic
    public function handle_ajax_request() {
        global $wpdb;

        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : '';
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_title($_POST['taxonomy']) : '';
        $search_query = isset($_POST['search_query']) ? sanitize_text_field($_POST['search_query']) : '';

        $shortcode_atts = get_option('sfb_shortcode_atts');
        $posts_per_page = $shortcode_atts['posts_per_page'];
        $taxonomy_terms_per_page = $shortcode_atts['taxonomy_terms_per_page'];
    
        error_log('$posts_per_page : ' . print_r($posts_per_page, true));
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

          $scripture_ids = $wpdb->get_col( $wpdb->prepare( "
            SELECT DISTINCT tr.object_id FROM {$wpdb->term_relationships} AS tr
            INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = %s
            AND t.name LIKE %s
          ", 'scripture', $keyword ) );
    
          $post_ids = array_merge( $post_ids_meta, $post_ids_post, $speaker_ids, $scripture_ids );
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
        } elseif ($filter == 'recent' || $filter == '') {
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
                Sermon_Filter_Helper::display_taxonomies($terms, $filter);
                wp_die();
            }
        }
    
        $query = new WP_Query($args);
        Sermon_Filter_Helper::display_sermons($query, $filter, $taxonomy, $search_query);

        wp_die();
    }
}

// Instantiate AJAX handler
new Sermon_Filter_Ajax_Handler();
