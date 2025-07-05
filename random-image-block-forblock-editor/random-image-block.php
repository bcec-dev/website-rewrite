<?php
/**
 * Plugin Name:       Random Image Block for Block Editor
 * Description:       Display random images from a gallery.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.4
 * Author:            ultraDevs
 * Author URI:        https://ultradevs.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       random-image-block-for-block-editor
 *
 * @package           random-image-block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'udrib_fs' ) ) {
	// Create a helper function for easy SDK access.
	function udrib_fs() {
		global $udrib_fs;

		if ( ! isset( $udrib_fs ) ) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$udrib_fs = fs_dynamic_init( array(
				'id'                  => '14134',
				'slug'                => 'random-image-block-for-block-editor',
				'premium_slug'        => 'random-image-block-for-block-editor-pro',
				'type'                => 'plugin',
				'public_key'          => 'pk_da2bdf83cbd8954d15e5f3b40ad8b',
				'is_premium'          => true,
				'premium_suffix'      => 'Pro',
				// If your plugin is a serviceware, set this option to false.
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'trial'               => array(
					'days'               => 3,
					'is_require_payment' => false,
				),
				'menu'                => array(
					'slug'           => 'random-image-block',
				),
			) );
		}

		return $udrib_fs;
	}

	// Init Freemius.
	udrib_fs();
	// Signal that SDK was initiated.
	do_action( 'udrib_fs_loaded' );
}


/**
 * Loads a plugin’s translated strings.
 *
 * @return void
 */
function ud_random_image_block_text_domain() {
	load_plugin_textdomain( 'random-image-block-for-block-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'ud_random_image_block_text_domain' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function ud_random_image_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'ud_random_image_block_render',
		)
	);

	// Localize the script with new data.
	$script_handle = 'ultradevs-random-image-block-editor-script';

	$licensing = array(
		'current_plan'         => udrib_fs()->get_plan_name(),
		'can_use_premium_code' => udrib_fs()->can_use_premium_code(),
		'is_plan_pro'          => udrib_fs()->is_plan( 'pro' ),
	);

	wp_localize_script(
		$script_handle,
		'ultraDevsRandomImageBlock',
		array(
			'licensing'   => $licensing,
			'upgradeLink' => admin_url( 'plugin-install.php?s=ultradevs&tab=search&type=term' ),
		)
	);
}

/**
 * Render Random Image Block.
 *
 * @param array $attributes Attributes.
 * @return void | string
 */
function ud_random_image_block_render( $attributes ) {

	require_once plugin_dir_path( __FILE__ ) . '/includes/functions.php';
	require_once plugin_dir_path( __FILE__ ) . '/includes/class-generate-css.php';

	$wrapper_attributes = get_block_wrapper_attributes([
		'class' => classNames(
			'ud-random-img-block',
			[ 'ud-random-img-block-pro' => udrib_fs()->is_plan( 'pro' ) ],
			[
				'ud-random-img-block-caption-on-hover' => $attributes['captionStyle']['showCaptionOn'] && 'hover' === $attributes['captionStyle']['showCaptionOn']
			],
			[
				'ud-radmon-img-block-caption-over-img' => $attributes['captionStyle']['captionOverlay']["enabled"] && 'yes' === $attributes['captionStyle']['captionOverlay']["enabled"]
			]
		),
		'style' => '--ud-rib-overlay-position-x:' . ( isset( $attributes['captionStyle']['captionOverlay']['position']['x'] ) ? esc_attr( $attributes['captionStyle']['captionOverlay']['position']['x'] * 100 ) . '%' : '50%' ) . '; --ud-rib-overlay-position-y:' . ( isset( $attributes['captionStyle']['captionOverlay']['position']['y'] ) ? esc_attr( $attributes['captionStyle']['captionOverlay']['position']['y'] * 100 ) . '%' : '50%' ) . ';--ud-rib-overlay-position-t-x:-' . ( isset( $attributes['captionStyle']['captionOverlay']['position']['x'] ) ? esc_attr( $attributes['captionStyle']['captionOverlay']['position']['x'] * 100 ) . '%' : '50%' ) . '; --ud-rib-overlay-position-t-y:-' . ( isset( $attributes['captionStyle']['captionOverlay']['position']['y'] ) ? esc_attr( $attributes['captionStyle']['captionOverlay']['position']['y'] * 100 ) . '%' : '50%' ) . ';'
	]);

	$images = $attributes['images'];

	if ( empty( $images ) || ! is_array( $images ) ) {
		return;
	}

	$count    = count( $images );
	$index    = ( $count > 1 ) ? wp_rand( 0, $count - 1 ) : 0;
	$content  = ( new UDRIB_Generate_CSS( $attributes ) )->css_output();
	$content .= '<div '. $wrapper_attributes .'>';

    // new logic -- start
	// Safely encode images array
	$encoded_images = wp_json_encode( $images );
    $content .= sprintf(
	  '<script type="text/javascript">window.udRandomImages = %s;</script>',
	  $encoded_images
	);
    // new logic -- end
    
	$content .= '<figure class="ud-random-img-block__images">';
	$content .= sprintf(
		'<img src="%s" alt="%s" />',
		$images[ $index ]['url'],
		$images[ $index ]['alt'] ? esc_attr( $images[ $index ]['alt'] ) : ''
	);
	if ( $images[ $index ]['caption'] ) {

		$content .= '<div class="ud-random-img-block__content">';
		$content .= '<figcaption class="ud-random-img-block__content-caption">';
		$content .= esc_html( $images[ $index ]['caption'] );
		$content .= '</figcaption>';
		$content .= '</div>';
	}

	$content .= '</figure>';
	$content .= '</div>';

	return $content;
}
add_action( 'init', 'ud_random_image_block_init' );

/**
 * Added Submenu Page.
 *
 * @return void
 */
function udrib_add_menu_page() {
	add_menu_page(
		__( 'Random Image Block Settings', 'random-image-block-for-block-editor' ),
		__( 'Random Image Block', 'random-image-block-for-block-editor' ),
		'manage_options',
		'random-image-block',
		'udrib_settings_page',
		'dashicons-images-alt2',
		'50'
	);
}
add_action( 'admin_menu', 'udrib_add_menu_page' );

/**
 * Settings Page.
 *
 * @return void
 */
function udrib_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Random Image Block Settings', 'random-image-block-for-block-editor' ); ?></h1>
		<p><?php esc_html_e( 'This plugin is developed by ultraDevs. If you need any help, please contact with us', 'random-image-block-for-block-editor' ); ?></p>
		<a target="_blank" href="https://ultradevs.com/contact-us/" class="button button-primary"><?php esc_html_e( 'Contact', 'random-image-block-for-block-editor' ); ?></a>
		<p><?php esc_html_e( 'If you like this plugin, please leave us a 5 star review on WordPress.org', 'random-image-block-for-block-editor' ); ?></p>
		<a target="_blank" href="https://wordpress.org/support/plugin/random-image-block-for-block-editor/reviews/#new-post" class="button button-primary"><?php esc_html_e( 'Review', 'random-image-block-for-block-editor' ); ?></a>
		<p><?php esc_html_e( 'If you want to upgrade to pro version, please click on the button below', 'random-image-block-for-block-editor' ); ?></p>

		<?php
			if ( udrib_fs()->can_use_premium_code() ) {
				?>
				<a href="<?php echo esc_url( udrib_fs()->get_account_url() ); ?>" class="button button-primary"><?php esc_html_e( 'My Account', 'random-image-block-for-block-editor' ); ?></a>
				<?php
			} else {
				?>
				<a href="<?php echo esc_url( udrib_fs()->get_upgrade_url() ); ?>" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'random-image-block-for-block-editor' ); ?></a>
				<?php
			}
		?>
	</div>
	<?php
}