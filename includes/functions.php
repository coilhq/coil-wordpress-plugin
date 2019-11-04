<?php
declare(strict_types=1);

namespace Coil;

/**
 * Initialise and set up the plugin.
 *
 * @return void
 */
function init_plugin() : void {
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\load_block_frontend_assets' );
	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\load_block_editor_assets' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_assets' );

	add_filter( 'body_class', __NAMESPACE__ . '\add_body_class' );
	add_action( 'wp_head', __NAMESPACE__ . '\print_meta_tag' );
}

/**
 * Enqueue block frontend assets.
 *
 * @return void
 */
function load_block_frontend_assets() : void {

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

	if ( ! is_admin() ) {
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

	wp_set_script_translations( 'coil-editor', 'coil-monetize-content' );
}

/**
 * Enqueue required CSS/JS.
 *
 * @return void
 */
function load_assets() : void {

	// Only load Coil on actual content.
	if ( is_admin() || is_home() || is_front_page() || ! is_singular() || is_feed() || is_preview() ) {
		return;
	}

	$coil_status = get_post_meta( get_queried_object_id(), '_coil_monetize_post_status', true );
	if ( empty( $coil_status ) || $coil_status === 'no' ) {
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
		[ 'jquery' ],
		PLUGIN_VERSION,
		true
	);

	// TODO: can we use WP 5.0 string translation process rather than this old approach?
	$strings = apply_filters(
		'coil_js_ui_messages',
		[
			'coil_for_wp_version'         => PLUGIN_VERSION,
			'content_container'           => get_option( 'coil_content_container' ),
			'verifying_browser_extension' => esc_html__( 'This post is monetized. Please wait while we verify you are a subscriber...', 'coil-monetize-content' ),
			'verifying_coil_account'      => esc_html__( 'Verifying your Coil account. Please wait...', 'coil-monetize-content' ),
			'loading_content'             => esc_html__( 'Loading content. Please wait...', 'coil-monetize-content' ),
			'post_excerpt'                => get_the_excerpt(),

			/* translators: 1 + 2) HTML link tags (to the Coil website). */
			'browser_extension_missing'   => sprintf( __( 'You need to %1$sinstall the Coil browser extension%2$s in order to view this posts content.', 'coil-monetize-content' ), '<a href="https://help.coil.com/en/articles/2701494-supported-browsers">', '</a>' ),
			/* translators: 1 + 2) HTML link tags (to the Coil website). */
			'unable_to_verify'            => sprintf( __( 'Unable to verify your Coil account. Please %1$scheck that you are logged in%2$s to view content.', 'coil-monetize-content' ), '<a href="' . esc_url( 'https://coil.com/login' ) . '">', '</a>' ),
			/* translators: 1 + 2) HTML link tags (to the Coil website). */
			'unable_to_verify_hidden'     => sprintf( __( 'Unable to verify your Coil account. Please %1$scheck that you are logged in%2$s to view hidden content.', 'coil-monetize-content' ), '<a href="' . esc_url( 'https://coil.com/login' ) . '">', '</a>' ),

			// TODO: protect against if user not logged in,
			/* translators: 1) current user's name, 2 + 3) HTML link tags (to the Coil settings page). */
			'admin_missing_id_notice'     => sprintf( __( '%1$s, this post is monetized but you have not set your payment pointer ID in the %2$sCoil settings page%3$s. Only content set to show for all visitors will show.', 'coil-monetize-content' ), wp_get_current_user()->display_name, '<a href="' . admin_url( 'admin.php?page=coil' ) . '">', '</a>' ),
		],
		get_queried_object_id()
	);

	wp_localize_script(
		'coil-monetization',
		'coil_params',
		$strings
	);
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

	$payout_pointer_id = get_option( 'coil_payout_pointer_id' );
	$coil_status       = get_post_meta( get_queried_object_id(), '_coil_monetize_post_status', true );

	if ( ! empty( $coil_status ) && $coil_status !== 'no' ) {
		$classes[] = 'monetization-not-initialized';

		if ( ! empty( $payout_pointer_id ) ) {
			// Monetise.
			$classes[] = sanitize_html_class( 'coil-' . $coil_status );
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

	$coil_status       = get_post_meta( get_queried_object_id(), '_coil_monetize_post_status', true );
	$payout_pointer_id = get_option( 'coil_payout_pointer_id' );

	// If the post is not set for monetising, bail out.
	if ( empty( $coil_status ) || $coil_status === 'no' ) {
		return;
	}

	if ( ! empty( $payout_pointer_id ) ) {
		echo '<meta name="monetization" content="' . esc_attr( $payout_pointer_id ) . '" />' . PHP_EOL;
	}
}
