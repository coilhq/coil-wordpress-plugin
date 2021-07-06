<?php
declare(strict_types=1);
/**
 * Coil loader file.
 */

namespace Coil;

use \Coil\Admin;
use \Coil\Gating;
use \Coil\User;

/**
 * @var string Plugin version number.
 */
const PLUGIN_VERSION = '1.9.0';

/**
 * @var string Plugin root folder.
 */
const COIL__FILE__ = __DIR__;

/**
 * Initialise and set up the plugin.
 *
 * @return void
 */
function init_plugin() : void {

	// CSS/JS.
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\load_block_frontend_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\load_block_editor_assets' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_full_assets' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_messaging_assets' );

	// Admin-only CSS/JS.
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\Admin\load_admin_assets' );
	add_filter( 'admin_body_class', __NAMESPACE__ . '\Admin\add_admin_body_class' );

	// Modify output.
	add_filter( 'body_class', __NAMESPACE__ . '\add_body_class' );
	add_filter( 'the_content', __NAMESPACE__ . '\Gating\maybe_restrict_content' );
	add_filter( 'the_title', __NAMESPACE__ . '\Gating\maybe_add_padlock_to_title', 10, 2 );
	add_action( 'wp_head', __NAMESPACE__ . '\print_meta_tag' );

	// Admin screens and settings.
	add_filter( 'plugin_action_links_coil-web-monetization/plugin.php', __NAMESPACE__ . '\Admin\add_plugin_action_links' );
	add_filter( 'plugin_row_meta', __NAMESPACE__ . '\Admin\add_plugin_meta_link', 10, 2 );
	add_action( 'admin_menu', __NAMESPACE__ . '\Settings\register_admin_menu' );
	add_action( 'admin_init', __NAMESPACE__ . '\Settings\register_admin_content_settings' );
	add_action( 'admin_notices', __NAMESPACE__ . '\Settings\admin_welcome_notice' );
	add_action( 'admin_notices', __NAMESPACE__ . '\Settings\admin_no_payment_pointer_notice' );
	add_action( 'wp_ajax_dismiss_welcome_notice', __NAMESPACE__ . '\Settings\dismiss_welcome_notice' );
	add_action( 'init', __NAMESPACE__ . '\Settings\transfer_customizer_message_settings' );
	add_action( 'init', __NAMESPACE__ . '\Settings\transfer_customizer_appearance_settings' );

	// Term meta.
	add_action( 'edit_term', __NAMESPACE__ . '\Admin\maybe_save_term_meta', 10, 3 );
	add_action( 'create_term', __NAMESPACE__ . '\Admin\maybe_save_term_meta', 10, 3 );
	add_action( 'delete_term', __NAMESPACE__ . '\Admin\delete_term_monetization_meta' );
	add_term_edit_save_form_meta_actions();

	// Customizer settings.
	add_action( 'customize_register', __NAMESPACE__ . '\Admin\add_redirect_customizer_section' );

	// User profile settings.
	add_action( 'personal_options', __NAMESPACE__ . '\User\add_user_profile_payment_pointer_option' );
	add_action( 'personal_options_update', __NAMESPACE__ . '\User\maybe_save_user_profile_payment_pointer_option' );
	add_action( 'edit_user_profile_update', __NAMESPACE__ . '\User\maybe_save_user_profile_payment_pointer_option' );
	add_filter( 'option_coil_payment_pointer_id', __NAMESPACE__ . '\User\maybe_output_user_payment_pointer' );

	// Metaboxes.
	add_action( 'load-post.php', __NAMESPACE__ . '\Admin\load_metaboxes' );
	add_action( 'load-post-new.php', __NAMESPACE__ . '\Admin\load_metaboxes' );
	add_action( 'save_post', __NAMESPACE__ . '\Admin\maybe_save_post_metabox' );

	// Modal messaging
	add_action( 'wp_footer', __NAMESPACE__ . '\load_plugin_templates' );

	// Load order - important.
	add_action( 'init', __NAMESPACE__ . '\Gating\register_content_meta' );
	add_action( 'init', __NAMESPACE__ . '\Gating\register_term_meta' );
}

/**
 * Enqueue block frontend assets.
 *
 * @return void
 */
function load_block_frontend_assets() : void {

	if ( ! in_array( $GLOBALS['post']->post_type, get_supported_post_types(), true ) ) {
		return;
	}

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'coil-blocks',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'dist/blocks.style.build' . $suffix . '.css' ),
		[],
		PLUGIN_VERSION
	);
}

/**
 * Enqueue block editor assets.
 *
 * @return void
 */
function load_block_editor_assets() : void {

	if (
		! is_admin() ||
		! in_array( get_current_screen()->post_type, get_supported_post_types(), true )
	) {
		return;
	}

	if ( $GLOBALS['pagenow'] !== 'post.php' && $GLOBALS['pagenow'] !== 'post-new.php' ) {
		return;
	}

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'coil-editor-css',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'dist/blocks.editor.build' . $suffix . '.css' ),
		[],
		PLUGIN_VERSION
	);

	// Scripts.
	wp_enqueue_script(
		'coil-editor',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'dist/blocks.build.js' ),
		[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-plugins', 'wp-components', 'wp-edit-post', 'wp-api', 'wp-editor', 'wp-hooks', 'wp-data' ],
		PLUGIN_VERSION,
		false
	);

	// Load JS i18n, requires WP 5.0+.
	if ( ! function_exists( 'wp_set_script_translations' ) ) {
		return;
	}

	wp_set_script_translations( 'coil-editor', 'coil-web-monetization' );
}

/**
 * Enqueue required CSS/JS.
 *
 * @return void
 */
function load_full_assets() : void {

	// Only load Coil on actual content.
	if ( is_admin() || ! is_singular() || is_feed() || is_preview() ) {
		return;
	}

	if ( ! Gating\is_content_monetized( get_queried_object_id() ) ) {
		return;
	}

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'coil-monetize-css',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'assets/css/frontend/coil' . $suffix . '.css' ),
		[],
		PLUGIN_VERSION
	);

	wp_enqueue_script(
		'coil-monetization-js',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'assets/js/initialize-monetization' . $suffix . '.js' ),
		[ 'jquery', 'wp-util' ],
		PLUGIN_VERSION,
		true
	);

	$site_logo = false;
	if ( function_exists( 'get_custom_logo' ) ) {
		$site_logo = get_custom_logo();
	}

	$coil_logo = '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM22.2293 20.7672C21.8378 19.841 21.2786 19.623 20.8498 19.623C20.6964 19.623 20.5429 19.6534 20.4465 19.6725C20.4375 19.6743 20.429 19.676 20.421 19.6775C20.2663 19.7435 20.1103 19.8803 19.9176 20.0493C19.3674 20.5319 18.5178 21.277 16.5435 21.3846H16.2266C14.1759 21.3846 12.2744 20.3313 11.305 18.6423C10.8576 17.8433 10.6339 16.9534 10.6339 16.0635C10.6339 15.0283 10.9322 13.975 11.5474 13.067C12.0134 12.3587 12.9269 11.2145 14.5674 10.7242C15.3504 10.4881 16.0401 10.3973 16.6367 10.3973C18.5009 10.3973 19.3584 11.3598 19.3584 12.0681C19.3584 12.4495 19.1161 12.7582 18.65 12.8127C18.5941 12.8309 18.5568 12.8309 18.5009 12.8309C18.3331 12.8309 18.1467 12.7945 17.9976 12.7037C17.9416 12.6674 17.8671 12.6493 17.7925 12.6493C17.2146 12.6493 16.413 13.6299 16.413 14.4653C16.413 15.0828 16.8604 15.6276 18.184 15.6276C18.4049 15.6276 18.6392 15.6016 18.9094 15.5716C18.9584 15.5661 19.0086 15.5606 19.0602 15.555C20.5142 15.3552 21.7633 14.3382 22.1361 13.0125C22.192 12.849 22.248 12.5766 22.248 12.2134C22.248 11.378 21.9124 10.0886 20.2905 8.90811C19.1347 8.05455 17.8111 7.80029 16.618 7.80029C15.3877 7.80029 14.3064 8.07271 13.6912 8.27248C11.2677 9.05339 9.88822 10.4881 9.17981 11.5778C8.26635 12.9398 7.80029 14.5198 7.80029 16.0998C7.80029 17.4619 8.13585 18.8058 8.82561 20.0226C10.2983 22.6014 13.1506 24.1996 16.2266 24.1996C16.3011 24.1996 16.3804 24.195 16.4596 24.1905C16.5388 24.186 16.618 24.1814 16.6926 24.1814C18.7619 24.0725 22.3225 22.6922 22.3225 21.1667C22.3225 21.0396 22.2853 20.8943 22.2293 20.7672Z" fill="black"/></svg>';

	$coil_logo_white = '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 6.66667C0 2.98477 2.98477 0 6.66667 0H25.3333C29.0152 0 32 2.98477 32 6.66667V25.3333C32 29.0152 29.0152 32 25.3333 32H6.66667C2.98477 32 0 29.0152 0 25.3333V6.66667Z" fill="white"/><path d="M21.0116 19.7656C21.4391 19.7656 21.9967 19.9825 22.3871 20.9042C22.4428 21.0307 22.48 21.1753 22.48 21.3018C22.48 22.8199 18.9297 24.1935 16.8664 24.3019C16.7177 24.3019 16.5504 24.32 16.4017 24.32C13.3347 24.32 10.4908 22.7296 9.02234 20.1632C8.33458 18.9523 8 17.6149 8 16.2594C8 14.687 8.4647 13.1147 9.37551 11.7592C10.0818 10.6748 11.4574 9.24704 13.8738 8.4699C14.4872 8.2711 15.5653 8 16.7921 8C17.9817 8 19.3015 8.25302 20.4539 9.10246C22.0711 10.2772 22.4056 11.5604 22.4056 12.3918C22.4056 12.7532 22.3499 13.0243 22.2941 13.187C21.9224 14.5063 20.677 15.5184 19.2271 15.7172C18.8925 15.7534 18.6137 15.7895 18.3535 15.7895C17.0337 15.7895 16.5876 15.2473 16.5876 14.6328C16.5876 13.8015 17.3869 12.8255 17.9631 12.8255C18.0375 12.8255 18.1118 12.8436 18.1676 12.8797C18.3163 12.9701 18.5022 13.0062 18.6695 13.0062C18.7252 13.0062 18.7624 13.0062 18.8182 12.9882C19.2829 12.934 19.5245 12.6267 19.5245 12.2472C19.5245 11.5423 18.6695 10.5845 16.8107 10.5845C16.2159 10.5845 15.5281 10.6748 14.7474 10.9098C13.1117 11.3977 12.2009 12.5363 11.7362 13.2412C11.1228 14.1448 10.8254 15.1931 10.8254 16.2233C10.8254 17.1088 11.0484 17.9944 11.4945 18.7896C12.4611 20.4704 14.3571 21.5187 16.4017 21.5187C16.5133 21.5187 16.6062 21.5187 16.7177 21.5187C19.3758 21.3741 19.9892 20.0728 20.584 19.8198C20.677 19.8017 20.8443 19.7656 21.0116 19.7656Z" fill="#2D333A"/></svg>';

	$strings = apply_filters(
		'coil_js_ui_messages',
		[
			'content_container'       => Admin\get_global_settings( 'coil_content_container' ),
			'unable_to_verify'        => Admin\get_messaging_setting_or_default( 'coil_unable_to_verify_message' ),
			'voluntary_donation'      => Admin\get_messaging_setting_or_default( 'coil_voluntary_donation_message' ),
			'loading_content'         => Admin\get_messaging_setting_or_default( 'coil_verifying_status_message' ),
			'fully_gated'             => Admin\get_messaging_setting_or_default( 'coil_fully_gated_content_message' ),
			'partial_gating'          => Admin\get_messaging_setting_or_default( 'coil_partially_gated_content_message' ),
			'learn_more_button_text'  => Admin\get_messaging_setting_or_default( 'coil_learn_more_button_text' ),
			'learn_more_button_link'  => Admin\get_messaging_setting_or_default( 'coil_learn_more_button_link' ),
			'show_donation_bar'       => Admin\get_appearance_settings( 'coil_show_donation_bar' ),
			'post_excerpt'            => get_the_excerpt(),
			'coil_message_branding'   => Admin\get_appearance_settings( 'coil_message_branding' ),
			'site_logo'               => $site_logo,
			'coil_logo'               => $coil_logo,
			'coil_logo_white'         => $coil_logo_white,
			'exclusive_message_theme' => Admin\get_appearance_settings( 'coil_message_color_theme' ),
			'font_selection'          => Admin\get_appearance_settings( 'coil_message_font' ),

			/* translators: 1 + 2) HTML link tags (to the Coil settings page). */
			'admin_missing_id_notice' => sprintf( __( 'This post is monetized but you have not set your payment pointer ID in the %1$sCoil settings page%2$s. Only content set to show for all visitors will show.', 'coil-web-monetization' ), '<a href="' . admin_url( 'admin.php?page=coil' ) . '">', '</a>' ),
		],
		get_queried_object_id()
	);

	wp_localize_script(
		'coil-monetization-js',
		'coilParams',
		$strings
	);
}

/**
 * Enqueue messaging CSS.
 *
 * @return void
 */
function load_messaging_assets() : void {

	if ( is_admin() ) {
		return;
	}

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'coil-messaging',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'assets/css/messages/coil' . $suffix . '.css' ),
		[],
		PLUGIN_VERSION
	);

	// Only load Coil cookie message styles on singular posts.
	if ( is_home() || is_front_page() || ! is_singular() || is_feed() || is_preview() ) {
		return;
	}

	wp_enqueue_script(
		'messaging-cookies',
		esc_url_raw( plugin_dir_url( __DIR__ ) . 'assets/js/js-cookie.3.0.0.min.js' ),
		[],
		PLUGIN_VERSION,
		true
	);
}

/**
 * Load templates used by the plugin to render in javascript using
 * WP Template.
 *
 * @return void
 */
function load_plugin_templates() : void {

	require_once plugin_dir_path( __FILE__ ) . '../templates/messages/subscriber-only-message.php';
	require_once plugin_dir_path( __FILE__ ) . '../templates/messages/split-content-message.php';
	require_once plugin_dir_path( __FILE__ ) . '../templates/messages/banner-message.php';
}

/**
 * Add body class if content has enabled monetization.
 *
 * @param array $classes Initial body classes.
 *
 * @return array $classes Updated body classes.
 */
function add_body_class( $classes ) : array {

	if ( ! is_singular() ) {
		return $classes;
	}

	$payment_pointer_id = Admin\get_global_settings( 'coil_payment_pointer_id' );

	if ( Gating\is_content_monetized( get_queried_object_id() ) ) {
		$classes[] = 'monetization-not-initialized';

		$coil_status = Gating\get_content_gating( get_queried_object_id() );
		$classes[]   = sanitize_html_class( 'coil-' . $coil_status );

		if ( ! empty( $payment_pointer_id ) ) {
			$classes[] = ( Gating\get_excerpt_gating( get_queried_object_id() ) ) ? 'coil-show-excerpt' : 'coil-hide-excerpt';
		} else {
			// Error: payment pointer ID is missing.
			$classes[] = 'coil-missing-id';

			// If current user is an admin,toggle error message in wp-admin.
			if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
				$classes[] = 'coil-show-admin-notice';
			}
		}
	}

	return $classes;
}

/**
 * Print the monetisation tag to <head>.
 *
 * @return void
 */
function print_meta_tag() : void {

	$payment_pointer_id  = get_payment_pointer();
	$payment_pointer_url = $payment_pointer_id;

	// check if url starts with $
	if ( '' !== $payment_pointer_url && $payment_pointer_url[0] === '$' ) {
		// replace $ with https://
		$payment_pointer_url = str_replace( '$', 'https://', $payment_pointer_url );
		// remove trailing slash
		$payment_pointer_url = rtrim( $payment_pointer_url, '/' );
		// check if url path exists
		$parsed_url = wp_parse_url( $payment_pointer_url, PHP_URL_PATH );

		// if no url path, append /.well-known/pay
		if ( empty( $parsed_url ) ) {
			$payment_pointer_url = $payment_pointer_url . '/.well-known/pay';
		}
	}

	if ( ! empty( $payment_pointer_id ) ) {
		echo '<meta name="monetization" content="' . esc_attr( $payment_pointer_id ) . '" />' . PHP_EOL;
		echo '<link rel="monetization" href="' . esc_url( $payment_pointer_url ) . '" />' . PHP_EOL;
	}
}

/**
 * Get the filterable payment pointer meta option from the database.
 *
 * @return string
 */
function get_payment_pointer() : string {

	// Fetch the global payment pointer
	$global_payment_pointer_id = Admin\get_global_settings( 'coil_payment_pointer_id' );

	// If payment pointer is set on the user, use that instead of the global payment pointer.
	$payment_pointer_id = User\maybe_output_user_payment_pointer( $global_payment_pointer_id );

	// If the post is not set for monetising, bail out.
	if ( ! Gating\is_content_monetized( (int) get_queried_object_id() ) || empty( $payment_pointer_id ) ) {
		return '';
	}

	return $payment_pointer_id;
}

/**
 * Generate actions for every taxonomy to handle the output
 * of the gating options for the term add/edit forms.
 *
 * @return array $actions Array of WordPress actions.
 */
function add_term_edit_save_form_meta_actions() {

	$valid_taxonomies = Admin\get_valid_taxonomies();

	$actions = [];
	if ( is_array( $valid_taxonomies ) && ! empty( $valid_taxonomies ) ) {
		foreach ( $valid_taxonomies as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				$actions[] = add_action( esc_attr( $taxonomy ) . '_edit_form_fields', __NAMESPACE__ . '\Settings\coil_add_term_custom_meta', 10, 2 );
				$actions[] = add_action( esc_attr( $taxonomy ) . '_add_form_fields', __NAMESPACE__ . '\Settings\coil_edit_term_custom_meta', 10, 2 );
			}
		}
	}
	return $actions;
}

/**
 * Return post types to integrate with Coil.
 *
 * @param string $output Optional. The type of output to return. Either 'names' or 'objects'. Default 'names'.
 *
 * @return array Supported post types (if $output is 'names', then strings, otherwise WP_Post objects).
 */
function get_supported_post_types( $output = 'names' ) : array {

	$output        = ( $output === 'names' ) ? 'names' : 'objects';
	$content_types = get_post_types(
		[ 'public' => true ],
		$output
	);

	$excluded_types = [
		'attachment',
		'custom_css',
		'customize_changeset',
		'revision',
		'nav_menu_item',
		'oembed_cache',
		'user_request',
		'wp_block',
	];

	$supported_types = [];

	foreach ( $content_types as $post_type ) {
		$type_name = ( $output === 'names' ) ? $post_type : $post_type->name;

		if (
			! in_array( $type_name, $excluded_types, true ) &&
			post_type_supports( $type_name, 'custom-fields' )
		) {
			$supported_types[] = $post_type;
		}
	}

	return apply_filters( 'coil_supported_post_types', $supported_types, $output );
}


/**
 * Filter customiser settings
 *
 * See wp_filter_nohtml_kses
 *
 * @param string $message
 *
 * @return string $message
 */
function filter_customiser_settings( $message ) : string {
	$message = wp_kses( stripslashes( $message ), 'strip' );
	return $message;
}

/**
 * Show warning message to sites on old versions of PHP.
 */
function coil_show_php_warning() {

	echo '<div class="error"><p>' . esc_html__( 'Coil Web Monetization requires PHP 7.2 or newer. Please contact your web host for information on updating PHP.', 'coil-web-monetization' ) . '</p></div>';
	unset( $_GET['activate'] );
}

/**
 * Deactivate the plugin.
 */
function coil_deactive_self() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
}
