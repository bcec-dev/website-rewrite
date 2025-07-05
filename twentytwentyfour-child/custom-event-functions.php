<?php
/**
 * This file contains code for custom filter buttons and hooks for event calendar plugin
 */

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

  // fixed for zh_HK locale; the translation file contains Simplified Chinese,
  // which causes some issue when create navigation link
  $original_key2 = '(?:page|页)/(\d+)';
  $new_key2 = '(?:page)/(\d+)';
  if (isset($matchers[$original_key2]) && !isset($matchers[$new_key2])) {
    // Set the new key with the same value as the original key
    $matchers[$new_key2] = $matchers[$original_key2];
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
        padding: 16px 20px;
        color: #ffffff;
        border: 1px solid #000000;
        cursor: pointer;
        border-radius: 6px;
        font-size: 18px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        margin-right: 20px;
		margin-bottom: 20px;
        text-align: center;
      }
      @media (max-width: 767px) {
        .event-category-buttons-container .event-category-button { 
          padding: 4px 6px;
		  font-size: 16px;
		  margin: 5px;
        }
      }
      .event-category-button-default {
        background-color: #0670A7;
      }
      .event-category-button-active {
        background-color: #662C83;
      }
      .event-category-button:hover {
        background-color: #008882;
      }
      .event-category-button-active:hover {
        background-color: #008882;
      }
	  .event-category-buttons-container .event-clear-button {
        background-color: white;
        color: black;
      }
      .event-clear-button:hover {
        background-color: #008882;
        color: white;
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
    $allEvents = 'Clear Selection';
    if ($current_language == 'zh-hant' || $current_language == 'zh-hans') {
      $allEvents = '清除選項';
      $all_events_url = '/' . $current_language . '/events/' . $relevant_path;
    }

    $all_events_url = esc_url($all_events_url);
    echo "<div class=\"last-row\"><div class=\"event-category-button last-div event-clear-button\" onclick=\"location.href='$all_events_url'\">$allEvents</div></div>";

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
  $custom_title = 'Happenings';
  if ($current_language == 'zh-hant' || $current_language == 'zh-hans') {
    $custom_title = '近期活動';
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

add_filter('tribe_template_include_html:events/v2/day/event/title',
function($html, $file, $name, $template) {
  return insertEventIconInHeader($html, $template);
}, 10, 4);

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
    $chinatown_campus = '華埠堂';
    $newton_campus = '牛頓堂';
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
  if (strpos( $locale, 'zh_' ) === 0) {
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
  if (strpos( $locale, 'zh_' ) === 0) {
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
    $locale = get_locale();
    $is_chinese = strpos( $locale, 'zh_' ) === 0; // Check if the locale starts with zh_
    // Add "日" to the day if the locale is Chinese
    if ( $is_chinese ) {
      $day .= ' 日';
    }
    // Set flex direction based on the locale
    $flex_direction = $is_chinese ? 'column-reverse' : 'column';

    // Start output buffering
    ob_start();
    ?>
    <div 
      class="bcec-event-date-vertical-container" 
      style="display:flex;flex-direction:<?php echo esc_attr( $flex_direction ); ?>;align-items:center;">
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
 * Hook for increasing the limit of ical export; default is 30
 */
add_filter( "tribe_ical_feed_posts_per_page", function() { return 100; } );

/**
 * Hook for force skipping zh_HK translation for event calendar plugin because the translation is not correct
 */
add_filter( 'override_load_textdomain', function( $override, $domain, $mofile, $locale ) {
    if ( $domain === 'the-events-calendar' && $locale === 'zh_HK' ) {
        return true; // Skip loading zh_HK translation
    }
    return $override;
}, 10, 4 );

