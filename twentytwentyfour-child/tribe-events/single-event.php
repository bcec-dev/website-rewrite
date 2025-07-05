<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 * @version 4.6.19
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular = tribe_get_event_label_singular();
$events_label_plural   = tribe_get_event_label_plural();

$event_id = Tribe__Events__Main::postIdHelper( get_the_ID() );

/**
 * Allows filtering of the event ID.
 *
 * @since 6.0.1
 *
 * @param numeric $event_id
 */
$event_id = apply_filters( 'tec_events_single_event_id', $event_id );

/**
 * Allows filtering of the single event template title classes.
 *
 * @since 5.8.0
 *
 * @param array   $title_classes List of classes to create the class string from.
 * @param numeric $event_id      The ID of the displayed event.
 */
$title_classes = apply_filters( 'tribe_events_single_event_title_classes', [ 'tribe-events-single-event-title' ], $event_id );
$title_classes = implode( ' ', tribe_get_classes( $title_classes ) );

/**
 * Allows filtering of the single event template title before HTML.
 *
 * @since 5.8.0
 *
 * @param string  $before   HTML string to display before the title text.
 * @param numeric $event_id The ID of the displayed event.
 */
$before = apply_filters( 'tribe_events_single_event_title_html_before', '<h1 class="' . $title_classes . '">', $event_id );

/**
 * Allows filtering of the single event template title after HTML.
 *
 * @since 5.8.0
 *
 * @param string  $after    HTML string to display after the title text.
 * @param numeric $event_id The ID of the displayed event.
 */
$after = apply_filters( 'tribe_events_single_event_title_html_after', '</h1>', $event_id );

/**
 * Allows filtering of the single event template title HTML.
 *
 * @since 5.8.0
 *
 * @param string  $after    HTML string to display. Return an empty string to not display the title.
 * @param numeric $event_id The ID of the displayed event.
 */
$title = apply_filters( 'tribe_events_single_event_title_html', the_title( $before, $after, false ), $event_id );

/**
 * Added for customerize template for BCEC
 */
$header_title = apply_filters( 'tec_events_views_v2_view_header_title', '', null);
$date_with_year_format    = tribe_get_date_format( true );
$start_date = tribe_get_start_date( $event_id, false, $date_with_year_format ); 
$end_date = tribe_get_end_date( $event_id, false, $date_with_year_format );
$start_time = tribe_get_start_time( $event_id );
$end_time = tribe_get_end_time( $event_id );
$location = tribe_get_venue( $event_id );
$permalink = get_permalink();
// Get the current language
$current_language = apply_filters('wpml_current_language', null);
$back_to_search_result = 'Back to search result';
$chinatown_campus = 'Chinatown Campus';
$newton_campus = 'Newton Campus';
if ($current_language == 'zh-hant' || $current_language == 'zh-hans') {
		$back_to_search_result = '返回搜尋結果';
		$chinatown_campus = '華埠堂';
		$newton_campus = '牛頓堂';
}
?>

<div id="tribe-events-content" class="tribe-events-single">

	<p class="tribe-events-back">remove me please
		<a href="<?php echo esc_url( tribe_get_events_link() ); ?>"> <?php printf( '&laquo; ' . esc_html_x( 'All %s', '%s Events plural label', 'the-events-calendar' ), $events_label_plural ); ?></a>
	</p>

	<!-- extra header to display event icons description-->
	<div class="tribe-events-header__title">
		<h1 class="tribe-events-header__title-text">
			<?php echo esc_html( $header_title ); ?>
		</h1>
		<div class="bcec-event-icons-description">
        <div>
            <span class="bcec-event-icon bcec-chinatown"></span>
            <span><?php echo esc_html( $chinatown_campus ); ?></span>
        </div>
        <div>
            <span class="bcec-event-icon bcec-newton"></span>
            <span><?php echo esc_html( $newton_campus ); ?></span>
        </div>
    </div>
	</div>

	<!-- Notices -->
	<?php tribe_the_notices() ?>

	<?php echo $title; ?>

	<!-- Event header -->
	<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>
		<!-- Navigation -->
		<nav class="tribe-events-nav-pagination" aria-label="<?php printf( esc_html__( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?>">
			<ul class="tribe-events-sub-nav">
				<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '<span>&laquo;</span> %title%' ) ?></li>
				<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title% <span>&raquo;</span>' ) ?></li>
			</ul>
			<!-- .tribe-events-sub-nav -->
		</nav>
	</div>
	<!-- #tribe-events-header -->

	<?php while ( have_posts() ) :  the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<!-- Event featured image, but exclude link -->
			<?php echo tribe_event_featured_image( $event_id, 'full', false ); ?>

			<!-- Time -->
			<div class="tribe_events_single_event_time">
			 <span>Time:</span><span><?php echo esc_html($start_time); ?> - <?php echo esc_html($end_time); ?><span>
	  	</div>
			<!-- Date -->
			<div class="tribe_events_single_event_date">
			 <span>Date:</span>
			 <span>
				 <?php
					if (tribe_event_is_all_day( $event_id )) {
						echo esc_html($start_date) . ' - ' . esc_html($end_date);
					} else {
						echo esc_html($start_date);
					}
				?>
		     <span>
	  	</div>
			<!-- Location -->
			<div class="tribe_events_single_event_location">
			 	<span>Location:</span><span><?php echo esc_html($location); ?><span>
	  	</div>
			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
			<div class="tribe-events-single-event-description tribe-events-content">
				<?php the_content(); ?>

				<!-- go back to previous -->
				<?php if ( isset( $_SERVER['HTTP_REFERER'] ) ) : ?>
				<div class="bcec-back-to-search-result">
					<a href="<?php echo esc_url( $_SERVER['HTTP_REFERER'] ); ?>">
						<div class="bcec-left-arrow">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24.57 19.61" width="25px" height="20px">
								<g>
									<polygon points="15.33 19.61 7.32 11.8 24.57 11.8 24.57 7.8 7.32 7.8 15.33 0 9.93 0 0 9.8 9.93 19.61 15.33 19.61"/>
								</g>
							</svg>
						</div>
						<div class="bcec-back-to-search-result-text"><?php echo esc_html($back_to_search_result); ?></div>
					</a>
				</div>
				<?php endif; ?>
			</div>


			<!-- .tribe-events-single-event-description -->
			<?php do_action( 'tribe_events_single_event_after_the_content' ) ?>

			<!-- Event meta -->
			<?php do_action( 'tribe_events_single_event_before_the_meta' ) ?>
			<?php tribe_get_template_part( 'modules/meta' ); ?>
			<?php do_action( 'tribe_events_single_event_after_the_meta' ) ?>
		</div> <!-- #post-x -->
		<?php if ( get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option( 'showComments', false ) ) comments_template() ?>
	<?php endwhile; ?>

</div><!-- #tribe-events-content -->