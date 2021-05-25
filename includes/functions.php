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
	add_action( 'init', __NAMESPACE__ . '\Settings\transfer_customizer_monetization_settings' );

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
			'show_donation_bar'       => Admin\get_visual_settings( 'coil_show_donation_bar', true ),
			'post_excerpt'            => get_the_excerpt(),
			'site_logo'               => $site_logo,

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
