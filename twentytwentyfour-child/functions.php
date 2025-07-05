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

// Include custom event functions
require_once get_stylesheet_directory() . '/custom-event-functions.php';

// Include custom javascript functions
function my_child_theme_enqueue_scripts() {
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/custom-script.js', array(), '1.0.1', true);
}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_scripts');

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
                        includedLanguages: "zh-CN",
                        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    },
                    "google_translate_element");
                }
            </script>
            <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>';
}
add_shortcode('google_translate', 'google_translate_shortcode');

/**
 * Hook for adjusting the excerpt length according to locale
 */
function mytheme_custom_excerpt_length( $length ) {
    $locale = get_locale();
    if ($locale == 'zh_HK' || $locale == 'zh_TW') {
        return 150;
    }
    return $length;
}
add_filter( 'excerpt_length', 'mytheme_custom_excerpt_length', 999 );
