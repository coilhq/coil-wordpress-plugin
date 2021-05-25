<?php
declare(strict_types=1);
/**
 * Coil settings.
 * Creates and renders the Coil settings panel
 */

namespace Coil\Settings;

use Coil;
use Coil\Admin;
use Coil\Gating;
use const Coil\COIL__FILE__;

/* ------------------------------------------------------------------------ *
 * Menu Registration
 * ------------------------------------------------------------------------ */

/**
 * Add Coil settings to the admin navigation menu.
 *
 * @return void
 */
function register_admin_menu() : void {

	add_menu_page(
		esc_html__( 'Coil', 'coil-web-monetization' ),
		esc_html( _x( 'Coil', 'admin menu name', 'coil-web-monetization' ) ),
		apply_filters( 'coil_settings_capability', 'manage_options' ),
		'coil_settings',
		__NAMESPACE__ . '\render_coil_settings_screen',
		'data:image/svg+xml;base64,' . base64_encode( '<svg height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="m10 18c4.4183 0 8-3.5817 8-8 0-4.41828-3.5817-8-8-8-4.41828 0-8 3.58172-8 8 0 4.4183 3.58172 8 8 8zm3.1274-5.5636c-.1986-.4734-.4822-.5848-.6997-.5848-.0778 0-.1556.0156-.2045.0253-.0046.0009-.0089.0018-.0129.0026-.0785.0337-.1576.1036-.2553.19-.2791.2466-.7099.6274-1.7113.6824h-.1607c-1.03998 0-2.00434-.5383-2.49598-1.4014-.22691-.4084-.34036-.8632-.34036-1.318 0-.529.15127-1.06731.46327-1.53137.23636-.36197.69963-.94669 1.53163-1.19728.39709-.12066.74691-.16706 1.04944-.16706.9455 0 1.3804.4919 1.3804.85387 0 .1949-.1229.35268-.3593.38053-.0284.00928-.0473.00928-.0756.00928-.0851 0-.1797-.01856-.2553-.06497-.0284-.01856-.0662-.02784-.104-.02784-.2931 0-.6996.50118-.6996.92812 0 .31556.2269.594.8981.594.1121 0 .2309-.0133.3679-.02864.0249-.00279.0504-.00564.0765-.00849.7375-.10209 1.3709-.62184 1.56-1.29937.0284-.08353.0567-.22275.0567-.40837 0-.42694-.1702-1.08591-.9927-1.68919-.5862-.43621-1.2575-.56615-1.8625-.56615-.62404 0-1.1724.13922-1.4844.24131-1.22909.39909-1.92872 1.13231-2.288 1.68919-.46327.69609-.69963 1.50355-.69963 2.31103 0 .6961.17018 1.3829.52 2.0047.74691 1.318 2.19345 2.1347 3.75343 2.1347.0378 0 .078-.0023.1182-.0046s.0804-.0047.1182-.0047c1.0494-.0557 2.8552-.761 2.8552-1.5406 0-.065-.0189-.1393-.0472-.2042z" fill-rule="evenodd" fill="black"/></svg>' )
	);
}

/* ------------------------------------------------------------------------ *
 * Setting Registration
 * ------------------------------------------------------------------------ */

/**
 * Initialize the theme options page by registering the Sections,
 * Fields, and Settings.
 *
 * @return void
 */
function register_admin_content_settings() {

	// Tab 1 - Global Settings.
	register_setting(
		'coil_global_settings_group',
		'coil_global_settings_group',
		__NAMESPACE__ . '\coil_global_settings_group_validation'
	);

	// ==== Global Settings.
	add_settings_section(
		'coil_global_settings_top_section',
		__( 'Global Settings', 'coil-web-monetization' ),
		false,
		'coil_global_settings_global'
	);

	add_settings_field(
		'coil_payment_pointer',
		__( 'Payment Pointer', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_global_settings_payment_pointer_render_callback',
		'coil_global_settings_global',
		'coil_global_settings_top_section'
	);

	// ==== Advanced Config.
	add_settings_section(
		'coil_global_settings_bottom_section',
		__( 'Advanced Config', 'coil-web-monetization' ),
		'\__return_empty_string',
		'coil_global_settings_advanced'
	);

	add_settings_field(
		'coil_content_container',
		__( 'CSS Selectors', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_global_settings_advanced_config_render_callback',
		'coil_global_settings_advanced',
		'coil_global_settings_bottom_section'
	);

	// Tab 2 - Content Settings.
	register_setting(
		'coil_monetization_settings_group',
		'coil_monetization_settings_group',
		__NAMESPACE__ . '\coil_monetization_settings_validation'
	);

		// ==== Content Settings.
	add_settings_section(
		'coil_content_settings_posts_section',
		__( 'Content Settings', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_content_settings_posts_render_callback',
		'coil_content_settings_posts'
	);

	add_settings_section(
		'coil_visual_settings_section',
		false,
		false,
		'coil_visual_settings'
	);

	// ==== Padlock Settings.
	add_settings_field(
		'coil_title_padlock',
		__( 'Padlock settings', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_title_padlock_settings_render_callback',
		'coil_visual_settings',
		'coil_visual_settings_section'
	);

	// ==== Donation bar Settings.
	add_settings_field(
		'coil_show_donation_bar',
		__( 'Display support creator message', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_show_donation_bar_settings_render_callback',
		'coil_visual_settings',
		'coil_visual_settings_section'
	);

	// Tab 3 - Excerpt settings.
	register_setting(
		'coil_content_settings_excerpt_group',
		'coil_content_settings_excerpt_group',
		__NAMESPACE__ . '\coil_content_settings_excerpt_validation'
	);

	add_settings_section(
		'coil_content_settings_excerpts_section',
		false,
		__NAMESPACE__ . '\coil_content_settings_excerpts_render_callback',
		'coil_content_settings_excerpts'
	);

	// Tab 4 - Messaging settings.
	register_setting(
		'coil_messaging_settings_group',
		'coil_messaging_settings_group',
		__NAMESPACE__ . '\coil_messaging_settings_validation'
	);

	add_settings_section(
		'coil_fully_gated_content_message',
		__( 'Message replacing posts / pages set to Coil Only Access', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Partially gated content message
	add_settings_section(
		'coil_partially_gated_content_message',
		__( 'Message replacing blocks set to Only Show Coil Members', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Monetization status pending message
	add_settings_section(
		'coil_verifying_status_message',
		__( 'Pending message', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Invalid monetization message
	add_settings_section(
		'coil_unable_to_verify_message',
		__( 'Invalid Web Monetization message', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Voluntry donation message
	add_settings_section(
		'coil_voluntary_donation_message',
		__( 'Support creator message', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Learn more button text
	add_settings_section(
		'coil_learn_more_button_text',
		__( 'Learn more button text', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);

	// === Learn more button link
	add_settings_section(
		'coil_learn_more_button_link',
		__( 'Learn more button link', 'coil-web-monetization' ),
		__NAMESPACE__ . '\coil_messaging_settings_render_callback',
		'coil_messaging_settings'
	);
}

/* ------------------------------------------------------------------------ *
 * Section Validation
 * ------------------------------------------------------------------------ */

/**
 * Allow the text inputs in the global settings section to
 * be properly validated. These allow the payment pointer
 * to be saved.
 *
 * @param array $global_settings The posted text input fields.
 * @return array
 */
function coil_global_settings_group_validation( $global_settings ) : array {

	if ( ! current_user_can( apply_filters( 'coil_settings_capability', 'manage_options' ) ) ) {
		return [];
	}

	if ( isset( $global_settings['coil_content_container'] ) && empty( $global_settings['coil_content_container'] ) ) {
		$global_settings['coil_content_container'] = '.content-area .entry-content';
	}

	return array_map(
		function( $global_settings_input ) {

			return sanitize_text_field( $global_settings_input );
		},
		(array) $global_settings
	);
}

/**
 * Allow the radio button options in the posts content section
 * and the padlock and donation bar display checkboxes
 * to be properly validated
 *
 * @param array $monetization_settings The posted radio options from the content settings section and the padlock
 * and donation bar display checkboxes
 * @return array
 */
function coil_monetization_settings_validation( $monetization_settings ) : array {

	foreach ( $monetization_settings as $key => $option_value ) {

		if ( 'on' === $monetization_settings[ $key ] ) {
			$monetization_settings[ $key ] = true;
		} else {

			// If it is not a value returned for the padlock and donation bar settings then it must relate to a post type.
			$valid_choices = array_keys( Gating\get_monetization_setting_types() );

			// The default value is no-gating (Enable Earning with Public Access)
			$value = in_array( $option_value, $valid_choices, true ) ? sanitize_key( $option_value ) : 'no-gating';
		}
	}

	// Set the array keys to include 'coil_show_donation_bar' and 'coil_title_padlock'
	$coil_checkbox_settings = [ 'coil_show_donation_bar', 'coil_title_padlock' ];

	// Loop through the keys and check if they exist, if not set them to false
	foreach ( $coil_checkbox_settings as $setting_id ) {
		$monetization_settings[ $setting_id ] = isset( $_POST['coil_monetization_settings_group'][ $setting_id ] );
	}

	return $monetization_settings;
}

/**
 * Allow each "Display Excerpt" checkbox in the content setting table to be properly validated
 *
 * @param array $excerpt_content_settings The posted checkbox options from the content settings section.
 * @return array
 */
function coil_content_settings_excerpt_validation( $excerpt_content_settings ) : array {

	return array_map(
		function( $checkbox_value ) {
			return ( isset( $checkbox_value ) ) ? true : false;
		},
		(array) $excerpt_content_settings
	);
}

/**
* Allow the text inputs in the messaging settings section to
* be properly validated. These allow the customized messages
* to be saved.
*
* @param array $messaging_settings The posted text input fields.
* @return array
*/
function coil_messaging_settings_validation( $messaging_settings ) : array {

	if ( ! current_user_can( apply_filters( 'coil_settings_capability', 'manage_options' ) ) ) {
		return [];
	}

	foreach ( $messaging_settings as $key => $option_value ) {
		if ( $key === 'coil_learn_more_button_link' ) {
			$messaging_settings[ $key ] = esc_url_raw( $option_value );
		} else {
			$messaging_settings[ $key ] = ( isset( $message_value ) ) ? sanitize_text_field( $message_value ) : '';
		}
	}

	return $messaging_settings;
}

/**
 * Allow the checkbox options in the visual settings section to
 * be properly validated
 *
 * @param array $visual_settings The checkbox setting for the padlock display and the donation bar display
 * @return array
 */
function coil_visual_settings_validation( $visual_settings ) : array {
	return array_map(
		function( $checkbox_value ) {
			return ( isset( $checkbox_value ) ) ? true : false;
		},
		(array) $visual_settings
	);
}

/* ------------------------------------------------------------------------ *
 * Settings Rendering
 * ------------------------------------------------------------------------ */

/**
 * Renders the output of the help links sidebar tab.
 *
 * @return void
 */
function coil_settings_sidebar_render_callback() {

	?>
	<div class="coil settings-sidebar">
		<header>
			<svg width="22px" height="22px" viewBox="0 0 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					<g id="help-wheel" fill="#EE8249" fill-rule="nonzero">
						<path d="M11,21.5 C9.073,21.5 7.186,20.97 5.545,19.969 C5.545,19.969 5.531,19.96 5.524,19.956 C4.112,19.09 2.908,17.885 2.042,16.473 C2.038,16.467 2.031,16.455 2.031,16.455 C1.03,14.813 0.5,12.927 0.5,11 C0.5,9.073 1.03,7.187 2.031,5.545 C2.031,5.545 2.042,5.528 2.047,5.52 C3.978,2.377 7.325,0.5 11,0.5 C14.677,0.5 18.024,2.378 19.955,5.523 C19.96,5.53 19.969,5.545 19.969,5.545 C20.97,7.186 21.5,9.073 21.5,11 C21.5,12.927 20.97,14.814 19.969,16.455 C19.969,16.455 19.962,16.466 19.959,16.471 C19.092,17.886 17.886,19.093 16.47,19.96 C16.465,19.963 16.455,19.97 16.455,19.97 C14.814,20.97 12.927,21.5 11,21.5 Z M7.123,19.12 C8.328,19.697 9.658,20 11,20 C12.342,20 13.672,19.697 14.877,19.12 L11.25,15.493 C11.164,15.498 11.081,15.5 11,15.5 C10.919,15.5 10.837,15.498 10.751,15.492 L7.123,19.12 Z M3.668,16.211 C4.254,17.034 4.966,17.746 5.789,18.333 L9.063,15.06 C8.136,14.616 7.385,13.865 6.941,12.938 L3.668,16.211 Z M16.211,18.332 C17.035,17.746 17.746,17.034 18.333,16.21 L15.059,12.937 C14.615,13.863 13.864,14.614 12.938,15.058 L16.211,18.332 Z M19.12,14.877 C19.697,13.672 20,12.342 20,11 C20,9.658 19.697,8.328 19.12,7.123 L15.493,10.75 C15.498,10.836 15.5,10.919 15.5,11 C15.5,11.082 15.498,11.165 15.492,11.251 L19.12,14.877 Z M2.88,7.123 C2.303,8.328 2,9.658 2,11 C2,12.342 2.303,13.672 2.88,14.877 L6.508,11.25 C6.503,11.164 6.5,11.081 6.5,11 C6.5,10.919 6.503,10.836 6.508,10.75 L2.88,7.123 Z M11,8 C9.346,8 8,9.346 8,11 C8,12.654 9.346,14 11,14 C12.654,14 14,12.654 14,11 C14,9.346 12.654,8 11,8 Z M6.941,9.063 C7.385,8.137 8.136,7.385 9.062,6.942 L5.789,3.668 C4.965,4.254 4.254,4.966 3.668,5.789 L6.941,9.063 Z M12.938,6.941 C13.864,7.385 14.615,8.136 15.059,9.062 L18.332,5.789 C17.746,4.966 17.034,4.254 16.211,3.668 L12.938,6.941 Z M10.75,6.508 C10.836,6.503 10.919,6.5 11,6.5 C11.081,6.5 11.164,6.503 11.25,6.508 L14.878,2.881 C13.672,2.303 12.342,2 11,2 C9.658,2 8.328,2.303 7.122,2.88 L10.75,6.508 Z" id="Shape"></path>
					</g>
				</g>
			</svg>

			<h3><?php esc_html_e( 'Useful links &amp; how to guides', 'coil-web-monetization' ); ?></h3>
		</header>
		<section>
			<ul>
				<?php
				printf(
					'<li><a target="_blank" href="%1$s">%2$s</a></li>',
					esc_url( 'https://help.coil.com/docs/monetize/content/wp-overview/' ),
					esc_html__( 'How to configure the Coil plugin', 'coil-web-monetization' )
				);
				?>
				<?php
				printf(
					'<li><a target="_blank" href="%1$s">%2$s</a></li>',
					esc_url( 'https://help.coil.com/docs/monetize/content/wp-faq-troubleshooting' ),
					esc_html__( 'FAQs and Troubleshooting', 'coil-web-monetization' )
				);
				?>
				<?php
				printf(
					'<li><a target="_blank" href="%1$s">%2$s</a></li>',
					esc_url( 'https://help.coil.com/docs/general-info/intro-to-coil/' ),
					esc_html__( 'About Coil and Web Monetization', 'coil-web-monetization' )
				);
				?>
				<?php
				printf(
					'<li><a target="_blank" href="%1$s">%2$s</a></li>',
					esc_url( 'https://webmonetization.org/docs/ilp-wallets' ),
					esc_html__( 'Digital wallets and payment pointers', 'coil-web-monetization' )
				);
				?>
				<?php
				printf(
					'<li><a target="_blank" href="%1$s">%2$s</a></li>',
					esc_url( 'https://help.coil.com/docs/monetize/get-creator-account/' ),
					esc_html__( 'Get a free Coil creator account', 'coil-web-monetization' )
				);
				?>
			</ul>
		</section>
	</div>
	<?php
}

// Render the text field for the payment point in global settings.
function coil_global_settings_payment_pointer_render_callback() {

	printf(
		'<input class="%s" type="%s" name="%s" id="%s" value="%s" placeholder="%s" />',
		esc_attr( 'wide-input' ),
		esc_attr( 'text' ),
		esc_attr( 'coil_global_settings_group[coil_payment_pointer_id]' ),
		esc_attr( 'coil_payment_pointer_id' ),
		esc_attr( Admin\get_global_settings( 'coil_payment_pointer_id' ) ),
		esc_attr( '$wallet.example.com/alice' )
	);

	echo '<p class="' . esc_attr( 'description' ) . '">';

	$payment_pointer_description = esc_html__( 'Enter the payment pointer assigned by your digital wallet provider. Don\'t have a digital wallet or know your payment pointer?', 'coil-web-monetization' );
	echo $payment_pointer_description . '</p>'; // phpcs:ignore. Output already escaped.

	printf(
		'<br><a href="%s" target="%s" class="%s">%s</a>',
		esc_url( 'https://webmonetization.org/docs/ilp-wallets' ),
		esc_attr( '_blank' ),
		esc_attr( 'button button-large' ),
		esc_html__( 'Learn more about digital wallets and payment pointers', 'coil-web-monetization' )
	);

}

/**
 * Render the advanced config settings fields.
 *
 * @return void
 */
function coil_global_settings_advanced_config_render_callback() {

	printf(
		'<input class="%s" type="%s" name="%s" id="%s" value="%s" placeholder="%s" required="required"/>',
		esc_attr( 'wide-input' ),
		esc_attr( 'text' ),
		esc_attr( 'coil_global_settings_group[coil_content_container]' ),
		esc_attr( 'coil_content_container' ),
		esc_attr( Admin\get_global_settings( 'coil_content_container' ) ),
		esc_attr( '.content-area .entry-content' )
	);

	echo '<p class="description">';

	printf(
		/* translators: 1) HTML link open tag, 2) HTML link close tag, 3) HTML link open tag, 4) HTML link close tag. */
		esc_html__( 'Enter the CSS selectors used in your theme that could include gated content. Most themes use the pre-filled CSS selectors. (%1$sLearn more%2$s)', 'coil-web-monetization' ),
		sprintf( '<a href="%s" target="_blank">', esc_url( 'https://help.coil.com/docs/monetize/content/wp-faq-troubleshooting#everyoneno-one-can-see-my-monetized-content-why' ) ),
		'</a>'
	);

	echo '</p>';
}

/**
 * Renders the output of the post settings showing radio buttons
 * based on the post types available in WordPress.
 *
 * @return void
 */
function coil_content_settings_posts_render_callback() {

	$post_type_options = Coil\get_supported_post_types( 'objects' );

	// If there are post types available, output them:
	if ( ! empty( $post_type_options ) ) {

		$form_gating_settings           = Gating\get_monetization_setting_types();
		$content_settings_posts_options = Gating\get_global_posts_gating();

		?>
		<p><?php esc_html_e( 'Use the settings below to control the defaults across your whole site to determine whether earning is enabled and who can access content. You can override the defaults by configuring them against your categories and taxonomies. You can also override the defaults against individual pages and posts or even specific blocks inside of them.', 'coil-web-monetization' ); ?>
		</p>
		<table class="widefat">
			<thead>
				<th><?php esc_html_e( 'Post Type', 'coil-web-monetization' ); ?></th>
				<?php foreach ( $form_gating_settings as $setting_key => $setting_value ) : ?>
					<th class="posts_table_header">
						<?php echo esc_html( $setting_value ); ?>
					</th>
				<?php endforeach; ?>
			</thead>
			<tbody>
				<?php foreach ( $post_type_options as $post_type ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $post_type->label ); ?></th>
						<?php
						foreach ( $form_gating_settings as $setting_key => $setting_value ) :
							$input_id   = $post_type->name . '_' . $setting_key;
							$input_name = 'coil_monetization_settings_group[' . $post_type->name . ']';

							/**
							 * Specify the default checked state on the input from
							 * any settings stored in the database. If the individual
							 * input status is not set, default to the first radio
							 * option (Disable Earning)
							 */
							$checked_input = false;
							if ( $setting_key === 'no' ) {
								$checked_input = 'checked="true"';
							} elseif ( isset( $content_settings_posts_options[ $post_type->name ] ) ) {
								$checked_input = checked( $setting_key, $content_settings_posts_options[ $post_type->name ], false );
							} elseif ( 'no-gating' === $setting_key ) {
								$checked_input = 'checked="true"';
							}
							?>
							<td>
								<?php
								printf(
									'<input type="radio" name="%s" id="%s" value="%s"%s />',
									esc_attr( $input_name ),
									esc_attr( $input_id ),
									esc_attr( $setting_key ),
									$checked_input
								);
								?>
							</td>
							<?php
						endforeach;
						?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}

/**
 * Renders the output of the excerpt settings showing checkbox
 * inputs based on the post types available in WordPress.
 *
 * @return void
 */
function coil_content_settings_excerpts_render_callback() {

	$post_type_options = Coil\get_supported_post_types( 'objects' );

	// If there are post types available, output them:
	if ( ! empty( $post_type_options ) ) {

		$content_settings_excerpt_options = Gating\get_global_excerpt_settings();
		?>
		<p><?php esc_html_e( 'Use the settings below to select whether to show a short excerpt for any pages, posts, or other content types you choose to gate access to. Support for displaying an excerpt may depend on your particular theme and setup of WordPress.', 'coil-web-monetization' ); ?></p>
		<table class="widefat">
			<thead>
				<th><?php esc_html_e( 'Post Type', 'coil-web-monetization' ); ?></th>
				<th><?php esc_html_e( 'Display Excerpt', 'coil-web-monetization' ); ?></th>
			</thead>
			<tbody>
				<?php foreach ( $post_type_options as $post_type ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $post_type->label ); ?></th>
						<td>
						<?php
						$excerpt_name = 'coil_content_settings_excerpt_group[' . $post_type->name . ']';
						$excerpt_id   = $post_type->name . '_display_excerpt';

						$checked_excerpt = false;
						if ( isset( $content_settings_excerpt_options[ $post_type->name ] ) ) {
							$checked_excerpt = checked( 1, $content_settings_excerpt_options[ $post_type->name ], false );
						}
						printf(
							'<input type="checkbox" name="%s" id="%s" %s />',
							esc_attr( $excerpt_name ),
							esc_attr( $excerpt_id ),
							$checked_excerpt
						);
						?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
}

/**
 * Renders the output of a generic message customization textarea.
 *
 * @return void
 */
function coil_messaging_textbox_render_callback( $content_id ) {

	printf(
		'<textarea class="%s" name="%s" id="%s" placeholder="%s" style="%s">%s</textarea>',
		esc_attr( 'wide-input' ),
		esc_attr( 'coil_messaging_settings_group[' . $content_id . ']' ),
		esc_attr( $content_id ),
		esc_attr( Admin\get_messaging_setting( $content_id, true ) ),
		esc_attr( 'min-width: 440px' ),
		esc_attr( Admin\get_messaging_setting( $content_id ) )
	);
}

/**
 * Renders the output of the content messaging customization setting
 * @return void
 */


function coil_messaging_settings_render_callback( $args ) {

	switch ( $args['id'] ) {
		case 'coil_fully_gated_content_message':
			$helper_text = __( 'Appears for non-Coil members when an entire post / page has earning enabled with Coil Only Access.', 'coil-web-monetization' );
			break;
		case 'coil_partially_gated_content_message':
			$helper_text = __( 'Appears for non-Coil members over blocks set to Only Show Coil Members on a post / page where earning is enabled with Split Access.', 'coil-web-monetization' );
			break;
		case 'coil_verifying_status_message':
			$helper_text = __( 'Appears while the plugin checks that an active Web Monetization account is in place.', 'coil-web-monetization' );
			break;
		case 'coil_unable_to_verify_message':
			$helper_text = __( 'Appears when content is set to Enable Earning with Coil Only Access and browser setup is correct, but Web Monetization doesn\'t start. This can happen when the user doesn\'t have an active Coil account.', 'coil-web-monetization' );
			break;
		case 'coil_voluntary_donation_message':
			$helper_text = __( 'Appears for non-Coil members in a footer bar when content is set to Enable Earning with Public Access.', 'coil-web-monetization' );
			break;
		case 'coil_learn_more_button_text':
			$helper_text = __( 'Text on the "Learn more" button which is shown below the message replacing posts / pages set to Coil Only Access as well as in the support creator footer.', 'coil-web-monetization' );
			break;
		case 'coil_learn_more_button_link':
			$helper_text = __( '"Learn more" button link/URL to direct non-Coil members to Coil\'s website. Shown below the message replacing posts / pages set to Coil Only Access as well as in the support creator footer.', 'coil-web-monetization' );
			break;
		default:
			$helper_text = '';
			break;
	}

	if ( '' !== $helper_text ) {
		?>
		<p><?php echo esc_html( $helper_text ); ?></p>
		<?php
	}

	// Print <textarea> containing the setting value
	coil_messaging_textbox_render_callback( $args['id'] );
}

function coil_title_padlock_settings_render_callback() {

	/**
	 * Specify the default checked state on the input form
	 * any settings stored in the database. If the
	 * input status is not set, default to checked
	 */

	$checked_input_value = Admin\get_visual_settings( 'coil_title_padlock', true );

	printf(
		'<input type="%s" name="%s" id="%s" "%s"/>',
		esc_attr( 'checkbox' ),
		esc_attr( 'coil_monetization_settings_group[coil_title_padlock]' ),
		esc_attr( 'display_padlock_id' ),
		checked( 1, $checked_input_value, false )
	);

	printf(
		'<label for="%s">%s</label>',
		esc_attr( 'display_padlock_id' ),
		esc_html_e( 'Show padlock next to post title if the post is set to Enable Earning with Coil Only Access.', 'coil-web-monetization' )
	);
}

function coil_show_donation_bar_settings_render_callback() {

	/**
	 * Specify the default checked state on the input from
	 * any settings stored in the database. If the
	 * input status is not set, default to checked
	 */
	$checked_input_value = Admin\get_visual_settings( 'coil_show_donation_bar', true );

	printf(
		'<input type="%s" name="%s" id="%s" "%s">',
		esc_attr( 'checkbox' ),
		esc_attr( 'coil_monetization_settings_group[coil_show_donation_bar]' ),
		esc_attr( 'display_donation_bar' ),
		checked( 1, $checked_input_value, false )
	);

	printf(
		'<label for="%s">%s</label>',
		esc_attr( 'display_donation_bar' ),
		esc_html_e( 'Show the support creator message in a footer bar on posts that are set to Enable Earning with Public Access.', 'coil-web-monetization' )
	);
}

/**
 * Creates dismissable welcome notice on coil admin screen
 */
function admin_welcome_notice() {

	global $current_user;

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	if ( $screen->id !== 'toplevel_page_coil_settings' ) {
		return;
	}

	$payment_pointer_id = Admin\get_global_settings( 'coil_payment_pointer_id' );
	$notice_dismissed   = get_user_meta( $current_user->ID, 'coil-welcome-notice-dismissed', true );

	if ( $payment_pointer_id || $notice_dismissed === 'true' ) {
		return;
	}

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'global_settings';

	if ( $active_tab !== 'global_settings' ) {
		return;
	}
	?>

	<div class="notice is-dismissible coil-welcome-notice">
		<img width="48" height="48" class="coil-welcome-notice__icon" src="<?php echo esc_url( plugins_url( 'assets/images/web-mon-icon.svg', COIL__FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Coil', 'coil-web-monetization' ); ?>" />
		<div class="coil-welcome-notice__content">
			<h3><?php esc_html_e( 'Welcome to Coil Web Monetization for WordPress', 'coil-web-monetization' ); ?></h3>
			<p>
			<?php
			printf(
				/* translators: 1) HTML link open tag, 2) HTML link close tag */
				esc_html__( 'To start using Web Monetization please set up your %1$spayment pointer%2$s.', 'coil-web-monetization' ),
				sprintf( '<a href="%1$s">', esc_url( '?page=coil_settings&tab=global_settings' ) ),
				'</a>'
			);
			?>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Admin notice to ensure payment pointer has been set
 */
function admin_no_payment_pointer_notice() {

	// Only nag admins that can manage coil settings
	if ( ! current_user_can( apply_filters( 'coil_settings_capability', 'manage_options' ) ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	if ( $screen->id !== 'toplevel_page_coil_settings' ) {
		return;
	}

	$payment_pointer_id = Admin\get_global_settings( 'coil_payment_pointer_id' );

	if ( $payment_pointer_id ) {
		return;
	}

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';

	if ( $active_tab !== 'global_settings' ) {
		return;
	}
	?>

	<div style="display: none;" class="notice coil-no-payment-pointer-notice">
		<img width="48" height="48" class="coil-no-payment-pointer-notice__icon" src="<?php echo esc_url( plugins_url( 'assets/images/web-mon-icon.svg', COIL__FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Coil', 'coil-web-monetization' ); ?>" />
		<div class="coil-no-payment-pointer-notice__content">
			<h3><?php esc_html_e( 'Warning', 'coil-web-monetization' ); ?></h3>
			<p><?php esc_html_e( 'You haven\'t entered a payment pointer. A payment pointer is required to receive payments and for content gating to be recognized.', 'coil-web-monetization' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Render the Coil submenu setting screen to display options to gate posts
 * and taxonomy content types.
 *
 * @return void
 */
function render_coil_settings_screen() : void {

	?>
	<div class="wrap coil plugin-header">
		<div class="plugin-branding">
			<svg id="coil-icn-32" float="left" width="40" height="40" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM22.2293 20.7672C21.8378 19.841 21.2786 19.623 20.8498 19.623C20.6964 19.623 20.5429 19.6534 20.4465 19.6725C20.4375 19.6743 20.429 19.676 20.421 19.6775C20.2663 19.7435 20.1103 19.8803 19.9176 20.0493C19.3674 20.5319 18.5178 21.277 16.5435 21.3846H16.2266C14.1759 21.3846 12.2744 20.3313 11.305 18.6423C10.8576 17.8433 10.6339 16.9534 10.6339 16.0635C10.6339 15.0283 10.9322 13.975 11.5474 13.067C12.0134 12.3587 12.9269 11.2145 14.5674 10.7242C15.3504 10.4881 16.0401 10.3973 16.6367 10.3973C18.5009 10.3973 19.3584 11.3598 19.3584 12.0681C19.3584 12.4495 19.1161 12.7582 18.65 12.8127C18.5941 12.8309 18.5568 12.8309 18.5009 12.8309C18.3331 12.8309 18.1467 12.7945 17.9976 12.7037C17.9416 12.6674 17.8671 12.6493 17.7925 12.6493C17.2146 12.6493 16.413 13.6299 16.413 14.4653C16.413 15.0828 16.8604 15.6276 18.184 15.6276C18.4049 15.6276 18.6392 15.6016 18.9094 15.5716C18.9584 15.5661 19.0086 15.5606 19.0602 15.555C20.5142 15.3552 21.7633 14.3382 22.1361 13.0125C22.192 12.849 22.248 12.5766 22.248 12.2134C22.248 11.378 21.9124 10.0886 20.2905 8.90811C19.1347 8.05455 17.8111 7.80029 16.618 7.80029C15.3877 7.80029 14.3064 8.07271 13.6912 8.27248C11.2677 9.05339 9.88822 10.4881 9.17981 11.5778C8.26635 12.9398 7.80029 14.5198 7.80029 16.0998C7.80029 17.4619 8.13585 18.8058 8.82561 20.0226C10.2983 22.6014 13.1506 24.1996 16.2266 24.1996C16.3011 24.1996 16.3804 24.195 16.4596 24.1905C16.5388 24.186 16.618 24.1814 16.6926 24.1814C18.7619 24.0725 22.3225 22.6922 22.3225 21.1667C22.3225 21.0396 22.2853 20.8943 22.2293 20.7672Z" fill="black"/>
			</svg>
			<h3 class="plugin-branding"><?php _e( 'Coil Web Monetization', 'coil-web-monetization' ); ?></h3>
		</div>
		<?php $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'global_settings'; ?>

		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( '?page=coil_settings&tab=global_settings' ); ?>" id="coil-global-settings" class="nav-tab <?php echo $active_tab === 'global_settings' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Global Settings', 'coil-web-monetization' ); ?></a>
			<a href="<?php echo esc_url( '?page=coil_settings&tab=monetization_settings' ); ?>" id="coil-monetization-settings" class="nav-tab <?php echo $active_tab === 'monetization_settings' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Monetization Settings', 'coil-web-monetization' ); ?></a>
			<a href="<?php echo esc_url( '?page=coil_settings&tab=excerpt_settings' ); ?>" id="coil-excerpt-settings" class="nav-tab <?php echo $active_tab === 'excerpt_settings' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Excerpt Settings', 'coil-web-monetization' ); ?></a>
			<a href="<?php echo esc_url( '?page=coil_settings&tab=messaging_settings' ); ?>" id="coil-messaging-settings" class="nav-tab <?php echo $active_tab === 'messaging_settings' ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php esc_html_e( 'Messaging Settings', 'coil-web-monetization' ); ?></a>
		</h2>
	</div>
	<div class="wrap coil plugin-settings">

		<?php coil_settings_sidebar_render_callback(); ?>

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php
			switch ( $active_tab ) {
				case 'global_settings':
					settings_fields( 'coil_global_settings_group' );
					do_settings_sections( 'coil_global_settings_global' );
					do_settings_sections( 'coil_global_settings_advanced' );
					submit_button();
					break;
				case 'monetization_settings':
					settings_fields( 'coil_monetization_settings_group' );
					do_settings_sections( 'coil_content_settings_posts' );
					settings_fields( 'coil_monetization_settings_group' );
					do_settings_sections( 'coil_visual_settings' );
					submit_button();
					break;
				case 'excerpt_settings':
					settings_fields( 'coil_content_settings_excerpt_group' );
					do_settings_sections( 'coil_content_settings_excerpts' );
					submit_button();
					break;
				case 'messaging_settings':
					settings_fields( 'coil_messaging_settings_group' );
					do_settings_sections( 'coil_messaging_settings' );
					submit_button();
					break;
			}
			?>
		</form>
	</div>
	<?php
}

/**
 * Add a set of gating controls to the "Add Term" screen i.e.
 * when creating a brand new term.
 *
 * @param WP_Term_Object $term
 * @return void
 */
function coil_add_term_custom_meta( $term ) {

	// Get gating options.
	$gating_options = Gating\get_monetization_setting_types( true );
	if ( empty( $gating_options ) || ! current_user_can( apply_filters( 'coil_settings_capability', 'manage_options' ) ) ) {
		return;
	}

	// Retrieve the gating saved on the term.
	$gating = Gating\get_term_gating( $term->term_id );

	?>
	<tr class="form-field">
		<th scope="row">
			<label><?php esc_html_e( 'Coil Web Monetization', 'coil-web-monetization' ); ?></label>
		</th>
		<td>
			<fieldset id="coil-category-settings">
			<?php
			foreach ( $gating_options as $setting_key => $setting_value ) {

				$checked_input = false;
				if ( $setting_key === 'default' ) {
					$checked_input = 'checked="true"';
				} elseif ( ! empty( $gating ) ) {
					$checked_input = checked( $setting_key, $gating, false );
				}
				?>
				<label for="<?php echo esc_attr( $setting_key ); ?>">
				<?php
				printf(
					'<input type="radio" name="%s" id="%s" value="%s"%s />%s',
					esc_attr( 'coil_monetize_term_status' ),
					esc_attr( $setting_key ),
					esc_attr( $setting_key ),
					$checked_input,
					esc_attr( $setting_value )
				);
				?>
				</label><br>
				<?php
			}
			?>
			</fieldset>
		</td>
	</tr>

	<?php
	wp_nonce_field( 'coil_term_gating_nonce_action', 'term_gating_nonce' );
}

/**
 * Add a set of gating controls to the "Edit Term" screen, i.e.
 * when editing an existing term.
 *
 * @return void
 */
function coil_edit_term_custom_meta() {

	// Get gating options.
	$gating_options = Gating\get_monetization_setting_types( true );
	if ( empty( $gating_options ) || ! current_user_can( apply_filters( 'coil_settings_capability', 'manage_options' ) ) ) {
		return;
	}
	?>
	<div class="form-field">
		<h2><?php esc_html_e( 'Coil Web Monetization', 'coil-web-monetization' ); ?></h2>
		<fieldset id="coil-category-settings">
		<?php
		foreach ( $gating_options as $setting_key => $setting_value ) {
			$checked_input = false;
			if ( $setting_key === 'default' ) {
				$checked_input = 'checked="true"';
			}
			?>
			<label for="<?php echo esc_attr( $setting_key ); ?>">
			<?php
			printf(
				'<input type="radio" name="%s" id="%s" value="%s"%s />%s',
				esc_attr( 'coil_monetize_term_status' ),
				esc_attr( $setting_key ),
				esc_attr( $setting_key ),
				$checked_input,
				esc_attr( $setting_value )
			);
			?>
			</label>
			<?php
		}
		?>
		<br>
		</fieldset>
	</div>

	<?php
	wp_nonce_field( 'coil_term_gating_nonce_action', 'term_gating_nonce' );
}

function dismiss_welcome_notice() {

	global $current_user;

	// Bail early - no user set (somehow).
	if ( empty( $current_user ) ) {
		return;
	}

	// User meta stored as strings, so use 'true' to avoid data type issues.
	update_user_meta( $current_user->ID, 'coil-welcome-notice-dismissed', 'true' );
}


/**
 * Translate customizer settings
 *
 * If a user has settings which they saved in the customizer, switch them to settings saved in the wp_options table
 *
 */

function transfer_customizer_message_settings() {

	$existing_options = get_option( 'coil_messaging_settings_group' );

	if ( ! empty( $existing_options ) ) {
		return;
	}

	$messaging_settings = [];

	$coil_partial_gating_message     = 'coil_partial_gating_message';
	$coil_unsupported_message        = 'coil_unsupported_message';
	$coil_verifying_status_message   = 'coil_verifying_status_message';
	$coil_unable_to_verify_message   = 'coil_unable_to_verify_message';
	$coil_voluntary_donation_message = 'coil_voluntary_donation_message';
	$coil_learn_more_button_text     = 'coil_learn_more_button_text';
	$coil_learn_more_button_link     = 'coil_learn_more_button_link';

	if ( get_theme_mod( $coil_partial_gating_message ) ) {
		$messaging_settings['coil_partially_gated_content_message'] = get_theme_mod( $coil_partial_gating_message );
		remove_theme_mod( $coil_partial_gating_message );
	}
	if ( get_theme_mod( $coil_unsupported_message ) ) {
		$messaging_settings['coil_fully_gated_content_message'] = get_theme_mod( $coil_unsupported_message );
		remove_theme_mod( $coil_unsupported_message );
	}

	if ( get_theme_mod( $coil_verifying_status_message ) ) {
		$messaging_settings['coil_verifying_status_message'] = get_theme_mod( $coil_verifying_status_message );
		remove_theme_mod( $coil_verifying_status_message );
	}

	if ( get_theme_mod( $coil_unable_to_verify_message ) ) {
		$messaging_settings['coil_unable_to_verify_message'] = get_theme_mod( $coil_unable_to_verify_message );
		remove_theme_mod( $coil_unable_to_verify_message );
	}

	if ( get_theme_mod( $coil_voluntary_donation_message ) ) {
		$messaging_settings['coil_voluntary_donation_message'] = get_theme_mod( $coil_voluntary_donation_message );
		remove_theme_mod( $coil_voluntary_donation_message );
	}

	if ( get_theme_mod( $coil_learn_more_button_text ) ) {
		$messaging_settings['coil_learn_more_button_text'] = get_theme_mod( $coil_learn_more_button_text );
		remove_theme_mod( $coil_learn_more_button_text );
	}

	if ( get_theme_mod( $coil_learn_more_button_link ) ) {
		$messaging_settings['coil_learn_more_button_link'] = get_theme_mod( $coil_learn_more_button_link );
		remove_theme_mod( $coil_learn_more_button_link );
	}

	update_option( 'coil_messaging_settings_group', $messaging_settings );

}

function transfer_customizer_monetization_settings() {

	$existing_options = get_option( 'coil_monetization_settings_group' );

	// We've already saved or transferred this setting
	if ( ( ! get_theme_mod( 'coil_title_padlock', true ) && ! get_theme_mod( 'coil_show_donation_bar', true ) ) || ( isset( $existing_options['coil_title_padlock'] ) || isset( $existing_options['coil_show_donation_bar'] ) ) ) {
		return;
	}

	$coil_title_padlock     = 'coil_title_padlock';
	$coil_show_donation_bar = 'coil_show_donation_bar';

	$new_monetization_settings = [
		'coil_title_padlock'     => get_theme_mod( $coil_title_padlock, true ),
		'coil_show_donation_bar' => get_theme_mod( $coil_show_donation_bar, true ),
	];

	remove_theme_mod( $coil_title_padlock );
	remove_theme_mod( $coil_show_donation_bar );

	if ( false !== $existing_options ) {
		update_option( 'coil_monetization_settings_group', array_merge( $existing_options, $new_monetization_settings ) );
	} else {
		update_option( 'coil_monetization_settings_group', $new_monetization_settings );
	}
}
