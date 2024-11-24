<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION

/**
 * Hook for 'The Event Calendar' plugin - 'tec_events_rewrite_dynamic_matchers'
 * 
 * Add custom rewrite rule and query variable for multiple event categories.
 * This is for the event calendar plugin to generate url in Rewrite.php
 * We added checking for multiple values of 'tribe_events_cat, and added the RegExp to $matchers
 * so that the mutliple value of categories (e.g test1, test2) can be kept.
 */
function custom_tec_events_rewrite_matchers($matchers, $query_vars, $caller) {
    if ( isset( $query_vars['tribe_events_cat'] ) ) {
        // this following logic is copied from 
        // the-events-calendar/src/Tribe/Rewrite.php::get_dynamic_matchers
        $category_slug = $query_vars['tribe_events_cat'];
        $bases = (array) $caller->get_bases();
        $cat_regex = $bases['tax'];
        // Create a capturing and non-capturing version of the taxonomy match.
        $matchers["(?:{$cat_regex})/(?:[^/]+/)*([^/]+)"] = "category/{$category_slug}";
        $matchers["{$cat_regex}/(?:[^/]+/)*([^/]+)"] = "category/{$category_slug}";
    }
	// there is bug that the pagination link is not correct for Chinese locale
    // so uses this logic to workaround the issues.

    // Define the original key and the new key
    $original_key = '(?:page|页面|页)/(\d+)';
    $new_key = '(?:page|页面)/(\d+)';

    // Check if the original key exists in the $matchers array and the new key does not exist
    if (isset($matchers[$original_key]) && !isset($matchers[$new_key])) {
        // Set the new key with the same value as the original key
        $matchers[$new_key] = $matchers[$original_key];
    }
    return $matchers;
}
add_filter('tec_events_rewrite_dynamic_matchers', 'custom_tec_events_rewrite_matchers', 10, 3);

/**
 * Hook for 'tribe_repository_events_query_args' filter
 * 
 * Modify query operator to 'AND' support multiple values in tribe_events_cat query.
 */
function custom_tec_events_query_args($query_args, $query) {
    // Check if the query is for tribe events categories
    if (isset($query_args['tax_query'])) {
        foreach ($query_args['tax_query'] as &$tax_query_item) {
            if (isset($tax_query_item['taxonomy']) && $tax_query_item['taxonomy'] === 'tribe_events_cat') {
                // Change operator to AND
                $tax_query_item['operator'] = 'AND';
            }
        }
    }
    return $query_args;
}
add_filter('tribe_repository_events_query_args', 'custom_tec_events_query_args', 10, 2);

/**
 * Helper function to get the last path or the last two paths for
 * create new url for the filter.
 * If there is no query string at the end, it will return the last path.
 * If there is query string at the end, it will return the last two paths
 * including the query string.
 * If it is a page url /page/number/, only return the query part
 */
function get_relevant_end_path($current_url) {
	// Check if it's a "page" URL
    $is_page_url = preg_match('/\/page\/\d+\//', $current_url);
    // Split the path into segments
    $path_segments = explode('/', trim($current_url, '/'));
    // Determine if the last segment contains a query string
    $last_segment_contains_query = strpos(end($path_segments), '?') !== false;

	// there is case that the url contains ?post_type=, which is not same as shown in browser
    // return empty string for that case.
    $query_args = get_query_arguments($current_url);
    $post_type = isset($query_args['post_type']) ? $query_args['post_type'] : '';
    if (!empty($post_type)) {
        return '';
    }
	
    // Get the relevant segments based on the query string condition
    $relevant_path = '';
    if ($is_page_url) {
        if ($last_segment_contains_query) {
            $relevant_path = end($path_segments);
        }
    } else if ($last_segment_contains_query) {
        $last_two_segments = array_slice($path_segments, -2);
        $relevant_path = implode('/', $last_two_segments);
    } else {
        $relevant_path = end($path_segments);
    }
    return $relevant_path;
}

/**
 * Helper function to get the query parts from the provided url
 */
function get_query_arguments($url) {
    // Parse the URL to get its components
    $url_parts = wp_parse_url($url);

    // Initialize an array to hold query arguments
    $query_args = array();

    // Check if the query component exists and parse it
    if (isset($url_parts['query'])) {
        wp_parse_str($url_parts['query'], $query_args);
    }

    return $query_args;
}

/**
 * Helper function to generate the Url for the filter button
 */
function modify_event_category_url($category_name, $term_link) {
    // Get the current URL including query parameters
    $current_url = add_query_arg(null, null);
	
	$relevant_path = get_relevant_end_path($current_url);
	$query_args = get_query_arguments($current_url);
    $post_type = isset($query_args['post_type']) ? $query_args['post_type'] : '';
    $tribe_events_cat = isset($query_args['tribe_events_cat']) ? $query_args['tribe_events_cat'] : '';

    // Check if the URL contains '/events/category/'
     if (!empty($tribe_events_cat) || preg_match('#/(?:events|活动)/(?:category|类别)/([^/]+)/(.*)#', $current_url, $matches)) {
        // Extract the categories from the URL
        $categories = explode(',', !empty($tribe_events_cat) ? $tribe_events_cat : $matches[1]);

        // Add or remove the category name
        if (($key = array_search($category_name, $categories)) !== false) {
            unset($categories[$key]);
        } else {
            $categories[] = $category_name;
        }

        // If no categories remain, remove the entire '/events/category/' part
        if (empty($categories)) {
            $new_url = preg_replace('#/(?:events|活动)/(?:category|类别)/([^/]+)/#', '/events/', $term_link);            
            // Construct the new URL using term permalink and relevant_path
            $new_url = rtrim($new_url, '/') . '/' . ltrim($relevant_path, '/') ;
        } else {
            // Rebuild the category string
            $new_categories = implode(',', $categories);
            // Replace the old category string with the new one in the URL
            $new_url = preg_replace('#/(?:events|活动)/(?:category|类别)/([^/]+)/(.*)#', '/events/category/' . $new_categories . '/' . ltrim($relevant_path, '/'), $term_link);
        }
    } else {
        // If '/events/category/' is not in the path, add the category name
        $new_url = rtrim($term_link, '/') . '/' . $relevant_path;
    }

    return $new_url;
}

/**
 * Hook for 'tribe_template_before_include:events/v2/components/events-bar' action
 * to add category buttons before the events-bar
 */
add_action(
    'tribe_template_before_include:events/v2/components/events-bar',
    function() {
        $terms = get_terms( [ 'taxonomy' => Tribe__Events__Main::TAXONOMY ] );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return;
        }
        $current_url = add_query_arg(null, null);
 
        // CSS styles
        echo '<style>
            .event-category-buttons-container {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 20px;
                margin-left: -10px;
           }
                .event-category-buttons-container .event-category-button {
                    display: inline-block;
                     padding: 16px 50px;
                    color: #ffffff;
                    border: 1px solid #000000;
                    cursor: pointer;
                    border-radius: 6px;
                    font-size: 18px;
                    font-weight: bold;
                    transition: background-color 0.3s ease;
                    margin: 10px;
                    min-width: 140px;
                    text-align: center;
                }
				@media (max-width: 767px) {
                  .event-category-buttons-container .event-category-button { 
                    padding: 2px 10px;
                    min-width: 80px;
                  }
                }
                .event-category-button-default {
                    background-color: #003E7F;
                }
                .event-category-button-active {
                    background-color: #000000;
                }
                .event-category-button:hover {
                    background-color: #007bff;
                }
                .event-category-button-active:hover {
                    background-color: #0056b3;
                }
                .event-category-button-row-break {
                    flex-basis: 100%;
                    height: 0;
                    margin:0;
                    padding: 0;
                }

        </style>';

        // Start container for buttons
        echo '<div class="event-category-buttons-container">';
        
        $chinatown_events_btn = '';
        $newton_events_btn = '';

        // Generate category buttons
        foreach ( $terms as $single_term ) {
            $gettermlink = get_term_link($single_term);
            $url = esc_url(modify_event_category_url($single_term->slug, $gettermlink));
            $name = esc_html( get_term_field( 'name', $single_term ) );

            $is_active = strpos($current_url, $single_term->slug) !== false;
            $active_class = $is_active ? 'event-category-button-active' : 'event-category-button-default';
            
            $btn = "<div class=\"event-category-button $active_class\" onclick=\"location.href='$url'\">$name</div>";

			if (strpos($single_term->slug, 'chinatown_events') !== false) {
                $chinatown_events_btn = $btn;
            } elseif (strpos($single_term->slug, 'newton_events') !== false) {
                $newton_events_btn = $btn;
            } else {
                echo $btn;
            }
        }
         // output the chinatwon_events_btn and newton_events_btn 
         echo "<div class=\"event-category-button-row-break\"></div>";
         echo $chinatown_events_btn;
         echo $newton_events_btn;

        $pattern = '#/(?:events|活动)/(?:category|类别)#';
        $is_empty_category = true;
		$query_args = get_query_arguments($current_url);
        $tribe_events_cat = isset($query_args['tribe_events_cat']) ? $query_args['tribe_events_cat'] : '';

        if (preg_match($pattern, $current_url) || !empty($tribe_events_cat)) {
            $is_empty_category = false;
        }
        $relevant_path = get_relevant_end_path($current_url);

        // Get the current language
        $current_language = apply_filters('wpml_current_language', null);

        $all_events_url = '/events/' . $relevant_path;
        $allEvents = 'All English Events';
        if ($current_language == 'zh-hant') {
            $allEvents = '粵語堂活動';
            $all_events_url = '/' . $current_language . '/events/' . $relevant_path;
        }
        if ($current_language == 'zh-hans') {
            $allEvents = '國語堂活动';
            $all_events_url = '/' . $current_language . '/events/' . $relevant_path;
        }
        

        $all_events_url = esc_url($all_events_url);
        $active_class = $is_empty_category ? 'event-category-button-active' : 'event-category-button-default';
        echo "<div class=\"last-row\"><div class=\"event-category-button last-div $active_class\" onclick=\"location.href='$all_events_url'\">$allEvents</div></div>";

        // End container for buttons
        echo '</div>';
    }
);

/**
 * Hook for the 'tec_events_views_v2_view_header_title' to set the title be always 'Events'
 */
function custom_events_view_header_title( $title, $view ) {
    // Get the current language
    $current_language = apply_filters('wpml_current_language', null);

    // set the title according to the current language
    $custom_title = 'Events';
    if ($current_language == 'zh-hant') {
        $custom_title = '活動';
    }
    if ($current_language == 'zh-hans') {
        $custom_title = '活动';
    }
    return $custom_title;
}
add_filter( 'tec_events_views_v2_view_header_title', 'custom_events_view_header_title', 10, 2 );


/**
 * Hook for the 'tribe_events_views_v2_view_breadcrumbs'
 * to remove the breadcrumbs from view
 */
function remove_event_breadcrumbs() {
    return ''; // Return an empty string to remove the breadcrumbs
}
add_filter( 'tribe_events_views_v2_view_breadcrumbs', 'remove_event_breadcrumbs' );

/**
 * Hook for inserting event icon to the title component. 
 * add the event icon html before the ending tag
 */
function insertEventIconInHeader($html, $template, $event_id = null) {
    // Initialize $post_id to null
    $post_id = null;

    // Check if $template is provided and not null
    if ($template !== null) {
        // Retrieve the post ID from the template's local values
        $local_values = $template->get_local_values();
        if (isset($local_values['event']->ID)) {
            $post_id = $local_values['event']->ID;
        }
    }

    // If $template is null, use the provided $event_id if available
    if ($post_id === null && $event_id !== null) {
        $post_id = $event_id;
    }

    // If $post_id is still null, return the original $html
    if ($post_id === null) {
        return $html;
    }

    $categories = tribe_get_event_cat_slugs($post_id);
    $newHTML = '';
    if ( $categories ) {
        foreach ( $categories as $category ) {
            if ( strpos( $category, 'newton' ) !== false ) {
            $newHTML = $newHTML . '<span class="bcec-event-icon bcec-newton"></span>';
            } 
            if ( strpos( $category, 'chinatown' ) !== false ) {
            $newHTML = $newHTML . '<span class="bcec-event-icon bcec-chinatown"></span>';
            }
        }
    }
    // Use strrpos to find the last occurrence of '</' and insert new HTML before it
    $headerEndIndex = strrpos($html, '</');
    if ($headerEndIndex !== false) {
        $html = substr($html, 0, $headerEndIndex) . $newHTML . substr($html, $headerEndIndex);
    } else {
        // Handle the case where there are no </h3> elements
        // You can choose to append the newHTML at the end of the HTML or handle it differently
        $html .= $newHTML;
    }

    return $html;
}
add_filter('tribe_template_include_html:events/v2/month/mobile-events/mobile-day/mobile-event/title',
function($html, $file, $name, $template) {
    return insertEventIconInHeader($html, $template);
}, 10, 4);

add_filter('tribe_template_include_html:events/v2/latest-past/event/title',
function($html, $file, $name, $template) {
    return insertEventIconInHeader($html, $template);
}, 10, 4);

add_filter('tribe_template_include_html:events/v2/list/event/title',
function($html, $file, $name, $template) {
    return insertEventIconInHeader($html, $template);
}, 10, 4);

add_filter('tribe_template_include_html:events/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/title',
function($html, $file, $name, $template) {
    return insertEventIconInHeader($html, $template);
}, 10, 4);

add_filter('tribe_template_include_html:events/v2/month/calendar-body/day/calendar-events/calendar-event/title',
function($html, $file, $name, $template) {
    return insertEventIconInHeader($html, $template);
}, 10, 4);

add_filter('tribe_events_single_event_title_html_after',
function($html, $event_id) {
    return insertEventIconInHeader($html, null, $event_id);
}, 10, 2);


/**
 * Hook for inserting event icon description to header-title component
 */
function insertEventIconDescription($html, $file, $name, $eventEntry) {
	$current_language = apply_filters('wpml_current_language', null);
    $chinatown_campus = 'Chinatown Campus';
    $newton_campus = 'Newton Campus';
    if ($current_language == 'zh-hant') {
        $chinatown_campus = '華埠堂';
        $newton_campus = '牛頓堂';
    } else if ($current_language == 'zh-hans') {
        $chinatown_campus = '华埠堂';
        $newton_campus = '牛顿堂';
    }
    $newHTML = "
        <div class=\"bcec-event-icons-description\">
            <div>
                <span class=\"bcec-event-icon bcec-chinatown\"></span>
                <span>$chinatown_campus</span>
            </div>
            <div>
                <span class=\"bcec-event-icon bcec-newton\"></span>
                <span>$newton_campus</span>
            </div>
        </div>";

    $headerEndIndex = strrpos($html, '</div>');
    if ($headerEndIndex !== false) {
        $html = substr($html, 0, $headerEndIndex) . $newHTML . substr($html, $headerEndIndex);
    } else {
        // Handle the case where there are no </div> elements
        // You can choose to append the newHTML at the end of the HTML or handle it differently
        $html .= $newHTML;
    }

    return $html;
}
add_filter('tribe_template_include_html:events/v2/components/header-title',
'insertEventIconDescription', 10, 4);

/**
 * Hook for customize the date format for zh_TW and zh_CN locale
 */
add_filter('tribe_date_format', 'custom_tribe_date_format_based_on_locale', 10, 1);
function custom_tribe_date_format_based_on_locale($format) {
    $locale = get_locale();
    // Customize date format based on locale and context
    if ($locale === 'zh_TW' || $locale === 'zh_CN') {
        if (strpos($format, 'Y') === false) {
            $format = 'F j日';
        } else {
            $format = 'Y年 F j日';
        }
    }
    return $format;
};

/**
 * Hook for customize the time format for zh_TW and zh_CN locale
 */
add_filter('option_time_format', 'custom_option_time_format_based_on_locale', 10, 1);
function custom_option_time_format_based_on_locale($value) {
    $locale = get_locale();
    // Customize time format based on locale and context
    if ($locale === 'zh_TW' || $locale === 'zh_CN') {
        if (strpos($value, 'a') !== false) {
            $value = 'a g:i';
        } 
    }
    return $value;
};

/**
 * Shortcode to display the event start date in vertical
 */
function show_event_start_date_vertical() {
    if ( function_exists( 'tribe_get_start_date' ) ) {
        global $post;
        $month = tribe_get_start_date( $post->ID, false, 'M' ); // Get the month abbreviation
        $day = tribe_get_start_date( $post->ID, false, 'j' );   // Get the day without leading zeros

        // Start output buffering
        ob_start();
        ?>
        <div class="bcec-event-date-vertical-container" style="display:flex;flex-direction:column;align-items:center;">
            <div class="bcec-event-day"><?php echo esc_html( $day ); ?></div>
            <div class="bcec-event-month"><?php echo esc_html( $month ); ?></div>
        </div>
        <?php
        // Get the buffered content and clean the buffer
        $html = ob_get_clean();

        return $html;
    }
    return '';
}
add_shortcode( 'event_start_date_vertical', 'show_event_start_date_vertical' );

/**
 * For the genereate blocks to display custom field sermon_date field
 */
add_filter('generateblocks_dynamic_content_post_meta', function($value, $sourceId, $attributes){
    error_log('value: ' . print_r($value, true));
    if (
        ! is_admin() &&
        strpos( $attributes['metaFieldName'], 'sermon_date' ) !== false 
    ) {
        // use get_field to get date value from ACF
        $newValue = get_field($attributes['metaFieldName'], $sourceId);

        return  wp_kses_post($newValue);
    }
    return $value;
}, 10, 3);

/**
 * Extend WordPress search to include custom fields
 *
 * https://adambalee.com
 */

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

/**
 * Modify the search query with posts_where
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

/**
 * Prevent duplicates
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );


add_filter( 'get_the_excerpt', function ( $excerpt, $post ) {
if ( ! empty( $excerpt ) ) {
return $excerpt;
}
// On a specific post type, use an ACF field value as the excerpt.
if ( $post->post_type === 'sermon' ) {
$excerpt = get_field( 'sermon_theme', $post->ID );
}
return $excerpt;
}, 10, 2 );

/**
 * ShortCode to add Google translate button
 */
function google_translate_shortcode() {
    $locale = get_locale();
    return '<div id="google_translate_element"></div>
            <script type="text/javascript">
                function googleTranslateElementInit() {
                    new google.translate.TranslateElement(
                    {
                        pageLanguage: "' . $locale . '",
                        includedLanguages: "en,zh-CN,zh-TW",
                        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    },
                    "google_translate_element");
                }
            </script>
            <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>';
}
add_shortcode('google_translate', 'google_translate_shortcode');
